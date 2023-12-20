<?php

namespace Drupal\va_gov_events\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\va_gov_content_types\Entity\Event;
use Drupal\va_gov_content_types\Traits\EventOutreachTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Event content type subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

  use EventOutreachTrait;

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
   * Entity pre-save Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event.
   *
   * @throws \Exception
   */
  public function entityPresave(EntityPresaveEvent $event): void {
    $entity = $event->getEntity();
    if (is_a($entity, Event::class)) {
      $entity->eventEntityPresave($entity);
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
    $this->modifyFormFieldSetElements($form);
    $this->modifyRecurringEventsWidgetFieldPresentation($form);
    $this->modifyAddToOutreachCalendarElements($form);
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

    // Remove the extra fieldset and add more button.
    $extra_fieldset = $form['field_datetime_range_timezone']['widget']['#max_delta'];
    if ($extra_fieldset > 0) {
      unset($form['field_datetime_range_timezone']['widget'][$extra_fieldset]);
    }
    unset($form['field_datetime_range_timezone']['widget']['add_more']);
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
    $form['field_url_of_an_online_event']['widget'][0]['uri']['#description'] = (string) $form['field_url_of_an_online_event']['widget'][0]['#description'];
  }

}
