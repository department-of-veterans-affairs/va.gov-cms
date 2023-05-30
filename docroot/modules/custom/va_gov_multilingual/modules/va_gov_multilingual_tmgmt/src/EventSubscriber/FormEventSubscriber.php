<?php

namespace Drupal\va_gov_multilingual_tmgmt\EventSubscriber;

use Drupal\Core\Link;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\tmgmt\Entity\JobItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov home event subscriber.
 */
class FormEventSubscriber implements EventSubscriberInterface {

  const CONFLICTING_ITEMS_MESSAGE_SINGLE = 'The translation for the following item is already in progress, so it will not be added to this job: @items.';
  const CONFLICTING_ITEMS_MESSAGE_PLURAL = 'The translation for the following @count items is already in progress, so they will not be added to this job: @items.';

  /**
   * The translation manager.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  protected TranslationManager $translationManager;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\StringTranslation\TranslationManager $translation_manager
   *   The translation manager.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   THe messenger service.
   */
  public function __construct(TranslationManager $translation_manager, Messenger $messenger) {
    $this->translationManager = $translation_manager;
    $this->messenger = $messenger;
  }

  /**
   * Form alters for va_gov_home.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormAlterEvent $event
   *   The form event.
   */
  public function formAlter(FormAlterEvent $event) {
    if ($event->getFormId() === 'tmgmt_job_edit_form') {
      /** @var Drupal\tmgmt\Form\JobForm $form_object */
      $form_object = $event->getFormState()->getFormObject();
      $job = $form_object->getEntity();
      if (!$job->isContinuous()) {
        $conflicting_items_by_item = $job->getConflictingItems();
        $form = &$event->getForm();
        $form['message'] = [];
        if (count($conflicting_items_by_item)) {
          // Make a list of links to the existing items causing the conflicts.
          $conflicts = [];
          $num_of_existing_items = 0;
          foreach ($conflicting_items_by_item as $conflicting_items) {
            $num_of_existing_items += count($conflicting_items);
            foreach (JobItem::loadMultiple($conflicting_items) as $id => $conflicting_item) {
              $conflicts[] = Link::createFromRoute($conflicting_item->label(), 'entity.tmgmt_job_item.canonical', ['tmgmt_job_item' => $id])->toString();
            }
          }
          // Make the links usable in formatPlural().
          $conflict_list = Markup::create(implode(', ', $conflicts));
          $message = $this->translationManager
            ->formatPlural($num_of_existing_items, $this::CONFLICTING_ITEMS_MESSAGE_SINGLE, $this::CONFLICTING_ITEMS_MESSAGE_PLURAL, ['@items' => $conflict_list]);
          $this->messenger->addWarning($message);
        }
      }
    };
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
