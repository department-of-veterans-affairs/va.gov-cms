<?php

namespace Drupal\va_gov_preview\EventSubscriber;

use Drupal\va_gov_preview\StaticServiceProvider;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\EventSubscriber\HttpExceptionSubscriberBase;

/**
 * Redirect pages to their static counterpart when ?_format=static_html is used.
 */
class StaticSubscriber extends HttpExceptionSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function getHandledFormats() {
    return ['html', 'static_html'];
  }

  /**
   * {@inheritdoc}
   */
  public function onException(GetResponseForExceptionEvent $event) {
    $request = $event->getRequest();
    print "onException!!!";
  }

  /**
   * Try to load Static assets when _format=static_html.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The triggered event.
   */
  public function loadStatic(GetResponseEvent $event) {

    $request = \Drupal::request();

    // When requesting static_html, load the content and return it.
    if ($request->query->get('_format') == 'static_html') {
      if (file_exists(StaticServiceProvider::urlPathToServerPath($request->server->get('REDIRECT_URL', NULL)))) {
        // @TODO: Do something.

      }
    }
  }

  /**
   * Listen to kernel.request events and call customRedirection.
   *
   * {@inheritdoc}.
   *
   * @return array
   *   Event names to listen to (key) and methods to call (value)
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['loadStatic'];
    return $events;
  }

}
