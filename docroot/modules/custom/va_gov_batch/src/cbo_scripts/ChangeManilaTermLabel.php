<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\taxonomy\TermInterface;

/**
 * For VACMS-20606.
 *
 * @see https://github.com/department-of-veterans-affairs/va.gov-cms/issues/20606
 */
class ChangeManilaTermLabel extends BatchOperations implements BatchScriptInterface {

  /**
   * {@inheritdoc}
   */
  public function getTitle():string {
    return "Changes the name of the 'Manila VA Clinic' Section to 'VA Manila health care'";
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
    return 'A total of @total term update was attempted. @completed was completed.';
  }

  /**
   * {@inheritdoc}
   */
  public function getItemType(): string {
    return 'term';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    // The Manila VA Clinic term.
    $manila_term_id = '1187';

    return [$manila_term_id];
  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    /** @var \Drupal\taxonomy\TermInterface $term_storage */
    $term_storage = $this->getTermStorage();

    $term = $term_storage->load($item);
    if (!$term) {
      return "There was a problem loading term {$item}. Further investigation is needed. Skipping.";
    }
    return $this->renameTerm($term, $item);
  }

  /**
   * Renames the term.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   The term to change.
   * @param string $tid
   *   The term id.
   */
  public function renameTerm(TermInterface $term, string $tid) {
    $new_name = "VA Manila health care";
    $old_name = $term->getName();
    try {
      $term->setNewRevision(TRUE);
      // Setting revision as CMS migrator.
      // Core bug: setting the user is not reflected in term revisions.
      $term->setRevisionUserId(1317);
      $term->setRevisionCreationTime(time());
      $term->setChangedTime(time());
      $term->setSyncing(TRUE);
      $term->setValidationRequired(FALSE);
      $term->setName($new_name);
      $term->setRevisionLogMessage("CMS Migrator changed the term from '$old_name' to '$new_name'.");
      $term->isDefaultRevision(TRUE);
      $term->save();
      // Repeated save to clear out revision log.  There is an intentional bit
      // in core that if the revision_log is not intentionally set to empty
      // it will carry forward with each save.
      $term->setNewRevision(TRUE);
      $term->setRevisionLogMessage('');
      $term->isDefaultRevision(TRUE);
      $term->save();
      return "Term $tid changed from '$old_name' to '$new_name'.";
    }
    catch (EntityStorageException $e) {
      $this->batchOpLog->appendError('The error was {$e->getMessage()}');
      return "An error was encountered trying to change '$old_name' to '$new_name': {$e->getMessage()}";

    }
  }

}
