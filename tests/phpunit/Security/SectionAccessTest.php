<?php

namespace tests\phpunit\Security;

use Drupal\Core\Url;
use Drupal\user\UserInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm section access permissions.
 */
class SectionAccessTest extends ExistingSiteBase {

  /**
   * Test method to confirm section access permissions.
   *
   * @group edit
   * @group all
   */
  public function testSectionAccess() {
    $author = $this->createUser();
    $author->addRole('content_editor');
    $this->addUserToSections(['Veterans Health Administration'], $author);

    $sections = $this->getSectionIds(['Veterans Health Administration']);
    $vha_section = reset($sections);
    $vha_node = $this->createNode([
      'title' => '[TEST] test vha page',
      'type' => 'page',
      'uid' => 1,
      'field_administration' => [$vha_section],
    ]);
    $vha_node->save();

    $sections = $this->getSectionIds(['National Cemetery Administration']);
    $nca_section = reset($sections);
    $nca_node = $this->createNode([
      'title' => '[TEST] test nca page',
      'type' => 'page',
      'uid' => 1,
      'field_administration' => [$nca_section],
    ]);
    $nca_node->save();

    $url = Url::fromRoute('entity.node.edit_form', ['node' => $vha_node->id()]);
    $this->assertTrue($url->access($author));

    $url = Url::fromRoute('entity.node.edit_form', ['node' => $nca_node->id()]);
    $this->assertFalse($url->access($author));
  }

  /**
   * Add user to specified sections.
   *
   * @param array $sections
   *   The sections.
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   */
  private function addUserToSections(array $sections, UserInterface $user) {
    if ($section_ids = $this->getSectionIds($sections)) {
      $user_section_scheme = \Drupal::service('entity_type.manager')->getStorage('access_scheme')->load('section');
      \Drupal::service('workbench_access.user_section_storage')->addUser($user_section_scheme, $user, $section_ids);
    }
  }

  /**
   * Get section term IDs from section names.
   *
   * @param array $sections
   *   Array of section names.
   *
   * @return array[int]
   *   Array of term IDs.
   */
  private function getSectionIds(array $sections) : array {
    $section_ids = [];

    foreach (array_filter($sections) as $section) {
      $terms = \Drupal::service('entity_type.manager')
        ->getStorage('taxonomy_term')
        ->loadByProperties(['name' => $section]);

      if (count($terms)) {
        $term = reset($terms);
        $section_ids[] = $term->id();
      }
    }

    return $section_ids;
  }

}
