<?php

namespace CustomDrupal;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Tester\Result\TestResult;
use Drupal\DrupalExtension\Context\DrushContext;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\user\Entity\User;

/**
 * FeatureContext class defines custom step definitions for Behat.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  use \Traits\FieldTrait;
  use \Traits\UserEntityTrait;
  use \Traits\ContentTrait;
  use \Traits\GroupTrait;

  /**
   * Make DrushContext available.
   *
   * @var \Drupal\DrupalExtension\Context\DrushContext
   */
  private $drushContext;

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
   * Is the personal flag set on the content for the current user?
   *
   * @Then the :arg1 flag for node :arg2 should be set for me
   */
  public function theFlagForNodeShouldBeSetForTheRevisionEditor(string $flagName, string $title) {
    $account = User::load($this->getUserManager()->getCurrentUser()->uid);
    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById($flagName);
    if (empty($flag)) {
      throw new \InvalidArgumentException(sprintf('The flag "%s" does not exist', $flagName));
    }
    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['title' => $title]);
    if (empty($nodes) || empty(reset($nodes))) {
      throw new \InvalidArgumentException(sprintf('Node with title "%s" does not exist', $title));
    }
    $node = reset($nodes);
    if (!$flag_service->getFlagging($flag, $node, $account)) {
      throw new \InvalidArgumentException(sprintf('The flag with name "%s" was not set', $flagName));
    }
  }

  /**
   * Prepare DrushContext so we can use Drush commands easily.
   *
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    $this->drushContext = $scope->getEnvironment()->getContext(DrushContext::CLASS);
  }

  /**
   * Print watchdog logs after any failed step.
   *
   * @AfterStep
   */
  public function printWatchdogLogAfterFailedStep($event) {
    if ($event->getTestResult()->getResultCode() === TestResult::FAILED) {
      $this->drushContext->assertDrushCommand('wd-show');
      $this->drushContext->printLastDrushOutput();
    }
  }

  /**
   * Print HTML after any failed step.
   *
   * @AfterStep
   */
  public function printHtmlAfterFailedStep($event) {
    if ($event->getTestResult()->getResultCode() === TestResult::FAILED) {
      $dumpPath = 'behat_failures';
      $session = $this->getSession();
      $page = $session->getPage();
      $html = $page->getContent();
      $text = preg_replace('/\s+/u', ' ', $page->getText());
      $date = date('Y-m-d--H-i-s');
      $featureFilePath = $event->getFeature()->getFile();
      $featureFileName = basename($featureFilePath);
      if (!file_exists($dumpPath)) {
        mkdir($dumpPath);
      }
      $htmlPath = "$dumpPath/$date-$featureFileName.html";
      $textPath = "$dumpPath/$date-$featureFileName.txt";
      file_put_contents($htmlPath, $html);
      file_put_contents($textPath, $text);
      echo "\nDumped HTML to $htmlPath\nDumped Text to $textPath\n";
    }
  }

}
