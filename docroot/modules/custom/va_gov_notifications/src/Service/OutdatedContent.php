<?php

namespace Drupal\va_gov_notifications\Service;

use Drupal\advancedqueue\Job;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\Settings;
use Drupal\workbench_access\Entity\AccessSchemeInterface;
use Drupal\workbench_access\UserSectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Gathers and queues outdated content email notifications to product editors.
 */
class OutdatedContent extends ServiceProviderBase implements OutdatedContentInterface {

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
  protected $vaGovNotificationsLogger;

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
   * @param \Drupal\Core\Logger\LoggerChannelInterface $va_gov_notifications_logger
   *   The logger.channel.va_gov_notifications service.
   * @param \Drupal\workbench_access\UserSectionStorageInterface $user_section_storage
   *   The user section storage service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelInterface $va_gov_notifications_logger,
    UserSectionStorageInterface $user_section_storage
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->vaGovNotificationsLogger = $va_gov_notifications_logger;
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
   * Gets the product id from a product name.
   *
   * @param string $product_name
   *   The simple name of the product.
   *
   * @return string
   *   A term id matching the appropriate product.
   *
   * @throws \InvalidArgumentException
   *   Thrown when a product_name has not been defined in the map.
   */
  protected function getProductId(string $product_name): string {
    // Update the map as new outdated notifications are added.
    // We don't look them up in drupal by name because a name is content and
    // if changed, would break this.
    $map = [
      // 'name' => 'product term id'.
      'nca' => '1000',
      'vamc' => '284',
      'vba' => '1050',
      'vet_center' => '289',
    ];
    if (!empty($map[$product_name])) {
      return $map[$product_name];
    }
    else {
      throw new \InvalidArgumentException("'{$product_name}' is not a defined product name.");
    }
  }

  /**
   * Gets the email subject based on the product.
   *
   * @param string $product_name
   *   The simple name of the product. See getProductId() for the accepted list.
   *
   * @return string[]
   *   An array of node bundle machine names to exclude.
   */
  protected function getSubject(string $product_name) {
    // Update the cases as new product outdated notifications are added.
    switch ($product_name) {
      case 'vet_center':
        $subject = '[ACTION REQUIRED] Vet Center Website Content Review';
        break;

      case 'nca':
      case 'vamc':
      case 'vba':
      default:
        $subject = '[ACTION REQUIRED] Review Content Report';
        break;
    }

    return $subject;

  }

  /**
   * Gets a list of content types to exclude from the expired query.
   *
   * @param string $product_name
   *   The simple name of the product. See getProductId() for the accepted list.
   *
   * @return string[]
   *   An array of node bundle machine names to exclude.
   */
  protected function getExcludedContentTypes(string $product_name): array {
    // Update the cases as new product outdated notifications are added.
    switch ($product_name) {
      case 'nca':
      case 'vamc':
      case 'vba':
      case 'vet_center':
      default:
        // We usually exclude these because this content is intended to age
        // without needing updating.  They represent an instant in time,
        // not evergreen content.
        $exclusion_types = [
          'event',
          'news_story',
          'press_release',
        ];
        break;
    }

    return $exclusion_types;
  }

