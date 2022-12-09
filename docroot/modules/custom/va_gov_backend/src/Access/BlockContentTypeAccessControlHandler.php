<?php

namespace Drupal\va_gov_backend\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access control handler for block content type config entities.
 */
class BlockContentTypeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  public $viewLabelOperation = TRUE;

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    // Bundle label is not privileged information.
    if ($operation === 'view label') {
      return AccessResult::allowed();
    }
    return parent::checkAccess($entity, $operation, $account);
  }

}
