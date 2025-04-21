<?php

namespace Drupal\va_gov_form_builder\Entity\Paragraph\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\va_gov_form_builder\Entity\Paragraph\FormBuilderParagraphInterface;

/**
 * Abstract base Form Builder Paragraph action.
 */
abstract class ActionBase implements ActionInterface {

  /**
   * {@inheritDoc}
   */
  public function checkAccess(FormBuilderParagraphInterface $paragraph): bool {
    $result = AccessResult::allowed();
    // Delegate access check to the Paragraph.
    $result = $result->andIf($paragraph->actionAccess($this));
    return $result->isAllowed();
  }

  /**
   * {@inheritDoc}
   */
  public function execute(FormBuilderParagraphInterface $paragraph) {
    if (!$this->checkAccess($paragraph)) {
      return;
    }
  }

}
