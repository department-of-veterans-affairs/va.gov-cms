<?php

namespace Drupal\va_gov_bulk\Plugin\Action;

use Drupal\node\NodeInterface;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\va_gov_bulk\AdminModeration;

/**
 * An example action covering most of the possible options.
 *
 * If type is left empty, action will be selectable for all
 * entity types.
 *
 * @Action(
 *   id = "publish_latest_revision_action",
 *   label = @Translation("Publish Latest Revision"),
 *   type = "node",
 *   confirm = TRUE,
 * )
 */
class PublishLatestRevisionAction extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    /*
     * All config resides in $this->configuration.
     * Passed view rows will be available in $this->context.
     * Data about the view used to select results and optionally
     * the batch context are available in $this->context or externally
     * through the public getContext() method.
     * The entire ViewExecutable object  with selected result
     * rows is available in $this->view or externally through
     * the public getView() method.
     */
    \Drupal::Messenger()->addStatus(utf8_encode('Publish bulk operation by va_gov_bulk module'));
    \Drupal::logger('ADMIN_MODERATION')->notice("EXECUTING PUBLISH LATEST REVISION OF " . $entity->label());

    $adminModeration = new AdminModeration($entity, NodeInterface::PUBLISHED);
    $entity = $adminModeration->publish();

    // Check if published.
    if (!$entity->isPublished()) {
      $msg = "Something went wrong, the entity must be published by this point.  Review your content moderation configuration make sure you have archive state which sets current revision and a published state and try again.";
      \Drupal::Messenger()->addError(utf8_encode($msg));
      \Drupal::logger('ADMIN_MODERATION')->warning($msg);
      return $msg;
    }

    return sprintf('Example action (configuration: %s)', print_r($this->configuration, TRUE));
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object->getEntityType() === 'node') {
      $access = $object->access('update', $account, TRUE)
        ->andIf($object->status->access('edit', $account, TRUE));
      return $return_as_object ? $access : $access->isAllowed();
    }

    // Other entity types may have different
    // access methods and properties.
    return TRUE;
  }

}
