<?php

namespace Drupal\va_gov_home\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\va_gov_header_footer\Traits\MenuFormAlter;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov home event subscriber.
 */
class FormEventSubscriber implements EventSubscriberInterface {

  use MenuFormAlter;

  /**
   * The VA user permission service.
   *
   * @var \Drupal\va_gov_user\Service\UserPermsService
   */
  protected UserPermsService $permsService;

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
    if ($event->getFormId() === 'menu_link_content_home-page-hub-list_form') {
      $admin = $this->permsService->hasAdminRole(TRUE);
      $this->hubMenuFormAlter($form, $admin);
    }
  }

  /**
   * Modifies the menu link content create/edit form for home page hub list.
   *
   * @param array $form
   *   The form element array.
   * @param bool $admin
   *   TRUE if current user is an admin.
   */
  public function hubMenuFormAlter(array &$form, bool $admin): void {
    $this->hubMenuHideAddtributes($form)
      ->hubMenuHideExpanded($form)
      ->hubMenuHideViewMode($form, $admin)
      ->hubMenuHideParentLink($form, $admin);
  }

}
