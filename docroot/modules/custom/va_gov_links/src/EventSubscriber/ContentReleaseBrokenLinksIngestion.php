<?php

namespace Drupal\va_gov_links\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\State\StateInterface;
use Drupal\va_gov_build_trigger\Event\ReleaseStateTransitionEvent;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Re-queues a content release when an error condition happens.
 */
class ContentReleaseBrokenLinksIngestion implements EventSubscriberInterface {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Settings Service.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Constructor for ContentReleaseBrokenLinksSubscriber objects.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   Logger logger logger logger.
   * @param \Drupal\Core\Site\Settings $settings
   *   Settings.
   */
  public function __construct(StateInterface $state, LoggerChannelFactoryInterface $loggerFactory, Settings $settings) {
    $this->state = $state;
    $this->logger = $loggerFactory->get('va_gov_links');
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ReleaseStateTransitionEvent::NAME] = 'retrieveBrokenLinks';
    return $events;
  }

  /**
   * Retrieve the broken links report when a content release finishes.
   *
   * @param \Drupal\va_gov_build_trigger\Event\ReleaseStateTransitionEvent $event
   *   The release state transition event.
   */
  public function retrieveBrokenLinks(ReleaseStateTransitionEvent $event) {
    if ($this->settings->get('broken_link_report_import_enabled')) {
      if ($event->getNewReleaseState() === ReleaseStateManager::STATE_READY) {
        if (in_array($event->getOldReleaseState(), [
          ReleaseStateManager::STATE_COMPLETE,
          ReleaseStateManager::STATE_ERROR,
        ])) {
          $reportLocation = $this->settings->get('broken_link_report_location');
          $reportContents = file_get_contents($reportLocation);
          if ($reportContents !== FALSE) {
            $this->state->set('content_release.broken_links', $reportContents);
            $this->logger->info('The broken links report was successfully read and committed to state.');
          }
          else {
            $this->logger->error('Unable to read ' . $reportLocation);
          }
        }
      }
    }
  }

}
