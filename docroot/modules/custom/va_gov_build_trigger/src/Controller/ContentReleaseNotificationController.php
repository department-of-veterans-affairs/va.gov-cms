<?php

namespace Drupal\va_gov_build_trigger\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface;

/**
 * Handles content release notifications.
 */
class ContentReleaseNotificationController extends ControllerBase {

  /**
   * The release state manager.
   *
   * @var \Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface
   */
  protected $releaseStateManager;

  /**
   * Get a list of the states that content release is allowed to notify about.
   *
   * Note: this is also used in the NotificationRouteProvider.
   *
   * @return array
   *   A list of release states.
   */
  public static function allowedNotifications() : array {
    return [
      ReleaseStateManager::STATE_STARTING,
      ReleaseStateManager::STATE_INPROGRESS,
      ReleaseStateManager::STATE_COMPLETE,
      ReleaseStateManager::STATE_READY,
    ];
  }

  /**
   * Constructor for the content release notification controller.
   *
   * @param \Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface $releaseStateManager
   *   The build requester service.
   */
  public function __construct(ReleaseStateManagerInterface $releaseStateManager) {
    $this->releaseStateManager = $releaseStateManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov_build_trigger.release_state_manager')
    );
  }

  /**
   * Handle a starting notification.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   An HTTP response
   */
  public function starting() {
    return $this->handle(ReleaseStateManager::STATE_STARTING);
  }

  /**
   * Handle an inprogress notification.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   An HTTP response
   */
  public function inprogress() {
    return $this->handle(ReleaseStateManager::STATE_INPROGRESS);
  }

  /**
   * Handle a complete notification.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   An HTTP response
   */
  public function complete() {
    return $this->handle(ReleaseStateManager::STATE_COMPLETE);
  }

  /**
   * Handle a ready notification.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   An HTTP response
   */
  public function ready() {
    return $this->handle(ReleaseStateManager::STATE_READY);
  }

  /**
   * Handle notifications from content release.
   *
   * @param string $state
   *   The state that we were notified about.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   An HTTP response.
   */
  protected function handle($state) {
    $is_allowed_notification = in_array($state, self::allowedNotifications());
    $can_transition = $this->releaseStateManager->canAdvanceStateTo($state);
    $can_transition = ($can_transition === ReleaseStateManager::STATE_TRANSITION_OK);

    if ($is_allowed_notification && $can_transition) {
      $this->releaseStateManager->advanceStateTo($state);
    }

    return new Response('Notification successful: ' . $state);
  }

}
