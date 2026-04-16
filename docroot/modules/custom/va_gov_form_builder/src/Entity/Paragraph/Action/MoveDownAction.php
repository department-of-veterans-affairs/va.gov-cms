<?php

namespace Drupal\va_gov_form_builder\Entity\Paragraph\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\StringTranslation\StringTranslationTrait;
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
        ->addError($this->t('%label <em>cannot</em> be moved. Access denied.', [
          '%label' => $label,
        ]));
      return;
    }
    try {
      $siblings = $paragraph->getFieldEntities();
      $parentFieldName = $paragraph->get('parent_field_name')->value;

      $parentField = $paragraph->getParentEntity()->get($parentFieldName);
      // Find the current paragraph and its next sibling.
      $currentDelta = NULL;
      $nextDelta = NULL;
      $siblingIds = array_keys($siblings);
      $currentIndex = NULL;

      // Find the current paragraph's index in the array.
      foreach ($siblingIds as $index => $delta) {
        if ($siblings[$delta]->id() === $paragraph->id()) {
          $currentDelta = $delta;
          $currentIndex = $index;
          break;
        }
      }

      // Get the next delta if it exists.
      if (isset($currentIndex) && isset($siblingIds[$currentIndex + 1])) {
        $nextDelta = $siblingIds[$currentIndex + 1];
      }

      // Only proceed if we found both deltas.
      if (isset($currentDelta, $nextDelta)) {
        $currentEntity = $siblings[$currentDelta];
        $nextEntity = $siblings[$nextDelta];

        // Swap positions.
        $parentField->set($nextDelta, $currentEntity);
        $parentField->set($currentDelta, $nextEntity);

        // Save the parent.
        $paragraph->getParentEntity()->save();

        // Add success message.
        \Drupal::messenger()->addStatus($this->t('%label was moved down successfully', [
          '%label' => $label,
        ]));
      }
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError($this->t('An error occurred while moving %label. The error was %error', [
        '%label' => $label,
        '%error' => $e->getMessage(),
      ]));
    }
  }

}
