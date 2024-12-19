<?php

declare(strict_types=1);

namespace Tests\va_gov_notifications\kernel;

use Drupal\advancedqueue\Job;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\KernelTests\KernelTestBase;
use Drupal\message\Entity\MessageTemplate;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\va_gov_notifications\JobTypeMessageNotifyBase;

/**
 * Tests for JobTypeMessageNotifyBase.
 *
 * @group va_gov_notifications
 * @covers JobTypeMessageNotifyBase
 */
class JobTypeMessageNotifyBaseTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'user',
    'message',
    'message_notify',
    'va_gov_notifications',
    'advancedqueue',
    'flag',
    'workbench_access',
  ];

  /**
   * The job type plugin under test.
   *
   * @var \Drupal\va_gov_notifications\JobTypeMessageNotifyBase
   */
  protected JobTypeMessageNotifyBase $jobType;

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('message');
    $this->createUser(admin: FALSE);

    // Create message template.
    $message_template = MessageTemplate::create([
      'template' => 'foo_template',
      'label' => 'Example Template',
    ]);
    $message_template->save();

    // Instantiate the job type.
    $this->jobType = new JobTypeMessageNotifyBase(
      ['id' => 'test_job_type'],
      'test_job_type',
      ['provider' => 'va_gov_notifications'],
      $this->container->get('logger.factory'),
      $this->container->get('message_notify.sender')
    );
  }

  /**
   * Tests the process method for a successful message send.
   */
  public function testProcessSuccess() {
    $payload = [
      'values' => [],
      'template_values' => [
        'uid' => 1,
        'template' => 'foo_template',
      ],
      'restrict_delivery_to' => [2],
    ];
    $job = new Job([
      'type' => 'test_job_type',
      'payload' => $payload,
      'state' => Job::STATE_QUEUED,
    ]);

    $result = $this->jobType->process($job);
    $this->assertEquals(Job::STATE_SUCCESS, $result->getState());
    $this->assertEquals('Message 1 sent successfully.', $result->getMessage());
  }

  /**
   * Tests the process method with an error due to restricted delivery setting.
   */
  public function testProcessFailureRecipientRestrictList() {
    $payload = [
      'values' => [],
      'template_values' => [
        'uid' => 1,
        'template' => 'foo_template',
      ],
      'restrict_delivery_to' => [1],
    ];
    $job = new Job([
      'type' => 'test_job_type',
      'payload' => $payload,
      'state' => Job::STATE_QUEUED,
    ]);

    $result = $this->jobType->process($job);
    $this->assertEquals(Job::STATE_FAILURE, $result->getState());
    $this->assertEquals('Recipient is not on the allow list for message 1.', $result->getMessage());
  }

  /**
   * Tests the process method for successful sending using allow list.
   */
  public function testProcessSuccessRecipientAllowList() {
    $payload = [
      'values' => [],
      'template_values' => [
        'uid' => 1,
        'template' => 'foo_template',
      ],
      'allow_delivery_only_to' => [1],
    ];
    $job = new Job([
      'type' => 'test_job_type',
      'payload' => $payload,
      'state' => Job::STATE_QUEUED,
    ]);

    $result = $this->jobType->process($job);
    $this->assertEquals(Job::STATE_SUCCESS, $result->getState());
    $this->assertEquals('Message 1 sent successfully.', $result->getMessage());
  }

  /**
   * Tests the process method for failure sending using allow list.
   */
  public function testProcessFailureRecipientAllowList() {
    $payload = [
      'values' => [],
      'template_values' => [
        'uid' => 2,
        'template' => 'foo_template',
      ],
      'allow_delivery_only_to' => [1],
    ];
    $job = new Job([
      'type' => 'test_job_type',
      'payload' => $payload,
      'state' => Job::STATE_QUEUED,
    ]);

    $result = $this->jobType->process($job);
    $this->assertEquals(Job::STATE_FAILURE, $result->getState());
    $this->assertEquals('Recipient is not on the allow list for message 1.', $result->getMessage());
  }

  /**
   * Tests the process method with an error due to missing template values.
   */
  public function testProcessFailureMissingTemplateValues() {
    $payload = [
      'values' => [],
    ];
    $job = new Job([
      'type' => 'test_job_type',
      'payload' => $payload,
      'state' => Job::STATE_QUEUED,
    ]);
    $this->expectException(MissingDataException::class);
    $this->expectExceptionMessage('Missing template_values in payload for job id');
    $result = $this->jobType->process($job);
    $this->assertEquals(Job::STATE_FAILURE, $result->getState());
  }

  /**
   * Tests the process with Message Notifier Options and no Message owner.
   */
  public function testProcessMessageNotifierOptionsNoOwner() {
    $payload = [
      'values' => [],
      'mail' => 'test@example.com',
      'template_values' => [
        'template' => 'foo_template',
        'uid' => 1,
      ],
    ];
    $job = new Job([
      'type' => 'test_job_type',
      'payload' => $payload,
      'state' => Job::STATE_QUEUED,
    ]);
    $result = $this->jobType->process($job);
    $this->assertEquals(Job::STATE_SUCCESS, $result->getState());
    $this->assertEquals('Message 1 sent successfully.', $result->getMessage());
  }

}
