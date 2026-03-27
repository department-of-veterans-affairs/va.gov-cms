<?php

namespace Drupal\va_gov_notifications\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines product owner contacts for VA.gov notifications.
 *
 * @ConfigEntityType(
 *   id = "product_owner_contact",
 *   label = @Translation("Product owner contact"),
 *   label_collection = @Translation("Product owner contacts"),
 *   handlers = {
 *     "list_builder" = "Drupal\va_gov_notifications\ProductOwnerContactListBuilder",
 *     "form" = {
 *       "add" = "Drupal\va_gov_notifications\Form\ProductOwnerContactForm",
 *       "edit" = "Drupal\va_gov_notifications\Form\ProductOwnerContactForm",
 *       "delete" = "Drupal\va_gov_notifications\Form\ProductOwnerContactDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     }
 *   },
 *   admin_permission = "administer site configuration",
 *   config_prefix = "product_owner_contact",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "status" = "status"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "status",
 *     "email",
 *     "notes",
 *     "products"
 *   },
 *   links = {
 *     "collection" = "/admin/people/product-owner-contacts",
 *     "add-form" = "/admin/people/product-owner-contacts/add",
 *     "edit-form" = "/admin/people/product-owner-contacts/{product_owner_contact}",
 *     "delete-form" = "/admin/people/product-owner-contacts/{product_owner_contact}/delete"
 *   }
 * )
 */
class ProductOwnerContact extends ConfigEntityBase implements ProductOwnerContactInterface {

  /**
   * Product options keyed by product term ID.
   */
  public const PRODUCT_OPTIONS = [
    '284' => 'VAMC',
    '289' => 'Vet Center',
    '1050' => 'VBA',
    '1000' => 'NCA',
  ];

  /**
   * The recipient machine ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable label.
   *
   * @var string
   */
  protected $label;

  /**
   * The recipient email address.
   *
   * @var string
   */
  protected $email = '';

  /**
   * Optional notes.
   *
   * @var string
   */
  protected $notes = '';

  /**
   * Product term IDs this recipient applies to.
   *
   * Empty means all products.
   *
   * @var string[]
   */
  protected $products = [];

  /**
   * Whether this recipient is enabled.
   *
   * @var bool
   */
  protected $status = TRUE;

  /**
   * {@inheritdoc}
   */
  public function getEmail(): string {
    return (string) $this->email;
  }

  /**
   * {@inheritdoc}
   */
  public function getNotes(): string {
    return (string) $this->notes;
  }

  /**
   * {@inheritdoc}
   */
  public function getProducts(): array {
    return array_values(array_filter(array_map('strval', (array) $this->products)));
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled(): bool {
    return (bool) $this->status;
  }

}
