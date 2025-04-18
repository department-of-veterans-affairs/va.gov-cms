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
  private static ?string $classShortName = 'FormBuilderParagraphBase';

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
    static::$classShortName = static::getClassShortName();
  }

  /**
   * {@inheritDoc}
   */
  public static function getClassShortName(): string {
    return static::$classShortName ??= (new \ReflectionClass(new static))->getShortName();
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
    // Use reflection to get the short name of the currently called class,
    // rather than the full namespace, which would be what get_class() would
    // give us.
    $method = 'executeFor' . static::getClassShortName();
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
    // Check delete access if needed.
    if ($action->getKey() === 'delete') {
      $result = $result->andIf($this->access(operation: 'delete', return_as_object: TRUE));
    }
    // Prevent action if parent cannot be updated.
    $result = $result->andIf($this->getParentEntity()->access(operation:'update', return_as_object: TRUE));
    // Ensure this Paragraph has this action in its ActionCollection.
    return $result->andIf(AccessResult::allowedIf($this->actionCollection->has($action->getKey())));
  }

}
