<?php

namespace tests\phpunit\API;

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
   * Provides list of resource routes for the JSON:API response tests.
   */
  public function routeProvider() {
    return [
      ['/jsonapi/node/event'],
      ['/jsonapi/node/event_listing'],
      ['/jsonapi/node/news_story'],
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
      'field_last_saved_by_an_editor',
    ];

    $user = $this->createUser();
    $user->addRole('content_api_consumer');
    $user->setPassword('t3st0ma4tic');
    $user->save();

    $userInfo = [
      'name' => $user->getAccountName(),
      'password' => 't3st0ma4tic',
    ];
    $json_string = $this->getBodyFromPath("$route?page[limit]=5", $userInfo);
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
    $nodeResponse = $this->getBodyFromPath("$route/$nodeUuid", $userInfo);
    $nodeData = json_decode($nodeResponse, TRUE);

    // Assert that fields are not present in the individual node response.
    array_walk_recursive($nodeData['data'],
      fn($value, $key) => $this->assertNotContains($key, $keysToExclude,
        "Key '$key' should not be present")
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
