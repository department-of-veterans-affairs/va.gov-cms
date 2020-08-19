<?php

namespace CustomDrupal;

use Behat\Behat\Context\SnippetAcceptingContext;
use DevShop\Behat\DrupalExtension\Context\DevShopDrupalContext;

/**
 * FeatureContext class defines custom step definitions for Behat.
 */
class FeatureContext extends DevShopDrupalContext implements SnippetAcceptingContext {

  use \Traits\FieldTrait;
  use \Traits\UserEntityTrait;
  use \Traits\ContentTrait;
  use \Traits\GroupTrait;

  /**
   * @var array
   */
  private $privateStorage = [];

  /**
   * @var int
   *   Useful to have different timestamp even if nodes created at same time.
   */
  private $timestamp;

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
    $this->timestamp = time();
  }

  /**
   * @AfterScenario
   */
  public function cleanUp() {
    if (!empty($this->privateStorage)) {
      foreach ($this->privateStorage as $deletable) {
        $deletable->delete();
      }
    }
  }

  /**
   * Go to a view or edit page for a node.
   *
   * @param string $page
   *   Either the view or form page.
   * @param string $title
   *   The node title.
   *
   * @Then I visit the :arg1 page for a node with the title :arg2
   */
  public function iViewNode($page, $title) {
    $nid = $this->getTestContentNidByTitle($title);
    if ($nid) {
      $this->visitPath("node/$nid/$page");
    }
    else {
      throw new \Exception("Cannot locate a valid node with the title '$title'");
    }
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
   * Check that an exact match for text exists.
   *
   * @param string $string
   *   The string.
   *
   * @Then I should see exactly :string
   */
  public function iShouldSeeExactly($string) {
    $session = $this->getSession();
    $element = $session->getPage()->findAll('xpath', "//*[contains(text(), '$string')]");

    if (empty($element)) {
      throw new \InvalidArgumentException(sprintf('This string is not visible: "%s"', $string));
    }
  }

  /**
   * Check that an exact match for text does not exist.
   *
   * @param string $string
   *   The string.
   *
   * @Then I should not see exactly :string
   */
  public function iShouldNotSeeExactly($string) {
    $session = $this->getSession();
    $element = $session->getPage()->findAll('xpath', "//*[contains(text(), '$string')]");

    if ($element) {
      throw new \InvalidArgumentException(sprintf('This string is not visible: "%s"', $string));
    }
  }

  /**
   * Check that an html element does not exist.
   *
   * @param string $element
   *   The css selector.
   *
   * @Then the :element element should not exist
   */
  public function theElementShouldNotExist($element) {
    $session = $this->getSession();
    $element = $session->getPage()->find('css', $element);

    if ($element) {
      throw new \InvalidArgumentException(sprintf('This element should not appear: "%s"', $element));
    }
  }

  /**
   * Check that an html element exists.
   *
   * @param string $element
   *   The css selector.
   *
   * @Then the :element element should exist
   */
  public function theElementShouldExist($element) {
    $session = $this->getSession();
    $element = $session->getPage()->find('css', $element);

    if (NULL === $element) {
      throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $element));
    }
  }

  /**
   * Check value of html attribute.
   *
   * @param string $element
   *   The css selector.
   * @param string $attribute
   *   The attribute.
   * @param string $value
   *   The attribute value.
   *
   * @Then :element should have the :attribute with :value
   */
  public function theElementShouldHaveAttributeValue($element, $attribute, $value) {
    $session = $this->getSession();
    $element = $session->getPage()->find('css', $element);
    if (NULL === $element) {
      throw new \InvalidArgumentException(sprintf('This element is not available: "%s"', $element));
    }

    $attribute_val = $element->getAttribute($attribute);
    if (empty($attribute_val)) {
      throw new \InvalidArgumentException(sprintf('This attribute is not available: "%s"', $attribute_val));
    }

    if ($attribute_val !== $value) {
      throw new \InvalidArgumentException(sprintf('This attribute value is incorrect: "%s"', $attribute_val));
    }

  }

  /**
   * Step to publish/unpublish node by title.
   *
   * @param string $status
   *   The node publishing status.
   * @param string $title
   *   The node title.
   *
   * @Given I set the node with title :title to :status
   */
  public function setTheNodeWithTitle($status, $title) {
    if ($status === 'published') {
      $status = ['pub' => TRUE, 'mod' => 'published'];
    }
    else {
      $status = ['pub' => FALSE, 'mod' => 'archived'];
    }

    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['title' => $title]);

    if (empty($nodes) || empty(reset($nodes))) {
      throw new \InvalidArgumentException(sprintf('Node with title "%s" does not exist', $title));
    }
    $node = reset($nodes);
    $node->setPublished($status['pub']);
    $node->set('moderation_state', $status['mod']);
    $node->save();
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
      throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $element));
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

  /**
   * Check option from select with specified id|name|label|value.
   *
   * @param string $option
   *   The option selected.
   * @param string $select
   *   Input id, name or label.
   *
   * @throws Exception
   *
   * @Then the :option option from :select should be selected
   */
  public function theOptionFromShouldBeSelected($option, $select) {
    $select_field = $this->getSession()->getPage()->find('css', $select);
    if (NULL === $select_field) {
      throw new \Exception(sprintf('The select "%s" was not found in the page %s', $select, $this->getSession()->getCurrentUrl()));
    }

    $option_field = $select_field->find('xpath', "//option[@selected='selected']");
    if (NULL === $option_field) {
      throw new \Exception(sprintf('No option is selected in the %s select in the page %s', $select, $this->getSession()->getCurrentUrl()));
    }

    if ($option_field->getValue() !== $option) {
      throw new \Exception(sprintf('The option "%s" was not selected in the page %s, %s was selected', $option, $this->getSession()->getCurrentUrl(), $option_field->getValue()));
    }
  }

  /**
   * Check option from select with specified id|name|label|value.
   *
   * @param string $selector
   *   The haystack.
   * @param string $item
   *   The needle.
   *
   * @throws Exception
   *
   * @Then :selector should contain :item
   */
  public function thenSelectorShouldContain($selector, $item) {
    $session = $this->getSession();
    $select_item = $session->getPage()->find('css', $selector);
    if (empty($select_item)) {
      throw new \Exception('The selector ' . $select_item . ' does not exist.');
    }
    $vals = $select_item->getText();
    $match = preg_match("/{$item}/i", $vals);
    // DraftIn reviewStagedPublished.
    if (empty($match)) {
      throw new \Exception('Current selection value ' . $item . ' is not present.');
    }
  }

  /**
   * Check option from select with specified id|name|label|value does not exist.
   *
   * @param string $selector
   *   The haystack.
   * @param string $item
   *   The needle.
   *
   * @throws Exception
   *
   * @Then :selector should not contain :item
   */
  public function thenSelectorShouldNotContain($selector, $item) {
    $session = $this->getSession();
    $select_item = $session->getPage()->find('css', $selector);
    if (empty($select_item)) {
      throw new \Exception('The selector ' . $select_item . ' does not exist.');
    }
    $vals = $select_item->getText();
    $match = preg_match("/{$item}/i", $vals);
    // DraftIn reviewStagedPublished.
    if ($match) {
      throw new \Exception('Current selection value ' . $item . ' is present.');
    }
  }

}
