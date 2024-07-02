<?php

namespace tests\phpunit\va_gov_live_field_migration\Migration\Status;

use Drupal\va_gov_live_field_migration\Migration\Status\Status;
use Drupal\va_gov_live_field_migration\Migration\Status\StatusInterface;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the migration status class.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_live_field_migration\Migration\Status\Status
 */
class StatusTest extends VaGovUnitTestBase {

  /**
   * Test that we can construct a status from arguments.
   *
   * @covers ::__construct
   * @covers ::getEntityType
   * @covers ::getFieldName
   * @covers ::getMigrationId
   * @covers ::getStatus
   */
  public function testConstruct() {
    $status = new Status('node', 'field_test', 'test_migration', 'test');
    $this->assertInstanceOf(Status::class, $status);
    $this->assertEquals('node', $status->getEntityType());
    $this->assertEquals('field_test', $status->getFieldName());
    $this->assertEquals('test_migration', $status->getMigrationId());
    $this->assertEquals('test', $status->getStatus());
  }

  /**
   * Test that we can construct a status from arguments, including a default.
   */
  public function testConstructWithDefault() {
    $status = new Status('node', 'field_test', 'test_migration');
    $this->assertInstanceOf(Status::class, $status);
    $this->assertEquals('node', $status->getEntityType());
    $this->assertEquals('field_test', $status->getFieldName());
    $this->assertEquals('test_migration', $status->getMigrationId());
    $this->assertEquals(StatusInterface::DEFAULT_STATUS, $status->getStatus());
  }

  /**
   * Test that we can set and get the status.
   *
   * @covers ::setStatus
   * @covers ::getStatus
   */
  public function testSetAndGetStatus() {
    $status = new Status('node', 'field_test', 'test_migration');
    $this->assertEquals(StatusInterface::DEFAULT_STATUS, $status->getStatus());
    $status->setStatus('test');
    $this->assertEquals('test', $status->getStatus());
  }

  /**
   * Test that we can get the key.
   *
   * @covers ::getKey
   */
  public function testGetKey() {
    $status = new Status('node', 'field_test', 'test_migration');
    $this->assertEquals('va_gov_live_field_migration__test_migration__node__field_test', $status->getKey());
  }

  /**
   * Test that we can serialize and deserialize the status.
   *
   * @covers ::jsonSerialize
   * @covers ::fromJson
   * @covers ::toJson
   */
  public function testSerialize() {
    $status = new Status('node', 'field_test', 'test_migration');
    $this->assertEquals('{"entityType":"node","fieldName":"field_test","migrationId":"test_migration","status":"not_started"}', $status->toJson());
    $status = Status::fromJson('{"entityType":"node","fieldName":"field_test","migrationId":"test_migration","status":"not_started"}');
    $this->assertEquals('node', $status->getEntityType());
    $this->assertEquals('field_test', $status->getFieldName());
    $this->assertEquals('test_migration', $status->getMigrationId());
    $this->assertEquals(StatusInterface::DEFAULT_STATUS, $status->getStatus());
  }

}
