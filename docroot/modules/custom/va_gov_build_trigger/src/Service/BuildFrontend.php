<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;
use Drupal\va_gov_build_trigger\WebBuildStatusInterface;

/**
 * Class for processing facility status to GovDelivery Bulletin.
 */
class BuildFrontend implements BuildFrontendInterface {

  use StringTranslationTrait;

  /**
   * The active user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   *  The user object.
   */
  protected $currentUser;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The Web Status provider.
   *
   * @var \Drupal\va_gov_build_trigger\WebBuildStatusInterface
   */
  protected $webStatus;

  /**
   * EnvironmentDiscovery Service.
   *
   * @var \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery
   */
  protected $environmentDiscovery;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * BuildFrontend constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger interface.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param \Drupal\va_gov_build_trigger\WebBuildStatusInterface $web_build_status
   *   The Web Build Status.
   * @param \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery $environment_discovery
   *   The Environment Discovery Service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(
    MessengerInterface $messenger,
    LoggerChannelFactoryInterface $logger_factory,
    WebBuildStatusInterface $web_build_status,
    EnvironmentDiscovery $environment_discovery,
    AccountInterface $current_user
  ) {
    $this->messenger = $messenger;
    $this->logger = $logger_factory->get('va_gov_build_trigger');
    $this->webStatus = $web_build_status;
    $this->environmentDiscovery = $environment_discovery;
    $this->currentUser = $current_user;

  }

