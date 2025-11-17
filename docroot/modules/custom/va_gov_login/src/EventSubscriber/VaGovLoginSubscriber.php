<?php

namespace Drupal\va_gov_login\EventSubscriber;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableRedirectResponse;
use Drupal\Core\EventSubscriber\HttpExceptionSubscriberBase;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * Redirects anonymous users to <front> on 403.
 */
class VaGovLoginSubscriber extends HttpExceptionSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function getHandledFormats() {
    // Return an array of formats this subscriber handles, e.g. ['html'].
    return ['html'];
  }

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new VaGovLoginSubscriber.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * Redirect anonymous users to <front> on 403.
   *
   * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
   *   The Event to process.
   */
  public function onException(ExceptionEvent $event) {
    if ($this->currentUser->isAnonymous()) {

      $redirectPath = "/";

      $response = new CacheableRedirectResponse($redirectPath);
      // Add caching dependencies so the cache of the redirection will be
      // updated when necessary.
      $cacheableMetadata = new CacheableMetadata();
      // Add original 403 response cache metadata.
      $cacheableMetadata->addCacheableDependency($event->getThrowable());
      // We still need to add the client error tag manually since the core
      // wil not recognize our redirection as an error.
      $cacheableMetadata->addCacheTags(['4xx-response']);
      // Attach cache metadata to the response.
      $response->addCacheableDependency($cacheableMetadata);

      $event->setResponse($response);

    }

  }

}
