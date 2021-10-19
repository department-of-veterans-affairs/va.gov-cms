<?php

namespace Drupal\va_gov_post_api\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\post_api\Service\AddToQueue;

/**
 * Class PostFacilityService posts specific service info to Lighthouse.
 */
abstract class PostFacilityBase {
  use StringTranslationTrait;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerChannelFactory;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The Post queue add service.
   *
   * @var \Drupal\post_api\Service\AddToQueue
   */
  protected $postQueue;

  /**
   * Constructs a new PostFacilityBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   The logger factory service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger interface.
   * @param \Drupal\post_api\Service\AddToQueue $post_queue
   *   The PostAPI service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger_channel_factory, MessengerInterface $messenger, AddToQueue $post_queue) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerChannelFactory = $logger_channel_factory;
    $this->messenger = $messenger;
    $this->postQueue = $post_queue;
  }

  /**
   * Checks to see if the data checks should be bypassed.
   *
   * @return bool
   *   TRUE if bypass, FALSE if no bypass.
   */
  protected function shouldBypass() : bool {
    return !empty($this->configFactory->get('va_gov_post_api.settings')->get('bypass_data_check'));
  }

  /**
   * Checks to see if the post queueing should dedupe.
   *
   * @return bool
   *   TRUE if deduping, FALSE otherwise.
   */
  protected function shouldDedupe() : bool {
    // If bypass_data_check setting is enabled, do not dedupe..
    return !$this->shouldBypass();
  }

}
