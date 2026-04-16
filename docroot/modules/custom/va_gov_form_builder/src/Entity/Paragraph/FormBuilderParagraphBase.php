<?php

namespace Drupal\va_gov_form_builder\Entity\Paragraph;

use Drupal\Core\Access\AccessResult;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionCollection;
use Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionInterface;
use Drupal\va_gov_form_builder\Entity\Paragraph\Action\DeleteAction;
use Drupal\va_gov_form_builder\Entity\Paragraph\Action\MoveDownAction;
use Drupal\va_gov_form_builder\Entity\Paragraph\Action\MoveUpAction;

/**
 * Base class for Form Builder paragraph bundles.
 */
abstract class FormBuilderParagraphBase extends Paragraph implements FormBuilderParagraphInterface {

  /**
   * The short name of this class for convenient dual dispatching.
   *
   * @var string|null
   */
  protected ?string $classShortName = NULL;

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
    $this->classShortName = $this->getClassShortName();
  }

  /**
   * {@inheritDoc}
   */
  public function getClassShortName(): string {
    return $this->classShortName ??= (new \ReflectionClass($this))->getShortName();
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
    // Adds DeleteAction, MoveUpAction, and MoveDownAction. These are possible
    // actions for the Paragraph. Before using any action, the
    // $Action->checkAccess() method should be used to verify it is usable in
    // the current context.
    $collection = new ActionCollection();
    $collection->add(new MoveUpAction());
    $collection->add(new MoveDownAction());
    $collection->add(new DeleteAction());
    return $collection;
  }

  /**
   * {@inheritDoc}
   */
  public function getFieldEntities(): array {
    $parentFieldName = $this->get('parent_field_name')->value;
    /** @var \Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList $parentField */
    $parentField = $this->getParentEntity()->get($parentFieldName);
    return $parentField->referencedEntities();
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
    $method = 'executeFor' . $this->getClassShortName();
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
