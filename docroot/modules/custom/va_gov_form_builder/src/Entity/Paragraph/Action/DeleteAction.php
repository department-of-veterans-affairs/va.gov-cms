<?php

namespace Drupal\va_gov_form_builder\Entity\Paragraph\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\va_gov_form_builder\Entity\Paragraph\FormBuilderParagraphInterface;

/**
 * Action to delete a Form Builder Paragraph.
 */
class DeleteAction extends ActionBase {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function getTitle(): string {
    return $this->t('Delete');
  }

  /**
   * {@inheritDoc}
   */
  public function getKey(): string {
    return 'delete';
  }

  /**
   * {@inheritDoc}
   */
  public function checkAccess(FormBuilderParagraphInterface $paragraph): bool {
    $result = AccessResult::allowedIf(parent::checkAccess($paragraph));
    return $result->isAllowed();
  }

  /**
   * {@inheritDoc}
   */
  public function execute(FormBuilderParagraphInterface $paragraph) {
    $label = $paragraph->get('field_title')->value;
    // Check if delete is allowed for this paragraph.
    if (!$this->checkAccess($paragraph)) {
      \Drupal::messenger()
        ->addError($this->t('%label <em>cannot</em> be deleted. Access denied.', [
          '%label' => $label,
        ]));
      return;
    }
    try {
      $siblings = $paragraph->getFieldEntities();
      $parentFieldName = $paragraph->get('parent_field_name')->value;
      /** @var \Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList $parentField */
      $parentField = $paragraph->getParentEntity()->get($parentFieldName);
      foreach ($siblings as $delta => $entity) {
        if ($paragraph->id() === $entity->id()) {
          $parentField->removeItem($delta);
          // Save the parent entity.
          $paragraph->getParentEntity()->save();

          // Delete the paragraph.
          $paragraph->delete();

          // Add success message.
          \Drupal::messenger()
            ->addStatus($this->t('%label was deleted successfully', [
              '%label' => $label,
            ]));
          break;
        }
      }
    }
    catch (\Exception $e) {
      // Saving node or deleting paragraph failed. Add error message.
      \Drupal::messenger()->addError($this->t('%label was <em>not</em> deleted successfully. The error was %error', [
        '%label' => $label,
        '%error' => $e->getMessage(),
      ]));
    }
  }

}
