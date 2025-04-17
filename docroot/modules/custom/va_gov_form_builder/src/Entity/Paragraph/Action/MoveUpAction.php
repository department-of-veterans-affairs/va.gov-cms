<?php

namespace Drupal\va_gov_form_builder\Entity\Paragraph\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\va_gov_form_builder\Entity\Paragraph\FormBuilderParagraphInterface;

/**
 * Move a paragraph up.
 */
class MoveUpAction extends ActionBase {

  use StringTranslationTrait;

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
    $result = AccessResult::allowedIf(parent::checkAccess());
    $steps = $paragraph->getFieldItemGroup();
    $first = $steps[(array_key_first($steps))];
    $isNotFirstStep = AccessResult::allowedIf(!($first['paragraph']->id() === $paragraph->id()));
    $result = $result->andIf($isNotFirstStep);
    return $result->isAllowed();
  }

  /**
   * {@inheritDoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function execute(FormBuilderParagraphInterface $paragraph) {
    $label = $paragraph->get('field_title')->value;

    // Check if this move is allowed for this step.
    if (!$this->checkAccess($paragraph)) {
      \Drupal::messenger()
        ->addError($this->t('%label <em>cannot</em> be moved. Access denied.', [
          '%label' => $label,
        ]));
      return;
    }
    $previousStep = NULL;
    $previousDelta = NULL;
    /** @var \Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList $chapters */
    $chapters = $this->node->get('field_chapters');
    $fields = $paragraph->getFieldItemGroup();
    foreach ($fields as $delta => $field) {
      if ($field->entity->id() === $paragraph->id() && $previousStep instanceof ParagraphInterface && !is_null($previousDelta)) {
        $chapters->set($previousDelta, $field->entity);
        $chapters->set($delta, $previousStep);
        break;
      }
      $previousStep = $field->entity;
      $previousDelta = $delta;
    }
    try {
      // Save the parent.
      $paragraph->getParentEntity()->save();

      // Add success message.
      \Drupal::messenger()->addWarning($this->t('Step %label was moved up successfully', [
        '%label' => $label,
      ]));
    }
    catch (\Exception $e) {
      // Persisting to node failed.
      \Drupal::messenger()->addError($this->t('An error occurred while moving step %label. The error was %error', [
        '%label' => $label,
        '%error' => $e->getMessage(),
      ]));
    }
  }

  /**
   * {@inheritDoc}
   */
  public function render(FormBuilderParagraphInterface $paragraph): array {
    return [];
  }

}
