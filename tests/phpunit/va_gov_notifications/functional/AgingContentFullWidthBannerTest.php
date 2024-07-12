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
      'title' => 'Expirable Content Test Node',
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
      $node = $this->createNode($node_base);
      $node->set('field_last_saved_by_an_editor', $date);
      $node->set('title', $node->getTitle() . ':' . $key);
      $node->save();
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
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testFullWidthBannerJobQueued(string $type) {
    // Enable the ECA if it is not already.
    /** @var \Drupal\eca\Entity\Eca $eca */
    $eca = \Drupal::entityTypeManager()->getStorage('eca')->load("aging_content_{$type}_fwb");

    $status = $eca->status();
    $eca->enable();
    // Ensure that the ECA model will always fire during cron.
    $current_events = $events = $eca->get('events');
    $events['eca_base_eca_cron']['configuration']['frequency'] = '* * * * *';
    $eca->set('events', $events);
    $eca->save();

    // Run cron to queue the job.
    $this->drupalGet('admin/reports/status');
    $this->clickLink($this->t('Run cron'));

    // Check that the job is queued.
    $this->drupalGet('admin/config/system/queues/jobs/aging_content');
    $this->assertSession()->pageTextContains("Expirable Content Test Node:{$type}");

    // Set ECA to previous state. This is to prevent duplicate queued items.
    $eca->setStatus($status);
    $eca->set('events', $current_events);
    $eca->save();

    // Run cron again to execute the job, which sends the notification.
    $this->drupalGet('admin/reports/status');
    $this->clickLink($this->t('Run cron'));

    $this->assertSession()->pageTextContains('Success');
  }

}
