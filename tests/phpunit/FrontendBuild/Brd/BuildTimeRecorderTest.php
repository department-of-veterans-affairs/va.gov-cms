<?php

namespace tests\phpunit\FrontendBuild\Brd;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests of the build-time recorder.
 *
 * @coversDefaultClass \Drupal\va_gov_build_trigger\Service\BuildTimeRecorder
 */
class BuildTimeRecorderTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'advancedqueue',
    'datetime',
    'field',
    'node',
    'system',
    'user',
    'va_gov_build_trigger',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    static::installEntitySchema('node');
    \Drupal::moduleHandler()
      ->loadInclude('node', 'install');
    NodeType::create([
      'type' => 'build_time_recorder_test_node',
      'label' => 'Build Time Recorder Test Node',
    ])
      ->save();
    FieldStorageConfig::create([
      'field_name' => 'field_page_last_built',
      'entity_type' => 'node',
      'type' => 'datetime',
      'settings' => [
        'datetime_type' => 'datetime',
      ],
      'cardinality' => 1,
    ])
      ->save();
    FieldConfig::create([
      'field_name' => 'field_page_last_built',
      'entity_type' => 'node',
      'bundle' => 'build_time_recorder_test_node',
      'label' => 'Page Last Built',
    ])
      ->save();
    static::createUser([], [
      'access content',
      'administer site configuration',
    ]);
    $this->node1 = Node::create([
      'type' => 'build_time_recorder_test_node',
      'title' => '1',
      'field_page_last_built' => 1,
    ])
      ->save();
  }

  /**
   * Test the client's request dispatch.
   *
   * @covers ::recordBuildTime
   */
  public function testRecordBuildTime() {
    $buildTimeRecorder = $this->container->get('va_gov_build_trigger.build_time_recorder');
    $buildTimeRecorder->recordBuildTime(1232);
    $node1 = Node::load(1);
    $this->assertEquals($node1->get('field_page_last_built')->getValue()[0]['value'], '1970-01-01T00:20:32');
  }

}
