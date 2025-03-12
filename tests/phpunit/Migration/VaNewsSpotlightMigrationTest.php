<?php

namespace tests\phpunit\Migration;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\Support\Classes\VaGovExistingSiteBase;
use Tests\Support\Entity\Storage as EntityStorage;
use Tests\Support\Migration\Migrator;

/**
 * A test to confirm that the News Spotlight Migration works correctly.
 *
 * @group functional
 * @group all
 */
class VaNewsSpotlightMigrationTest extends VaGovExistingSiteBase {

  /**
   * Test the News Spotlight BLocks Migration.
   *
   * @dataProvider vaNewsSpotlightDataProvider
   */
  public function testVaNewsSpotlightMigration(
    string $migration_id,
    string $bundle,
    string $json,
    array $conditions,
    int $count,
    bool $cleanup,
  ) : void {
    // Setup mocking for migration source http fetcher.
    $response = new Response('200', ['Content-Type' => 'application/json'], $json);
    $mock = new MockHandler([new Response($response->getStatusCode(), $response->getHeader('Content-Type'), $response->getBody())]);
    // Appending an extra response here to avoid sniping by other processes.
    $mock->append($response);
    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);
    $this->container->set('http_client', $client);
    // Run the migration.
    Migrator::doImport($migration_id);
    // Asser we have successfully created the news promo block.
    $entityCount = EntityStorage::getMatchingEntityCount('block_content', $bundle, $conditions);
    $this->assertSame($count, $entityCount);

    if ($cleanup) {
      EntityStorage::deleteMatchingEntities('block_content', $bundle, $conditions);
    }
  }

  /**
   * Data provider for testVaNewsSpotlightMigration.
   *
   * @return \Generator
   *   Test assertion data.
   */
  public function vaNewsSpotlightDataProvider() : \Generator {
    yield 'Initial migration completes successfully' => [
      'news_spotlight_blocks',
      'news_promo',
      file_get_contents(__DIR__ . '/fixtures/news-va-gov-wp-json.json'),
      [
        'info' => 'TEST: Download the VA Health and Benefits App',
      ],
      1,
      TRUE,
    ];
  }

}
