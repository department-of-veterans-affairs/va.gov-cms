<?php

declare(strict_types = 1);

namespace Drupal\expirable_content;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a class to build a listing of expirable content type entities.
 *
 * @see \Drupal\expirable_content\Entity\ExpirableContentType
 */
final class ExpirableContentTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['entity_type'] = $this->t('Entity type');
    $header['entity_bundle'] = $this->t('Entity bundle');
    $header['id'] = $this->t('id');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\expirable_content\Entity\ExpirableContentType $entity */
    $row['entity_type'] = $entity->entityType();
    $row['entity_bundle'] = $entity->entityBundle();
    $row['id'] = $entity->id();
    $row['status'] = $entity->status() ? $this->t('Enabled') : $this->t('Disabled');
    return $row + parent::buildRow($entity);
  }

}
