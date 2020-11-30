<?php

namespace Drupal\va_gov_preview\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Show deploy status messages to the user.
 */
class DeployStatusMessagesSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Drupal\Core\Session\AccountProxyInterface definition.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $stringTranslation;

  /**
   * DeployStatusMessagesSubscriber constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory service.
   * @param Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AccountProxyInterface $current_user, MessengerInterface $messenger, TranslationInterface $string_translation) {
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
    $this->messenger = $messenger;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Determine whether we should show deploy status messages.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The triggered event.
   *
   * @return bool
   *   TRUE if we should show deploy status messages, FALSE if not.
   */
  protected function showDeployStatusMessages(GetResponseEvent $event) {
    $config = $this->configFactory->getEditable('va_gov.build');

    return $config->get('web.build.pending', 0) &&
      $this->currentUser->isAuthenticated() &&
      $this->currentUser->hasPermission('access content');
  }

  /**
   * Request event responder.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The triggered event.
   */
  public function onRequest(GetResponseEvent $event) {
    if ($event->getRequest()->getRequestFormat() !== 'html') {
      return;
    }

    if ($this->showDeployStatusMessages($event)) {
      $message = $this->t('Web Rebuild & Deploy is in progress: See %link for status.', [
        '%link' => Link::createFromRoute($this->t('Build & Deploy Page'), 'va_gov_build_trigger.build_trigger_form')->toString(),
      ]);
      $this->messenger->addWarning($message);
    }

    if ($this->showDeployStatusMessages($event) && (getenv('CMS_ENVIRONMENT_TYPE') === 'lando')) {
      $this->messenger->addWarning(
        $this->t('You are using Lando. Run the command <code>lando composer va:web:build</code> to rebuild the front-end and unlock this form.')
      );
    }
  }

  /**
   * Listen to kernel.request events and call onRequest.
   *
   * {@inheritdoc}.
   *
   * @return array
   *   Event names to listen to (key) and methods to call (value)
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => 'onRequest'];
  }

}
