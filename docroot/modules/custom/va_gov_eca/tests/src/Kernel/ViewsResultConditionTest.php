<?php declare(strict_types = 1);

namespace Drupal\Tests\va_gov_eca\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\views\Entity\View;
use Drupal\views\Tests\ViewTestData;

/**
 * Test description.
 *
 * @group va_gov_eca
 */
final class ViewsResultConditionTest extends KernelTestBase {

  use ContentTypeCreationTrait;
  use NodeCreationTrait;

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
    'va_gov_eca_views_tests',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig(static::$modules);
    ViewTestData::createTestViews(ViewsResultConditionTest::class, ['va_gov_eca_views_tests']);

    $this->conditionManager = \Drupal::service('plugin.manager.eca.condition');

    // Create a new content type, 'default'.
    $this->createContentType(['type' => 'default']);
  }

  /**
   * Test the condition when a View contains no results.
   *
   * @return void
   */
  public function testViewsResultConditionWithNoResult(): void {
    /** @var \Drupal\views\Entity\View $view */
    $view = View::load('test_default');
    $result = views_get_view_result($view->id(), 'eca_result_1', []);

    self::assertTrue(count($result) === 0, "The condition is TRUE");
  }

//  /**
//   * Test the condition when a View contains results.
//   *
//   * @return void
//   */
//  public function testViewsResultConditionWithResult(): void {
//    self::assertTrue(TRUE, "The condition is FALSE");
//  }
//
//  /**
//   * Test the condition when no View exists.
//   *
//   * @return void
//   */
//  public function testViewsResultConditionWithNoView(): void {
//    self::assertTrue(TRUE, "The condition is FALSE");
//  }

}
