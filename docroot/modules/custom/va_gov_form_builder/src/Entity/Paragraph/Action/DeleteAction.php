<?php

namespace Drupal\va_gov_form_builder\Paragraph\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionBase;
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
    $canDelete = $paragraph->access('delete');
    $result = $result->andIf(AccessResult::allowedIf($canDelete));
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
    $deleted = FALSE;
    // Check if delete is allowed for this step.
    if (!$this->checkAccess($paragraph)) {
      \Drupal::messenger()
        ->addError($this->t('%label <em>cannot</em> be deleted. Access denied.', [
          '%label' => $label,
        ]));
      return;
    }
    $fields = $paragraph->getFieldItemGroup();
    foreach ($fields as $delta => $field) {
      if ($paragraph->id() === $field->entity->id()) {
        $field->removeItem($delta);
        $deleted = TRUE;
        break;
      }
    }

    if ($deleted) {
      try {
        // Save the node.
        $paragraph->getParentEntity()->save();

        // Delete the paragraph.
        $paragraph->delete();

        // Add success message.
        \Drupal::messenger()->addWarning($this->t('%label was deleted successfully', [
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

}
