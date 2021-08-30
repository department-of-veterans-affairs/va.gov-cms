<?php

namespace Drupal\va_gov_vamc\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;

/**
 * Class ContentHardeningDeduper remove unhardened dopplegangers.
 */
class ContentHardeningDeduper {

  use StringTranslationTrait;

  /**
   * The active user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   *  The user object.
   */
  private $currentUser;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The Content type replacements.
   *
   * @var array
   */
  protected $contentTypeReplacements = [
    // Key: content_type => [related data].
    // This deduper needs to act on these content types as soon as they have a
    // Front End presence. Commented out ones need to be un-commented as they
    // come online.
    // 'vamc_system_billing_insurance' => [
    // 'title' => 'Billing and insurance',
    // ],
    // 'vamc_system_medical_records_offi' => [
    // 'title' => 'Medical records office',
    // ],
    // Uncomment these as the content types have a Front End presence.
    'vamc_system_policies_page' => [
      'title' => 'Policies',
    ],
    // 'vamc_system_register_for_care' => [
    // 'title' => 'Register for care',
    // ],
  ];

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Constructs a new ContentHardeningDedupper object.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger factory service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger interface.
   */
  public function __construct(AccountInterface $current_user, EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger, MessengerInterface $messenger) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->messenger = $messenger;
  }

  /**
   * Remove any unhardend nodes that are the 1.0 version of $entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being created.
   */
  public function removeDuplicate(EntityInterface $entity): void {
    if ($this->isHardendType($entity)) {
      /** @var \Drupal\node\NodeInterface $entity */
      $existing_moderation_state = '';
      $duplicate_alias = '';
      $title = $this->contentTypeReplacements[$entity->bundle()]['title'];
      // @phpstan-ignore-next-line
      $system_nid = $entity->get('field_office')->target_id;

      $duplicate_entities = $this->getExistingDuplicates($title, $system_nid);
      foreach ($duplicate_entities as $duplicate_entity) {
        /** @var \Drupal\node\NodeInterface $duplicate_entity */
        // @phpstan-ignore-next-line
        $duplicate_alias = $duplicate_entity->path->alias;
        // Risk: only the last duplicate determines the moderation state.
        // Risk is small since there should only be one. If there more than one,
        // there is no reliable way to determine which is proper.
        // @phpstan-ignore-next-line
        $existing_moderation_state = $duplicate_entity->moderation_state->value;
        $this->retireDuplicateEntity($duplicate_entity, $entity, $title);
        $this->logAndMessage($duplicate_entity, $title);
      }

      $this->updateNewModerationState($entity, $existing_moderation_state);
      $this->updateNewAlias($entity, $duplicate_alias);
    }
  }

  /**
   * Loads vamc detail pages that match params.
   *
   * @param string $title
   *   The title to use in the lookup.
   * @param string $system_nid
   *   The system nid to use in the lookup of field_office.
   *
   * @return array
   *   An array of entities that match the params.
   */
  protected function getExistingDuplicates($title, $system_nid): array {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->accessCheck(FALSE);
    $query->condition('type', 'health_care_region_detail_page', '=');
    $query->condition('title', $title, '=');
    $query->condition('field_office', $system_nid, '=');
    $nids = $query->execute();
    if (!empty($nids)) {
      $loaded_nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    }
    return $loaded_nodes ?? [];
  }

  /**
   * Checks to see if this is node type that needs to be deduped.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being checked.
   *
   * @return bool
   *   TRUE if this is type that might have a duplicate.  FALSE otherwise.
   */
  protected function isHardendType(EntityInterface $entity): bool {
    if ($entity instanceof NodeInterface) {
      $bundle = $entity->bundle();
      if (!empty($this->contentTypeReplacements[$bundle])) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Create a log and message.
   *
   * @param \Drupal\node\NodeInterface $retired_entity
   *   The node being retired.
   * @param string $title
   *   The title of the node being retired and created.
   */
  protected function logAndMessage(NodeInterface $retired_entity, $title) : void {
    $vars = [
      '%title' => $title,
      '%nid' => $retired_entity->id(),
    ];
    $message = $this->t('The VAMC Detail Page "%title" (node: %nid) has been archived by the creation of this page.', $vars);
    $this->messenger->addStatus($message);
    $this->logger->get('va_gov_vamc')->info($message);
  }

  /**
   * Archives and alters the title of the node being retired.
   *
   * @param \Drupal\node\NodeInterface $retiring_node
   *   The node being retired.
   * @param \Drupal\node\NodeInterface $new_node
   *   The hardened node being saved.
   * @param string $title
   *   The title of the node being retired and created.
   */
  protected function retireDuplicateEntity(NodeInterface $retiring_node, NodeInterface $new_node, $title) : void {
    $retiring_node->setTitle("{$title} - REPLACED");
    // @phpstan-ignore-next-line
    $retiring_node->path->alias = "{$retiring_node->path->alias}-replaced";
    $retiring_node->set('moderation_state', 'archived');
    $retiring_node->set('revision_log', "Archived by the creation of its replacement {$new_node->bundle()}.");
    $retiring_node->set('changed', time());
    $retiring_node->set('revision_timestamp', time());
    $retiring_node->set('revision_default', TRUE);
    $retiring_node->set('revision_uid', $this->currentUser->id());
    $retiring_node->save();
  }

  /**
   * Updates the alias of the new hardened node.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The node being saved.
   * @param string $duplicate_alias
   *   The alias of the unhardened node being replaced.
   */
  protected function updateNewAlias(NodeInterface $entity, $duplicate_alias) : void {
    if (!$entity->isNew() && !empty($duplicate_alias)) {
      // This entity is not new and likely has a bad alias, so set it right.
      // @phpstan-ignore-next-line
      $entity->path->alias = $duplicate_alias;
    }
  }

  /**
   * Updates the moderation state of the new hardened node.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The node being saved.
   * @param string $existing_moderation_state
   *   The moderation state of the unhardened node being replaced.
   */
  protected function updateNewModerationState(NodeInterface $entity, $existing_moderation_state) : void {
    // If we have a moderation_state on the existing, only carry it forward if
    // it has not already been archived.
    // If the current state is already set to published, do not override.
    // @phpstan-ignore-next-line
    if ($existing_moderation_state !== 'archived' && $entity->moderation_state->value !== 'published') {
      $entity->set('moderation_state', $existing_moderation_state);
    }
  }

}
