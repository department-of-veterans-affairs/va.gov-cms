<?php

namespace tests\phpunit\va_gov_notifications\functional;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use JetBrains\PhpStorm\ArrayShape;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Tests Aging Content News Promos.
 *
 * @group va_gov_notifications
 * @group aging_content
 */
class AgingContentNewsSpotlightBlockTest extends VaGovExistingSiteBase {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {
    parent::setUp();
    $user = $this->createUser([], 'Admin', TRUE);
    $this->drupalLogin($user);

    $base_entity = [
      'info' => 'Expirable Content Test Block',
      'type' => 'news_promo',
      'langcode' => 'en',
      'status' => 1,
      'moderation_state' => 'published',
      'reusable' => 1,
    ];

    $base_date = DrupalDateTime::createFromTimestamp(time());
    $warn_node = clone $base_date;
    $exp_node = clone $base_date;

    $entities = [
      'not expired or warn' => $base_date->getTimestamp(),
      'warn' => $warn_node->sub(new \DateInterval('P13D'))->getTimestamp(),
      'expired' => $exp_node->sub(new \DateInterval('P15D'))->getTimestamp(),
    ];

    foreach ($entities as $key => $date) {
      $block = BlockContent::create($base_entity);
      $block->setChangedTime($date);
      $block->setInfo($base_entity['info'] . ':' . $key);
      $block->setPublished();
      $block->save();
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
   * Tests that News Promo notifications are queued.
   *
   * @param string $type
   *   The type of test either 'warn' or 'expired'.
   *
   * @dataProvider dataProvider
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testNewsSpotlightJobQueued(string $type) {
    $session = $this->getSession();

    // Set cron frequency so that the ECA Cron Event will always fire.
    $this->drupalGet("/admin/config/workflow/eca/aging_content_{$type}_news_promo/event/eca_base_eca_cron/edit");
    $frequencyElement = $session->getPage()->findById('edit-event-frequency');
    $currentFrequency = $frequencyElement->getValue();
    $frequencyElement->setValue('* * * * *');
    $session->getPage()->findById('edit-actions-submit')->click();

    // Enable the ECA Model if it is not already.
    $this->drupalGet("/admin/config/workflow/eca/aging_content_{$type}_news_promo/edit");
    $session->getPage()->findById('edit-options-status')->check();
    $session->getPage()->findById('edit-submit')->click();

    // Run cron to queue the job.
    $this->drupalGet('admin/reports/status');
    $this->clickLink($this->t('Run cron'));

    // Check that the job is queued.
    $this->drupalGet('admin/config/system/queues/jobs/aging_content');
    $this->assertSession()->pageTextContains("Expirable Content Test Block:{$type}");

    // Disable the ECA Model. This is to prevent duplicate queued items.
    $this->drupalGet("/admin/config/workflow/eca/aging_content_{$type}_news_promo/edit");
    $session->getPage()->findById('edit-options-status')->uncheck();
    $session->getPage()->findById('edit-submit')->click();

    // Set cron frequency back to previous state.
    $this->drupalGet("/admin/config/workflow/eca/aging_content_{$type}_news_promo/event/eca_base_eca_cron/edit");
    $frequencyElement = $session->getPage()->findById('edit-event-frequency');
    $frequencyElement->setValue($currentFrequency);
    $session->getPage()->findById('edit-actions-submit')->click();

    // Run cron again to process queued job, which sends the notification.
    $this->drupalGet('admin/reports/status');
    $this->clickLink($this->t('Run cron'));

    $this->assertSession()->pageTextContains('Success');
  }

}