  /**
   * {@inheritdoc}
   */
  public function triggerFrontendBuild(string $front_end_git_ref = NULL, bool $full_rebuild = FALSE) : void {
    try {
      $this->environmentDiscovery->triggerFrontendBuild($front_end_git_ref, $full_rebuild);
    }
    catch (PluginException $e) {
      // In an unaccounted for environment without a plugin.
      $message = $this->t('You cannot trigger a build in this environment. Only the tugboat, lando, DEV, STAGING and PROD environments support triggering builds.');
      $this->messenger->addWarning($message);
      $this->logger->warning($message);
      $this->setPendingState(FALSE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setPendingState(bool $state) : void {
    if ($state) {
      $this->webStatus->enableWebBuildStatus();
    }
    else {
      $this->webStatus->disableWebBuildStatus();
    }
  }

  /**
   * Check to see if this had a status or status info change.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object of a node just updated or saved.
   *
   * @return bool
   *   TRUE if there was a status related change, FALSE if there was not.
   */
  protected function facilityChangedStatus(NodeInterface $node) {
    $status_field = 'field_operating_status_facility';
    $status_info_field = 'field_operating_status_more_info';
    if ($this->isFacility($node) && $node->hasField($status_field)) {
      // This is a node that should be checked for status change.
      $has_status_related_change = $this->changedValue($status_field, $node) || $this->changedValue($status_info_field, $node);
      // If the old state was draft, we can not detect a previous status change,
      // so we will have release because we can not be sure.
      $oldstate_was_draft = $this->getOriginalFieldValue('moderation_state', $node) === 'draft';
      $oldstate_was_archived = $this->getOriginalFieldValue('moderation_state', $node) === 'archived';
      $archived_from_published = ($this->getOriginalFieldValue('moderation_state', $node) === 'published' && $node->get('moderation_state')->value === 'archived');
      if ($this->isTriggerableState($node) && ($has_status_related_change || $oldstate_was_draft || $oldstate_was_archived || $archived_from_published)) {
        // The status related info changed, so release.
        return TRUE;
      }
    }
    // Made it this far, nothing changed.
    return FALSE;
  }

  /**
   * Gets the previously saved value of a field.
   *
   * @param string $field_name
   *   The machine name of the field to get.
   * @param \Drupal\node\NodeInterface $node
   *   The node object of a node just updated or saved.
   *
   * @return string
   *   The value of the field, or '' if not found.
   */
  protected function getOriginalFieldValue($field_name, NodeInterface $node) {
    $value = '';
    if (isset($node->original) && ($node->original instanceof NodeInterface)) {
      // There was a previous save.
      $value = $node->original->get($field_name)->value;
    }

    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function triggerFrontendBuildFromContentSave(NodeInterface $node) {
    if ($this->isTriggerableState($node) && ($this->isTriggerableType($node) || $this->facilityChangedStatus($node))) {
      $msg_vars = [
        '%link_to_node' => $node->toLink(NULL, 'canonical', ['absolute' => TRUE])->toString(),
        '%nid' => $node->id(),
        '%type' => $node->getType(),
        '%user' => $this->currentUser->getAccountName(),
      ];
      if (!$this->environmentDiscovery->shouldTriggerFrontendBuild()) {
        $message = $this->t('A content release would have been triggered by a change to %type: %link_to_node , but this environment has it disabled.', $msg_vars);
        $this->messenger->addStatus($message);
        return;
      }
      $this->triggerFrontendBuild();
      $log_message = $this->t('A content release was triggered by a change to %type: %link_to_node (node%nid) by user %user.', $msg_vars);
      $this->logger->info($log_message);
      $message = $this->t('A content release has been triggered by the change you made to the %type: %link_to_node.', $msg_vars);
      $this->messenger->addStatus($message);
    }
  }

  /**
   * Checks if a node is content type that can trigger a content release.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node being altered.
   *
   * @return bool
   *   TRUE if it is a content type that can trigger a build.  FALSE otherwise.
   */
  protected function isTriggerableType(NodeInterface $node): bool {
    $triggerable_content_types = [
      'banner',
      'full_width_banner_alert',
    ];
    return in_array($node->getType(), $triggerable_content_types);
  }

  /**
   * Checks if a node is content type that is a facility.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node being altered.
   *
   * @return bool
   *   TRUE if it is a content type is a facility.  FALSE otherwise.
   */
  protected function isFacility(NodeInterface $node): bool {
    $facility_content_types = [
      'health_care_local_facility',
      // 'nca_facility',  // Not rendered on the FE yet.  Add it when it is.
      // 'vba_facility',  // Not rendered on the FE yet.  Add it when it is.
      'vet_center_cap',
      'vet_center_outstation',
      'vet_center',
    ];
    return in_array($node->getType(), $facility_content_types);
  }

  /**
   * Checks if a node has gone through a state change that warrants a release.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node being altered.
   *
   * @return bool
   *   TRUE if state change needs a release.  FALSE otherwise.
   */
  protected function isTriggerableState(NodeInterface $node): bool {
    $moderation_state_new = $node->get('moderation_state')->value;
    $is_published = $node->isPublished();
    // If the current state is archived, isPublished lies to us because the save
    // just happened, so we have to look back in time.
    $was_published = (isset($node->original) && ($node->original instanceof NodeInterface)) ? $node->original->isPublished() : FALSE;
    $has_been_published = $is_published || $was_published;

    switch (TRUE) {
      case ($moderation_state_new === 'published'):
        // Normal publish of revision.
      case ($has_been_published && ($moderation_state_new === 'archived')):
        // Archive of published node.
      case ($is_published && ($moderation_state_new === NULL)):
        // Covers publishing of entity not governed by workbench moderation.
      case ($was_published && !$is_published && ($moderation_state_new === NULL)):
        // Covers unpublishing of entity not governed by workbench moderation.
        return TRUE;

      default:
        return FALSE;
    }
  }

  /**
   * Checks if the value of the field on the node changed.
   *
   * @param string $field_name
   *   The machine name of the field to check on.
   * @param \Drupal\node\NodeInterface $node
   *   The node being altered.
   *
   * @return bool
   *   TRUE if the value changed.  FALSE otherwise.
   */
  protected function changedValue($field_name, NodeInterface $node): bool {
    $value = $node->get($field_name)->value;
    $original_value = $this->getOriginalFieldValue($field_name, $node);
    return $value !== $original_value;
  }

}
