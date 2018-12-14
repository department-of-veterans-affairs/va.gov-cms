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
   * @param string $menuname
   *   The menu name.
   *
   * @Then the following items should exist :item in :menuname menu
   */
  public function theFollowingItemsShouldExist($item, $menuname) {
    $links = [];
    $storage = \Drupal::entityManager()->getStorage('menu_link_content');
    $menu_links = $storage->loadByProperties(['menu_name' => $menuname]);
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

  /**
   * This permits click on non-link, non-button text.
   *
   * @param string $text
   *   The text to click on.
   *
   * @When I click on the text :text
   */
  public function iClickOnTheText($text) {
    $session = $this->getSession();
    $element = $session->getPage()->find(
      'xpath',
      $session->getSelectorsHandler()->selectorToXpath('xpath', '*//*[text()="' . $text . '"]')
    );
    if (NULL === $element) {
      throw new \InvalidArgumentException(sprintf('Cannot find text: "%s"', $text));
    }

    $element->click();
  }

  /**
   * Click on the element with the provided css selector.
   *
   * @When I click on the element with selector :element
   */
  public function iClickOnTheElementWithSelector($element) {
    $session = $this->getSession();
    $element = $session->getPage()->find('css', $element);

    if (NULL === $element) {
      throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $xpath));
    }

    $element->click();
  }

  /**
   * Fills in field (input, textarea, select) with specified locator.
   *
   * @param string $locator
   *   Input id, name or label.
   * @param string $value
   *   The text for the field.
   *
   * @throws ElementNotFoundException
   *
   * @When I fill in :locator with the text :value
   */
  public function whenIfillInWith($locator, $value) {
    $session = $this->getSession();
    $element = $session->getPage()->find('css', $locator);

    if (NULL === $element) {
      throw new \InvalidArgumentException(sprintf('Could not evaluate element: "%s"', $locator));
    }
    $session->getPage()->find('css', $locator)->setValue($value);

  }

}
