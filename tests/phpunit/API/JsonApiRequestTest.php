<?php

namespace tests\phpunit\API;

use Drupal\paragraphs\Entity\Paragraph;
use Http\Client\Exception\RequestException;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Tests to confirm JSON:API responses match consumer expectations.
 *
 * @group functional
 * @group all
 */
class JsonApiRequestTest extends VaGovExistingSiteBase {

  /**
   * The user info to use for the tests.
   *
   * @var array
   */
  protected $userInfo;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $user = $this->createUser();
    $user->addRole('content_api_consumer');
    $user->setPassword('t3st0ma4tic');
    $user->save();

    $this->userInfo = [
      'name' => $user->getAccountName(),
      'password' => 't3st0ma4tic',
    ];

    // Load config for jsonapi_extras.jsonapi_field_type_config.
    /** @var \Drupal\Core\Config\Config $config */
    $this->configFactory = \Drupal::service('config.factory');
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown(): void {
    parent::tearDown();

    // Reset the absolute URL setting.
    $config = $this->configFactory->getEditable('jsonapi_extras.jsonapi_field_type_config');
    $config->set('resourceFields.link.enhancer.settings.absolute_url', 0);
    $config->save();
  }

  /**
   * Provides list of resource routes for the JSON:API response tests.
   */
  public function routeProvider() {
    return [
      ['/jsonapi/node/event'],
      ['/jsonapi/node/event_listing'],
      ['/jsonapi/node/news_story'],
      ['/jsonapi/node/person_profile'],
      ['/jsonapi/node/story_listing'],
    ];
  }

  /**
   * Test JSON:API responses exclude certain fields.
   *
   * @group services
   * @group all
   *
   * @dataProvider routeProvider
   */
  public function testJsonApiResponseExcludesFields($route) {
    $keysToExclude = [
      'vid',
      'revision_timestamp',
      'revision_uid',
      'revision_log',
      'uid',
      'promote',
      'sticky',
      'default_langcode',
      'revision_default',
      'revision_translation_affected',
      'menu_link',
      'content_translation_source',
      'content_translation_outdate',
    ];

    $json_string = $this->getBodyFromPath("$route?page[limit]=5", $this->userInfo);
    $collectionData = json_decode($json_string, TRUE);

    // Ensure that the collection respects the limit parameter.
    $this->assertCount(5, $collectionData['data']);

    // Assert that fields are not present in the collection response.
    array_walk_recursive($collectionData['data'],
      fn($value, $key) => $this->assertNotContains($key, $keysToExclude,
        "Key '$key' should not be present")
    );

    // Pick an ID to check from the collection.
    $nodeUuid = $collectionData['data'][1]['id'];
    $nodeResponse = $this->getBodyFromPath("$route/$nodeUuid", $this->userInfo);
    $nodeData = json_decode($nodeResponse, TRUE);

    // Assert that fields are not present in the individual node response.
    array_walk_recursive($nodeData['data'],
      fn($value, $key) => $this->assertNotContains($key, $keysToExclude,
        "Key '$key' should not be present")
    );
  }

  /**
   * Test JSON:API responses respect field type enhancers.
   *
   * @group services
   * @group all
   */
  public function testFieldEnhancerResponses() {
    // Create a paragraph of type react_widget.
    $paragraph = Paragraph::create([
      'type' => 'react_widget',
      'field_cta_widget' => FALSE,
      'field_default_link' => [
        'uri' => 'internal:/pension/application/527EZ',
        'title' => 'Apply for Veterans Pension Benefits',
        'options' => [],
      ],
      'field_error_message' => [
        'value' => '<strong>We’re sorry. Something went wrong when we tried to load your saved application.</strong><br/>Please try refreshing your browser in a few minutes.',
        'format' => 'rich_text',
        'processed' => '<strong>We’re sorry. Something went wrong when we tried to load your saved application.</strong><br>Please try refreshing your browser in a few minutes.',
      ],
      'field_loading_message' => 'Checking your application status.',
      'field_timeout' => 20,
      'field_widget_type' => 'health-care-app-status',
    ]);
    $paragraph->save();

    // Get uuid of the paragraph.
    $uuid = $paragraph->uuid();

    // Get the JSON:API response for the paragraph.
    $json_string = $this->getBodyFromPath("/jsonapi/paragraph/react_widget/$uuid", $this->userInfo);

    // Decode the JSON:API response.
    $paragraphData = json_decode($json_string, TRUE);

    // Assert that the field enhancer has been applied to the paragraph.
    // The enhancer always adds a 'url' key to the field_default_link field.
    $this->assertArrayHasKey('url', $paragraphData['data']['attributes']['field_default_link']);

    // Load config for jsonapi_extras.jsonapi_field_type_config.
    $config = $this->configFactory->getEditable('jsonapi_extras.jsonapi_field_type_config');

    // Check to see the absolute URL setting is not enabled.
    $absolute_url_enabled = $config->get('resourceFields.link.enhancer.settings.absolute_url');
    $this->assertEquals(0, $absolute_url_enabled);

    // Check that the "url" key does not have https:// or http://.
    $this->assertThat(
      $paragraphData['data']['attributes']['field_default_link']['url'],
      $this->logicalNot(
        $this->logicalOr(
          $this->stringContains('http://'),
          $this->stringContains('https://')
        )
      )
    );

    // Enable the absolute URL setting.
    $config->set('resourceFields.link.enhancer.settings.absolute_url', TRUE);
    $config->save();

    // Get the JSON:API response for the paragraph again.
    $json_string = $this->getBodyFromPath("/jsonapi/paragraph/react_widget/$uuid", $this->userInfo);

    // Decode the JSON:API response.
    $paragraphData = json_decode($json_string, TRUE);

    // Assert that the field enhancer has been applied to the paragraph.
    // The enhancer always adds a 'url' key to the field_default_link field.
    $this->assertArrayHasKey('url', $paragraphData['data']['attributes']['field_default_link']);

    // Check that the field_default_link "url" key has https:// or http://.
    $this->assertThat(
      $paragraphData['data']['attributes']['field_default_link']['url'],
      $this->logicalOr(
        $this->stringContains('http://'),
        $this->stringContains('https://')
      )
    );
  }

  /**
   * Helper function to retrieve JSON:API response from a path.
   */
  public function getBodyFromPath(string $path, array $user): string {
    $json_string = '';
    try {
      $response = \Drupal::httpClient()->get($this->baseUrl . $path, [
        'auth' => [$user['name'], $user['password']],
        'headers' => [
          'Content-Type' => 'application/json',
        ],
      ]);

      $this->assertEquals('200', $response->getStatusCode(),
        'Request returned status code ' . $response->getStatusCode());

      $json_string = $response->getBody();
    }
    catch (RequestException $e) {
      fwrite(STDERR, print_r($e->getMessage(), TRUE));
    }
    return $json_string;
  }

}
