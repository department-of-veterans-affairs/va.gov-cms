<?php

namespace Drupal\va_gov_form_builder\Paragraph\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionBase;
use Drupal\va_gov_form_builder\Entity\Paragraph\FormBuilderParagraphInterface;

/**
 * Move a paragraph down.
 */
class MoveDownAction extends ActionBase {

  use StringTranslationTrait;

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
    $result = AccessResult::allowedIf(parent::checkAccess());
    $group = $paragraph->getFieldItemGroup();
    $last = $group[(array_key_last($group))];
    $isNotFirstStep = AccessResult::allowedIf(!($last['paragraph']->id() === $paragraph->id()));
    $result = $result->andIf($isNotFirstStep);
    return $result->isAllowed();
  }

  /**
   * {@inheritDoc}
   */
  public function render(FormBuilderParagraphInterface $paragraph): array {
    return [];
  }

  /**
   * {@inheritDoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
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
    $fields = $paragraph->getFieldItemGroup();
    foreach ($fields as $delta => $field) {
      if ($field->entity->id() === $paragraph->id()) {
        $sourceStep = $field->entity;
        $sourceStepId = $delta;
      }
      elseif ($sourceStep instanceof ParagraphInterface && isset($sourceStepId)) {
        $fields->set($sourceStepId, $field->entity);
        $fields->set($delta, $sourceStep);
        break;
      }
    }

    try {
      // Save the parent.
      $paragraph->getParentEntity()->save();

      // Add success message.
      \Drupal::messenger()->addWarning($this->t('%label was moved down successfully', [
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
