<?php

namespace tests\phpunit\API;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm amount of nodes by type.
 */
class GraphQLTest extends ExistingSiteBase {

  /**
   * A test method to check data returned by GraphQL service.
   *
   * @group services
   * @group all
   *
   * @dataProvider gqlqueries
   */
  public function testGraphql($query) {

    $json_string = $this->getGraphQLData($query);
    $decodedText = html_entity_decode($json_string);
    $entities = json_decode($decodedText, TRUE)['data']['nodeQuery']['entities'];

    $this->assertGreaterThan('0', count($entities), 'No entity data was returned from request');

    foreach ($entities as $entity) {
      if (is_array($entity)) {
        $this->assertArrayHasKey('title', $entity, 'Returned GraphQL does not contain title');
        $this->assertArrayHasKey('fieldIntroText', $entity, 'Returned GraphQL does not contain fieldIntroText');
        $this->assertArrayHasKey('fieldContentBlock', $entity, 'Returned GraphQL does not contain fieldContentBlock');
      }
    }

  }

  /**
   * Retrieve GraphQL data through HTTP request.
   *
   * @return string
   *   JSON String.
   *
   * @throws exception.
   *    Request exception.
   */
  public function getGraphqlData($query) {
    $author = $this->createUser();
    $author->addRole('content_api_consumer');
    $name = $author->getAccountName();
    $author->setPassword('t3st0ma4tic');
    $author->save();

    $url = $this->baseUrl;

    try {
      $response = \Drupal::httpClient()->post($url . "/graphql?_format=json,", [
        'auth' => [$name, 't3st0ma4tic'],
        'headers' => [
          'Content-Type' => 'application/json',
        ],
        'json' => [
          'query' => $query,
        ],
      ]);

      $this->assertEquals('200', $response->getStatusCode(), 'Request returned status code ' . $response->getStatusCode());

      $json_string = $response->getBody();
      return $json_string;

    }
    catch (RequestException $e) {
      fwrite(STDERR, print_r($e, TRUE));
    }
  }

  /**
   * Returns graphql queries to be tested.
   *
   * @return array
   *   Array containing graphql queries to be tested.
   */
  public function gqlqueries() {
    return [
      [
        '{
          nodeQuery(limit: 1, filter: {conditions: [{field: "type", value: "page"}]}) {
            count
            entities {
              ... on NodePage {
                nid
                entityBundle
                entityPublished
                title
                fieldIntroText
                fieldContentBlock {
                  targetId
                  targetRevisionId
                }
              }
            }
          }
        }
        ',
      ],
    ];
  }

}
