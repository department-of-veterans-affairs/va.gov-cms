<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\NodeInterface;
use League\Csv\Reader;
use League\Csv\Statement;

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
   * The path to the source of truth for the data migration.
   *
   * @var string
   */
  const DATA_FILE_LOCATION = __DIR__ . '/../../data/NodesForMHVReactWidgetMigration.csv';

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
    return 'node';
  }

  /**
   * {@inheritdoc}
   *
   * @throws \League\Csv\UnavailableStream
   * @throws \League\Csv\Exception
   */
  public function gatherItemsToProcess(): array {
    // Items to process represent Node IDs which we gather from the source csv.
    $csvReader = Reader::createFromPath(self::DATA_FILE_LOCATION);
    $csvReader->setHeaderOffset(0);
    $records = (new Statement())->process($csvReader);
    $nids = [];
    foreach ($records->fetchColumnByOffset(0) as $value) {
      $nids[$value] = $value;
    }
    return $nids;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   * @throws \Drupal\Core\Entity\EntityMalformedException
   *   Thrown if the toUrl() could not find an entity id.
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    /** @var \Drupal\node\NodeStorageInterface $nodeStorage */
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    /** @var \Drupal\node\NodeInterface|null $node */
    $node = $nodeStorage->load($item);
    if (!$node) {
      return "There was a problem loading node id {$item}. Further investigation is needed. Skipping.";
    }
    return $this->updateWidget($node);
  }

  /**
   * Updates the node's React widget to the modern name.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to update.
   *
   * @return string
   *   The message indicating the action taken when updating.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   *   Thrown if the toUrl() could not find an entity id.
   */
  private function updateWidget(NodeInterface $node): string {
    $path = $node->toUrl()->toString();
    $nodeMessage = "node_{$node->id()}: {$node->getTitle()}:{$path}: ";
    // Store message for each widget we update. While unlikely that a node will
    // have multiple widgets, we need to ensure we are not missing any updates.
    $updates = [];
    // The widget will always be in field field_content_block as a paragraph.
    /** @var \Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem $item */
    foreach ($node->get('field_content_block') as $item) {
      /** @var \Drupal\paragraphs\ParagraphInterface $paragraph */
      $paragraph = $item->entity;
      if ($paragraph->bundle() === 'react_widget') {
        $currentWidgetName = $paragraph->get(self::WIDGET_FIELD_NAME)->value;
        if (in_array($currentWidgetName, array_keys(self::WIDGET_MAP))) {
          try {
            $newWidgetName = self::WIDGET_MAP[$currentWidgetName];
            // Update the name of this widget to the new MHV widget name.
            $paragraph->set(self::WIDGET_FIELD_NAME, $newWidgetName);
            $paragraph->save();
            $updates[] = $nodeMessage . "updated widget from {$currentWidgetName} to {$newWidgetName} in paragraph id {$paragraph->id()}";
          }
          catch (EntityStorageException $e) {
            $this->batchOpLog->appendError($nodeMessage . "Error saving paragraph id {$paragraph->id()}. This is unexpected and manual migration may be required. The error was {$e->getMessage()}");
          }
        }
        else {
          $this->batchOpLog->appendLog($nodeMessage . "node has a React widget {$currentWidgetName} but it is not in target list.");
        }
      }
    }
    if (count($updates) > 0) {
      array_walk($updates, fn($message) => $this->batchOpLog->appendLog($message));
      $count = count($updates);
      return $nodeMessage . "{$count} widget update(s) made.";
    }
    else {
      return $nodeMessage . "no widget updates were made.";
    }
  }

}
