<?php

namespace Tests\Support\Classes;

use Prophecy\PhpUnit\ProphecyTrait;
use Tests\Support\Traits\FileLoggerTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Common functionality for ExistingSiteBase derivative test classes.
 */
abstract class VaGovExistingSiteBase extends ExistingSiteBase {

  use FileLoggerTrait;
  use ProphecyTrait;

  /**
   * Executed before each test case.
   */
  public function setUp() : void {
    parent::setUp();
    $timestamp = time();
    $date = gmdate(DATE_RFC2822);
    $testString = $this->toString();
    $message = "VA_GOV_DEBUG $timestamp $date BEFORE $testString";
    $this->writeLogMessage($message);
    // Disable failures on Watchdog messages.
    // This is necessary since we're running at the same time as GraphQL,
    // which can cause errors unrelated to the tests we're performing.
    $this->failOnPhpWatchdogMessages = FALSE;
  }

  /**
   * Executed after each test case.
   */
  public function tearDown() : void {
    parent::tearDown();
    $timestamp = time();
    $date = gmdate(DATE_RFC2822);
    $testString = $this->toString();
    $message = "VA_GOV_DEBUG $timestamp $date AFTER $testString";
    $this->writeLogMessage($message);
  }

  /**
   * Get an arbitrary node of the given type.
   *
   * The specifics of the node shouldn't matter, so we just grab the first one.
   *
   * Don't use this if you're going to change the node.
   *
   * @param string $type
   *   The content type to get.
   *
   * @return \Drupal\va_gov_content_types\Entity\VaNodeInterface
   *   The node of the given type.
   */
  public function getArbitraryNodeOfType(string $type) {
    $entityTypeManager = \Drupal::entityTypeManager();
    $nodeStorage = $entityTypeManager->getStorage('node');
    $nids = $nodeStorage->getQuery()
      ->condition('type', $type)
      ->execute();
    $firstNid = reset($nids);
    $node = $nodeStorage->load($firstNid);
    return $node;
  }

}
