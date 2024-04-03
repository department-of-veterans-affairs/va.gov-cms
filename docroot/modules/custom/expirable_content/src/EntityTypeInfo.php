<?php

declare(strict_types=1);

namespace Drupal\expirable_content;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\expirable_content\Plugin\Field\ExpirableContentFieldItemList;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Alters entities for expirable content.
 */
class EntityTypeInfo implements ContainerInjectionInterface {

  /**
   * The expirable_content.information service.
   *
   * @var \Drupal\expirable_content\ExpirableContentInformationInterface
   */
  protected ExpirableContentInformationInterface $expirableContentInfo;

  /**
   * Constructs a new EntityTypeInfo object.
   *
   * @param \Drupal\expirable_content\ExpirableContentInformationInterface $expirableContentInfo
   *   The expirable_content.information service.
   */
  public function __construct(ExpirableContentInformationInterface $expirableContentInfo) {
    $this->expirableContentInfo = $expirableContentInfo;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('expirable_content.information')
    );
  }

  /**
   * Adds computed expiration and warning fields to an entity type.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   Entity type for adding base fields to.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition[]
   *   New fields added.
   *
   * @see hook_entity_base_field_info()
   */
  public function entityBaseFieldInfo(EntityTypeInterface $entity_type): array {
    if (!$this->expirableContentInfo->isExpirableEntityType($entity_type)) {
      return [];
    }
    $fields = [];
    $fields['expiration_date'] = BaseFieldDefinition::create('timestamp')
      ->setLabel('Expiration date')
      ->setDescription(t('The date this entity will expire or has expired.'))
      ->setComputed(TRUE)
      ->setClass(ExpirableContentFieldItemList::class)
      ->setReadOnly(FALSE);

    $fields['warning_date'] = BaseFieldDefinition::create('timestamp')
      ->setLabel('Warning date')
      ->setDescription(t('The date the entity warning is established.'))
      ->setComputed(TRUE)
      ->setClass(ExpirableContentFieldItemList::class)
      ->setReadOnly(FALSE);

    return $fields;
  }

}
