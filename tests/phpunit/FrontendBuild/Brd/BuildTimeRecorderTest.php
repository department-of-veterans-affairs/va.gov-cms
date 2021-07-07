<?php

namespace tests\phpunit\FrontendBuild\Brd;

use Drupal\node\Entity\Node;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests of the build-time recorder.
 *
 * @coversDefaultClass \Drupal\va_gov_build_trigger\Service\BuildTimeRecorder
 */
class BuildTimeRecorderTest extends ExistingSiteBase {

  /**
   * Test node ID.
   *
   * @var int
   */
  protected $nodeId;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $testNode = Node::create([
      'type' => 'landing_page',
      'title' => 'PHPUnit Test Landing Page',
      'field_page_last_built' => 1,
    ]);
    $testNode->save();
    $this->nodeId = $testNode->id();
  }

  /**
   * Test the client's request dispatch.
   *
   * @covers ::recordBuildTime
   */
  public function testRecordBuildTime() {
    $buildTimeRecorder = $this->container->get('va_gov_build_trigger.build_time_recorder');
    $buildTimeRecorder->recordBuildTime(1232);
    $testNode = Node::load($this->nodeId);
    $this->assertEquals($testNode->get('field_page_last_built')->getValue()[0]['value'], '1970-01-01T00:20:32');
  }

}
