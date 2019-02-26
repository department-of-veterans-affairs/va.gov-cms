<?php

namespace Drupal\va_gov_backend\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Extend the EventSubscriberInterface class.
 */
class VagovbackendSubscriber implements EventSubscriberInterface {

  /**
   * Hook_init isn't in D8. This is a way to test logic on page load.
   */
  public function checkForPageLoad(GetResponseEvent $event) {
    if ($event->getRequest()->query->get('some-string')) {
      // Do stuff.
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['checkForPageLoad'];
    return $events;
  }

}
