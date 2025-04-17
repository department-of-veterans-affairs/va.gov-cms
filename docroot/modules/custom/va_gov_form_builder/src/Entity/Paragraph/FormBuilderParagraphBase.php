<?php

namespace Drupal\va_gov_form_builder\Entity\Paragraph;

use Drupal\Core\Access\AccessResult;
use Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionCollection;
use Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    $parentFieldName = $this->get('parent_field_name')->value;
    if (!$this->getParentEntity()->hasField($parentFieldName)) {
      throw new NotFoundHttpException();
    }
    // Clone the field so changes do not persist on the parent field.
    /** @var \Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList $group */
    $group = clone($this->getParentEntity()->get($parentFieldName));
    return $group;
  }

  /**
   * {@inheritDoc}
   */
  public function executeAction(string $action): void {
    if ($this->actionCollection->has($action)) {
      $this->accept($this->actionCollection->get($action));
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
    if ($action->getKey() === 'delete') {
      $canDelete = $this->access(operation: 'delete', return_as_object: TRUE);
      $result = $result->andIf($canDelete);
    }
    // Prevent action if parent cannot be updated.
    $update = $this->getParentEntity()->access(operation:'update', return_as_object: TRUE);
    $result = $result->andIf($update);
    // Ensure this Paragraph has this action in its ActionCollection.
    return $result->andIf(AccessResult::allowedIf($this->actionCollection->has($action->getKey())));
  }

}
