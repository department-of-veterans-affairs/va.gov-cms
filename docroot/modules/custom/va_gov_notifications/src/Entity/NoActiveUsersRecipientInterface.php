<?php

namespace Drupal\va_gov_notifications\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for ad hoc missing-editor recipients.
 */
interface NoActiveUsersRecipientInterface extends ConfigEntityInterface {

  /**
   * Gets the recipient label.
   */
  public function label();

  /**
   * Gets the recipient email address.
   */
  public function getEmail(): string;

  /**
   * Gets optional notes for the recipient.
   */
  public function getNotes(): string;

  /**
   * Checks if the recipient is enabled.
   */
  public function isEnabled(): bool;

}
