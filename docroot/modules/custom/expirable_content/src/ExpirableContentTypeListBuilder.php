<?php declare(strict_types = 1);

namespace Drupal\expirable_content;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

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
    $header['label'] = $this->t('Label');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    $row['label'] = $entity->label();
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render(): array {
    $build = parent::render();

    $build['table']['#empty'] = $this->t(
      'No expirable content types available. <a href=":link">Add expirable content type</a>.',
      [':link' => Url::fromRoute('entity.expirable_content_type.add_form')->toString()],
    );

    return $build;
  }

}
