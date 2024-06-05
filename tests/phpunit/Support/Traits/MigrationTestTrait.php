<?php

namespace Tests\Support\Traits;

use Tests\Support\Entity\Storage as EntityStorage;
use Tests\Support\Migration\Migrator;
use Tests\Support\Mock\HttpClient as MockHttpClient;

/**
 * Methods for supporting testing of migrations.
 *
 * This trait is meant to be used only by test classes.
 */
trait MigrationTestTrait {

  /**
   * Performs a test migration using mocked json calls.
   *
   * @param string $migration_id
   *   The migration id.
   * @param string $bundle
   *   The entity bundle to migrate into.
   * @param string $json
   *   The json data used for mocked http calls.
   * @param array $conditions
   *   Entity query conditions used to retrieve migrated content.
   * @param int $count
   *   The expected number of migrated items.
   * @param bool $cleanup
   *   Whether to clean up entities after performing migration.
   */
  public function testMockedJsonDataFetchMigration(
    string $migration_id,
    string $bundle,
    string $json,
    array $conditions,
    int $count,
    bool $cleanup
  ) : void {
    $mockClient = MockHttpClient::create('200', ['Content-Type' => 'application/json;charset=UTF-8'], $json);
    $this->container->set('http_client', $mockClient);
    // Each url defined in the migration source configuration will make a fresh
    // call to the mockClient. Guzzle pops each new request off the request
    // queue, so if there is no parity between the number of urls in the source
    // config, and the number of requests expected (queued), an
    // OutOfBoundsException exception with the message 'Mock queue is empty'
    // will be thrown, but is visible only in the Drupal log, not in test result
    // output. We avoid this by ensuring there is only one url in the source
    // config. Since there will be no actual http requests made, we can set the
    // url to anything.
    $source_config_overrides = ['urls' => 'https://example.com/any/url/will/do'];
    Migrator::doImport($migration_id, $source_config_overrides);
    $entityCount = EntityStorage::getMatchingEntityCount('node', $bundle, $conditions);
    $this->assertSame($count, $entityCount);

    if ($cleanup) {
      EntityStorage::deleteMatchingEntities('node', $bundle, $conditions);
    }
  }

}
