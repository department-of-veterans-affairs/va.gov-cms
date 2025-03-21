<?php

namespace tests\phpunit\va_gov_form_builder\Traits;

/**
 * Provides a trait for creating a test paragraph.
 */
trait ParagraphCreationTrait {

  /**
   * Creates a paragraph and marks it for automatic cleanup.
   *
   * This mimics the method `createNode` that is available
   * in VaGovExistingSiteBase. There is no `createParagraph`
   * available in that class hierarchy.
   *
   * @param array $settings
   *   The settings for defining the paragraph.
   *
   * @return \Drupal\paragraphs\Entity\Paragraph
   *   A newly created paragraph marked for deletion
   *   at the conclusion of the test.
   */
  protected function createParagraph(array $settings = []) {
    $paragraph = \Drupal::entityTypeManager()->getStorage('paragraph')->create($settings);
    $this->markEntityForCleanup($paragraph);
    return $paragraph;
  }

}
