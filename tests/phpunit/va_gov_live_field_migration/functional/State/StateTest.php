<?php

namespace tests\phpunit\va_gov_live_field_migration\functional\State;

use Drupal\va_gov_live_field_migration\Exception\StatusNotFoundException;
use Drupal\va_gov_live_field_migration\Migration\Status\Status;
use Drupal\va_gov_live_field_migration\Migration\Status\StatusInterface;
use Drupal\va_gov_live_field_migration\State\State;
use Drupal\va_gov_live_field_migration\State\StateInterface;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of this service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_live_field_migration\State\State
 */
class StateTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $state = \Drupal::service('va_gov_live_field_migration.state');
    $this->assertInstanceOf(State::class, $state);
    $this->assertInstanceOf(StateInterface::class, $state);
  }

  /**
   * Test that the service throws an exception when the status is not found.
   */
  public function testStatusNotFoundException() {
    $this->expectException(StatusNotFoundException::class);
    $state = \Drupal::service('va_gov_live_field_migration.state');
    $state->deleteStatus('test_migration', 'node', 'field_test');
    $state->getStatus('test_migration', 'node', 'field_test');
  }

  /**
   * Test that the service can set and get status.
   *
   * @covers ::setStatus
   * @covers ::getStatus
   */
  public function testSetAndGetStatus() {
    $state = \Drupal::service('va_gov_live_field_migration.state');
    $status = new Status('node', 'field_test', 'test_migration', 'not_started');
    $this->assertEquals('test_migration', $status->getMigrationId());
    $this->assertEquals('node', $status->getEntityType());
    $this->assertEquals('field_test', $status->getFieldName());
    $this->assertEquals(StatusInterface::DEFAULT_STATUS, $status->getStatus());
    $this->assertEquals('{"entityType":"node","fieldName":"field_test","migrationId":"test_migration","status":"not_started"}', $status->toJson());
    $status->setStatus('started');
    $state->setStatus($status);
    $status = $state->getStatus('test_migration', 'node', 'field_test');
    $this->assertEquals('started', $status->getStatus());
  }

}
