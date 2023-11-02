<?php

namespace Drupal\va_gov_events\EventSubscriber;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\feature_toggle\FeatureStatus;
use Drupal\node\NodeInterface;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VAMC Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The 'publish to the national outreach calendar' field name.
   */
  const PUBLISH_TO_OUTREACH_CAL_FIELD = 'field_publish_to_outreach_cal';

  /**
   * The 'field_listing' field name.
   */
  const LISTING_FIELD = 'field_listing';

  /**
   * The 'field_additional_listings' field name.
   */
  const ADDITIONAL_LISTING_FIELD = 'field_additional_listings';

  /**
   * The National Outreach Calendar node id.
   */
  const OUTREACH_CAL_NID = 736;

  /**
   * The 'Outreach Hub' Section term id.
   */
  const OUTREACH_HUB_TID = 7;

  /**
   * The Feature toggle name for outreach checkbox.
   */
  const OUTREACH_CHECKBOX_FEATURE_NAME = 'feature_event_outreach_checkbox';

  /**
   * The list of users allowed to view the outreach checkbox.
   */
  const OUTREACH_CHECKBOX_TEST_USERS = [
    2910,
    1448,
    4356,
    2861,
    2922,
    3421,
    3314,
    4573,
    3864,
    1583,
    3610,
    2927,
    2955,
    3314,
    2957,
    3420,
    2962,
    2719,
    4356,
    1448,
    2574,
    1444,
    2906,
    31,
  ];

  /**
   * The User Perms Service.
   *
   * @var \Drupal\va_gov_user\Service\UserPermsService
   */
  protected UserPermsService $userPermsService;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected AccountInterface $currentUser;

  /**
   * TRUE if the outreach checkbox feature toggle is enabled.
   *
   * @var bool
   */
  private bool $outreachCheckboxFeatureEnabled;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\va_gov_user\Service\UserPermsService $user_perms_service
   *   The current user perms service.
   * @param \Drupal\Core\Session\AccountProxy $account_proxy
   *   The account proxy service.
   * @param \Drupal\feature_toggle\FeatureStatus $feature_status
   *   The feature status service.
   */
  public function __construct(UserPermsService $user_perms_service, AccountProxy $account_proxy, FeatureStatus $feature_status) {
    $this->userPermsService = $user_perms_service;
    $this->currentUser = $account_proxy->getAccount();
    $this->outreachCheckboxFeatureEnabled = $feature_status->getStatus(self::OUTREACH_CHECKBOX_FEATURE_NAME);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      'hook_event_dispatcher.form_node_event_form.alter' => 'alterEventNodeForm',
      'hook_event_dispatcher.form_node_event_edit_form.alter' => 'alterEventNodeForm',
      EntityHookEvents::ENTITY_PRE_SAVE => 'entityPresave',
    ];
  }

  /**
   * Determines if the 'add to National Outreach Calendar' checkbox is enabled.
   *
   * @return bool
   *   TRUE if the outreach checkbox should be enabled.
   */
  protected function outreachCheckboxEnabled(): bool {
    $admin = $this->userPermsService->hasAdminRole(TRUE);
    return (
      $this->outreachCheckboxFeatureEnabled
      && (in_array($this->currentUser->id(), self::OUTREACH_CHECKBOX_TEST_USERS) || $admin)
    );
  }

  /**
   * Determines if the current user is an 'Outreach Hub' only user.
   *
   * @return bool
   *   TRUE if the current user only has the 'Outreach Hub' section.
   */
  protected function outreachHubOnlyUser(): bool {
    $sections = $this->userPermsService->getSections($this->currentUser);
    if (count($sections) === 1 && array_key_first($sections) === self::OUTREACH_HUB_TID) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Entity presave Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function entityPresave(EntityPresaveEvent $event): void {
    $entity = $event->getEntity();
    if ($entity instanceof NodeInterface) {
      $this->addToNationalOutreachCalendar($entity);
    }
  }

  /**
   * Form alterations for event content type.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterEventNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $this->addDisplayManagementToEventFields($form);
    $this->modifyFormFieldsetElements($form);
    $this->modifyRecurringEventsWidgetFieldPresentation($form);
    $this->modifyAddToOutreachCalendarElements($form);
  }

  /**
   * Adds the event to the National Outreach Calendar (event_listing).
   *
   * The purpose of this method is to add the current node event to the National
   * Outreach Calendar (an event listing node) if the $listingField
   * checkbox/boolean has been set, or if the current user is an Outreach Hub
   * user.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to be modified.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function addToNationalOutreachCalendar(NodeInterface $node): void {
    if ($node->hasField(self::LISTING_FIELD) &&
      $node->hasField(self::PUBLISH_TO_OUTREACH_CAL_FIELD) &&
      $node->hasField(self::ADDITIONAL_LISTING_FIELD) &&
      $this->outreachCheckboxEnabled()) {
      $addToCalValue = $node->get(self::PUBLISH_TO_OUTREACH_CAL_FIELD)->first()->getValue();
      if (isset($addToCalValue['value'])) {
        $listings = $node->get(self::LISTING_FIELD)->getValue();
        $additionalListings = $node->get(self::ADDITIONAL_LISTING_FIELD)->getValue();
        if ($addToCalValue['value'] === 1 || $this->outreachHubOnlyUser()) {
          // Add to Outreach calendar selected, or user is Outreach Hub only
          // user.
          if (!in_array(self::OUTREACH_CAL_NID, array_column($listings + $additionalListings, 'target_id'))) {
            $additionalListings[] = [
              'target_id' => self::OUTREACH_CAL_NID,
            ];
          }
        }
        else {
          // Checkbox is unset. Ensure that additional listings are removed.
          $additionalListings = [];
        }
        $node->set(self::ADDITIONAL_LISTING_FIELD, $additionalListings);
      }
    }
  }

  /**
   * Form changes for 'Publish to National Outreach Calendar' related elements.
   *
   * @param array $form
   *   The form array.
   */
  public function modifyAddToOutreachCalendarElements(array &$form) :void {
    if ($this->outreachHubOnlyUser() && $this->outreachCheckboxEnabled()) {
      // Disable the checkbox.
      $form[self::PUBLISH_TO_OUTREACH_CAL_FIELD]['#disabled'] = TRUE;
      // Set the default value of the checkbox.
      $form[self::PUBLISH_TO_OUTREACH_CAL_FIELD]['widget']['value']['#default_value'] = TRUE;
      // Override the field label for the checkbox.
      $form[self::PUBLISH_TO_OUTREACH_CAL_FIELD]['widget']['value']['#title'] = $this->t('This event will automatically be published to the National Outreach Calendar');
      // Set the default value to the Outreach cal on the dropdown if is not
      // already set.
      if (empty($form[self::LISTING_FIELD]['widget']['#default_value'])) {
        $form[self::LISTING_FIELD]['widget']['#default_value'] = self::OUTREACH_CAL_NID;
      }
    }
    // Add the '- Select a value -' option (_none) since it was removed by
    // the Limited Widgets for Unlimited Field module.
    if (isset($form[self::LISTING_FIELD]['widget']['#options']) && !array_key_exists('_none', $form[self::LISTING_FIELD]['widget']['#options'])) {
      $form[self::LISTING_FIELD]['widget']['#options'] = ['_none' => '- Select a value -'] + $form[self::LISTING_FIELD]['widget']['#options'];
    }
    // Only allow access to the checkbox if it should be enabled.
    $form[self::PUBLISH_TO_OUTREACH_CAL_FIELD]['#access'] = $this->outreachCheckboxEnabled();
  }

  /**
   * Adds overrides to recurring dates widget.
   *
   * @param array $form
   *   The form.
   */
  public function modifyRecurringEventsWidgetFieldPresentation(array &$form): void {
    // Add our js for toggling items depending on duration choices.
    $form['#attached']['library'][] = 'va_gov_events/recurring_dates';

    // Add element for recurring div toggle control.
    $form['field_datetime_range_timezone']['widget'][0]['make_recurring'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Make recurring'),
      '#default_value' => 0,
      '#attributes' => ['class' => ['make-recurring-toggle']],
    ];

    // Wrap our repeating choices in a show-hide div.
    $form['field_datetime_range_timezone']['widget'][0]['interval']['#prefix'] = "<div id='recurring-items-reveal-wrap' class='recurring-items-reveal-wrap'><div class='clearfix'></div>";

    // Wraps "Repeat every" section of form.
    $form['field_datetime_range_timezone']['widget'][0]['repeat-advanced']['which']['#prefix'] = "<div id='repeat-on-the-wrap' class='repeat-on-the-wrap'>";
    // Close the "repeat-on-the-wrap" and our recurring-items-reveal-wrap wrap.
    $form['field_datetime_range_timezone']['widget'][0]['repeat-advanced']['weekday']['#suffix'] = "</div></div>";

    // Change the interval text to be VA relevant.
    $form['field_datetime_range_timezone']['widget'][0]['interval']['#title'] = $this->t('Repeat every') . ' ';

    // Add a padding class to the label.
    $form['field_datetime_range_timezone']['widget'][0]['interval']['#label_attributes'] = ['class' => ['display-top-no-pad-left']];

    // Change the repeat end time to default to Until field value.
    $form['field_datetime_range_timezone']['widget'][0]['repeat-end']['#value'] = 'UNTIL';

    // Reveal and change the end date title text.
    unset($form['field_datetime_range_timezone']['widget'][0]['repeat-end-date']['#title_display']);
    $form['field_datetime_range_timezone']['widget'][0]['repeat-end-date']['#title'] = $this->t('Until');

    // Replace details treatment with generic div.
    $form['field_datetime_range_timezone']['widget'][0]['repeat-advanced']['#type'] = 'div';

    // Update title to be more specific to time increment.
    $form['field_datetime_range_timezone']['widget'][0]['repeat-advanced']['byday']['#title'] = $this->t('On these days');

    // Move timezone up beside start and end fields.
    $form['field_datetime_range_timezone']['widget'][0]['timezone']['#weight'] = 0;

    // Change the Manage instance button text.
    $form['field_datetime_range_timezone']['widget'][0]['manage-instances']['#title'] = $this->t('Edit event series');
    // Change recurrence text options.
    $form['field_datetime_range_timezone']['widget'][0]['repeat']['#options']['DAILY'] = $this->t('days');
    $form['field_datetime_range_timezone']['widget'][0]['repeat']['#options']['WEEKLY'] = $this->t('weeks');
    $form['field_datetime_range_timezone']['widget'][0]['repeat']['#options']['MONTHLY'] = $this->t('months');

    // Set defaults to prevent validation errors.
    $form['field_datetime_range_timezone']['widget'][0]['value']['#value']['object'] = '';
    $form['field_datetime_range_timezone']['widget'][0]['end_value']['#value']['object'] = '';

    // Remove states management now handled by custom js.
    unset($form['field_datetime_range_timezone']['widget'][0]['interval']['#states']);
    unset($form['field_datetime_range_timezone']['widget'][0]['repeat-end-date']['#states']);
    unset($form['field_datetime_range_timezone']['widget'][0]['repeat-advanced']['#states']);
    unset($form['field_datetime_range_timezone']['widget'][0]['repeat-advanced']['by-day']['#states']);
    unset($form['field_datetime_range_timezone']['widget'][0]['repeat-advanced']['which']['#states']);
    unset($form['field_datetime_range_timezone']['widget'][0]['repeat-advanced']['weekday']['#states']);
    // These fields are now redundant and not part of new ux.
    unset($form['field_datetime_range_timezone']['widget'][0]['repeat-advanced']['restrict-hours']);
    unset($form['field_datetime_range_timezone']['widget'][0]['repeat-advanced']['restrict-minutes']);
    unset($form['field_datetime_range_timezone']['widget'][0]['repeat-label']);
    unset($form['field_datetime_range_timezone']['widget'][0]['duration']['#title']);
  }

  /**
   * Show fields depending on value of checkbox.
   *
   * @param array $form
   *   The form.
   */
  public function addDisplayManagementToEventFields(array &$form): void {
    $form['#attached']['library'][] = 'va_gov_events/event_form_states_helpers';
  }

  /**
   * Add prefix to cta button.
   *
   * Simplify address widget appearance.
   *
   * Replace linkit module help text with config help text.
   *
   * @param array $form
   *   The form.
   */
  public function modifyFormFieldSetElements(array &$form): void {
    // Remove the wrap and title around address widget.
    $form['field_address']['widget'][0]['#type'] = 'div';
    unset($form['field_address']['widget'][0]['#title']);
    // Use help text from config instead of linkit module.
    $form['field_url_of_an_online_event']['widget'][0]['uri']['#description'] = $form['field_url_of_an_online_event']['widget'][0]['#description']->__toString();
  }

}
