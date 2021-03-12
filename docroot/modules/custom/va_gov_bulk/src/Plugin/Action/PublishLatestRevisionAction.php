<?php

namespace Drupal\va_gov_bulk\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\va_gov_bulk\Service\ModerationActionsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Publish latest node revision action.
 *
 * @Action(
 *   id = "publish_latest_revision_action",
 *   label = @Translation("Publish latest revision"),
 *   type = "node",
 *   confirm = TRUE,
 * )
 */
class PublishLatestRevisionAction extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Logger\LoggerChannelFactoryInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Drupal\va_gov_bulk\Service\ModerationActionsInterface definition.
   *
   * @var Drupal\va_gov_bulk\Service\ModerationActionsInterface
   */
  protected $moderationActions;

  /**
   * Constructs a new PublishLatestRevisionAction object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param Drupal\va_gov_bulk\Service\ModerationActionsInterface $moderationActions
   *   The moderation actions service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $loggerFactory, MessengerInterface $messenger, ModerationActionsInterface $moderationActions) {
    $this->loggerFactory = $loggerFactory;
    $this->messenger = $messenger;
    $this->moderationActions = $moderationActions;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('messenger'),
      $container->get('va_gov_bulk.moderation_actions')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute(NodeInterface $node = NULL) {
    $node = $this->moderationActions->publishLatestRevision($node);

    if ($node->isPublished()) {
      $this->loggerFactory->get('va_gov_bulk')->info('Published latest revision of %title (id %id)',
        ['%title' => $node->label(), '%id' => $node->id()]
      );
    }
    else {
      $message = 'Something went wrong, the node should have been published. Review your content moderation configuration and ensure that you have an "archived" state which sets current revision and a "published" state and try again.';
      $this->messenger->addError($message);
      $this->loggerFactory->get('va_gov_bulk')->warning($message);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object->getEntityType() === 'node') {
      $access = $object->access('update', $account, TRUE)
        ->andIf($object->status->access('edit', $account, TRUE));
      return $return_as_object ? $access : $access->isAllowed();
    }

    // Other entity types may have different
    // access methods and properties.
    return TRUE;
  }

}
