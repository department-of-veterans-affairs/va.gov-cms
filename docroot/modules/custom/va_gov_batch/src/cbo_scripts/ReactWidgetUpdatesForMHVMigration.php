<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\NodeInterface;
use Drupal\path_alias\AliasManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * For VACMS-20726.
 *
 * @see https://github.com/department-of-veterans-affairs/va.gov-cms/issues/20726
 */
class ReactWidgetUpdatesForMHVMigration extends BatchOperations implements BatchScriptInterface {

  /**
   * Map of widget names from old to new.
   *
   * @var array
   */
  const WIDGET_MAP = [
    'rx' => 'modern-refill-track-prescriptions-page',
    'health-records' => 'modern-get-medical-records-page',
    'lab-and-test-results' => 'modern-get-medical-records-page',
    'messaging' => 'modern-secure-messaging-page',
    'schedule-appointments' => 'modern-schedule-view-va-appointments-page',
  ];

  /**
   * The widget type field name. This is the main target for the migration.
   *
   * @var string
   */
  const WIDGET_FIELD_NAME = 'field_widget_type';

  /**
   * The Drupal Path Alias manager service.
   *
   * @var \Drupal\path_alias\AliasManager
   */
  protected AliasManager $pathAliasManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->pathAliasManager = $container->get('path_alias.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle():string {
    return "CTA Widget Updates for 'MHV on VA.gov' migration";
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription():string {
    return <<<ENDHERE
    MHV Classic experience is moving into 'MHV on VA.gov'.
    MHV links in CTA widgets will be either updated to point to MHV on VA.gov, or will be replaced by new CTA widgets.
    As a result, certain Drupal CTA widget names need to be updated for those being used by replacement. 'MHV on VA.gov' widgets.
    ENDHERE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return 'A total of @total widget updates were attempted. @completed were completed.';
  }

  /**
   * {@inheritdoc}
   */
  public function getItemType(): string {
    return 'paragraph';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    // Finds CTA widget paragraphs for MHV. Returns ~413 records.
    $select = $this->databaseConnection->select('paragraph__field_widget_type', 'wt');
    $select->fields('wt', ['entity_id']);
    $select->condition('wt.field_widget_type_value', array_keys(self::WIDGET_MAP), 'IN');
    return $select->execute()->fetchCol();
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    /** @var \Drupal\paragraphs\ParagraphInterface $paragraph */
    $paragraph = $this->entityTypeManager->getStorage('paragraph')->load($item);
    if (!$paragraph) {
      return "There was a problem loading paragraph id {$item}. Further investigation is needed. Skipping.";
    }
    if (!$paragraph->hasField(self::WIDGET_FIELD_NAME)) {
      return "The paragraph id {$item} doesn't have the necessary field. This is unexpected and further investigation is needed. Skipping.";
    }
    $parentEntity = $paragraph->getParentEntity();
    if (!$parentEntity) {
      return "There is no parent node for paragraph id {$item}. This appears to be an orphaned paragraph. Skipping.";
    }
    // Make sure we are acting on a node.
    if (!$parentEntity instanceof NodeInterface) {
      $parentEntityType = $parentEntity->getEntityType()->getLabel();
      $parentEntityBundle = $parentEntity->bundle();
      return "The parent entity for paragraph id {$item} is not a node. This is unexpected and warrants manual migration for this paragraph. The parent bundle is {$parentEntityBundle} and the entity type is {$parentEntityType}.";
    }
    try {
      $currentWidgetName = $paragraph->get(self::WIDGET_FIELD_NAME)->value;
      $newWidgetName = self::WIDGET_MAP[$currentWidgetName];
      if (!isset(self::WIDGET_MAP[$currentWidgetName])) {
        return "New widget name not found for paragraph id {$item} with widget name {$currentWidgetName}. Skipping.";
      }
      // Set the name of this widget to the new MHV widget name.
      $paragraph->set(self::WIDGET_FIELD_NAME, $newWidgetName);
      $paragraph->save();
      // Get the path alias for this node for logging.
      $alias = $this->pathAliasManager->getAliasByPath('/node' . $parentEntity->id());
      return "Updated from {$currentWidgetName} to {$newWidgetName} id {$item} for node id {$parentEntity->id()} having path alias of {$alias}";
    }
    catch (EntityStorageException $e) {
      return "There was an error saving paragraph id {$item}. This is unexpected and manual migration may be required. The error was {$e->getMessage()}";
    }
  }

}
