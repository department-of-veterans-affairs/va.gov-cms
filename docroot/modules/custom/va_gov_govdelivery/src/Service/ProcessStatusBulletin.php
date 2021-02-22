<?php

namespace Drupal\va_gov_govdelivery\Service;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\NodeInterface;
use Drupal\path_alias\AliasManager;
use Drupal\govdelivery_bulletins\Service\AddBulletinToQueue;

/**
 * Class for processing facility status to GovDelivery Bulletin.
 */
class ProcessStatusBulletin {

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
   * Node.
   *
   * @var mixed
   */
  private $node;

  /**
   * The Renderer Service.
   *
   * @var Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Send Type.
   *
   * @var mixed
   */
  private $sendType;

  /**
   * Situation Update.
   *
   * @var mixed
   */
  private $situationUpdate = NULL;

  /**
   * ProcessStatusBulletin constructor.
   *
   * @var \Drupal\govdelivery_bulletins\Service\AddBulletinToQueue $add_bulletin_to_queue
   *   The add bulletin to queue service.
   * @var Drupal\path_alias\AliasManager
   *   The path alias manager service.
   * @var \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter service.
   * @var Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(
    AddBulletinToQueue $add_bulletin_to_queue,
    AliasManager $alias_manager,
    DateFormatter $date_formatter,
    RendererInterface $renderer
  ) {
    $this->addBulletinToQueue = $add_bulletin_to_queue;
    $this->aliasManager = $alias_manager;
    $this->dateFormatter = $date_formatter;
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
    $this->node = $node;
    $this->published = $node->isPublished();

    $this->setSendType();
    if (!$this->sendType) {
      return;
    }

    $template = [
      '#alert_title' => $node->get('title')->getString(),
      // The type of alert 'information' or 'warning'.
      '#alert_type' => $node->get("field_alert_type")->value,
      '#theme' => 'va_gov_body_alert',
    ];

    if ($this->sendType === 'status alert') {
      $queue_id = "{$node->get('nid')->value}-alert";
      $template['#message'] = $node->get('field_body')->value;
      $subject_prefix = "Alert";
      $time = time();
      $time = $this->dateFormatter->format($time, 'custom', 'n/j/Y h:i A T');
    }
    elseif ($this->sendType === 'situation update') {
      // Might be multiples, used to dedupe so that only the last one goes.
      $queue_id = "{$node->get('nid')->value}-update";

      $template['#situation_update'] = TRUE;
      $template['#message'] = $this
        ->situationUpdate
        ->get('field_wysiwyg')
        ->value;
      $time = $this
        ->situationUpdate
        ->get('field_date_and_time')
        ->date
        ->getTimestamp();
      $time = $this->dateFormatter->format($time, 'custom', 'n/j/Y h:i A T');
      $subject_prefix = "Situation Update";
    }
    $template['#date_time'] = $time;

    // Loop through the VAMCs since each title will be VAMC specific.
    $vamcs = $this->getVamcs($node);
    foreach ($vamcs as $vamc) {
      $template['#vamc_name'] = $vamc['vamc_title'];
      $template['#vamc_url'] = $vamc['vamc_path'];
      $template['#ops_page_url'] = $vamc['vamc_op_status_path'];

      $this->addBulletinToQueue
        ->setFlag('dedupe', TRUE)
        ->setQueueUid("{$queue_id}-{$vamc['vamc_topic_id']}")
        ->setBody($this->renderer->renderPlain($template))
        ->setFooter(NULL)
        ->setHeader(NULL)
        ->setSubject("{$subject_prefix}: {$vamc['vamc_title']}")
        ->addTopic($vamc['vamc_topic_id'])
        ->setXmlBool('click_tracking', FALSE)
        ->setXmlBool('open_tracking', FALSE)
        ->setXmlBool('publish_rss', FALSE)
        ->setXmlBool('share_content_enabled', TRUE)
        ->setXmlBool('urgent', FALSE)
        ->addToQueue();
    }
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

    // Get a node storage object.
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');

    $vamc_office_nids = [];
    $computed_return = [];
    $vamc_op_nodes = $node_storage->loadMultiple($vamcs_op_status_ids);

    // Get out op status page paths.
    foreach ($vamc_op_nodes as $key => $vamc_op_node) {
      $vamc_office_nid = $vamc_op_node->get('field_office')->getString();
      $vamcs[$vamc_office_nid]['vamc_op_status_path'] =
        $this->aliasManager->getAliasByPath('/node/' . $vamc_op_node->id());
      $vamc_office_nids[] = $vamc_office_nid;
    }

    // Grab what we need from our vamcs.
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
   * Set the value of sendType.  Will only be set if something should be sent.
   */
  private function setSendType() {
    $sendType = FALSE;
    // If field_operating_status_sendemail is checked AND it is the first save
    // (no original) then it is a status alert.
    $original = $this->node->original;

    $first_save = (empty($this->node->original)) ? TRUE : FALSE;
    $send_status_email = $this->node->get('field_operating_status_sendemail')->value;
    $first_save_published = ($first_save && $this->published);
    $just_updated_to_published = (!$first_save && $this->published && !$this->node->original->isPublished());
    if ($this->published && $send_status_email) {
      // The node is set to send email.
      if ($first_save_published || $just_updated_to_published) {
        // This is the first that the node has been published, should be queued
        // as a status update.
        $sendType = 'status alert';
      }
      else {
        // Look for a new situation update that needs to be sent.
        // Grab the last situation update from the array of updates.
        // Risk: Assumes only the last one might need to be sent.
        $situationUpdatesList = $this->node->get('field_situation_updates')->referencedEntities();
        $situationUpdatesLast = end($situationUpdatesList);

        $situation_update_send = (!empty($situationUpdatesLast)) ? $situationUpdatesLast->get('field_send_email_to_subscribers')->value : FALSE;
        if ($situation_update_send) {
          // This should be sent or was already sent.
          $situation_update_id = (!empty($situationUpdatesLast)) ? $situationUpdatesLast->get('id')->value : FALSE;
          // Need to see if the original situation update is not a match.
          // If it is NOT a match, this needs to be sent.
          $situationUpdatesListOriginal = $this->node->original->get('field_situation_updates')->referencedEntities();
          $situationUpdatesLastOriginal = end($situationUpdatesListOriginal);
          $situation_update_id_original = (!empty($situationUpdatesLastOriginal)) ? $situationUpdatesLastOriginal->get('id')->value : FALSE;
          if ($situation_update_id !== $situation_update_id_original) {
            // This is a new situation update that needs to be sent.
            $this->situationUpdate = $situationUpdatesLast;
            $sendType = 'situation update';
          }
        }

      }
    }

    $this->sendType = $sendType;
  }

}
