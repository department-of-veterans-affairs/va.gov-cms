<?php

namespace tests\phpunit\Security;

use GuzzleHttp\Exception\ClientException;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to check access to graphql data.
 */
class GraphQLTest extends ExistingSiteBase {

  /**
   * A test method to check access to GraphQL service.
   *
   * @group security
   * @group all
   *
   * @dataProvider gqlqueries
   */
  public function testGraphqlAccess($query) {

    $url = $this->baseUrl;

    try {
      $response = \Drupal::httpClient()->post($url . "/graphql?_format=json,", [
        'headers' => [
          'Content-Type' => 'application/json',
        ],
        'json' => [
          'query' => $query,
        ],
      ]);

      $response->getStatusCode();

    }
    catch (ClientException $e) {
      $this->assertEquals(401, $e->getCode(), 'Graphql insecure request returned status code ' . $e->getCode());
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
