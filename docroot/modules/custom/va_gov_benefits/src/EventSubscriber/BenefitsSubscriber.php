<?php

namespace Drupal\va_gov_benefits\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_user\Service\UserPermsService;
use Drupal\Core\StringTranslation\StringTranslationTrait;
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
    if ($form_object instanceof ContentEntityForm) {
      $bundle = $form_object->getEntity()->bundle();
      if (!$this->userPermsService->hasAdminRole(TRUE) && $bundle === 'va_benefits_taxonomy') {
        // Disable Official Benefit name field from non-editors.
        $form['name']['#disabled'] = TRUE;
        // Hide API ID field from non-editors.
        $form['field_va_benefit_api_id']['#access'] = FALSE;
        // Hide taxonomy Relations field from non-editors.
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
