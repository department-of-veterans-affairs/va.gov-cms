<?php

namespace Drupal\va_gov_govdelivery\Service;

use Drupal\node\NodeInterface;

/**
 * Class for processing facility status to GovDelivery Bulletin.
 */
class ProcessStatusBulletin {

  private $node;
  private $situationUpdate = NULL;

  private $sendType;

  /**
   * ProcessStatusBulletin constructor.
   */
  public function __construct() {

  }

  /**
   * Triggers the process for the whole thing.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   */
  public function processNode(NodeInterface $node) {
    $this->node = $node;
    $this->setSendType();
    $this->published = $node->status;
    if ($this->sendType === 'status alert') {
      $queue_id = "{$node->get('nid')->value}-alert";
      // Pull the data from the alert fields.
      $body = $node->get('field_body')->getString();
      $type = $node->get("field_alert_type")->getString();
      $header = '';
      $footer = '';
      $subject = $node->get('title')->getString();
      // @TODO  get the time the alert was saved.
    }
    elseif ($this->sendType === 'situation update') {
      // Might be multiples, use this to dedupe so that only the last one goes.
      $queue_id = "{$node->get('nid')->value}-update";
      // Pull the data from the situation update fields $this->situationUpdate.
      $time = $this->situationUpdate->get('field_date_and_time')->value;
      $body = $this->situationUpdate->get('field_wysiwyg')->value;
      $type = $node->get("field_alert_type")->getString();
      $header = '';
      $footer = '';
      $subject = "{$node->get('title')->getString()} - Situation Update";
    }

    if (!empty($this->sendType)) {

      $vmacs_data = $node->get('field_banner_alert_computdvalues')->getValue();
      $vmacs_datum = reset($vmacs_data);
      $vmacs = !empty($vmacs_datum['value']) ? json_decode($vmacs_datum['value']) : [];

      // Loop through the VMACs since each title will be VAMC specific.
      foreach ($vmacs as $vmac) {
        $vamc_title = $vmac->vamc_title;
        $vamc_path = $vmac->vamc_path;
        $vamc_op_status_path = $vmac->vamc_op_status_path;
        // Add the item to queue.
        \Drupal::service('govdelivery_bulletins.add_bulletin_to_queue')
          ->setFlag('dedupe', TRUE)
          ->setQueueUid($queue_id)
          // @TODO run body through a twig template.
          ->setBody($body)
          ->setFooter($footer)
          ->setFromAddress('us@example.org')
          ->setGovDeliveryID('some_unique_id')
          ->setHeader($header)
          // @TODO make subject VMAC specific.
          ->setSubject("{$type}: {$vmac->vamc_title} {$subject}")
          // ->setSMSBody('Some text SMS text.')
          ->addTopic($vmac->vamc_topic_id)
          ->setXmlBool('click_tracking', FALSE)
          ->setXmlBool('open_tracking', FALSE)
          ->setXmlBool('publish_rss', FALSE)
          ->setXmlBool('share_content_enabled', TRUE)
          ->setXmlBool('urgent', FALSE)
          ->addToQueue();
      }
    }
  }

  /**
   * Set the value of sendType.  Will only be set if something should be sent.
   */
  private function setSendType() {
    $sendType = FALSE;
    // If field_operating_status_sendemail is checked AND it is the first save
    // (no original) then it is a status alert.
    $original = $this->node->original;
    $published = $this->node->isPublished();

    $first_save = (empty($this->node->original)) ? TRUE : FALSE;
    $send_status_email = $this->node->get('field_operating_status_sendemail')->value;
    $first_save_published = ($first_save && $published);
    $just_updated_to_published = (!$first_save && $published && !$this->node->original->isPublished());

    if ($send_status_email) {
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
