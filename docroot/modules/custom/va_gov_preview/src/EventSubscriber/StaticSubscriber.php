<?php

namespace Drupal\va_gov_preview\EventSubscriber;

use Drupal\Core\Link;
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
    $config = \Drupal::service('config.factory')->getEditable('va_gov.build');
    if ($config->get('web.build.pending', 0) && $request->query->get('_format') != 'static_html') {

      // Only show messages to users with access.]// Get the current user.
      $user = \Drupal::currentUser();
      if ($user->isAuthenticated() && $user->hasPermission('access content')) {
        drupal_set_message(t('Web Rebuild & Deploy is in progress: See the %link for status.', [
          '%link' => Link::createFromRoute(t('Build & Deploy Page'), 'va_gov_build_trigger.build_trigger_form')->toString(),
        ]), 'warning');

        if (getenv('CMS_ENVIRONMENT_TYPE') == 'lando') {
          drupal_set_message(t('You are using Lando. Run the command <code>lando composer va:web:build</code> to rebuild the front-end and unlock this form.'), 'warning');
        }
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
