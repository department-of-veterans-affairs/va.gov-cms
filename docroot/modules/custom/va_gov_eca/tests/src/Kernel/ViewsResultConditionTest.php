<?php

declare(strict_types = 1);

namespace Drupal\Tests\va_gov_eca\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\va_gov_eca\Plugin\ECA\Condition\ViewsResultCondition;
use Drupal\views\Entity\View;
use Drupal\views\Tests\ViewTestData;

/**
 * Kernel tests for the "views_result" condition plugin.
 *
 * @group va_gov_eca
 */
final class ViewsResultConditionTest extends KernelTestBase {

  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  /**
   * Views to be enabled.
   *
   * @var string[]
   */
  public static $testViews = ['va_gov_eca_default_content'];

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'node',
    'field',
    'text',
    'filter',
    'views',
    'eca',
    'eca_base',
    'va_gov_eca',
    'va_gov_eca_test_views',
  ];

  /**
   * The active Views Result Condition plugin.
   *
   * @var \Drupal\va_gov_eca\Plugin\ECA\Condition\ViewsResultCondition
   */
  private ViewsResultCondition $condition;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig(ViewsResultConditionTest::$modules);
    $this->conditionManager = \Drupal::service('plugin.manager.eca.condition');

    // Create a new content type, 'default'.
    $this->createContentType(['type' => 'default']);

    // Create View from config.
    ViewTestData::createTestViews(ViewsResultConditionTest::class, ['va_gov_eca_test_views']);
    $view = View::load('va_gov_eca_default_content');

    // Create our Condition plugin instance.
    $config = [
      'view_name' => $view->id(),
      'display_name' => 'eca_result_1',
      'arguments' => [],
    ];
    /** @var \Drupal\va_gov_eca\Plugin\ECA\Condition\ViewsResultCondition $condition */
    $this->condition = $this->conditionManager->createInstance('views_result', $config);
  }

  /**
   * Test the condition when a View contains no results.
   */
  public function testViewsResultConditionWithNoResult(): void {
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Test the condition when a View contains results.
   */
  public function testViewsResultConditionWithResult(): void {
    // Create a Default node.
    $this->createNode(['type' => 'default']);
    $this->assertTrue($this->condition->evaluate());
  }

}
