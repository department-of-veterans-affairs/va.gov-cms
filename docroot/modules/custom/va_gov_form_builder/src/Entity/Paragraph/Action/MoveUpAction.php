<?php

namespace Drupal\va_gov_form_builder\Entity\Paragraph\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\va_gov_form_builder\Entity\Paragraph\FormBuilderParagraphInterface;

/**
 * Move a paragraph up.
 */
class MoveUpAction extends ActionBase {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function getTitle(): string {
    return $this->t('Move up');
  }

  /**
   * {@inheritDoc}
   */
  public function getKey(): string {
    return 'moveup';
  }

  /**
   * {@inheritDoc}
   */
  public function checkAccess(FormBuilderParagraphInterface $paragraph): bool {
    $result = AccessResult::allowedIf(parent::checkAccess($paragraph));
    $siblings = $paragraph->getFieldEntities();
    $multiple = AccessResult::allowedIf(count($siblings) > 1);
    $result = $result->andIf($multiple);
    $first = $siblings[array_key_first($siblings)];
    $isNotFirst = AccessResult::allowedIf(!($first->id() === $paragraph->id()));
    $result = $result->andIf($isNotFirst);
    return $result->isAllowed();
  }

  /**
   * {@inheritDoc}
   */
  public function execute(FormBuilderParagraphInterface $paragraph) {
    $label = $paragraph->get('field_title')->value;

    // Check if this move is allowed for this paragraph.
    if (!$this->checkAccess($paragraph)) {
      \Drupal::messenger()
        ->addError($this->t('%label <em>cannot</em> be moved. Access denied.', [
          '%label' => $label,
        ]));
      return;
    }
    // Find the current paragraph and its previous sibling.
    $previousDelta = NULL;
    $currentDelta = NULL;
    try {
      $siblings = $paragraph->getFieldEntities();
      $parentFieldName = $paragraph->get('parent_field_name')->value;
      $parentField = $paragraph->getParentEntity()->get($parentFieldName);
      foreach ($siblings as $delta => $entity) {
        if ($entity->id() === $paragraph->id()) {
          $currentDelta = $delta;
          break;
        }
        $previousDelta = $delta;
      }
      // Only proceed if we found both deltas.
      if (isset($currentDelta, $previousDelta)) {
        $previousEntity = $siblings[$previousDelta];
        $currentEntity = $siblings[$currentDelta];

        // Swap positions.
        $parentField->set($previousDelta, $currentEntity);
        $parentField->set($currentDelta, $previousEntity);

        // Save the parent.
        $paragraph->getParentEntity()->save();

        // Add success message.
        \Drupal::messenger()->addStatus($this->t('%label was moved up successfully', [
          '%label' => $label,
        ]));
      }
    }
    catch (\Exception $e) {
      // Persisting to node failed.
      \Drupal::messenger()->addError($this->t('An error occurred while moving %label. The error was %error', [
        '%label' => $label,
        '%error' => $e->getMessage(),
      ]));
    }
  }

}
