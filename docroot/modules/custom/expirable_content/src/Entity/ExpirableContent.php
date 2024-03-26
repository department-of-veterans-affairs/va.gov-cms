<?php declare(strict_types = 1);

namespace Drupal\expirable_content\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\expirable_content\ExpirableContentInterface;

/**
 * Defines the expirable content entity class.
 *
 * @ContentEntityType(
 *   id = "expirable_content",
 *   label = @Translation("Expirable Content"),
 *   label_collection = @Translation("Expirable Content"),
 *   label_singular = @Translation("expirable content"),
 *   label_plural = @Translation("expirable content"),
 *   label_count = @PluralTranslation(
 *     singular = "@count expirable content",
 *     plural = "@count expirable content",
 *   ),
 *   bundle_label = @Translation("Expirable Content type"),
 *   handlers = {
 *     "list_builder" = "Drupal\expirable_content\ExpirableContentListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\expirable_content\Form\ExpirableContentForm",
 *       "edit" = "Drupal\expirable_content\Form\ExpirableContentForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\expirable_content\Routing\ExpirableContentHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "expirable_content",
 *   admin_permission = "administer expirable_content types",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "bundle",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/expirable-content",
 *     "add-form" = "/expirable-content/add/{expirable_content_type}",
 *     "add-page" = "/expirable-content/add",
 *     "canonical" = "/expirable-content/{expirable_content}",
 *     "edit-form" = "/expirable-content/{expirable_content}",
 *     "delete-form" = "/expirable-content/{expirable_content}/delete",
 *     "delete-multiple-form" = "/admin/content/expirable-content/delete-multiple",
 *   },
 *   bundle_entity_type = "expirable_content_type",
 *   field_ui_base_route = "entity.expirable_content_type.edit_form",
 * )
 */
final class ExpirableContent extends ContentEntityBase implements ExpirableContentInterface {

}
