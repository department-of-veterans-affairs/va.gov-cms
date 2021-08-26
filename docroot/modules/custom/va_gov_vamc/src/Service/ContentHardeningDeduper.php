<?php

namespace Drupal\va_gov_vamc\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;

/**
 * Class ContentHardeningDeduper remove unhardened dopplegangers.
 */
class ContentHardeningDeduper {

  use StringTranslationTrait;

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
    'vamc_system_billing_insurance' => [
      'title' => 'Billing and insurance',
    ],
    'vamc_system_medical_records_offi' => [
      'title' => 'Medical records office',
    ],
    'vamc_system_policies_page' => [
      'title' => 'Policies',
    ],
    'vamc_system_register_for_care' => [
      'title' => 'Register for care',
    ],
  ];

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Constructs a new PostFacilityService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger factory service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger interface.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger, MessengerInterface $messenger) {
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
      $title = $this->contentTypeReplacements[$entity->bundle()]['title'];
      $system_nid = $entity->get('field_office')->target_id;

      $duplicate_entities = $this->getExistingDuplicates($title, $system_nid);
      foreach ($duplicate_entities as $duplicate_entity) {
        /** @var \Drupal\node\NodeInterface $duplicate_entity */
        $duplicate_entity->setTitle("{$title} - REPLACED");
        $duplicate_entity->path->alias = "{$duplicate_entity->path->alias}-replaced";
        // Risk: only the last duplicate determines the moderation state.
        // Risk is small since there should only be one. If there more than one,
        // there is no reliable way to determine which is proper.
        $existing_moderation_state = $duplicate_entity->moderation_state->value;
        $duplicate_entity->set('moderation_state', 'archived');
        $duplicate_entity->set('revision_log', "Archived by the creation of its replacement {$entity->bundle()}.");
        $duplicate_entity->set('changed', time());
        $duplicate_entity->set('revision_timestamp', time());
        $duplicate_entity->set('revision_default', TRUE);
        $duplicate_entity->save();
        $vars = [
          '%title' => $title,
          '%nid' => $duplicate_entity->id(),
        ];
        $message = $this->t('The VAMC detail page "%title" (node: %nid) as been archived by the creation of this page.', $vars);
        $this->messenger->addStatus($message);
        $this->logger->get('va_gov_vamc')->info($message);
      }
      // If we have a moderation_state only carry it forward if it has not
      // already been archived and the current state is not published.
      if (!empty($existing_moderation_state)
        && $existing_moderation_state !== 'archived'
        && $entity->moderation_state->value !== 'published') {
        $entity->set('moderation_state', $existing_moderation_state);
      }
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

}
