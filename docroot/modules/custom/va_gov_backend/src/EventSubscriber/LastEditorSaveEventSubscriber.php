<?php

namespace Drupal\va_gov_backend\EventSubscriber;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VAMC Entity Event Subscriber.
 */
class LastEditorSaveEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   *  The entity manager.
   */
  private $entityTypeManager;

  /**
   * Drupal\Core\Datetime\DateFormatter definition.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The string entity type service.
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter service.
   */
  public function __construct(
    TranslationInterface $string_translation,
    EntityTypeManager $entity_type_manager,
    DateFormatter $date_formatter
  ) {
    $this->stringTranslation = $string_translation;
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormatter = $date_formatter;
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

    $base_form_id = $form_state->getBuildInfo()['base_form_id'] ?? '';
    if ($base_form_id === 'node_form') {
      /** @var \Drupal\Core\Entity\EntityFormInterface $form_object */
      $form_object = $form_state->getFormObject();
      /** @var \Drupal\node\NodeInterface $node */
      $node = $form_object->getEntity();

      $form['field_last_saved_by_an_editor']['#access'] = FALSE;
      $form['actions']['submit']['#submit'][] = [
        $this, 'lastSavedByEditorSetTimestamp',
      ];
      $form['meta']['saved'] = [
        '#type' => 'item',
        '#title' => $this->t('Last saved by an editor'),
        '#markup' => !empty($node->get('field_last_saved_by_an_editor')->value) ? $this->dateFormatter->format((int) $node->get('field_last_saved_by_an_editor')->value, 'short') : $this->t('Unknown'),
        '#suffix' => '<p class="helper-text helper-text-editor">' . $this->t('This is the last time an editor updated this content.') . '</p>',
        '#wrapper_attributes' => ['class' => ['entity-meta__last-saved-by-editor']],
      ];
      $form['meta']['changed'] = [
        '#type' => 'item',
        '#title' => $this->t('Last updated'),
        '#markup' => !$node->isNew() ? $this->dateFormatter->format($node->getChangedTime(), 'short') : $this->t('Not saved yet'),
        '#suffix' => '<p class="helper-text helper-text-saved">' . $this->t('This is the last time this content was saved either by a human or an automated process.') . '</p>',
        '#wrapper_attributes' => ['class' => ['entity-meta__last-saved']],
      ];
      $form['meta']['author'] = [
        '#type' => 'item',
        '#title' => $this->t('Updated by'),
        '#markup' => !$node->isNew() ? $node->getRevisionUser()->getAccountName() : $this->t('Not saved yet'),
        '#wrapper_attributes' => ['class' => ['entity-meta__author']],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      FormHookEvents::FORM_ALTER => 'formAlter',
    ];
  }

  /**
   * Custom form submit to set the value for the last saved by editor field.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function lastSavedByEditorSetTimestamp(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\EntityFormInterface $form_object */
    $form_object = $form_state->getFormObject();
    /** @var \Drupal\node\NodeInterface $node */
    $node = $form_object->getEntity();
    $timestamp = time();
    $node->set('field_last_saved_by_an_editor', $timestamp);
  }

}
