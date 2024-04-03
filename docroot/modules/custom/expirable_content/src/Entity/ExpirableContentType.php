<?php

declare(strict_types = 1);

namespace Drupal\expirable_content\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Expirable Content type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "expirable_content_type",
 *   label = @Translation("Expirable Content type"),
 *   label_collection = @Translation("Expirable Content types"),
 *   label_singular = @Translation("expirable content type"),
 *   label_plural = @Translation("expirable contents types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count expirable contents type",
 *     plural = "@count expirable contents types",
 *   ),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\expirable_content\Form\ExpirableContentTypeForm",
 *       "edit" = "Drupal\expirable_content\Form\ExpirableContentTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\expirable_content\ExpirableContentTypeListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer expirable_content types",
 *   bundle_of = "expirable_content",
 *   config_prefix = "type",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/expirable_content_types/add",
 *     "edit-form" = "/admin/structure/expirable_content_types/manage/{expirable_content_type}",
 *     "delete-form" = "/admin/structure/expirable_content_types/manage/{expirable_content_type}/delete",
 *     "collection" = "/admin/structure/expirable_content_types",
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
final class ExpirableContentType extends ConfigEntityBundleBase {

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
