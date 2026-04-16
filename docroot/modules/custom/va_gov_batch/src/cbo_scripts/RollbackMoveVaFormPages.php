<?php

namespace Drupal\va_gov_batch\cbo_scripts;

require_once __DIR__ . '/../../../../../../scripts/content/script-library.php';

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\node\Entity\Node;
use Drupal\pathauto\PathautoState;

/**
 * Rollback: Revert va_form nodes back to original path pattern.
 *
 * Only needed if we have a rollback to perform.
 *
 * Original path pattern:
 *   find-forms/about-form-[field_va_form_number].
 *
 * To run: drush codit-batch-operations:run RollbackMoveVaFormPages
 */
class RollbackMoveVaFormPages extends BatchOperations implements BatchScriptInterface {

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return 'Rollback: Revert va_form nodes to find-forms/about-form-[field_va_form_number] path pattern';
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    $description = <<<ENDHERE
    Rollback script to revert va_form nodes back to original path pattern.
    Original path pattern: find-forms/about-form-[field_va_form_number].
    This script updates the path alias for each va_form node translation
    using the original pattern.
    ENDHERE;
    return $description;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return '@completed out of @total va_form nodes processed.';
  }

  /**
   * {@inheritdoc}
   */
  public function getItemType(): string {
    return 'va_form node';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    $items = [];
    try {
      $nids = $this->entityTypeManager->getStorage('node')
        ->getQuery()
        ->condition('type', 'va_form')
        ->accessCheck(FALSE)
        ->execute();

      foreach ($nids as $nid) {
        $items[] = $nid;
      }
    }
    catch (\Exception $e) {
      $message = "Error gathering va_form nodes: " . $e->getMessage();
      $this->batchOpLog->appendError($message);
    }

    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    try {
      /** @var \Drupal\node\Entity\Node $node */
      $node = Node::load($item);

      if (!$node) {
        return "Node $item not found, skipped.";
      }

      // Build once; used for all translations (pattern doesn't vary by lang).
      if (!$node->hasField('field_va_form_number') || $node->get('field_va_form_number')->isEmpty()) {
        // Skip nodes missing the source field.
        return "Node $item skipped - missing field_va_form_number.";
      }

      $form_number = trim((string) $node->get('field_va_form_number')->value);
      if ($form_number === '') {
        return "Node $item skipped - empty field_va_form_number.";
      }

      $alias_value = '/find-forms/about-form-' . $form_number;

      $node->setRevisionUserId(1317);

      // Set path alias for each translation language.
      foreach ($node->getTranslationLanguages() as $langcode => $language) {
        $translation = $node->getTranslation($langcode);
        $translation->set('path', [
          'alias' => $alias_value,
          'pathauto' => PathautoState::SKIP,
          'langcode' => $langcode,
        ]);
      }

      save_node_revision($node, "Rollback: Updated path alias to $alias_value.", TRUE);

      $message = "Node $item processed - alias set to $alias_value for all languages.";
      $this->batchOpLog->appendLog($message);
      return "Node $item processed successfully.";
    }
    catch (\Exception $e) {
      $message = "Error processing va_form node ID $item: " . $e->getMessage();
      $this->batchOpLog->appendError($message);
      return "Error processing node $item.";
    }
  }

  /**
   * Post-run processing: Update landing page path alias.
   *
   * Updates the path alias for node 2352 from /find-forms to /forms.
   *
   * @param array $sandbox
   *   The batch operation sandbox array.
   *
   * @return string
   *   Status message indicating success or failure.
   */
  public function postRun(&$sandbox): string {
    // Change the /forms path to /find-forms on the landing page.
    /** @var \Drupal\node\Entity\Node $node */
    $node = Node::load(2352);

    if (!$node) {
      $message = "Node 2352 not found, skipped.";
      $this->batchOpLog->appendError($message);
      return "Node 2352 not found, skipped.";
    }

    $node->setRevisionUserId(1317);
    $node->set('path', [
      'alias' => '/find-forms',
      'pathauto' => PathautoState::SKIP,
      'langcode' => 'en',
    ]);
    save_node_revision($node, 'Rollback: Updated path alias from /forms to /find-forms for Centralized Forms initiative.', TRUE);

    $message = "Node 2352 path alias updated from /forms to /find-forms.";
    $this->batchOpLog->appendLog($message);
    return "Node 2352 processed successfully.";
  }

  /**
   * {@inheritdoc}
   */
  public function getAllowOnlyOneCompleteRun(): bool {
    return TRUE;
  }

}
