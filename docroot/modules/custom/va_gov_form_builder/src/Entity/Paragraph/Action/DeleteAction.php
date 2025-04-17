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
  public function render(FormBuilderParagraphInterface $paragraph): array {
    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function execute(FormBuilderParagraphInterface $paragraph) {
    $label = $paragraph->get('field_title')->value;
    // Check if delete is allowed for this step.
    if (!$this->checkAccess($paragraph)) {
      \Drupal::messenger()
        ->addError($this->t('%label <em>cannot</em> be deleted. Access denied.', [
          '%label' => $label,
        ]));
      return;
    }
    try {
      // Iterate only on the like group of fields.
      $group = $paragraph->getFieldItemGroup();
      // Grab the real field, so we can persist the data to the parent.
      $parentFieldName = $paragraph->get('parent_field_name')->value;
      $parentField = $paragraph->getParentEntity()->get($parentFieldName);
      foreach ($group as $delta => $groupedField) {
        if ($paragraph->id() === $groupedField->entity->id()) {
          $parentField->removeItem($delta);
          break;
        }
      }
      // Save the parent entity.
      $paragraph->getParentEntity()->save();

      // Delete the paragraph.
      $paragraph->delete();

      // Add success message.
      \Drupal::messenger()
        ->addWarning($this->t('%label was deleted successfully', [
          '%label' => $label,
        ]));
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
