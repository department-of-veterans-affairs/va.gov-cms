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
   * Test the News Spotlight BLocks Migrations.
   *
   * @dataProvider vaNewsSpotlightDataProvider
   */
  public function testVaNewsSpotlightMigration(
    string $migration_id,
    string $entity_type,
    string $bundle,
    string $bundle_key,
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
    // Assert we have successfully created the entity.
    $entityCount = EntityStorage::getMatchingEntityCount($entity_type, $bundle, $conditions, $bundle_key);
    $this->assertSame($count, $entityCount);
    if ($cleanup) {
      EntityStorage::deleteMatchingEntities($entity_type, $bundle, $conditions, $bundle_key);
    }
  }

  /**
   * Data provider for testVaNewsSpotlightMigration.
   *
   * @return \Generator
   *   Test assertion data.
   */
  public function vaNewsSpotlightDataProvider() : \Generator {
    yield 'Image migration completes successfully' => [
      'news_spotlight_images',
      'file',
      '',
      '',
      file_get_contents(__DIR__ . '/fixtures/news-va-gov-wp-json.json'),
      [
        'filename' => 'Test-Health-and-benefits-distro-graphics_sq.jpg',
      ],
      1,
      TRUE,
    ];
    yield 'Media migration completes successfully' => [
      'news_spotlight_media',
      'media',
      'image',
      'bundle',
      file_get_contents(__DIR__ . '/fixtures/news-va-gov-wp-json.json'),
      [
        'name' => 'Test-Health-and-benefits-distro-graphics_sq.jpg',
      ],
      1,
      TRUE,
    ];
    yield 'Block migration completes successfully' => [
      'news_spotlight_blocks',
      'block_content',
      'news_promo',
      'type',
      file_get_contents(__DIR__ . '/fixtures/news-va-gov-wp-json.json'),
      [
        'info' => 'TEST: Download the VA Health and Benefits App',
      ],
      1,
      TRUE,
    ];
  }

}
