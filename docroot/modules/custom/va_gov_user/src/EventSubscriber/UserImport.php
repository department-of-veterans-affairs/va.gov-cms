<?php

namespace Drupal\va_gov_user\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\externalauth\ExternalAuth;
use Drupal\migrate\Event\MigrateImportEvent;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\Event\MigratePreRowSaveEvent;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Row;
use Drupal\user\UserInterface;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;
use Drupal\workbench_access\UserSectionStorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * User import event subscriber.
 *
 * @package Drupal\va_gov_user\EventSubscriber
 */
class UserImport implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * The Entity Type Manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * EnvironmentDiscovery Service.
   *
   * @var \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery
   */
  protected $environmentDiscovery;

  /**
   * External Auth Service.
   *
   * @var \Drupal\externalauth\ExternalAuth
   */
  protected $externalAuth;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

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
   * @param \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery $environmentDiscovery
   *   Environment Discovery service.
   * @param \Drupal\externalauth\ExternalAuth $externalAuth
   *   External Authentication service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Extension\ModuleHandler $moduleHandler
   *   Module Handler service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\workbench_access\UserSectionStorageInterface $user_section_storage
   *   The user section storage service.
   */
  public function __construct(
    EntityTypeManager $entity_type_manager,
    EnvironmentDiscovery $environmentDiscovery,
    ExternalAuth $externalAuth,
    MessengerInterface $messenger,
    ModuleHandler $moduleHandler,
    TranslationInterface $string_translation,
    UserSectionStorageInterface $user_section_storage
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->environmentDiscovery = $environmentDiscovery;
    $this->externalAuth = $externalAuth;
    $this->messenger = $messenger;
    $this->moduleHandler = $moduleHandler;
    $this->string_translation = $string_translation;
    $this->userSectionStorage = $user_section_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::POST_ROW_SAVE] = 'onMigratePostRowSave';
    $events[MigrateEvents::PRE_ROW_SAVE] = 'onMigratePreRowSave';
    $events[MigrateEvents::POST_IMPORT] = 'onMigratePostImport';
    return $events;
  }

  /**
   * Perform Post Import events.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   Information about the event that triggered this function.
   */
  public function onMigratePostImport(MigrateImportEvent $event) : void {
    if ($event->getMigration()->label() !== 'User Import') {
      return;
    }

    if ($this->environmentDiscovery->isBRD()) {
      $this->messenger->addMessage(
        $this->t('All accounts are created as blocked.')
      );
    }
    else {
      $this->messenger->addMessage(
        $this->t('All accounts are created as active.')
      );
      $this->messenger->addMessage(
        $this->t('Password was set to %password for all imported users', [
          '%password' => 'drupal8',
        ])
      );
    }
  }

  /**
   * Perform Post Row Save events.
   *
   * @param \Drupal\migrate\Event\MigratePostRowSaveEvent $event
   *   Information about the event that triggered this function.
   */
  public function onMigratePostRowSave(MigratePostRowSaveEvent $event) : void {
    if ($event->getMigration()->label() !== 'User Import') {
      return;
    }

    /** @var \Drupal\migrate\Row $row */
    $row = $event->getRow();

    /** @var \Drupal\user\UserInterface $user */
    $user = $this->getUserBeingImported($row);

    if (!$user) {
      return;
    }

    $this->addUserToSections($row, $user);

    if ($this->environmentDiscovery->isBRD()) {
      $this->enableSamlAuth($user);
    }
    else {
      $user->setPassword('drupal8');
    }

    $user->save();
  }

  /**
   * Perform Pre Row Save events.
   *
   * @param \Drupal\migrate\Event\MigratePreRowSaveEvent $event
   *   Information about the event that triggered this function.
   *
   * @throws \Drupal\migrate\MigrateSkipRowException;
   */
  public function onMigratePreRowSave(MigratePreRowSaveEvent $event) : void {
    if ($event->getMigration()->label() !== 'User Import') {
      return;
    }

    /** @var \Drupal\migrate\Row $row */
    $row = $event->getRow();

    $email = $row->getSourceProperty('email');
    if (!$this->isVaGovEmail($email)) {
      throw new MigrateSkipRowException($this->t(
        'The user with email address @email was not created, since it is not a va.gov email address.',
        ['@email' => $email]
      ));
    }

    if ($this->environmentDiscovery->isBRD()) {
      // Newly imported users are blocked on prod.
      $row->setDestinationProperty('status', 0);
    }
  }

  /**
   * Get user being imported.
   *
   * @param \Drupal\migrate\Row $row
   *   The migration row.
   *
   * @return \Drupal\user\UserInterface
   *   The user object.
   */
  private function getUserBeingImported(Row $row) {
    $users = $this->entityTypeManager
      ->getStorage('user')
      ->loadByProperties(['name' => $row->getDestination()['name']]);
    $user = reset($users);

    return $user;
  }

  /**
   * Add user to specified sections.
   *
   * @param \Drupal\migrate\Row $row
   *   The migration row.
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   */
  private function addUserToSections(Row $row, UserInterface $user) {
    if ($section_ids = $this->getSectionIds($row->getDestinationProperty('sections'))) {
      $user_section_scheme = $this->entityTypeManager->getStorage('access_scheme')->load('section');
      $this->userSectionStorage->addUser($user_section_scheme, $user, $section_ids);
    }
  }

  /**
   * Enable SSO for a user.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   */
  private function enableSamlAuth(UserInterface $user) {
    $authname = $user->getAccountName();
    $this->moduleHandler->alter('simplesamlphp_auth_account_authname', $authname, $user);
    $this->externalAuth->linkExistingAccount($authname, 'simplesamlphp_auth', $user);
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

  /**
   * Validate that the given email is a va.gov address.
   *
   * @param string $email
   *   The email address.
   *
   * @return bool
   *   Whether or not the email is a va.gov address.
   */
  private function isVaGovEmail(string $email) : bool {
    $email_parsed = explode('@', $email);
    $domain = strtolower($email_parsed[1]);

    if ($domain !== 'va.gov') {
      return FALSE;
    }

    return TRUE;
  }

}
