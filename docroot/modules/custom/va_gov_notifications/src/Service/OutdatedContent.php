<?php

namespace Drupal\va_gov_notifications\Service;

use Drupal\advancedqueue\Job;
use Drupal\workbench_access\Entity\AccessSchemeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\workbench_access\UserSectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service description.
 */
class OutdatedContent implements OutdatedContentInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger.channel.va_gov_notifications service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $vaGovNotifications;

  /**
   * The user section storage service.
   *
   * @var \Drupal\workbench_access\UserSectionStorageInterface
   */
  protected $userSectionStorage;

  /**
   * Constructs an OutdatedContent object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $va_gov_notifications
   *   The logger.channel.va_gov_notifications service.
   * @param \Drupal\workbench_access\UserSectionStorageInterface $user_section_storage
   *   The user section storage service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelInterface $va_gov_notifications,
    UserSectionStorageInterface $user_section_storage
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->vaGovNotifications = $va_gov_notifications;
    $this->userSectionStorage = $user_section_storage;
  }

  /**
   * The create method.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('logger.channel.va_gov_notifications'),
      $container->get('workbench_access.user_section_storage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function checkForOutdatedVamcContent(): array {
    $have_outdated_content = [];
    $editors = $this->getAllEditors();
    foreach ($editors as $editor) {
      $editor_sections = $this->getEditorsSections($editor);
      foreach ($editor_sections as $section) {
        $product = $this->getSectionProduct($section);
        $outdated_content = $this->getOutdatedContentForSection($section);
        // VAMCs are product 284.
        if (!empty($outdated_content) && $product === '284') {
          $editorName = $editor->getAccountName();
          $sectionName = $this->getSectionName($section);
          $this->vaGovNotifications
            ->info('Outdated content found for @sectionName editor: @editor',
            ['@editor' => $editorName, '@sectionName' => $sectionName]);
          $this->queueNotification($editor, 'vamc_outdated_content');
          $have_outdated_content[] = [
            'editor' => $editorName,
            'section' => $sectionName,
          ];
        }
        break;
      }
    }
    // This is to provide output for the drush command only.
    return $have_outdated_content;
  }

  /**
   * {@inheritdoc}
   */
  public function checkForOutdatedVetCenterContent(): array {
    $have_outdated_content = [];
    $editors = $this->getAllEditors();
    foreach ($editors as $editor) {
      $editor_sections = $this->getEditorsSections($editor);
      foreach ($editor_sections as $section) {
        $product = $this->getSectionProduct($section);
        $outdated_content = $this->getOutdatedContentForSection($section);
        // Vet Centers are product 289.
        if (!empty($outdated_content) && $product === '289') {
          $editorName = $editor->getAccountName();
          $sectionName = $this->getSectionName($section);
          $this->vaGovNotifications
            ->info('Outdated content found for @sectionName editor: @editor',
            ['@editor' => $editorName, '@sectionName' => $sectionName]);
          $this->queueNotification($editor, 'vet_center_outdated_content');
          $have_outdated_content[] = [
            'editor' => $editorName,
            'section' => $sectionName,
          ];
        }
        break;
      }
    }
    // This is to provide output for the drush command only.
    return $have_outdated_content;
  }

  /**
   * Get the product for the section.
   *
   * @param string $section
   *   The section id.
   *
   * @return string
   *   The product id.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getSectionProduct(string $section): string {
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $term = $storage->load($section);
    if ($term && $term->hasField('field_product')) {
      $product = $term->get('field_product')->getValue();
      return $product[0]['target_id'] ?? '';
    }
    return '';
  }

  /**
   * Get the name for the section.
   *
   * @param string $section
   *   The section id.
   *
   * @return string
   *   The section name.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getSectionName(string $section): string {
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $term = $storage->load($section);
    if ($term) {
      return $term->get('name')->value;
    }
    return '';
  }

  /**
   * Sends a notification to VAMC editors.
   *
   * @param \Drupal\Core\Session\AccountInterface $editor
   *   The editor user object.
   * @param string $queue
   *   The queue to use.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function queueNotification(AccountInterface $editor, string $queue): void {
    // Get the queue.
    /** @var \Drupal\advancedqueue\Entity\Queue $queue */
    $queue = $this->entityTypeManager
      ->getStorage('advancedqueue_queue')
      ->load($queue);

    // Build the variables being passed to the template.
    $uid = $editor->id();
    $editor_username = $editor->getAccountName();
    $template_values = ['template' => 'va_outdated_content'];
    $template_values['uid'] = $uid;
    $values = [
      'field_editor_username' => $editor_username,
      'field_subject' => '[ACTION REQUIRED] Review Content Report',
    ];

    // Create the job.
    $job = Job::create('va_gov_outdated_content_notification', compact('template_values', 'values'));
    $queue->enqueueJob($job);
  }

  /**
   * Get the Outdated Content for a Section.
   */
  protected function getOutdatedContentForSection($section): array|int {
    $offset = strtotime('-12 month');
    $types = [
      'event',
      'news_story',
      'press_release',
    ];
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', $types, 'NOT IN')
      ->condition('status', 1)
      ->condition('field_last_saved_by_an_editor', $offset, '<=')
      ->condition('field_administration', $section, '=');
    return $query->execute();
  }

  /**
   * Gets all editors.
   */
  protected function getAllEditors(): array {
    $userStorage = $this->entityTypeManager->getStorage('user');
    $uids = $userStorage->getQuery()
      ->condition('status', 1)
      ->execute();
    return $userStorage->loadMultiple($uids);
  }

  /**
   * Gets the sections for the editor.
   *
   * @param \Drupal\Core\Session\AccountInterface $editor
   *   The editor user object.
   *
   * @return array
   *   The sections for the editor.
   */
  protected function getEditorsSections(AccountInterface $editor): array {
    return $this->userSectionStorage->getUserSections($this->getScheme(), $editor, FALSE);
  }

  /**
   * Gets the access scheme for the section.
   */
  protected function getScheme(): AccessSchemeInterface {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $scheme_storage */
    $scheme_storage = $this->entityTypeManager->getStorage('access_scheme');
    /** @var \Drupal\workbench_access\Entity\AccessSchemeInterface $scheme */
    $scheme = $scheme_storage->load('section');
    return $scheme;
  }

}
