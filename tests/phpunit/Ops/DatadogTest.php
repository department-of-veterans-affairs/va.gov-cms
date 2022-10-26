<?php

namespace tests\phpunit\Ops;

use PNX\Prometheus\Gauge;
use Drupal\Core\Site\Settings;
use Drupal\va_gov_backend\Service\Datadog;
use Tests\Support\Mock\HttpClient;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Test the Datadog service.
 *
 * @group functional
 * @group all
 */
class DatadogTest extends VaGovExistingSiteBase {

  /**
   * Mock client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $mockClient;

  /**
   * Settings array.
   *
   * @var array
   */
  protected $config = [];

  /**
   * Settings Service.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * The datadog service under test.
   *
   * @var \Drupal\va_gov_backend\Service\Datadog
   */
  protected $datadog;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->mockClient = HttpClient::create();
    $this->config = ['cms_datadog_api_key' => 'faketestkey'];
    $this->settings = new Settings($this->config);
    $this->datadog = new Datadog($this->mockClient, $this->settings);
  }

  /**
   * Tests that the datadog metrics api object is structured properly.
   *
   * @dataProvider datadogObjectFormattingDataProvider
   */
  public function testDatadogApiObjectFormatting($metrics_data, $validator) {
    // Build a list of gauge objects from the data provided.
    $metrics = [];
    foreach ($metrics_data as $m) {
      $metrics[] = $this->makeTestGaugeMetric($m['name'], $m['values']);
    }

    // Reflect the buildMetricsObject method so that we can call it directly.
    $bmo = new \ReflectionMethod(Datadog::class, 'buildMetricsObject');
    $bmo->setAccessible(TRUE);
    $metrics_object = $bmo->invoke($this->datadog, $metrics, "test");

    // Run the validator function provided against the metrics object returned
    // from the Datadog service.
    $validator($metrics_object);
  }

  /**
   * Provides data for the testDatadogApiObjectFormatting test.
   */
  public function datadogObjectFormattingDataProvider() {
    return [
      'Single value metric' => [
        'metrics_list' => [
          [
            'name' => 'test1',
            'values' => [
              ['value' => 12345],
            ],
          ],
        ],
        'validator' => function ($metrics_object) {
          // One unlabeled single value metric should result in a single value
          // being sent to Datadog.
          $this->assertObjectHasAttribute('series', $metrics_object);

          $this->assertEquals('gauge', $metrics_object->series[0]->type);
          $this->assertEquals('unsegmented', $metrics_object->series[0]->tags[0]);
          $this->assertEquals('env:test', $metrics_object->series[0]->tags[1]);
          $this->assertEquals('dsva_vagov.cms.test.test1_test1', $metrics_object->series[0]->metric);
          $this->assertEquals($metrics_object->series[0]->timestamp, $metrics_object->series[0]->points[0][0]);
          $this->assertEquals(12345, $metrics_object->series[0]->points[0][1]);
        },
      ],
      'Single value metric with a label' => [
        'metrics_list' => [
          [
            'name' => 'test2',
            'values' => [
              [
                'value' => 12345,
                'labels' => ["foo" => "bar"],
              ],
            ],
          ],
        ],
        'validator' => function ($metrics_object) {
          // One metric with labels should result in a single metric value being
          // sent that does *not* have the unsegmented tag.
          $this->assertObjectHasAttribute('series', $metrics_object);

          $this->assertEquals('gauge', $metrics_object->series[0]->type);
          $this->assertEquals('foo:bar', $metrics_object->series[0]->tags[0]);
          $this->assertEquals('env:test', $metrics_object->series[0]->tags[1]);
          $this->assertEquals('dsva_vagov.cms.test.test2_test2', $metrics_object->series[0]->metric);
          $this->assertEquals($metrics_object->series[0]->timestamp, $metrics_object->series[0]->points[0][0]);
          $this->assertEquals(12345, $metrics_object->series[0]->points[0][1]);
        },
      ],
      'Simple metric with labeled metric' => [
        'metrics_list' => [
          [
            'name' => 'test3',
            'values' => [
              ['value' => 12345],
              [
                'value' => 45678,
                'labels' => ["foo" => "bar"],
              ],
            ],
          ],
        ],
        'validator' => function ($metrics_object) {
          // Two values should result in two values being sent to datadog. Since
          // the second one has labels, we expect to see that converted to tags.
          $this->assertObjectHasAttribute('series', $metrics_object);

          $this->assertEquals('gauge', $metrics_object->series[0]->type);
          $this->assertEquals('unsegmented', $metrics_object->series[0]->tags[0]);
          $this->assertEquals('env:test', $metrics_object->series[0]->tags[1]);
          $this->assertEquals('dsva_vagov.cms.test.test3_test3', $metrics_object->series[0]->metric);
          $this->assertEquals(12345, $metrics_object->series[0]->points[0][1]);

          $this->assertEquals('gauge', $metrics_object->series[1]->type);
          $this->assertEquals('foo:bar', $metrics_object->series[1]->tags[0]);
          $this->assertEquals('env:test', $metrics_object->series[1]->tags[1]);
          foreach ($metrics_object->series[1]->tags as $tag) {
            $this->assertNotEquals('unsegmented', $tag);
          }
          $this->assertEquals('dsva_vagov.cms.test.test3_test3', $metrics_object->series[1]->metric);
          $this->assertEquals(45678, $metrics_object->series[1]->points[0][1]);

          // All of the timestamps in all sent metrics should be the same.
          $this->assertEquals($metrics_object->series[0]->timestamp, $metrics_object->series[0]->points[0][0]);
          $this->assertEquals($metrics_object->series[0]->timestamp, $metrics_object->series[1]->points[0][0]);
          $this->assertEquals($metrics_object->series[0]->timestamp, $metrics_object->series[1]->timestamp);
        },
      ],
      'Metrics with lots of labels' => [
        'metrics_list' => [
          [
            'name' => 'test4',
            'values' => [
              [
                'value' => 12345,
                'labels' => [
                  // If you ever need more weird placeholder words:
                  // http://www.jargon.net/jargonfile/m/metasyntacticvariable.html
                  "foo" => "bar",
                  "baz" => "fum",
                  "zot" => "qux",
                  "blarg" => "wibble",
                ],
              ],
            ],
          ],
        ],
        'validator' => function ($metrics_object) {
          $this->assertObjectHasAttribute('series', $metrics_object);

          $this->assertEquals('gauge', $metrics_object->series[0]->type);
          $this->assertEquals('foo:bar', $metrics_object->series[0]->tags[0]);
          $this->assertEquals('baz:fum', $metrics_object->series[0]->tags[1]);
          $this->assertEquals('zot:qux', $metrics_object->series[0]->tags[2]);
          $this->assertEquals('blarg:wibble', $metrics_object->series[0]->tags[3]);
          $this->assertEquals('env:test', $metrics_object->series[0]->tags[4]);
          foreach ($metrics_object->series[0]->tags as $tag) {
            $this->assertNotEquals('unsegmented', $tag);
          }
          $this->assertEquals('dsva_vagov.cms.test.test4_test4', $metrics_object->series[0]->metric);
          $this->assertEquals(12345, $metrics_object->series[0]->points[0][1]);
        },
      ],
    ];
  }

  /**
   * Helper function to create gauge metrics.
   */
  protected function makeTestGaugeMetric(string $name, array $values) {
    $m = new Gauge($name, $name, $name);
    foreach ($values as $value) {
      $m->set($value['value'], $value['labels'] ?? []);
    }
    return $m;
  }

}
