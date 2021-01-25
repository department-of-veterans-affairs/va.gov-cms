<?php

namespace CustomDrupal;

use Behat\Behat\Context\SnippetAcceptingContext;
use DevShop\Behat\DrupalExtension\Context\DevShopDrupalContext;
use Drupal\Component\Utility\Crypt;

/**
 * FeatureContext class defines custom step definitions for Behat.
 */
class FeatureContext extends DevShopDrupalContext implements SnippetAcceptingContext {

  use \Traits\FieldTrait;
  use \Traits\UserEntityTrait;
  use \Traits\ContentTrait;
  use \Traits\GroupTrait;

  /**
   * Private storage.
   *
   * @var array
   */
  private $privateStorage = [];

  /**
   * Timestamp property.
   *
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
   * Clean up private storage.
   *
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
   * Go to a view or edit page for a term.
   *
   * @param string $page
   *   Either the view or edit page.
   * @param string $title
   *   The term title.
   *
   * @Then I visit the :arg1 page for a term with the title :arg2
   */
  public function iViewTerm($page, $title) {
    $tid = $this->getTestContentTidByTitle($title);
    if ($tid) {
      $this->visitPath("taxonomy/term/$tid/$page");
    }
    else {
      throw new \Exception("Cannot locate a valid term with the title '$title'");
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
   * @param string $selector
   *   The css selector.
   *
   * @Then the :selector element should exist
   */
  public function theElementShouldExist($selector) {
    $session = $this->getSession();
    $element = $session->getPage()->find('css', $selector);

    if (NULL === $element) {
      throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $selector));
    }
  }

  /**
   * Check that an XPath expression does not find any matches.
   *
   * @param string $expression
   *   The XPath expression.
   *
   * @Then the xpath :expression should have no matches
   */
  public function theXpathExpressionShouldHaveNoMatches($expression) {
    $session = $this->getSession();
    $element = $session->getPage()->find(
      'xpath',
      $session->getSelectorsHandler()->selectorToXpath('xpath', $expression)
    );
    if ($element) {
      throw new \InvalidArgumentException(sprintf('This XPath expression should have no matches: "%s"', $expression));
    }
  }

