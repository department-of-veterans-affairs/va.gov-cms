<?php

namespace Drupal\va_gov_home\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov home event subscriber.
 */
class FormEventSubscriber implements EventSubscriberInterface {

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
   * Form alters for va_gov_home.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormAlterEvent $event
   *   The form event.
   */
  public function formAlter(FormAlterEvent $event) {
    if ($event->getFormId() === 'menu_link_content_home-page-hub-list_form') {
      $form = &$event->getForm();
      $admin = $this->permsService->hasAdminRole(TRUE);
      $this->hubMenuFormAlter($form, $admin);
    };
  }

  /**
   * Modifies the menu link content create/edit form for home page hub list.
   *
   * @param array $form
   *   The form element array.
   * @param bool $admin
   *   TRUE if current user is an admin.
   */
  public function hubMenuFormAlter(array &$form, bool $admin) {
    $this->hubMenuHideAddtributes($form)
      ->hubMenuHideExpanded($form)
      ->hubMenuHideViewMode($form, $admin)
      ->hubMenuHideParentLink($form, $admin);
  }

  /**
   * Hides the attributes form field for home page hub list link form.
   *
   * @param array $form
   *   The form element array.
   *
   * @return $this
   */
  public function hubMenuHideAddtributes(array &$form): static {
    if (!empty($form['options']['attributes'])) {
      $form['options']['attributes']['#access'] = FALSE;
    }
    return $this;
  }

  /**
   * Hides the expanded form field for home page hub list link form.
   *
   * @param array $form
   *   The form element array.
   *
   * @return $this
   */
  public function hubMenuHideExpanded(array &$form): static {
    if (!empty($form['expanded'])) {
      $form['expanded']['#access'] = FALSE;
    }
    return $this;
  }

  /**
   * Hides the view_mode form field for home page hub list link form.
   *
   * @param array $form
   *   The form element array.
   * @param bool $admin
   *   TRUE if current user is an administrator.
   *
   * @return $this
   */
  public function hubMenuHideViewMode(array &$form, bool $admin): static {
    if (!empty($form['view_mode'])) {
      $form['view_mode']['#access'] = $admin;
    }
    return $this;
  }

  /**
   * Hides the parent link form field for home page hub list link form.
   *
   * @param array $form
   *   The form element array.
   * @param bool $admin
   *   TRUE if current user is an administrator.
   *
   * @return $this
   */
  public function hubMenuHideParentLink(array &$form, bool $admin): static {
    if (!empty($form['menu_parent'])) {
      $form['menu_parent']['#access'] = $admin;
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      FormHookEvents::FORM_ALTER => ['formAlter'],
    ];
  }

}
