<?php

namespace Drupal\va_gov_notifications;

use Drupal\advancedqueue\Job;
use Drupal\message\Entity\Message;

/**
 * A common interface for all VA.gov notification AdvancedQueue job types.
 */
interface JobTypeMessageNotifyBaseInterface {

  /**
   * Return the message for Message send failure.
   *
   * @param \Drupal\advancedqueue\Job $job
   *   An advancedqueue job entity.
   * @param \Drupal\message\Entity\Message $message
   *   A Message entity.
   *
   * @return string
   *   The error message text.
   */
  public function getErrorMessage(Job $job, Message $message): string;

  /**
   * Return the message for Message send success.
   *
   * @param \Drupal\advancedqueue\Job $job
   *   An advancedqueue job entity.
   * @param \Drupal\message\Entity\Message $message
   *   A Message entity.
   *
   * @return string
   *   The success message text.
   */
  public function getSuccessMessage(Job $job, Message $message): string;

  /**
   * Return the message for Message send success.
   *
   * @param \Drupal\advancedqueue\Job $job
   *   An advancedqueue job entity.
   * @param \Drupal\message\Entity\Message $message
   *   A Message entity.
   *
   * @return string
   *   The success message text.
   */
  public function getRestrictedRecipientMessage(Job $job, Message $message): string;

  /**
   * Creates a notification Message entity from a Job's payload.
   *
   * @param array $payload
   *   The current job payload.
   * @param bool $save
   *   TRUE to save and persist the message in the database.
   *
   * @return \Drupal\message\Entity\Message
   *   The newly created Message entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   Thrown when the Message entity cannot be saved.
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *   Thrown when the template_values array key is missing from the payload.
   */
  public function createMessage(array $payload, bool $save = TRUE): Message;

  /**
   * Populates a Message with values from the Job's payload.
   *
   * @param \Drupal\message\Entity\Message $message
   *   The current message entity.
   * @param array $payload
   *   The current payload values.
   */
  public function populateMessage(Message $message, array $payload): void;

  /**
   * Determines if the message should be sent.
   *
   * @param \Drupal\message\Entity\Message $message
   *   The current message entity.
   * @param array $payload
   *   The current payload values.
   *
   * @return bool
   *   TRUE is the message is allowed to send.
   */
  public function allowedToSend(Message $message, array $payload): bool;

}
