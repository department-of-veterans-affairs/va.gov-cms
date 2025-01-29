<?php

namespace tests\phpunit\va_gov_notifications\functional;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use JetBrains\PhpStorm\ArrayShape;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Tests Aging Content FWB notifications.
 *
 * @group va_gov_notifications
 * @group aging_content
 */
class AgingContentFullWidthBannerTest extends VaGovExistingSiteBase {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {
    parent::setUp();
    $user = $this->createUser([], 'Admin', TRUE);
    $this->drupalLogin($user);

    $node_base = [
      'status' => 1,
      'moderation_state' => 'published',
      'type' => 'banner',
      'uid' => 1,
      'revision_default' => 1,
      'field_administration' => ['target_id' => 194],
    ];

    $base_date = DrupalDateTime::createFromTimestamp(time());
    $warn_node = clone $base_date;
    $exp_node = clone $base_date;

    $dates = [
      'not expired or warn' => $base_date->getTimestamp(),
      'warn' => $warn_node->sub(new \DateInterval('P5D'))->getTimestamp(),
      'expired' => $exp_node->sub(new \DateInterval('P8D'))->getTimestamp(),
    ];

    foreach ($dates as $key => $date) {
      $this->createNode($node_base + [
        'field_last_saved_by_an_editor' => $date,
        'title' => 'Expirable Content Test Node:' . $key,
      ]);
    }
  }

  /**
   * Data provider for expiration and warning tests.
   */
  #[ArrayShape(['expired' => "string[]", 'warn' => "string[]"])]
  public function dataProvider(): array {
    return [
      'expired' => ['expired'],
      'warn' => ['warn'],
    ];
  }

  /**
   * Tests that Full Width Alerts (banner) notifications are sent.
   *
   * @param string $type
   *   The type of test either 'warn' or 'expired'.
   *
   * @dataProvider dataProvider
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testFullWidthBannerJobQueued(string $type) {
    $session = $this->getSession();

    // Set cron frequency so that the ECA Cron Event will always fire.
    $this->drupalGet("/admin/config/workflow/eca/aging_content_{$type}_fwb/event/eca_base_eca_cron/edit");
    $frequencyElement = $session->getPage()->findById('edit-event-frequency');
    $currentFrequency = $frequencyElement->getValue();
    $frequencyElement->setValue('* * * * *');
    $session->getPage()->findById('edit-actions-submit')->click();

    // Enable the ECA Model if it is not already.
    $this->drupalGet("/admin/config/workflow/eca/aging_content_{$type}_fwb/edit");
    $session->getPage()->findById('edit-options-status')->check();
    $session->getPage()->findById('edit-submit')->click();

    // Run cron to queue the job.
    $this->drupalGet('admin/reports/status');
    $this->clickLink($this->t('Run cron'));

    // Check that the job is queued.
    $this->drupalGet('admin/config/system/queues/jobs/aging_content');
    $this->assertSession()->pageTextContains("Expirable Content Test Node:{$type}");

    // Disable the ECA Model. This is to prevent duplicate queued items.
    $this->drupalGet("/admin/config/workflow/eca/aging_content_{$type}_fwb/edit");
    $session->getPage()->findById('edit-options-status')->uncheck();
    $session->getPage()->findById('edit-submit')->click();

    // Set cron frequency back to previous state.
    $this->drupalGet("/admin/config/workflow/eca/aging_content_{$type}_fwb/event/eca_base_eca_cron/edit");
    $frequencyElement = $session->getPage()->findById('edit-event-frequency');
    $frequencyElement->setValue($currentFrequency);
    $session->getPage()->findById('edit-actions-submit')->click();

    // Run cron again to process queued job, which sends the notification.
    $this->drupalGet('admin/reports/status');
    $this->clickLink($this->t('Run cron'));

    $this->assertSession()->pageTextContains('Success');
  }

}
