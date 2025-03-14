<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\taxonomy\TermInterface;

/**
 * For VACMS-20606.
 *
 * @see https://github.com/department-of-veterans-affairs/va.gov-cms/issues/20606
 */
class UpdateManilaContent extends BatchOperations implements BatchScriptInterface {

  /**
   * {@inheritdoc}
   */
  public function getTitle():string {
    return "Resaves 'VA Manila health care' content";
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription():string {
    return <<<ENDHERE
    Manila is changing from a special VAMC System to a typical one.
    Thus, we are changing the section to follow typical naming conventions.
    ENDHERE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return '@total node updates were attempted. @completed was completed.';
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
  // The Manila VA Clinic term
  $manila_term_id = '1187';

  return \Drupal::entityQuery('node')
    ->condition('field_administration.target_id', $manila_term_id)
    ->accessCheck(FALSE)
    ->execute();
  }

  /**
   * {@inheritdoc}
   *
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
  /** @var \Drupal\node\NodeStorageInterface $nodeStorage */
  $nodeStorage = $this->entityTypeManager->getStorage('node');
  /** @var \Drupal\node\NodeInterface|null $node */
  $node = $nodeStorage->load($item);
  if (!$node) {
    return "There was a problem loading node id {$item}. Further investigation is needed. Skipping.";
  }
  return $this->updateNode($node);

  }

  /**
   * Renames the term.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to update
   *
   */
  public function updateNode(NodeInterface $node):string {
    $node_id = $node->id();
    $node_revisions = $this->getNodeAllRevisions($node_id);
    foreach ($node_revisions as $revision) {
      $revision->path->pathauto = 1;
      $result = \Drupal::service('pathauto.generator')->updateEntityAlias($revision, 'bulkupdate', ['force' => TRUE]);
      $this->saveNodeRevision($revision, "Saved");
      $this->batchOpLog->appendLog("Updated node $node_id");
      return "Updated $node_id";
    }

  }

}
