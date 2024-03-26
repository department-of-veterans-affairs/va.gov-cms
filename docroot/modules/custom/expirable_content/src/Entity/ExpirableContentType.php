<?php declare(strict_types = 1);

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
 *   config_prefix = "expirable_content_type",
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
 *     "uuid",
 *   },
 * )
 */
final class ExpirableContentType extends ConfigEntityBundleBase {

  /**
   * The machine name of this expirable content type.
   */
  protected string $id;

}
