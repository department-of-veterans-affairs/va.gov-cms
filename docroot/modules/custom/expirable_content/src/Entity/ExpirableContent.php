<?php

declare(strict_types = 1);

namespace Drupal\expirable_content\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\expirable_content\ExpirableContentInterface;

/**
 * Defines the expirable content entity type.
 *
 * @ConfigEntityType(
 *   id = "expirable_content",
 *   label = @Translation("Expirable Content"),
 *   label_collection = @Translation("Expirable Content"),
 *   label_singular = @Translation("expirable content"),
 *   label_plural = @Translation("expirable content"),
 *   label_count = @PluralTranslation(
 *     singular = "@count expirable content",
 *     plural = "@count expirable content",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\expirable_content\ExpirableContentListBuilder",
 *     "form" = {
 *       "add" = "Drupal\expirable_content\Form\ExpirableContentForm",
 *       "edit" = "Drupal\expirable_content\Form\ExpirableContentForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *   },
 *   config_prefix = "expirable_content",
 *   admin_permission = "administer expirable_content",
 *   links = {
 *     "collection" = "/admin/structure/expirable-content",
 *     "add-form" = "/admin/structure/expirable-content/add",
 *     "edit-form" = "/admin/structure/expirable-content/{expirable_content}",
 *     "delete-form" = "/admin/structure/expirable-content/{expirable_content}/delete",
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *   },
 *   config_export = {
 *     "id",
 *     "status",
 *     "field",
 *     "days",
 *     "warn",
 *     "entity_type",
 *     "entity_bundle",
 *   },
 * )
 */
final class ExpirableContent extends ConfigEntityBase implements ExpirableContentInterface {

  /**
   * The entity ID.
   */
  protected string $id;

  /**
   * The Field to use as base for expiration calculation.
   *
   * This needs to be a date field.
   *
   * @var string
   */
  protected string $field = '';

  /**
   * Expire the entity this number of days since the last change to an entity.
   *
   * Uses the date from the base field to calculate the expiration.
   *
   * @var int
   */
  protected int $days = 0;

  /**
   * A number of days before an entity expires.
   *
   * @var int
   */
  protected int $warn = 0;

  /**
   * An entity type with content to have expiration enabled.
   *
   * @var string
   */
  protected string $entity_type = '';

  /**
   * An entity bundle with content to have expiration enabled.
   *
   * @var string
   */
  protected string $entity_bundle = '';

  /**
   * Getter for field.
   *
   * @return string
   *   The field to calculate expiration from.
   */
  public function field(): string {
    return $this->field;
  }

  /**
   * Getter for days.
   *
   * @return int
   *   The number of days to expire the content.
   */
  public function days(): int {
    return $this->days;
  }

  /**
   * Getter for warn.
   *
   * @return int
   *   The number of days before warning content.
   */
  public function warn(): int {
    return $this->warn;
  }

  /**
   * Getter for entityType.
   *
   * @return string
   *   The entity type id.
   */
  public function entityType(): string {
    return $this->entity_type;
  }

  /**
   * Getter for entityBundle.
   *
   * @return string
   *   The entity bundle.
   */
  public function entityBundle(): string {
    return $this->entity_bundle;
  }

}
