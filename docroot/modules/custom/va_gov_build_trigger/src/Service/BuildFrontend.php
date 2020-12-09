<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\node\NodeInterface;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;
use Drupal\va_gov_build_trigger\WebBuildStatusInterface;

/**
 * Class for processing facility status to GovDelivery Bulletin.
 */
class BuildFrontend {

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
   */
  public function __construct(
    MessengerInterface $messenger,
    LoggerChannelFactoryInterface $logger_factory,
    WebBuildStatusInterface $web_build_status,
    EnvironmentDiscovery $environment_discovery
  ) {
    $this->messenger = $messenger;
    $this->logger = $logger_factory->get('va_gov_build_trigger');
    $this->webStatus = $web_build_status;
    $this->environmentDiscovery = $environment_discovery;
  }

  /**
   * Triggers the appropriate frontend Build based on the environment.
   */
  public function triggerFrontendBuild() {
    try {
      $this->environmentDiscovery->triggerFrontendBuild();
    }
    catch (PluginException $e) {
      // In an unaccounted for environment without a plugin.
      $message = t('You cannot trigger a build in this environment. Only the DEV, STAGING and PROD environments support triggering builds.');
      $this->messenger->addWarning($message);
      $this->logger->warning($message);
      $this->setPendingState(FALSE);
    }
  }

  /**
   * Set the config state of build pending.
   *
   * @param bool $state
   *   The state that should be set for build pending.
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
  private function changedStatus(NodeInterface $node) {
    // Check for change of workflow to published.
    $mod_state = $node->get('moderation_state')->value;
    $mod_state_original = $this->getOriginalFieldValue($node, 'moderation_state');
    if (($mod_state === 'published') && ($mod_state !== $mod_state_original)) {
      // The status is published and was not before.
      return TRUE;
    }

    // Check for change of operating status.
    $status_field = 'field_operating_status_facility';
    if ($node->hasField($status_field)) {
      $operating_status = $node->get($status_field)->value;
      $original_operating_status = $this->getOriginalFieldValue($node, $status_field);
      if ($operating_status !== $original_operating_status) {
        return TRUE;
      }
    }

    // Check for change of operating status more info.
    $status_info_field = 'field_operating_status_more_info';
    if ($node->hasField($status_info_field)) {
      $additional_info = $node->get($status_info_field)->value;
      $original_additional_info = $this->getOriginalFieldValue($node, $status_info_field);
      if ($additional_info !== $original_additional_info) {
        return TRUE;
      }
    }
    // Made it this far, nothing changed.
    return FALSE;
  }

  /**
   * Gets the previously saved value of a field.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object of a node just updated or saved.
   * @param string $fieldname
   *   The machine name of the field to get.
   *
   * @return string
   *   The value of the field, or '' if not found.
   */
  private function getOriginalFieldValue(NodeInterface $node, $fieldname) {
    $value = '';
    if (isset($node->original) && ($node->original instanceof NodeInterface)) {
      // There was a previous save.
      $value = $node->original->get($fieldname)->value;
    }

    return $value;
  }

  /**
   * Method to trigger a frontend build as the result of a save.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object of a node just updated or saved.
   */
  public function triggerFrontendBuildFromContentSave(NodeInterface $node) {
    if (!$this->environmentDiscovery->shouldTriggerFrontendBuild()) {
      return;
    }

    $allowed_content_types = [
      'full_width_banner_alert',
      'health_care_local_facility',
    ];
    if (in_array($node->getType(), $allowed_content_types)) {
      // This is the right content type to trigger a build. Is it published?
      if ($node->isPublished()) {
        // It is published.
        if ($node->getType() === 'health_care_local_facility') {
          // This is a facility, check if the status or status info changed.
          if ($this->changedStatus($node)) {
            // The status changed so trigger a build.
            $this->triggerFrontendBuild();
          }
        }
        else {
          $this->triggerFrontendBuild();
        }
      }
    }
  }

}
