<?php

namespace Drupal\va_gov_notifications;

use Drupal\Core\Render\Markup;

/**
 * For sending workflow status change emails.
 */
class WorkflowChangeEmail {

  /**
   * Creates and sends workflow change email.
   *
   * @param array $mail_load
   *   Contains node, recipient and revision author data for creating email.
   *   Sample array:
   *   $mail_load => [
   *    'users' => [
   *        356 => [
   *          'email' => 'test@gmail.com',
   *          'name' => 'sample.user',
   *          'lang' => en,
   *          'time' => 2019-08-04 11:46:11,
   *        ],
   *     ],
   *    'node_title' => 'Make an appointment'
   *    'original_workflow_state' => 'review'
   *    'new_workflow_state' => 'approved_by_reviewer'
   *    'url' => 'http://va.gov/pittsburgh-health-care/make-an-appointment'
   *    'log_message' => 'Some sample message',
   *    'change_author' => [
   *        'name' => 'don.quixote',
   *        'email' => '123@gmail.com',
   *        'uid' => 350,
   *     ],
   *   ].
   */
  public function sendEmail(array $mail_load) {
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'va_gov_notifications';
    // Defined in va_gov_notifications_mail.
    $key = 'workflow_status_change';

    // The email subject.
    $params['title'] = t(
      ':title | Status Changed', [
        ':title' => $mail_load['node_title'],
      ]
    );
    // Start building email body with content item title.
    $html = t(
      'Content title: :title :break',
      [
        ':title' => $mail_load['node_title'],
        ':break' => "\r\n",
      ]);
    // Link to the above title.
    $html .= t(
      'Link: :url :break',
      [
        ':url' => $mail_load['url'],
        ':break' => "\r\n",
      ]);
    // Previous workflow status.
    $html .= t(
      'Status Change From: :from :break',
      [
        ':from' => $mail_load['original_workflow_state'],
        ':break' => "\r\n",
      ]);
    // Current workflow status.
    $html .= t(
      'Status Change To: :to :break',
      [
        ':to' => $mail_load['new_workflow_state'],
        ':break' => "\r\n",
      ]);
    // If log message, include it.
    if (!empty($mail_load['log_message'])) {
      $html .= t(
        'Log Message: :message :break', [
          ':message' => $mail_load['log_message'],
          ':break' => "\r\n",
        ]
      );
    }
    // The name of the user who revised the node.
    $html .= t(
      'Change made by: :name :break', [
        ':name' => $mail_load['change_author']['name'],
        ':break' => "\r\n",
      ]
    );
    // Now loop through workflow participants.
    foreach ($mail_load['users'] as $mail) {
      // Get their language for appropriate translation.
      $langcode = $mail['lang'];
      $to = $mail['email'];
      // Don't filter out the timestamp formatting.
      $html .= t(
        'Time changed: @time :break', [
          '@time' => $mail['time'],
          ':break' => "\r\n",
        ]
      );
      // Run through Markup so that node link can be created in email.
      $params['message'] = Markup::create($html);
      $send = TRUE;
      // Put it all together.
      $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

      // Log issues.
      if ($result['result'] !== TRUE) {
        $message = t('There was a problem sending your email notification to @email.', ['@email' => $to]);
        drupal_set_message($message, 'error');
        \Drupal::logger('mail-log')->error($message);
        return;
      }
      // Display success.
      $message = t('An email notification has been sent to @email', ['@email' => $to]);
      drupal_set_message($message);
      \Drupal::logger('mail-log')->notice($message);
    }
  }

}
