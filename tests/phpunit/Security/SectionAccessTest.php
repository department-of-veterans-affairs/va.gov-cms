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
   *
   * @dataProvider sectionDataProvider
   */
  public function testSectionAccess(
    string $role,
    array $user_sections,
    array $content_sections,
    bool $should_have_access
  ) {
    $author = $this->createUser();
    $author->addRole($role);
    $this->addUserToSections($user_sections, $author);

    $sections = $this->getSectionIds($content_sections);
    $node = $this->createNode([
      'title' => '[TEST] section access',
      'type' => 'page',
      'uid' => 1,
      'field_administration' => $sections,
    ]);
    $node->save();

    $url = Url::fromRoute('entity.node.edit_form', ['node' => $node->id()]);
    $this->assertEquals($should_have_access, $url->access($author));
  }

  /**
   * Data provider for testSectionAccess.
   *
   * @return \Generator
   *   Test assertion data.
   */
  public function sectionDataProvider() : \Generator {
    yield 'Content editors may edit nodes in their sections' => [
      'content_editor',
      ['Veterans Health Administration'],
      ['Veterans Health Administration'],
      TRUE,
    ];
    yield 'Content editors may not edit nodes in other sections' => [
      'content_editor',
      ['Veterans Health Administration'],
      ['National Cemetery Administration'],
      FALSE,
    ];
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
