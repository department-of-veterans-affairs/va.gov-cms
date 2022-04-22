<?php

namespace Drupal\va_gov_notifications\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\message\Entity\Message;
use Drupal\message_notify\MessageNotifier;

/**
 * Service used for VA notifications.
 */
class NotificationsManager {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * The message notifier (sender).
   *
   * @var \Drupal\message_notify\MessageNotifier
   */
  public $messageNotifier;

  /**
   * Constructs the NotificationManager object.
   *
   * @param \Drupal\message_notify\MessageNotifier $message_notifier
   *   Message notifier (sender).
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(MessageNotifier $message_notifier, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->messageNotifier = $message_notifier;
  }

  /**
   * Sends a message using a template.
   *
   * @param string $template_name
   *   The machine name of the template to use.
   * @param int $user_id
   *   The user id to send the notification to.
   * @param array $values
   *   Array of key value pairs that will be passed to the template.
   *
   * @return bool
   *   Boolean value denoting success or failure of the notification.
   */
  public function send($template_name, $user_id, array $values = []) : bool {
    $message = Message::create(['template' => $template_name, 'uid' => $user_id]);
    foreach ($values as $field_name => $value) {
      $message->set($field_name, $value);
    }
    // $message->set('field_published', $comment->isPublished());
    $message->save();
    // Send message to message user specified user.
    return $this->messageNotifier->send($message);
  }

  /**
   * Determines if the node provided is test data.
   *
   * This is a hack to bypass flagging during phpunit tests.  The Flag interface
   * is not mocked so the contrib module throws fatal errors when test data
   * attempts to create a flag.  It is not ideal, but is a better alternative
   * than removing the phpunit save  tests.
   *
   * @return bool
   *   TRUE if test data detected, FALSE otherwise.
   */
  protected function isTestData() : bool {
    // Detect if phpunit is running.
    return (defined('PHPUNIT_COMPOSER_INSTALL')) ? TRUE : FALSE;
  }

}
