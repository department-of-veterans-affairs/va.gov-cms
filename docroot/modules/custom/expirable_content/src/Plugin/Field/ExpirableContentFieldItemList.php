<?php

declare(strict_types=1);

namespace Drupal\expirable_content\Plugin\Field;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\expirable_content\Entity\ExpirableContentType;
use Drupal\expirable_content\ExpirableContentInformationInterface;

/**
 * Computes expiration and warning dates for expirable content computed fields.
 */
class ExpirableContentFieldItemList extends FieldItemList {

  use ComputedItemListTrait {
    get as traitGet;
  }

  /**
   * {@inheritdoc}
   */
  public function get($index) {
    if ($index !== 0) {
      throw new \InvalidArgumentException('An entity can not have multiple expiration or warning values.');
    }
    return $this->traitGet($index);
  }

  /**
   * The expirable_content.information service.
   *
   * @var \Drupal\expirable_content\ExpirableContentInformationInterface
   */
  protected ExpirableContentInformationInterface $expirableContentInfo;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritDoc}
   */
  protected function computeValue() {
    $this->expirableContentInfo = \Drupal::service('expirable_content.information');
    $this->entityTypeManager = \Drupal::entityTypeManager();
    // We are utilizing the same class for both field types, so compute the
    // values accordingly.
    if ($this->getName() === 'expiration_date') {
      $this->computeExpireDate();
    }
    elseif ($this->getName() === 'warning_date') {
      $this->computeWarnDate();
    }
  }

  /**
   * Computes the expiration date for the entity.
   */
  protected function computeExpireDate(): void {
    $entity = $this->getEntity();
    if ($this->expirableContentInfo->isExpirableEntity($entity)) {
      if ($value = $this->getExpirationForEntity($entity)) {
        $this->list[0] = $this->createItem(0, $value);
      }
    }
  }

  /**
   * Computes the warning date for the entity.
   */
  protected function computeWarnDate() {
    $entity = $this->getEntity();
    if ($this->expirableContentInfo->isExpirableEntity($entity)) {
      if ($value = $this->getWarningForEntity($entity)) {
        $this->list[0] = $this->createItem(0, $value);
      }
    }
  }

  /**
   * Gets the expiration date for an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity for which to get the expiration date.
   *
   * @return int
   *   The expiration date if it could be calculated for this entity, 0
   *   otherwise.
   */
  public function getExpirationForEntity(ContentEntityInterface $entity): int {
    try {
      if ($type = $this->getExpirableContentTypeForEntity($entity)) {
        if ($entity->hasField($type->field())) {
          $value = $entity->get($type->field())->first()->getValue();
          assert(isset($value['value']));
          $base_date = DrupalDateTime::createFromTimestamp($value['value']);
          $expiration_date = clone $base_date;
          $expiration_interval = 'P' . $type->days() . 'D';
          $expiration_date->add(new \DateInterval($expiration_interval));
          return $expiration_date->getTimestamp();
        }
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('expirable_content')->error($this->t('Unable to create expiration for entity id %entity with exception: <pre>%exception</pre>', [
        '%entity' => $entity->id(),
        '%exception' => $e->getMessage(),
      ]));
    }
    return 0;
  }

  /**
   * Get the warning date for an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity for which to get the warning date.
   *
   * @return int
   *   The warning date if it could be calculated for this entity, 0 otherwise.
   */
  public function getWarningForEntity(ContentEntityInterface $entity): int {
    try {
      if ($type = $this->getExpirableContentTypeForEntity($entity)) {
        if ($expiration_timestamp = $this->getExpirationForEntity($entity)) {
          $base_date = DrupalDateTime::createFromTimestamp($expiration_timestamp);
          $warning_date = clone $base_date;
          $expiration_interval = 'P' . $type->warn() . 'D';
          $warning_date->sub(new \DateInterval($expiration_interval));
          return $warning_date->getTimestamp();
        }
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('expirable_content')->error($this->t('Unable to create warning for entity id %entity with exception: <pre>%exception</pre>', [
        '%entity' => $entity->id(),
        '%exception' => $e->getMessage(),
      ]));
    }
    return 0;
  }

  /**
   * Gets the Expirable Content Type for an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check.
   * @param bool $status
   *   TRUE to include only enabled types.
   *
   * @return \Drupal\expirable_content\Entity\ExpirableContentType|null
   *   The Expirable Content Type if it exists, or NULL otherwise.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getExpirableContentTypeForEntity(EntityInterface $entity, bool $status = TRUE): ExpirableContentType|NULL {
    $expirable_content_types = $this->entityTypeManager->getStorage('expirable_content_type')->loadByProperties([
      'status' => $status,
      'entity_type' => $entity->getEntityType()->id(),
      'entity_bundle' => $entity->bundle(),
    ]);
    if (!empty($expirable_content_types)) {
      return current($expirable_content_types);
    }
    return NULL;
  }

}
