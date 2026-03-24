<?php

namespace Drupal\va_gov_notifications;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\va_gov_notifications\Entity\NoActiveUsersRecipientInterface;

/**
 * Lists ad hoc recipient config entities.
 */
class NoActiveUsersRecipientListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['label'] = $this->t('Name');
    $header['email'] = $this->t('Email');
    $header['status'] = $this->t('Status');
    $header['notes'] = $this->t('Notes');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    if (!$entity instanceof NoActiveUsersRecipientInterface) {
      return parent::buildRow($entity);
    }

    $row['label'] = $entity->label();
    $row['email'] = $entity->getEmail();
    $row['status'] = $entity->status() ? $this->t('Enabled') : $this->t('Disabled');
    $row['notes'] = $entity->getNotes();
    return $row + parent::buildRow($entity);
  }

}
