<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
  }

  /**
   * Check that the title exists in the main menu.
   *
   * @param string $item
   *   The menu item title.
   *
   * @Then the following items should exist :item
   */
  public function theFollowingItemsShouldExist($item) {
    $links = [];
    $storage = \Drupal::entityManager()->getStorage('menu_link_content');
    $menu_links = $storage->loadByProperties(['menu_name' => 'main']);
    if (empty($menu_links)) {
      throw new \Exception('Menu is empty');
    }
    foreach ($menu_links as $mlid => $menu_link) {
      $links['menu_name'][] = $menu_link->title->value;
    }

    if (!in_array($item, $links['menu_name'])) {
      throw new \Exception('Menu link "' . $item . '" does not exist');
    }
  }

}