  /**
   * Check value of html attribute.
   *
   * @param string $element
   *   The css selector.
   * @param string $attribute
   *   The attribute.
   *
   * @Then :element should have the attribute :attribute
   */
  public function theElementShouldHaveAttribute($element, $attribute) {
    $session = $this->getSession();
    $element = $session->getPage()->find('css', $element);
    if (NULL === $element) {
      throw new \InvalidArgumentException(sprintf('This element is not available: "%s"', $element));
    }
    $attribute_val = $element->getAttribute($attribute);
    if (empty($attribute_val)) {
      throw new \InvalidArgumentException(sprintf('This attribute is not available: "%s"', $attribute_val));
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
   * @Then :element should have the attribute :attribute with value :value
   */
  public function theElementShouldHaveAttributeValue($element, $attribute, $value) {
    $this->theElementShouldHaveAttribute($element, $attribute);
    $session = $this->getSession();
    $element = $session->getPage()->find('css', $element);
    $attribute_val = $element->getAttribute($attribute);
    if ($attribute_val !== $value) {
      throw new \InvalidArgumentException(sprintf('This attribute value is incorrect: "%s"', $attribute_val));
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
   * @Then :element should have the attribute :attribute containing value :value
   */
  public function theElementShouldHaveAttributeContainingValue($element, $attribute, $value) {
    $this->theElementShouldHaveAttribute($element, $attribute);
    $session = $this->getSession();
    $element = $session->getPage()->find('css', $element);
    $attribute_val = $element->getAttribute($attribute);
    if (strpos($attribute_val, $value) === FALSE) {
      throw new \InvalidArgumentException(sprintf('This attribute value "%s" does not contain value "%s"', $attribute_val, $value));
    }
  }

  /**
   * Check value of html attribute.
   *
   * @param string $element
   *   The css selector.
   * @param string $attribute
   *   The attribute.
   * @param string $pattern
   *   A regular expression for matching the pattern..
   *
   * @Then :element should have the attribute :attribute matching pattern :pattern
   */
  public function theElementShouldHaveAttributeMatchingPattern($element, $attribute, $pattern) {
    $this->theElementShouldHaveAttribute($element, $attribute);
    $session = $this->getSession();
    $element = $session->getPage()->find('css', $element);
    $attribute_val = $element->getAttribute($attribute);
    if (!preg_match($pattern, $attribute_val)) {
      throw new \InvalidArgumentException(sprintf('The attribute value "%s" does not match the pattern "%s"', $attribute_val, $pattern));
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
   * This permits clicking on any XPath expression.
   *
   * @param string $expression
   *   An XPath expression describing at least one eleement.
   *
   * @When I click on the xpath :expression
   */
  public function iClickOnTheXpath($expression) {
    $session = $this->getSession();
    $element = $session->getPage()->find(
      'xpath',
      $session->getSelectorsHandler()->selectorToXpath('xpath', $expression)
    );
    if (NULL === $element) {
      throw new \InvalidArgumentException(sprintf('Cannot find XPath expression: "%s"', $expression));
    }
    $element->click();
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

  /**
   * Check that the Google Tag Manager dataLayer value is set.
   *
   * @Given the GTM data layer value for :arg1 should be set
   */
  public function googleTagManagerValueShouldBeSet($key) {
    $property_value = $this->getGoogleTagManagerValue($key);
    if (empty($property_value)) {
      throw new \Exception("The data layer value for \"{$key}\" should be set.");
    }
  }

  /**
   * Check that the Google Tag Manager dataLayer value is set correctly.
   *
   * @Given the GTM data layer value for :arg1 should be set to :arg2
   */
  public function googleTagManagerValueShouldBeSetTo($key, $value) {
    $property_value = $this->getGoogleTagManagerValue($key);
    if ($value != $property_value) {
      throw new \Exception("The data layer value for \"{$key}\" should be {$value}, but it is actually {$property_value}.");
    }
  }

  /**
   * Check that the dataLayer value is not set.
   *
   * @Given the GTM data layer value for :arg1 should be unset
   * @Given the GTM data layer value for :arg1 should not be set
   */
  public function googleTagManagerValueShouldBeUnset($key) {
    if ($this->hasGoogleTagManagerValue($key)) {
      $value = $this->getGoogleTagManagerValue($key);
      if (!empty($value)) {
        throw new \Exception("The data layer value for \"{$key}\" should not be set, but it is set to \"{$value}\".");
      }
    }
  }

  /**
   * Check that the dataLayer value is set correctly.
   *
   * @Given the GTM data layer user id should be correctly hashed
   */
  public function googleTagManagerUserIdShouldBeCorrectlyHashed() {
    $property_value = $this->getGoogleTagManagerValue('userId');
    $hashed_value = Crypt::hashBase64((string) $this->getUserManager()->getCurrentUser()->uid);
    if ($hashed_value != $property_value) {
      throw new \Exception("The userId value was \"{$property_value}\" , but it should be \"{$hashed_value}\".");
    }
  }

  /**
   * Indicate whether the dataLayer has a value for the specified key.
   *
   * @param string $key
   *   The dataLayer key.
   *
   * @return mixed
   *   Some value.
   *
   * @throws \Exception
   */
  protected function hasGoogleTagManagerValue($key) {
    $drupal_settings = $this->getDrupalSettings();
    $gtm_data = $drupal_settings['gtm_data'];
    return isset($gtm_data[$key]);
  }

  /**
   * Get Google Tag Manager dataLayer value for specified key.
   *
   * @param string $key
   *   The dataLayer key.
   *
   * @return mixed
   *   Some value.
   *
   * @throws \Exception
   */
  protected function getGoogleTagManagerValue($key) {
    $drupal_settings = $this->getDrupalSettings();
    $gtm_data = $drupal_settings['gtm_data'];
    if (isset($gtm_data[$key])) {
      return $gtm_data[$key];
    }
    throw new \Exception($key . ' not found.');
  }

  /**
   * Get Drupal Settings object.
   *
   * @return array
   *   The Drupal settings.
   */
  protected function getDrupalSettings() {
    $session = $this->getSession();
    $element = $session->getPage()->find('xpath', "//script[@data-drupal-selector='drupal-settings-json']");
    if (NULL === $element) {
      throw new \Exception('The Drupal Settings JSON could not be retrieved.');
    }
    $json = $element->getText();
    return json_decode($json, TRUE);
  }

  /**
   * Ensure workbench access sections are empty.
   *
   * @Given my workbench access sections are not set
   */
  public function myWorkbenchAccessSectionsAreNotSet() {
    $user = user_load($this->getUserManager()->getCurrentUser()->uid);
    $section_scheme = \Drupal::entityTypeManager()->getStorage('access_scheme')->load('section');
    $section_storage = \Drupal::service('workbench_access.user_section_storage');
    $current_sections = $section_storage->getUserSections($section_scheme, $user);
    if (!empty($current_sections)) {
      $section_storage->removeUser($section_scheme, $user, $current_sections);
      drupal_flush_all_caches();
    }
  }

  /**
   * Sets workbench access sections explicitly.
   *
   * @Then my workbench access sections are set to :arg1
   */
  public function myWorkbenchAccessSectionsAreSetTo($new_sections) {
    $this->myWorkbenchAccessSectionsAreNotSet();
    $user = user_load($this->getUserManager()->getCurrentUser()->uid);
    $section_scheme = \Drupal::entityTypeManager()->getStorage('access_scheme')->load('section');
    $section_storage = \Drupal::service('workbench_access.user_section_storage');
    $section_storage->addUser($section_scheme, $user, explode(',', $new_sections));
    drupal_flush_all_caches();
  }

}
