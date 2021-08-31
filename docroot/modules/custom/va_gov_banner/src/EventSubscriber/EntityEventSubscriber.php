<?php

namespace Drupal\va_gov_banner\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VAMC Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {
  /**
   * The User Perms Service.
   *
   * @var \Drupal\va_gov_user\Service\UserPermsService
   */
  protected $userPermsService;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\va_gov_user\Service\UserPermsService $user_perms_service
   *   The user perms service.
   */
  public function __construct(
    UserPermsService $user_perms_service
  ) {
    $this->userPermsService = $user_perms_service;
  }

  /**
   * Disable fields on Banner node form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterBannerNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    if (!$this->userPermsService->hasAdminRole()) {
      $form['field_target_paths']['#disabled'] = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      // React on banner forms.
      'hook_event_dispatcher.form_node_banner_form.alter' => 'alterBannerNodeForm',
      'hook_event_dispatcher.form_node_banner_edit_form.alter' => 'alterBannerNodeForm',
    ];
  }

}
