<?php

namespace CustomDrupal;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use DevShop\Behat\DrupalExtension\Context\DevShopDrupalContext;
use Drupal\file\Entity\File;
use Drupal\node\Entity\NodeType;
use Drupal\node\Entity\Node;
use PHPUnit\Framework\Assert;

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
   * @AfterScenario
   */
  public function cleanUpGroups() {
    $this->cleanGroups();
    $this->cleanCurrentGroup();
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
    $session = $this->getSession();
    $select_element = $session->getPage()->find('css', $select);
    if (empty($select_element)) {
      throw new \Exception('The select field ' . $select_element . ' does not exist.');
    }
    $option_field_val = $select_element->getText();
    if ($option !== $option_field_val) {
      throw new \Exception('Current selection value is ' . $option_field_val . '. The option ' . $option . ' is not selected.');
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
   * @Given the :arg1 content type exists
   */
  public function contentTypeExists($string) {
    $node_type = NodeType::load($string);
    if (empty($node_type)) {
      throw new \Exception('Content type ' . $string . ' does not exist.');
    }
  }

  /**
   * @Then delete node :arg1.
   */
  public function nodeDelete($node_title) {
    $nid = $this->getTestContentNidByTitle($node_title);
    $this->deleteNode($nid);
  }

  /**
   * @Then the field :arg1 is present for the :arg2 content type
   */
  public function isNodeField($field_name, $node_type) {
    $this->isField($field_name, 'node', $node_type);
  }

  /**
   * @Then the field :arg1 is present on the :arg2 paragraph
   */
  public function isParagraphField($field_name, $paragraph_type) {
    $this->isField($field_name, 'paragraph', $paragraph_type);
  }

  /**
   * @Then the :arg1 field should be required for :arg2 content
   */
  public function checkIsRequiredField($field_name, $node_type) {
    $this->isRequiredField($field_name, $node_type);
  }

  /**
   * @Then the :arg1 field should not be required for :arg2 content
   */
  public function checkIsNotRequiredField($field_name, $node_type) {
    $this->isNotRequiredField($field_name, $node_type);
  }

  /**
   * @Then users with the :arg1 role should be able to create :arg2 content
   */
  public function checkRoleCanCreateContent($role, $node_type) {
    $this->roleCanCreateContent($role, $node_type);
  }

  /**
   * @Then the :arg1 field on :arg2 content should allow references to :arg3 content/vocabulary (terms)
   */
  public function checkFieldAllowsEntityReferences($field_name, $node_type, $reference_bundles) {
    $reference_bundles = explode(',', $reference_bundles);
    $this->fieldAllowsEntityReferences($field_name, $node_type, $reference_bundles);
  }

  /**
   * @Given the :arg1 module exists
   */
  public function moduleExists($module) {
    if (!\Drupal::moduleHandler()->moduleExists($module)) {
      throw new \Exception($module . ' does not exist or is not enabled.');
    }
  }

  /**
   * @Then :arg1 has permission to :arg2
   */
  public function checkRolePermissions($role, $permission) {
    $this->roleHasPermission($role, $permission);
  }

  /**
   * @Then :arg1 does not have permission to :arg2
   */
  public function checkRoleDoesNotHavePermission($role, $node_type) {
    $permission = 'create ' . $node_type . ' content';
    $this->roleDoesNotHavePermission($role, $permission);
  }

  /**
   * @Given the following roles have content permissions for the :arg1 content type:
   */
  public function roleContentPermissions($node_type, TableNode $rolesTable) {
    foreach ($rolesTable as $rolePermission) {
      $role = $rolePermission['role'];
      $permission = $rolePermission['permission'] . ' ' . $node_type . ' content';
      $this->roleHasPermission($role, $permission);
    }
  }

  /**
   * @Given that only the following roles have content permissions for the :arg1 content type:
   */
  public function roleOnlyContentPermissions($node_type, TableNode $rolesTable) {
    $allowed_roles = array();
    foreach ($rolesTable as $rolePermission) {
      $role = $rolePermission['role'];
      $permission = $rolePermission['permission'] . ' ' . $node_type . ' content';
      $this->roleHasPermission($role, $permission);
      $allowed_roles[] = $role;
    }
    $allowed_roles[] = 'administrator';

    $all_roles = $this->getRoles();
    foreach ($all_roles as $role) {
      if (!in_array($role, $allowed_roles)) {
        $this->roleDoesNotHavePermission($role, $permission);
      }
    }
  }

  /**
   * @Given that only the following roles have the following permissions
   */
  public function roleOnlyPermissions(TableNode $rolesTable) {
    $allowed_roles = array();
    foreach ($rolesTable as $rolePermission) {
      $role = $rolePermission['role'];
      $permission = $rolePermission['permission'];
      $this->roleHasPermission($role, $permission);
      $allowed_roles[] = $role;
    }
    $allowed_roles[] = 'administrator';

    $all_roles = $this->getRoles();
    foreach ($all_roles as $role) {
      if (!in_array($role, $allowed_roles)) {
        $this->roleDoesNotHavePermission($role, $permission);
      }
    }
  }

  /**
   * @Given that only the following roles have revision permissions for the :arg1 content type:
   */
  public function roleOnlyRevisionPermissions($node_type, TableNode $rolesTable) {
    $allowed_roles = array();
    foreach ($rolesTable as $rolePermission) {
      $role = $rolePermission['role'];
      $permission = $rolePermission['permission'] . ' ' . $node_type . ' revisions';
      $this->roleHasPermission($role, $permission);
      $allowed_roles[] = $role;
    }
    $allowed_roles[] = 'administrator';

    $all_roles = $this->getRoles();
    foreach ($all_roles as $role) {
      if (!in_array($role, $allowed_roles)) {
        $this->roleDoesNotHavePermission($role, $permission);
      }
    }
  }

  /**
   * @Given that only the following roles have permissions for the :arg1 scheduled update type:
   */
  public function roleOnlyScheduledPermissions($scheduled_type, TableNode $rolesTable) {
    $allowed_roles = array();
    foreach ($rolesTable as $rolePermission) {
      $role = $rolePermission['role'];
      $permission = $rolePermission['permission'] . ' ' . $scheduled_type . ' scheduled updates';
      $this->roleHasPermission($role, $permission);
      $allowed_roles[] = $role;
    }
    $allowed_roles[] = 'administrator';

    $all_roles = $this->getRoles();
    foreach ($all_roles as $role) {
      if (!in_array($role, $allowed_roles)) {
        $this->roleDoesNotHavePermission($role, $permission);
      }
    }
  }

  /**
   * @Given the following roles do not have content permissions for the :arg1 content type:
   */
  public function roleContentNotPermissions($node_type, TableNode $rolesTable) {
    foreach ($rolesTable as $rolePermission) {
      $role = $rolePermission['role'];
      $permission = $rolePermission['permission'] . ' ' . $node_type . ' content';
      $this->roleDoesNotHavePermission($role, $permission);
    }
  }

  /**
   * @Given the following roles do not have revision permissions for the :arg1 content type:
   */
  public function theFollowingRolesDoNotHaveRevisionPermissionsForTheContentType($node_type, TableNode $rolesTable) {
    foreach ($rolesTable as $rolePermission) {
      $role = $rolePermission['role'];
      $permission = $rolePermission['permission'] . ' ' . $node_type . ' revisions';
      $this->roleDoesNotHavePermission($role, $permission);
    }
  }

  /**
   * @Given the following roles have revision permissions for the :arg1 content type:
   */
  public function roleRevisionPermissions($node_type, TableNode $rolesTable) {
    foreach ($rolesTable as $rolePermission) {
      $role = $rolePermission['role'];
      $permission = $rolePermission['permission'] . ' ' . $node_type . ' revisions';
      $this->roleHasPermission($role, $permission);
    }
  }

  /**
   * @Given :arg1 has permission to use the following transition states
   */
  public function roleRevisionTransisions($role, TableNode $transitionTable) {
    foreach ($transitionTable as $transitionPermission) {
      $state = $transitionPermission['transition'];
      $permission = 'use c8b41afe transition ' . $state;
      $this->roleHasPermission($role, $permission);
    }
  }

  /**
   * @Given the following roles have permissions for the :arg1 scheduled update type:
   */
  public function roleScheduleUpdatePermissions($scheduled_type, TableNode $rolesTable) {
    foreach ($rolesTable as $rolePermission) {
      $role = $rolePermission['role'];
      $permission = $rolePermission['permission'] . ' ' . $scheduled_type . ' scheduled updates';
      $this->roleHasPermission($role, $permission);
    }
  }

  /**
   * @Then the field :arg1 is present for users
   */
  public function isUserField($field_name) {
    $bundle_fields = \Drupal::getContainer()->get('entity_field.manager')->getFieldDefinitions('user', 'user');
    if (empty($bundle_fields[$field_name])) {
      throw new \Exception('Field ' . $field_name . ' is not required.');
    }
  }

  /**
   * @Then the :arg1 field should be required for users
   */
  public function checkIsRequiredUserField($field_name) {
    $this->isRequiredUserField($field_name);
  }

  /**
   * @Then the user :arg1 field should allow references to :arg2 content/vocabulary (terms)
   */
  public function checkUserFieldAllowsEntityReferences($field_name, $reference_bundles) {
    $reference_bundles = explode(',', $reference_bundles);
    $this->userFieldAllowsEntityReferences($field_name, $reference_bundles);
  }

  /**
   * @Given the following roles have these permissions:
   */
  public function roleUserPermissions(TableNode $rolesTable) {
    foreach ($rolesTable as $rolePermission) {
      $role = $rolePermission['role'];
      $permission = $rolePermission['permission'];
      $this->roleHasPermission($role, $permission);
    }
  }

  /**
   * @Given the following roles do not have these permissions:
   */
  public function roleUserPermissionsNot(TableNode $rolesTable) {
    foreach ($rolesTable as $rolePermission) {
      $role = $rolePermission['role'];
      $permission = $rolePermission['permission'];
      $this->roleDoesNotHavePermission($role, $permission);
    }
  }

  /**
   * @Given the following content types use workbench moderation:
   */
  public function contentTypeModeration(TableNode $contentTypesTable) {
    foreach ($contentTypesTable as $contentTypeRow) {
      if (!$this->contentTypeUsesModeration($contentTypeRow['content type'])) {
        throw new \Exception('Content type ' . $contentTypeRow['content type'] . ' does not use workbench moderation.');
      }
    }
  }

  /**
   * @Given the following content types do not use workbench moderation:
   */
  public function contentTypeNoModeration(TableNode $contentTypesTable) {
    foreach ($contentTypesTable as $contentTypeRow) {
      if ($this->contentTypeUsesModeration($contentTypeRow['content type'])) {
        throw new \Exception('Content type ' . $contentTypeRow['content type'] . ' uses workbench moderation.');
      }
    }
  }

  /**
   * @Then the file :arg1 has been generated with :arg2 data :arg3 relating to the :arg4 entity.
   */
  public function fileCheck($file_name, $format, $data, $entity_title) {
    $this->fileValidate($file_name, $format, $data, $entity_title);
  }

  /**
   * @Then the node :title has been saved:
   */
  public function saveNode($title, $fields) {
    $nid = $this->getTestContentNidByTitle($title);
    $node = Node::load($nid);
    foreach ($fields as $field) {
      foreach ($field as $key => $value) {
        switch ($key) {
          case 'field_display_location':
          case 'field_station':
            $nid = $this->getTestContentNidByTitle($value);
            $node->set($key, $nid);
            break;

          default:
            $node->set($key, $value);
            break;
        }
      }
    }
    $node->save();
  }

  /**
   * @Given the :arg1 file no longer contains :arg2.
   */
  public function fileEntityDeleteUpdate($file_name, $data) {
    $this->fileNotContains($file_name, $data);
  }

  /**
   * Check content type config entity to see if workbench moderation is enabled.
   *
   * @param string $content_type
   *   Machine name string for content type.
   *
   * @return bool
   *   Return boolean value.
   */
  private function contentTypeUsesModeration($content_type) {
    if (!($info = NodeType::load($content_type))) {
      return FALSE;
    }
    $info = \Drupal::configFactory()->getEditable('workflows.workflow.c8b41afe');
    $deps = $info->get('dependencies');
    foreach ($deps as $dep) {
      if ($dep[0] == "node.type.$content_type") {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * @Then I try to delete this content
   *
   * Query the node by title and redirect.
   */
  public function iTryToDeleteLatestContent() {
    $nid = $this->getLastCreatedNodeId();
    $this->getSession()->visit($this->locatePath('node/' . $nid . '/delete'));
  }

  /**
   * @Then I try to delete the last created content
   *
   * Query the node by title and visit it.
   */
  public function iDeleteContent() {
    $nid = $this->getLastCreatedNodeId();
    $this->getSession()->visit($this->locatePath('node/' . $nid . '/delete'));
  }

  /**
   * @Then I visit the last created content
   *
   * Query the node by title and visit it.
   */
  public function iVisitLatestContent() {
    $nid = $this->getLastCreatedNodeId();
    $this->getSession()->visit($this->locatePath('node/' . $nid));
  }

  /**
   * @Given I add a message for the last created content
   */
  public function iAddMessageForTheLastCreatedContent() {
    $nid = $this->getLastCreatedNodeId();
    $this->getSession()->visit($this->locatePath('node/add/message?display=' . $nid));
  }

  /**
   * @Then the :arg1 field should contain most recent entity reference :arg2
   */
  public function theFieldShouldContainMostRecentEntityReference($field, $value) {
    $query = \Drupal::entityQuery('node');
    $query->condition('title', $value);
    $nids = $query->execute();
    $nid = end($nids);
    $entity_reference = "{$value} ({$nid})";
    $this->assertSession()->fieldValueEquals($field, $entity_reference);
  }

  /**
   * @Then I try to visit :arg1
   */
  public function iTryToVisit($path) {
    $this->getSession()->visit($this->locatePath($path));
  }

  /**
   * @When I delete :id
   */
  public function iDelete($id) {
    /* @var $node \Drupal\node\Entity\Node */
    $node = $this->privateStorage[$id];
    $node->delete();
  }

  /**
   * @Then a new image :arg1 is added to the latest content :arg2 field
   */
  public function attachImagestoNode($image_filename, $field_name) {
    // Create file object from a locally copied file.
    $uri = file_unmanaged_copy($this->getMinkParameter('files_path') . '/' . $image_filename, "public://" . $image_filename, FILE_EXISTS_REPLACE);
    $file = File::Create([
      'uri' => $uri,
    ]);
    $file->save();

    // Load existing node and attach file.
    $nid = $this->getLastCreatedNodeId();
    $node = Node::load($nid);
    $node->{$field_name}->setValue([
      'target_id' => $file->id(),
    ]);
    $node->save();
  }

  /**
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
   * @When I unpublish the node titled :node_title
   */
  public function iUnpublishTheNodeTitled($node_title) {
    $nid = $this->getTestContentNidByTitle($node_title);
    $node = Node::load($nid);
    $node->setPublished(FALSE);
    $node->set('moderation_state', "draft");
    $node->save();
  }

  /**
   * @When I publish the node titled :node_title
   */
  public function iPublishTheNodeTitled($node_title) {
    $nid = $this->getTestContentNidByTitle($node_title);
    $node = Node::load($nid);
    $node->setPublished(FALSE);
    $node->set('moderation_state', "published");
    $node->save();
  }

  /**
   * @When I publish :id
   */
  public function iPublish($id) {
    /* @var $node Node */
    $node = $this->privateStorage[$id];
    $node->setPublished(TRUE);
    $node->set('moderation_state', "published");
    $node->save();
  }

  /**
   * @When I unpublish :id
   */
  public function iUnpublish($id) {
    /* @var $node Node */
    $node = $this->privateStorage[$id];
    $node->setPublished(FALSE);
    $node->set('moderation_state', "draft");
    $node->save();
  }

  /**
   * Gets a TableNode object from the data in a given CSV file.
   *
   * @param string $csv
   *   The name of a CSV file in the tests/behat/features/data directory, e.g.
   *   "file.csv".
   *
   * @return \Behat\Gherkin\Node\TableNode
   *   A TableNode object.
   */
  protected function getTableNodeFromCsv($csv) {
    $data = array_map('str_getcsv', file(__DIR__ . "../../../data/{$csv}"));
    return new TableNode($data);
  }

  /**
   * @Given there is a group of type :arg1 with the title :arg2
   */
  public function createNewGroup($group_type, $title) {
    $group = $this->createGroup($group_type, $title);
    Assert::assertNotEmpty($group->id(), "Group was not created.");
  }

  /**
   * @Then I am a member of the current group
   *
   * Adds user to the current group.
   */
  public function joinCurrentGroup() {
    $account = \Drupal::entityTypeManager()->getStorage('user')->load(
      $this->getUserManager()->getCurrentUser()->uid
    );
    $this->userJoinGroup($account, $this->currentGroup);
    $group_user = $this->currentGroup->getMember($account);
    Assert::assertNotFalse($group_user, "The new user did not get assigned to the group.");
  }

  /**
   * @Then I am a member of the current group with the role :group_role
   *
   * Adds current user to the current group with the specified role.
   */
  public function joinCurrentGroupWithRole($group_role) {
    $account = \Drupal::entityTypeManager()->getStorage('user')->load(
      $this->getUserManager()->getCurrentUser()->uid
    );
    $this->userJoinGroup($account, $this->currentGroup, $group_role);
    $group_user = $this->currentGroup->getMember($account);
    Assert::assertNotFalse($group_user, "The new user did not get assigned to the group.");
  }

}
