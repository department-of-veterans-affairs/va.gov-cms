<?php

namespace Drupal\va_gov_banner\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityBundleFieldInfoAlterEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
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
    // Fixes href on missing path constraint jump link.
    $form['#attached']['library'][] = 'va_gov_banner/fix_constraint_jump_link';
    // Ensures users add revision message on edit.
    if ($form['#form_id'] === 'node_banner_edit_form') {
      $this->requireRevisionMessage($event);
    }
  }

  /**
   * Add validation to paths field.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityBundleFieldInfoAlterEvent $event
   *   The event.
   */
  public function alterPathFieldInfo(EntityBundleFieldInfoAlterEvent $event): void {
    $type = $event->getEntityType();
    $bundle = $event->getBundle();
    $target_bundles = ['banner', 'promo_banner'];
    $fields = $event->getFields();
    if ($type->get('id') === 'node' && in_array($bundle, $target_bundles) && isset($fields['field_target_paths'])) {
      $fields['field_target_paths']->addConstraint('RequireScope');
    }
  }

  /**
   * Require revision message on banner edit form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function requireRevisionMessage(FormIdAlterEvent $event) {
    $form = &$event->getForm();
    $form['revision_log']['#required'] = TRUE;
    $form['revision_log']['widget']['#required'] = TRUE;
    $form['revision_log']['widget'][0]['#required'] = TRUE;
    $form['revision_log']['widget'][0]['value']['#required'] = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      // React on banner forms.
      'hook_event_dispatcher.form_node_banner_form.alter' => 'alterBannerNodeForm',
      'hook_event_dispatcher.form_node_banner_edit_form.alter' => 'alterBannerNodeForm',
      HookEventDispatcherInterface::ENTITY_BUNDLE_FIELD_INFO_ALTER => 'alterPathFieldInfo',
    ];
  }

}
