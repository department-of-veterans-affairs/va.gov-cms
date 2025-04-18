<?php

namespace Drupal\va_gov_form_builder\Entity\Paragraph\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\va_gov_form_builder\Entity\Paragraph\FormBuilderParagraphInterface;

/**
 * Move a paragraph down.
 */
class MoveDownAction extends ActionBase {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function getTitle(): string {
    return $this->t('Move down');
  }

  /**
   * {@inheritDoc}
   */
  public function getKey(): string {
    return 'movedown';
  }

  /**
   * {@inheritDoc}
   */
  public function checkAccess(FormBuilderParagraphInterface $paragraph): bool {
    $result = AccessResult::allowedIf(parent::checkAccess($paragraph));
    $siblings = $paragraph->getFieldEntities();
    $multiple = AccessResult::allowedIf(count($siblings) > 1);
    $result = $result->andIf($multiple);
    $lastParagraph = $siblings[array_key_last($siblings)];
    $isNotLast = AccessResult::allowedIf(!($lastParagraph->id() === $paragraph->id()));
    $result = $result->andIf($isNotLast);
    return $result->isAllowed();
  }

  /**
   * {@inheritDoc}
   */
  public function execute(FormBuilderParagraphInterface $paragraph) {
    $label = $paragraph->get('field_title')->value;

    // Check if this move is allowed for this Paragraph.
    if (!$this->checkAccess($paragraph)) {
      \Drupal::messenger()
        ->addError($this->t('Step %label <em>cannot</em> be moved. Access denied.', [
          '%label' => $label,
        ]));
      return;
    }
    $sourceStep = NULL;
    $sourceStepId = NULL;
    try {
      // Iterate only on the like group of fields.
      $siblings = $paragraph->getFieldEntities();
      // Grab the real field, so we can persist the data to the parent.
      $parentFieldName = $paragraph->get('parent_field_name')->value;
      $parentField = $paragraph->getParentEntity()->get($parentFieldName);
      foreach ($siblings as $delta => $entity) {
        if ($entity->id() === $paragraph->id()) {
          $sourceStep = $entity;
          $sourceStepId = $delta;
        }
        elseif ($sourceStep instanceof ParagraphInterface && isset($sourceStepId)) {
          $parentField->set($sourceStepId, $entity);
          $parentField->set($delta, $sourceStep);
          break;
        }
      }
      // Save the parent.
      $paragraph->getParentEntity()->save();

      // Add success message.
      \Drupal::messenger()->addStatus($this->t('%label was moved down successfully', [
        '%label' => $label,
      ]));
    }
    catch (\Exception $e) {
      // Persisting to parent failed.
      \Drupal::messenger()->addError($this->t('An error occurred while moving %label. The error was %error', [
        '%label' => $label,
        '%error' => $e->getMessage(),
      ]));
    }
  }

}