  /**
   * {@inheritdoc}
   */
  public function queueOutdatedContentNotifications(string $product_name, string $template_name, array $test_users = []): array {
    $have_outdated_content = [];
    $product_id = $this->getProductId($product_name);
    // If test users are not provided, look up all the users.
    $editors = $this->getAllEditors($test_users);
    foreach ($editors as $editor) {
      $editor_sections = $this->getEditorsSections($editor);
      foreach ($editor_sections as $section) {
        $product = $this->getSectionProduct($section);
        // These are content types that should be allowed to become outdated.
        $exempt_types = $this->getExcludedContentTypes($product_name);
        $outdated_content = $this->getOutdatedContentForSection($section, $exempt_types);
        if (!empty($outdated_content) && $product === $product_id) {
          $editorName = $editor->getAccountName();
          $sectionName = $this->getSectionName($section);
          $this->vaGovNotificationsLogger
            ->info('Outdated content found for @sectionName editor: @editor',
            ['@editor' => $editorName, '@sectionName' => $sectionName]);
          $this->queueNotification($editor, $product_name, $template_name, $template_name);
          $have_outdated_content[] = [
            'editor' => $editorName,
            'section' => $sectionName,
          ];
          // Once we find some outdated content that matches, it is good enough
          // to trigger. No need to keep digging.
          break;
        }
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
   * Sends a notification to an editor.
   *
   * @param \Drupal\Core\Session\AccountInterface $editor
   *   The editor user object.
   * @param string $product_name
   *   The name of the product.
   * @param string $queue
   *   The queue to use.
   * @param string $template
   *   The template to use.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function queueNotification(AccountInterface $editor, string $product_name, string $queue, string $template): void {
    // Get the queue.
    /** @var \Drupal\advancedqueue\Entity\Queue $queue */
    $queue = $this->entityTypeManager
      ->getStorage('advancedqueue_queue')
      ->load($queue);

    // Build the variables being passed to the template.
    $uid = $editor->id();
    $editor_username = $editor->getAccountName();
    $template_values = compact('template', 'uid');
    $values = [
      'field_editor_username' => $editor_username,
      'field_subject' => $this->getSubject($product_name),
      'field_webhost' => Settings::get('webhost', 'https://prod.cms.va.gov'),
    ];

    // Create the job.
    $job = Job::create('va_gov_outdated_content_notification', compact('template_values', 'values'));
    $queue->enqueueJob($job);
  }

  /**
   * Get the Outdated Content for a Section.
   *
   * @param string $section
   *   The term id of the section to gather content from.
   * @param array $exempt_types
   *   An array of content types (bundles) to ignore.
   *
   * @return string[]
   *   An array of node ids for nodes that are outdated and need editing.
   */
  protected function getOutdatedContentForSection(string $section, array $exempt_types): array {
    $offset = strtotime('-12 month');
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', $exempt_types, 'NOT IN')
      ->condition('status', 1)
      ->condition('field_last_saved_by_an_editor', $offset, '<=')
      ->condition('field_administration', $section, '=');
    return $query->accessCheck(FALSE)->execute();
  }

  /**
   * Gets specified editors or all editors.
   *
   * @param string[] $uids
   *   (optional) User ids to look up explicitly.
   *
   * @return \Drupal\user\UserInterface[]
   *   An array of all active users in the CMS.
   */
  protected function getAllEditors(array $uids): array {
    $explicit = !empty($uids);
    $userStorage = $this->entityTypeManager->getStorage('user');
    if (empty($uids)) {
      // No uids provided, so get them all.
      $uids = $userStorage->getQuery()
        ->condition('status', 1)
        ->accessCheck(FALSE)
        ->execute();
    }
    $users = $userStorage->loadMultiple($uids);
    $users = $this->removeBlockedUsers($users, $explicit);

    return $users;
  }

  /**
   * Removes any blocked users from the users if they were explicitly called.
   *
   * @param \Drupal\user\UserInterface[] $users
   *   CMS users.
   * @param bool $explicit
   *   A bool indicating whether the data was explicitly set by a caller.
   *
   * @return \Drupal\user\UserInterface[]
   *   Users with any blocked users removed if it was explicitly called.
   */
  protected function removeBlockedUsers(array $users, bool $explicit): array {
    if ($explicit) {
      // These users were hand specified.  Some of them might be blocked.
      foreach ($users as $key => $user) {
        if ($user->isBlocked()) {
          unset($users[$key]);
        }
      }
    }

    return $users;
  }

  /**
   * Gets the sections for the editor.
   *
   * @param \Drupal\Core\Session\AccountInterface $editor
   *   The editor user object.
   *
   * @return string[]
   *   The section ids assigned to the editor.
   */
  protected function getEditorsSections(AccountInterface $editor): array {
    return $this->userSectionStorage->getUserSections($this->getScheme(), $editor, FALSE);
  }

  /**
   * Gets the access scheme for the section.
   *
   * @return \Drupal\workbench_access\Entity\AccessSchemeInterface
   *   The Access scheme for the section.
   */
  protected function getScheme(): AccessSchemeInterface {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $scheme_storage */
    $scheme_storage = $this->entityTypeManager->getStorage('access_scheme');
    /** @var \Drupal\workbench_access\Entity\AccessSchemeInterface $scheme */
    $scheme = $scheme_storage->load('section');
    return $scheme;
  }

}
