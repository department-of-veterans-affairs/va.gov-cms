<?php

namespace Drupal\va_gov_govdelivery\Service;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\govdelivery_bulletins\Service\AddBulletinToQueue;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\AliasManager;

/**
 * Class for processing facility status to GovDelivery Bulletin.
 */
class ProcessStatusBulletin {

  /**
   * Send type for situation updates.
   *
   * @var string
   */
  const SEND_TYPE_SITUATION = 'situation update';

  /**
   * Send type for status alerts.
   *
   * @var string
   */
  const SEND_TYPE_STATUS = 'status alert';

  /**
   * Drupal\govdelivery_bulletins\Service\AddBulletinToQueue definition.
   *
   * @var \Drupal\govdelivery_bulletins\Service\AddBulletinToQueue
   */
  protected $addBulletinToQueue;

  /**
   * Drupal\path_alias\AliasManager definition.
   *
   * @var \Drupal\path_alias\AliasManager
   */
  protected $aliasManager;

  /**
   * Drupal\Core\Datetime\DateFormatter definition.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Renderer Service.
   *
   * @var Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * ProcessStatusBulletin constructor.
   *
   * @var \Drupal\govdelivery_bulletins\Service\AddBulletinToQueue $add_bulletin_to_queue
   *   The add bulletin to queue service.
   * @var Drupal\path_alias\AliasManager
   *   The path alias manager service.
   * @var \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter service.
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @var Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(
    AddBulletinToQueue $add_bulletin_to_queue,
    AliasManager $alias_manager,
    DateFormatter $date_formatter,
    EntityTypeManagerInterface $entity_type_manager,
    RendererInterface $renderer
  ) {
    $this->addBulletinToQueue = $add_bulletin_to_queue;
    $this->aliasManager = $alias_manager;
    $this->dateFormatter = $date_formatter;
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
  }

  /**
   * Triggers the process for the whole thing.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   *
   * @throws \Twig_Error_Runtime
   */
  public function processNode(NodeInterface $node) {
    if (!$this->bulletinsEnabled($node)) {
      return;
    }

    $situation_update = $this->getLatestSituationUpdate($node);
    $send_type = $this->getSendType($node, $situation_update);
    if (empty($send_type)) {
      return;
    }

    $template = $this->buildTemplate($node, $situation_update, $send_type);

    // Loop through the VAMCs since each title will be VAMC specific.
    $vamcs = $this->getVamcs($node);
    foreach ($vamcs as $vamc) {
      $this->queueBulletinForVamc($node, $vamc, $template, $send_type);
    }
  }

  /**
   * Build a situation update template.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   * @param array $vamc
   *   The VAMC information.
   * @param array $template
   *   The populated template.
   * @param string $send_type
   *   The alert send type.
   */
  protected function queueBulletinForVamc(
    NodeInterface $node,
    array $vamc,
    array $template,
    string $send_type
  ) : void {
    $template['#vamc_name'] = $vamc['vamc_title'];
    $template['#vamc_url'] = $vamc['vamc_path'];
    $template['#ops_page_url'] = $vamc['vamc_op_status_path'];

    if ($send_type === self::SEND_TYPE_STATUS) {
      $subject_prefix = 'Alert';
    }
    elseif ($send_type === self::SEND_TYPE_SITUATION) {
      $subject_prefix = 'Situation Update';
    }
    $subject = "{$subject_prefix}: {$vamc['vamc_title']}";

    $queue_id = $this->getQueueId($node, $send_type);

    $this->addBulletinToQueue
      ->setFlag('dedupe', TRUE)
      ->setQueueUid("{$queue_id}-{$vamc['vamc_topic_id']}")
      ->setBody($this->renderer->renderPlain($template))
      ->setFooter(NULL)
      ->setHeader(NULL)
      ->setSubject($subject)
      ->addTopic($vamc['vamc_topic_id'])
      ->setXmlBool('click_tracking', FALSE)
      ->setXmlBool('open_tracking', FALSE)
      ->setXmlBool('publish_rss', FALSE)
      ->setXmlBool('share_content_enabled', TRUE)
      ->setXmlBool('urgent', FALSE)
      ->addToQueue();
  }

  /**
   * Get queue ID.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   * @param string $send_type
   *   The alert send type.
   *
   * @return string
   *   The queue id.
   */
  protected function getQueueId(NodeInterface $node, $send_type) : string {
    $queue_type = '';

    if ($send_type === self::SEND_TYPE_STATUS) {
      $queue_type = 'alert';
    }

    if ($send_type === self::SEND_TYPE_SITUATION) {
      $queue_type = 'update';
    }

    return "{$node->get('nid')->value}-{$queue_type}";
  }

