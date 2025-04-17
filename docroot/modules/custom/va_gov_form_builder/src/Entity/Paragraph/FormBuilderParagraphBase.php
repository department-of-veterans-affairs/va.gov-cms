<?php

namespace Drupal\va_gov_form_builder\Entity\Paragraph;

use Drupal\Core\Access\AccessResult;
use Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionCollection;
use Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionInterface;

/**
 * Base class for Form Builder paragraph bundles.
 */
abstract class FormBuilderParagraphBase extends Paragraph implements FormBuilderParagraphInterface {

  /**
   * Collection of Actions for this Paragraph.
   *
   * @var \Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionCollection|null
   */
  protected ?ActionCollection $actionCollection = NULL;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $values, string $entity_type, $bundle = FALSE, array $translations = []) {
    parent::__construct($values, $entity_type, $bundle, $translations);
    $this->actionCollection = $this->getActionCollection();
  }

  /**
   * {@inheritDoc}
   */
  public function getActionCollection(): ActionCollection {
    return $this->actionCollection ??= $this->initializeActionCollection();
  }

  /**
   * Initialize the ActionCollection for this Paragraph.
   *
   * @return \Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionCollection
   *   The initialized ActionCollection for this Paragraph.
   */
  protected function initializeActionCollection(): ActionCollection {
    return new ActionCollection();
  }

  /**
   * {@inheritDoc}
   */
  public function getFieldItemGroup(): EntityReferenceRevisionsFieldItemList {
    $parentField = $this->get('parent_field_name');
    /** @var \Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList $group */
    $group = $this->getParentEntity()->get($parentField);
    return $group;
  }

  /**
   * {@inheritDoc}
   */
  public function executeAction(string $action): void {
    if ($this->actionCollection->has($action)) {
      $this->accept($action);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function accept(ActionInterface $action) {
    // Use reflection to the short name of the currently called class, rather
    // than the full namespace, which would be the output if get_class() were
    // used.
    $method = 'executeFor' . (new \ReflectionClass($this))->getShortName();
    if (method_exists($action, $method)) {
      return $action->$method($this);
    }
    return $action->execute($this);
  }

  /**
   * {@inheritDoc}
   */
  public function actionAccess(ActionInterface $action): AccessResult {
    // If this is a new Paragraph, it has not been saved, therefore there is no
    // parent node or paragraph to persist changes made by the action.
    $result = AccessResult::allowedIf(!$this->isNew());
    // Ensure this Paragraph has this action in its ActionCollection.
    return $result->andIf(AccessResult::allowedIf($this->actionCollection->has($action->getKey())));
  }

}
