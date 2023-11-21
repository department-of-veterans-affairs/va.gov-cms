<?php

namespace Drupal\va_gov_vamc\EventSubscriber;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityAccessEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityViewEvent;
use Drupal\node\NodeInterface;
use Drupal\va_gov_facilities\FacilityOps;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VAMC Entity Event Subscriber.
 */
class VAMCEntityPreventReuse implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * The Drupal Messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   *  The Drupal Messenger.
   */
  protected $messenger;

  /**
   * The route match object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The User Perms Service.
   *
   * @var \Drupal\va_gov_user\Service\UserPermsService
   */
  protected $userPermsService;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The current user.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match object.
   * @param \Drupal\va_gov_user\Service\UserPermsService $user_perms_service
   *   The user perms service.
   */
  public function __construct(
    Connection $database,
    MessengerInterface $messenger,
    RouteMatchInterface $route_match,
    UserPermsService $user_perms_service,
  ) {
    $this->database = $database;
    $this->messenger = $messenger;
    $this->routeMatch = $route_match;
    $this->userPermsService = $user_perms_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_ACCESS => 'entityAccess',
      EntityHookEvents::ENTITY_VIEW => 'entityView',
    ];
  }

  /**
   * Alteration to entity view pages.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewEvent $event
   *   The entity view alter service.
   */
  public function entityView(EntityViewEvent $event):void {
    $entity = $event->getEntity();
    if ($entity instanceof NodeInterface) {
      // Call this so admins see the message because admins skip node_access.
      $this->isRestricted($entity, 'view');
    }
  }

  /**
   * Alteration to entity view pages.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityAccessEvent $event
   *   The entity view alter service.
   */
  public function entityAccess(EntityAccessEvent $event) {
    $node = $event->getEntity();
    $op = $event->getOperation();
    if ($node instanceof NodeInterface && $this->isRestricted($node, $op)) {
      $tokens = ['@content_type' => $node->type->entity->label()];
      $reason_message = (string) $this->t('This @content_type has already been unpublished, it can no longer be edited.', $tokens);
      // Like other perms in Drupal, grants are additive, so we have to pull
      // this one grant out explicitly to operate only on the basis of
      // restricting to this grant.
      $result = AccessResult::forbiddenIf(TRUE, $reason_message);
      $result->addCacheTags(['tag_from_va_gov_vamc_node_access']);
      $result->cachePerUser();
      // Cache for a week.  This value should not change often.
      $result->setCacheMaxAge(604800);
      $event->setAccessResult($result);
    }
  }

  /**
   * Checks to see if the node moved from published to unpublished.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return bool
   *   TRUE if the node moved from published to unpublished. FALSE otherwise.
   */
  public static function isAnUnpublishEvent(NodeInterface $node): bool {
    $new_state = $node->isPublished();
    $previous_state = (!empty($node->original)) ? $node->original->isPublished() : 0;
    if (!$new_state && $previous_state) {
      // This was published on the previous revision, but is no longer.
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Checks to see if there are previously published revisions for the node.
   *
   * This is a heavier check. Use isAnUnpublishEvent() first, it's lighter.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node to check for published revisions.
   *
   * @return bool
   *   TRUE if it had previously published revisions. False otherwise.
   */
  public static function hasPublishedRevisions(NodeInterface $node): bool {
    if ($node->isPublished()) {
      // This is still published so no need to look for previous publishes.
      return FALSE;
    }
    $storage = \Drupal::entityTypeManager()
      ->getStorage('node');

    $count = $storage->getQuery()
      ->condition('nid', $node->id())
      ->condition('status', 1)
      ->allRevisions()
      ->count()
      ->accessCheck(FALSE)
      ->execute();
    // This has a published revision.
    return $count >= 1;
  }

  /**
   * Checks to see if the node is restricted by specific access grant.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node to check for restriction.
   * @param string $op
   *   The operation view, update, delete.
   *
   * @return bool
   *   TRUE if the node operation is restricted by node access grant,
   *   FALSE otherwise.
   */
  public function isRestricted(NodeInterface $node, $op): bool {
    $is_restricted = FALSE;
    $restricted_ops = [
      'clone',
      'delete',
      'update',
      'view',
    ];
    if (in_array($op, $restricted_ops) && $node->bundle() === 'full_width_banner_alert') {
      // Check the node for an existing access grant of this type.
      $select = $this->database
        ->select('node_access', 'na')
        ->fields('na', ['grant_view', 'grant_update', 'grant_delete'])
        ->condition('na.realm', 'node_reuse_restrict', '=')
        ->condition('na.gid', FacilityOps::getFacilityTypeSectionId('vamc'), '=')
        ->condition('nid', $node->id());
      $result = $select->execute()->fetchObject();
      $op_grant_name = "grant_{$op}";
      if (!empty($result)) {
        $route = $this->routeMatch->getRouteName();
        if ($route === 'entity.node.canonical') {
          // Note: For admins, this message will only be seen once because
          // admins bypass most access checks.  All others will see this as
          // persistent on node view.
          $tokens = ['@content_type' => $node->type->entity->label()];
          $reason_message = (string) $this->t('This @content_type has already been unpublished, it can no longer be edited.', $tokens);
          $this->messenger->addWarning($reason_message);
        }
      }
      if (!empty($result) && empty($result->$op_grant_name)) {
        $is_restricted = TRUE;
      }
      elseif (!empty($result) && $op === 'clone') {
        // Clone isn't covered by grants, we have to force based on grant exist.
        $is_restricted = TRUE;
      }
    }

    return $is_restricted;
  }

  /**
   * Evaluates the node to see if should get locked for editing.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node to check for restriction.
   *
   * @return array
   *   An array of grant data, or empty array.
   */
  public static function buildGrantForEditLock(NodeInterface $node): array {
    $grants = [];
    if (($node->bundle() === 'full_width_banner_alert')
      && !$node->isPublished()
      && (self::isAnUnpublishEvent($node) || self::hasPublishedRevisions($node))
    ) {
      // The node should be locked because it was published but is not anymore.
      $grants[] = [
        'langcode' => $node->get('langcode')->value,
        'gid' => FacilityOps::getFacilityTypeSectionId('vamc'),
        'realm' => 'node_reuse_restrict',
        'grant_view' => 1,
        'grant_update' => 0,
        'grant_delete' => 0,
      ];
    }
    return $grants;
  }

}
