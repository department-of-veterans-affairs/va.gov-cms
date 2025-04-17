<?php

namespace Drupal\va_gov_form_builder\Entity\Paragraph;

use Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionCollection;
use Drupal\va_gov_form_builder\Entity\Paragraph\Action\DeleteAction;
use Drupal\va_gov_form_builder\Entity\Paragraph\Action\MoveDownAction;
use Drupal\va_gov_form_builder\Entity\Paragraph\Action\MoveUpAction;

/**
 * Paragraph of type digital_form_custom_step.
 */
class CustomStepParagraph extends FormBuilderParagraphBase {

  /**
   * {@inheritDoc}
   */
  protected function initializeActionCollection(): ActionCollection {
    // Adds DeleteAction, MoveUpAction, and MoveDownAction. These are possible
    // actions for this Paragraph. Before using any action, the
    // $Action->checkAccess() method should be used to verify it is usable in
    // the current context.
    $collection = parent::initializeActionCollection();
    $collection->add(new DeleteAction());
    $collection->add(new MoveUpAction());
    $collection->add(new MoveDownAction());
    return $collection;
  }

}
