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
    $this->published = $node->isPublished();
    $this->setSendType();

    // Set common Variables.
    $type = $node->get("field_alert_type")->getString();
    $time_zone = drupal_get_user_timezone();
    // Set common template variables.
    $template_variables = [
      // The type of alert 'information' or 'warning'.
      'alert_type' => $node->get("field_alert_type")->value,
      // Needed to prevent notices when Twig debugging is enabled.
      'theme_hook_original' => 'not-applicable',
    ];

    if ($this->sendType === 'status alert') {
      $queue_id = "{$node->get('nid')->value}-alert";
      // Pull the data from the alert fields.
      $template_variables['message'] = $node->get('field_body')->value;
      $template_variables['situation_update'] = FALSE;
      $subject_prefix = "Alert";
      $time = time();
      $time = \Drupal::service('date.formatter')->format($time, 'custom', 'n/j/Y h:i A T');
    }
    elseif ($this->sendType === 'situation update') {
      // Might be multiples, used to dedupe so that only the last one goes.
      $queue_id = "{$node->get('nid')->value}-update";
      // Pull the data from the situation update fields $this->situationUpdate.
      $template_variables['message'] = $this->situationUpdate->get('field_wysiwyg')->value;
      $template_variables['situation_update'] = TRUE;
      $time = $this->situationUpdate->get('field_date_and_time')->date->getTimestamp();
      $time = \Drupal::service('date.formatter')->format($time, 'custom', 'n/j/Y h:i A T');
      $subject_prefix = "Situation Update";
    }

    if (!empty($this->sendType)) {
      $template_variables['date_time'] = $time;
      $template_variables['alert_title'] = $node->get('title')->getString();
      $vmacs_data = $node->get('field_banner_alert_computdvalues')->getValue();
      $vmacs_datum = reset($vmacs_data);
      $vmacs = !empty($vmacs_datum['value']) ? json_decode($vmacs_datum['value']) : [];

      // Loop through the VMACs since each title will be VAMC specific.
      foreach ($vmacs as $vmac) {
        $template_variables['vamc_name'] = $vmac->vamc_title;
        $template_variables['vamc_url'] = $vmac->vamc_path;
        $template_variables['ops_page_url'] = $vmac->vamc_op_status_path;
        if (PHP_SAPI === 'cli') {
          // The function twig_render_template throws a 'context not set'
          // exception when run from CLI.
          // Set a limited mock body for phpUnit Tests.
          $body = "This is a phpUnit body that should never be sent to GovDelivery.";
        }
        else {
          $body = (string) twig_render_template(drupal_get_path('module', 'va_gov_govdelivery') . '/templates/va-gov-body-alert.html.twig', $template_variables);
        }
        // Add the item to queue.
        \Drupal::service('govdelivery_bulletins.add_bulletin_to_queue')
          ->setFlag('dedupe', TRUE)
          ->setQueueUid("{$queue_id}-{$vmac->vamc_topic_id}")
          ->setBody($body)
          ->setFooter(NULL)
          ->setHeader(NULL)
          ->setSubject("{$subject_prefix}: {$vmac->vamc_title}")
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