  /**
   * Build a situation update template.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   * @param mixed $latest_situation_update
   *   The latest situation update if one exists.
   * @param string $send_type
   *   The alert send type.
   *
   * @return array
   *   The populated template.
   */
  protected function buildTemplate(NodeInterface $node, $latest_situation_update, string $send_type) : array {
    $template = [
      '#alert_title' => $node->get('title')->getString(),
      // The type of alert 'information' or 'warning'.
      '#alert_type' => $node->get('field_alert_type')->value,
      '#theme' => 'va_gov_body_alert',
    ];

    if ($send_type === self::SEND_TYPE_STATUS) {
      $template['#message'] = $node->get('field_body')->value;
      $template['#situation_update'] = FALSE;
      $time = time();
      $time = $this->dateFormatter->format($time, 'custom', 'n/j/Y h:i A T');
    }
    elseif ($send_type === self::SEND_TYPE_SITUATION) {
      $template['#situation_update'] = TRUE;
      $template['#message'] = $latest_situation_update
        ->get('field_wysiwyg')
        ->value;
      $time = (int) $latest_situation_update
        ->get('field_datetime_range_timezone')
        ->value;
      $time = $this->dateFormatter->format($time, 'custom', 'n/j/Y h:i A T');
      $subject_prefix = 'Situation Update';
    }
    $template['#date_time'] = $time;

    return $template;
  }

  /**
   * Get VAMCs referenced by this node.
   *
   * @param Drupal\node\NodeInterface $node
   *   The status update node.
   *
   * @return array[vamcs]
   *   Array of VAMCs for this node.
   */
  protected function getVamcs(NodeInterface $node) : array {
    $vamcs = [];

    $vamcs_op_status_ids = $node->get('field_banner_alert_vamcs')->getValue();
    foreach ($vamcs_op_status_ids as $key => $vamcs_op_status_id) {
      $vamcs_op_status_ids[$key] = $vamcs_op_status_id['target_id'] ?: '';
    }

    $node_storage = $this->entityTypeManager->getStorage('node');
    $vamc_op_nodes = $node_storage->loadMultiple($vamcs_op_status_ids);

    // Get out op status page paths.
    $vamc_office_nids = [];
    foreach ($vamc_op_nodes as $key => $vamc_op_node) {
      $vamc_office_nid = $vamc_op_node->get('field_office')->getString();
      $vamcs[$vamc_office_nid]['vamc_op_status_path'] =
        $this->aliasManager->getAliasByPath('/node/' . $vamc_op_node->id());
      $vamc_office_nids[] = $vamc_office_nid;
    }

    // Grab what we need from our VAMCs.
    $vamc_system_nodes = $node_storage->loadMultiple($vamc_office_nids);
    foreach ($vamc_system_nodes as $key => $vamc_system_node) {
      $vamcs[$key]['vamc_topic_id'] =
        $vamc_system_node->get('field_govdelivery_id_emerg')->getString() ?: '';
      $vamcs[$key]['vamc_title'] = $vamc_system_node->getTitle();
      $vamcs[$key]['vamc_path'] = $vamc_system_node->toUrl()->toString();
    }

    return $vamcs;
  }

  /**
   * Determine whether update bulletins are enabled for the node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return bool
   *   Whether or not update bulletins should be sent.
   */
  protected function bulletinsEnabled(NodeInterface $node) : bool {
    return $node->isPublished() &&
      $node->get('field_operating_status_sendemail')->value;
  }

  /**
   * Retrieve the latest situation update paragraph, if any.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return \Drupal\paragraphs\ParagraphInterface
   *   Latest situation update paragraph or null.
   */
  protected function getLatestSituationUpdate(NodeInterface $node) : ?ParagraphInterface {
    $situation_updates_list = $node
      ->get('field_situation_updates')
      ->referencedEntities();
    return end($situation_updates_list) ?: NULL;
  }

  /**
   * Get the sending type. Will only be set if something should be sent.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   * @param mixed $latest_situation_update
   *   The latest situation update if one exists.
   *
   * @return string
   *   Sending type (empty if none).
   */
  private function getSendType(NodeInterface $node, $latest_situation_update) : string {
    // If field_operating_status_sendemail is checked AND it is the first save
    // (no original) then it is a status alert.
    $original = $node->original;

    $first_save = empty($node->original) ? TRUE : FALSE;
    $first_save_published = ($first_save && $node->isPublished());
    $just_updated_to_published = (
      !$first_save &&
      $node->isPublished() &&
      !$node->original->isPublished()
    );

    if ($first_save_published || $just_updated_to_published) {
      // This is the first that the node has been published, should be queued
      // as a status update.
      return self::SEND_TYPE_STATUS;
    }
    else {
      $situation_update_send =
        !empty($latest_situation_update) ?
        $latest_situation_update->get('field_send_email_to_subscribers')->value :
        FALSE;

      if (!$situation_update_send) {
        return '';
      }

      // This should be sent or was already sent.
      $situation_update_id =
        !empty($latest_situation_update) ?
        $latest_situation_update->get('id')->value :
        FALSE;

      // We need to see if the original situation update is not a match.
      // If it is NOT a match, this needs to be sent.
      $situation_updates_list_original = $node->original
        ->get('field_situation_updates')
        ->referencedEntities();
      $situation_updates_last_original = end($situation_updates_list_original);
      $situation_update_id_original =
        !empty($situation_updates_last_original) ?
        $situation_updates_last_original->get('id')->value :
        FALSE;

      if ($situation_update_id !== $situation_update_id_original) {
        // This is a new situation update that needs to be sent.
        return self::SEND_TYPE_SITUATION;
      }
    }
  }

}
