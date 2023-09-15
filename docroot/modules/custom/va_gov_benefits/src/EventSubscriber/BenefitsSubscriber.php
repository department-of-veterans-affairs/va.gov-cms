<?php

namespace Drupal\va_gov_benefits\EventSubscriber;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VA Benefits Entity Event Subscriber.
 */
class BenefitsSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

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
  public function __construct(UserPermsService $user_perms_service) {
    $this->userPermsService = $user_perms_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [FormHookEvents::FORM_ALTER => 'formAlter'];
  }

  /**
   * Form alter Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormAlterEvent $event
   *   The event.
   */
  public function formAlter(FormAlterEvent $event): void {
    $form = &$event->getForm();
    $form_state = $event->getFormState();
    if (!empty($form['#form_id']) && $form['#form_id'] === 'taxonomy_term_va_benefits_taxonomy_form') {
      $this->protectSelectFields($form, $form_state);
      $this->renameTitleLabelAndDescription($form);
    }
  }

  /**
   * Lock down certain fields from non-admins.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function protectSelectFields(array &$form, FormStateInterface $form_state): void {
    $form_object = $form_state->getFormObject();
    if ($form_object instanceof ContentEntityForm
      && $form_object->getEntity()->bundle() === 'va_benefits_taxonomy') {
      // Distinguish between admins and lock down accordingly.
      $is_admin = $this->userPermsService->hasAdminRole();
      $is_administrator_only = $this->userPermsService->hasAdminRole(TRUE);
      if (!$is_admin) {
        // Disable Official Benefit name field from non-admins.
        $form['name']['#disabled'] = TRUE;
        // Disable API ID field from non-admins.
        $form['field_va_benefit_api_id']['#disabled'] = TRUE;
      }
      if (!$is_administrator_only) {
        // Hide taxonomy Relations field.
        $form['relations']['#access'] = FALSE;
      }
    }
  }

  /**
   * Renames VA Benefits title label and adds description.
   *
   * @param array $form
   *   The form.
   */
  public function renameTitleLabelAndDescription(array &$form): void {
    $form['name']['widget'][0]['value']['#title'] = 'Official Benefit name';
    $form['name']['widget'][0]['value']['#description'] = $this->t('The full name of the benefit.');
  }

}
