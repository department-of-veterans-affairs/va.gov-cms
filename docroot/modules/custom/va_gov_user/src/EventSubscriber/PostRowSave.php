<?php

namespace Drupal\va_gov_user\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\workbench_access\UserSectionStorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Post Row Save event subscriber.
 *
 * @package Drupal\va_gov_user\EventSubscriber
 */
class PostRowSave implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * The Entity Type Manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The User Section Storage service.
   *
   * @var \Drupal\workbench_access\UserSectionStorageInterface
   */
  protected $userSectionStorage;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\workbench_access\UserSectionStorageInterface $user_section_storage
   *   The user section storage service.
   */
  public function __construct(
    EntityTypeManager $entity_type_manager,
    MessengerInterface $messenger,
    TranslationInterface $string_translation,
    UserSectionStorageInterface $user_section_storage
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
    $this->string_translation = $string_translation;
    $this->userSectionStorage = $user_section_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::POST_ROW_SAVE] = 'onMigratePostRowSave';
    return $events;
  }

  /**
   * Perform Post Row Save events.
   *
   * @param \Drupal\migrate\Event\MigratePostRowSaveEvent $event
   *   Information about the event that triggered this function.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\migrate\MigrateException
   */
  public function onMigratePostRowSave(MigratePostRowSaveEvent $event) : void {
    if ($event->getMigration()->label() !== 'User Import') {
      return;
    }

    if ($section_ids = $this->getSectionIds($event->getRow()->getDestinationProperty('sections'))) {
      $users = $this->entityTypeManager
        ->getStorage('user')
        ->loadByProperties(['name' => $event->getRow()->getDestination()['name']]);
      $user = reset($users);
      $user_section_scheme = $this->entityTypeManager->getStorage('access_scheme')->load('section');

      $this->userSectionStorage->addUser($user_section_scheme, $user, $section_ids);
    }
  }

  /**
   * Get section term IDs from section names.
   *
   * @param array $sections
   *   Array of section names.
   *
   * @return array[int]
   *   Array of term IDs.
   */
  private function getSectionIds(array $sections) : array {
    $section_ids = [];

    foreach (array_filter($sections) as $section) {
      $terms = $this->entityTypeManager
        ->getStorage('taxonomy_term')
        ->loadByProperties(['name' => $section]);

      if (count($terms) === 0) {
        $this->messenger->addWarning($this->t(
          'A section with the name "@section" was not found, please add this section manually!',
          ['@section' => $section]
        ));
      }
      elseif (count($terms) > 1) {
        $this->messenger->addWarning($this->t(
          'More than one section with the name "@section" was found, please add this section manually!',
          ['@section' => $section]
        ));
      }
      else {
        $term = reset($terms);
        $section_ids[] = $term->id();
      }
    }

    return $section_ids;
  }

}
