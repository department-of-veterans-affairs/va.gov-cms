<?php

namespace tests\phpunit\API;

use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * A test to confirm amount of nodes by type.
 *
 * @group functional
 * @group all
 */
class JsonApiRequestTest extends VaGovExistingSiteBase
{

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

    $userInfo = ['name' => $user->getAccountName(), 'password' => 't3st0ma4tic'];
    $json_string = $this->getBodyFromURL( "$route?page[limit]=5", $userInfo);

    // Decode JSON response.
    $collectionData = json_decode($json_string, true);

    // Ensure that the collection contains items.
    $this->assertNotEmpty($collectionData);

    // Ensure that the collection contains five items.
    $this->assertCount(5, $collectionData['data']);

    // Ensure that the collection does not contain a 'uid' field.
    $this->assertArrayNotHasKey('uid', $collectionData['data'][1]['relationships']);

    // Assert that fields are not present in the collection response.
    array_walk_recursive($collectionData['data'],
      fn($value, $key) => $this->assertNotContains($key, $keysToExclude, "Key '$key' should not be present")
    );

    // Pick an ID from the collection.
    $nodeUuid = $collectionData['data'][1]['id']; // Assuming the first item is always present.

    // Request the individual node using the picked ID.
    $nodeResponse = $this->getBodyFromURL( "$route/$nodeUuid", $userInfo);

    // Decode JSON response for the individual node.
    $nodeData = json_decode($nodeResponse, true);

    // Assert that fields are not present in the individual node response.
    array_walk_recursive($nodeData['data'],
      fn($value, $key) => $this->assertNotContains($key, $keysToExclude, "Key '$key' should not be present")
    );
  }

  /**
   * @param string $path
   * @param string $name
   *
   * @return \Psr\Http\Message\StreamInterface
   */
  public function getBodyFromURL(string $path, array $user): \Psr\Http\Message\StreamInterface
  {
    try {
      $response = \Drupal::httpClient()->get($this->baseUrl . $path, [
        'auth' => [$user['name'], $user['password']],
        'headers' => [
          'Content-Type' => 'application/json',
        ],
      ]);

      $this->assertEquals('200', $response->getStatusCode(), 'Request returned status code ' . $response->getStatusCode());

      $json_string = $response->getBody();
    } catch (RequestException $e) {
      fwrite(STDERR, print_r($e, TRUE));
    }
    return $json_string;
  }
}
