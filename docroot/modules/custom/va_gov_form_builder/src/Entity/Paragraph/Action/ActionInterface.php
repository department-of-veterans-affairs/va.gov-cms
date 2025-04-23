<?php

namespace Drupal\va_gov_form_builder\Entity\Paragraph\Action;

use Drupal\va_gov_form_builder\Entity\Paragraph\FormBuilderParagraphInterface;

/**
 * An interface for paragraph actions.
 */
interface ActionInterface {

  /**
   * Returns the unique key/identifier for this action.
   *
   * @return string
   *   The key for this action.
   */
  public function getKey(): string;

  /**
   * Returns the title for this action.
   *
   * @return string
   *   The title for this action.
   */
  public function getTitle(): string;

  /**
   * Check access for this action on the paragraph provided.
   *
   * @param \Drupal\va_gov_form_builder\Entity\Paragraph\FormBuilderParagraphInterface $paragraph
   *   A Form Builder Paragraph.
   *
   * @return bool
   *   TRUE if access is allowed.
   */
  public function checkAccess(FormBuilderParagraphInterface $paragraph): bool;

  /**
   * Execute the action.
   *
   * @param \Drupal\va_gov_form_builder\Entity\Paragraph\FormBuilderParagraphInterface $paragraph
   *   The step performing the action.
   */
  public function execute(FormBuilderParagraphInterface $paragraph);

}
