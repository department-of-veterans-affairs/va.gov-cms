<?php

namespace Drupal\va_gov_media\EventSubscriber;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\field_event_dispatcher\Event\Field\WidgetSingleElementFormAlterEvent;
use Drupal\field_event_dispatcher\FieldHookEvents;
use Drupal\image\Plugin\Field\FieldWidget\ImageWidget;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Theme Event Subscriber.
 */
class MediaEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Max length for alt text.
   */
  const MAX_LENGTH = 150;

  /**
   * Boolean for whether to count HTML characters.
   */
  const COUNT_HTML = FALSE;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      FieldHookEvents::WIDGET_SINGLE_ELEMENT_FORM_ALTER => 'formWidgetAlter',
      FormHookEvents::FORM_ALTER => 'formAlter',
    ];
  }

  /**
   * Form alter Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormAlterEvent $event
   *   The event.
   */
  public function formAlter(FormAlterEvent $event): void {
    $form = &$event->getForm();

    $form_id = $form['#id'];
    if ($form_id === 'media-image-add-form') {
      $form['name']['widget'][0]['value']['#description'] = $this->t('Provide a name that will help other users of the CMS find and reuse this image. The name is not visible to end users.');
      unset($form['field_media_submission_guideline']);
    }
  }

  /**
   * Widget form alter Event call.
   *
   * @param \Drupal\field_event_dispatcher\Event\Field\WidgetSingleElementFormAlterEvent $event
   *   The event.
   */
  public function formWidgetAlter(WidgetSingleElementFormAlterEvent $event): void {
    $element = &$event->getElement();
    $context = $event->getContext();
    // If this is an image field type of instance.
    if ($context['widget'] instanceof ImageWidget) {
      $element['#process'][] = [static::class, 'imageFieldWidgetProcess'];
    }
  }

  /**
   * Changes the alt text description to be more helpful and add validation.
   *
   * @param array $element
   *   The element to change the alt text description.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array $form
   *   The form.
   *
   * @return array
   *   The element.
   */
  public static function imageFieldWidgetProcess(array $element, FormStateInterface &$form_state, array $form) {
    if (isset($element['alt'])) {
      $element['alt']['#description'] = t('Adding a clear and meaningful description of the image is important for accessibility.');
      $element['alt']['#element_validate'] = [
        [static::class, 'validateAltText'],
      ];
      $element['alt']['#type'] = 'textarea';
      $element['alt']['#rows'] = 3;

      // Add the textfield counter to the alt text field.
      $position = 'after';
      $form_id = $form['#form_id'];
      if ($form_id === 'media_library_add_form_dropzonejs') {
        $form_storage = $form_state->getStorage();
        $entity = $form_storage['media'][0];
      }
      else {
        /* var $form_object \Drupal\media\MediaForm */
        $form_object = $form_state->getFormObject();
        /* var $entity \Drupal\media\MediaInterface */
        $entity = $form_object->getEntity();
      }

      $delta = $element['#delta'];
      $fieldDefinition = $entity->getFieldDefinition($element['#field_name']);

      $keys = [$element['#entity_type']];
      $keys[] = $entity->id() ? $entity->id() : 0;
      if (is_object($fieldDefinition) && method_exists($fieldDefinition, 'id')) {
        $field_definition_id = str_replace('.', '--', $fieldDefinition->id());
      }
      else {
        $field_definition_id = "{$entity->getEntityTypeId()}--{$entity->bundle()}--{$fieldDefinition->getName()}";
      }

      $keys[] = $field_definition_id;
      $keys[] = $delta;
      $keys[] = 'alt';

      $key = implode('-', $keys);

      $element['alt']['#attributes']['class'][] = $key;
      $element['alt']['#attributes']['class'][] = 'textfield-counter-element';
      $element['alt']['#attributes']['data-field-definition-id'] = $field_definition_id;

      $element['alt']['#attached']['library'][] = 'textfield_counter/counter';
      $element['alt']['#attached']['drupalSettings']['textfieldCounter'][$key]['key'][$delta] = $key;
      $element['alt']['#attached']['drupalSettings']['textfieldCounter'][$key]['maxlength'] = (int) self::MAX_LENGTH;
      $element['alt']['#attached']['drupalSettings']['textfieldCounter'][$key]['counterPosition'] = $position;
      $element['alt']['#attached']['drupalSettings']['textfieldCounter'][$key]['textCountStatusMessage'] = 'Characters remaining: <span class="remaining_count">@remaining_count</span>';

      $element['alt']['#attached']['drupalSettings']['textfieldCounter'][$key]['preventSubmit'] = TRUE;

      $element['alt']['#attached']['drupalSettings']['textfieldCounter'][$key]['countHTMLCharacters'] = self::COUNT_HTML;

    }

    // Return the altered element.
    return $element;
  }

  /**
   * Custom validation of image widget alt text field.
   *
   * @param array $element
   *   The image widget alt text element.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   */
  public static function validateAltText(array $element, FormStateInterface $formState) {
    // Only perform validation if the function is triggered from other places
    // than the image process form. We don't want this validation to run when an
    // image was just uploaded, and they haven't had an opportunity to provide
    // the alt text. ImageWidget does this too, see ::validateRequiredFields.
    $triggering_element = $formState->getTriggeringElement();
    if (!empty($triggering_element['#submit']) && in_array('file_managed_file_submit', $triggering_element['#submit'], TRUE)) {
      $formState->setLimitValidationErrors([]);
      return;
    }

    $parents = $element['#parents'];
    array_pop($parents);

    // Back out if no image was submitted.
    $fid_form_element = array_merge($parents, ['fids']);
    if (empty($formState->getValue($fid_form_element))) {
      return;
    }

    $logger = \Drupal::logger('va_gov_media');
    $value = $formState->getValue($element['#parents']);
    $value_length = static::getLengthOfSubmittedValue($value);
    if ($value_length > self::MAX_LENGTH) {
      $formState->setErrorByName(implode('][', $element['#parents']), t('Alternative text cannot be longer than 150 characters.'));
      $logger->error("[CC] Alternative text ({$value}) cannot be longer than 150 characters. {$value_length} characters were submitted.");
    }

    if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $value)) {
      $formState->setErrorByName(implode('][', $element['#parents']), t('Alternative text cannot contain file names.'));
      $logger->error("[FN] Alternative text cannot contain file names. {$value} was submitted.");
    }

    if (preg_match('/(image|photo|graphic|picture) of/i', $value)) {
      $formState->setErrorByName(implode('][', $element['#parents']), t('Alternative text cannot contain phrases like “image of”, “photo of”, “graphic of”, “picture of”, etc.'));
      $logger->error("[RP] Alternative text cannot contain repetitive phrases. {$value} was submitted.");
    }
  }

  /**
   * Get the length of the submitted text value.
   *
   * @param string $value
   *   The value whose length is to be calculated.
   *
   * @return int
   *   The length of the value.
   */
  public static function getLengthOfSubmittedValue(string $value): int {
    $parts = explode(PHP_EOL, $value);
    $newline_count = count($parts) - 1;

    if (self::COUNT_HTML) {
      $value_length = mb_strlen($value) - $newline_count;
    }
    else {
      $value_length = str_replace('&nbsp;', ' ', $value);
      $value_length = trim($value_length);
      $value_length = preg_replace("/(\r?\n|\r)+/", "\n", $value_length);
      $value_length = mb_strlen(strip_tags($value_length));
    }

    return $value_length;
  }

}
