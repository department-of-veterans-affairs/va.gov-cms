<?php

namespace Drupal\va_gov_header_footer\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Va gov header footer event subscriber.
 */
class FormEventSubscriber implements EventSubscriberInterface {

  /**
   * The VA user permission service.
   *
   * @var \Drupal\va_gov_user\Service\UserPermsService
   */
  protected UserPermsService $permsService;

  /**
   * List of menu form Ids to alter.
   *
   * @var array
   */
  private array $menus = [
    'menu_link_content_va-gov-footer_form',
    'menu_link_content_footer-bottom-rail_form',
  ];

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\va_gov_user\Service\UserPermsService $permsService
   *   The messenger.
   */
  public function __construct(UserPermsService $permsService) {
    $this->permsService = $permsService;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      FormHookEvents::FORM_ALTER => ['formAlter'],
    ];
  }

  /**
   * Form alters for va_gov_home.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormAlterEvent $event
   *   The form event.
   */
  public function formAlter(FormAlterEvent $event): void {
    $form = &$event->getForm();
    $formId = $event->getFormId();
    $admin = $this->permsService->hasAdminRole(TRUE);
    if (in_array($formId, $this->menus) && !$admin) {
      $form['description']['#access'] = FALSE;
    }
  }

}
