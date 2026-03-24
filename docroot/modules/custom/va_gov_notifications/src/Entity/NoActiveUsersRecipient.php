<?php

namespace Drupal\va_gov_notifications\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines ad hoc recipients for no-active-users notifications.
 *
 * @ConfigEntityType(
 *   id = "no_active_users_recipient",
 *   label = @Translation("No active users ad hoc recipient"),
 *   label_collection = @Translation("No active users ad hoc recipients"),
 *   handlers = {
 *     "list_builder" = "Drupal\va_gov_notifications\NoActiveUsersRecipientListBuilder",
 *     "form" = {
 *       "add" = "Drupal\va_gov_notifications\Form\NoActiveUsersRecipientForm",
 *       "edit" = "Drupal\va_gov_notifications\Form\NoActiveUsersRecipientForm",
 *       "delete" = "Drupal\va_gov_notifications\Form\NoActiveUsersRecipientDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     }
 *   },
 *   admin_permission = "administer site configuration",
 *   config_prefix = "no_active_users_recipient",
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
 *     "notes"
 *   },
 *   links = {
 *     "collection" = "/admin/config/system/va-gov-notifications/no-active-users-recipients",
 *     "add-form" = "/admin/config/system/va-gov-notifications/no-active-users-recipients/add",
 *     "edit-form" = "/admin/config/system/va-gov-notifications/no-active-users-recipients/{no_active_users_recipient}",
 *     "delete-form" = "/admin/config/system/va-gov-notifications/no-active-users-recipients/{no_active_users_recipient}/delete"
 *   }
 * )
 */
class NoActiveUsersRecipient extends ConfigEntityBase implements NoActiveUsersRecipientInterface {

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
  public function isEnabled(): bool {
    return (bool) $this->status;
  }

}
