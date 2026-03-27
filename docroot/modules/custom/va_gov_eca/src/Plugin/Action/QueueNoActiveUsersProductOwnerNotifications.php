<?php

namespace Drupal\va_gov_eca\Plugin\Action;

use Drupal\advancedqueue\Entity\Queue;
use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobTypeManager;
use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca\Plugin\DataType\DataTransferObject;
use Drupal\va_gov_notifications\Service\NoActiveUsersNotificationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Queue product-owner notifications for sections without active editors.
 */
#[Action(
  id: 'queue_no_active_users_product_owner_notifications',
  label: new TranslatableMarkup('Queue product owner notifications for sections without active editors.')
)]
class QueueNoActiveUsersProductOwnerNotifications extends ConfigurableActionBase {

  /**
   * The no-active-users notification service.
   *
   * @var \Drupal\va_gov_notifications\Service\NoActiveUsersNotificationService
   */
  protected NoActiveUsersNotificationService $notificationService;

  /**
   * The job type manager service.
   *
   * @var \Drupal\advancedqueue\JobTypeManager
   */
  protected JobTypeManager $jobTypeManager;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected EntityFieldManagerInterface $entityFieldManager;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected LoggerChannelInterface $logger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->notificationService = $container->get('va_gov_notifications.no_active_users_notification');
    $instance->jobTypeManager = $container->get('plugin.manager.advancedqueue_job_type');
    $instance->entityFieldManager = $container->get('entity_field.manager');
    $instance->logger = $container->get('logger.channel.eca');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'token_name' => 'no_active_users_notification_summary',
      'job_type' => 'va_gov_product_owner_no_active_users_notification',
      'queue' => 'product_owner_no_active_users_notification',
      'template' => 'product_owner_no_active_users_notification',
      'owner_uid' => 1,
      'subject' => '[ACTION REQUIRED] Sections without active editors',
      'subject_field' => 'field_subject',
      'webhost_field' => 'field_webhost',
      'recipient_name_field' => 'field_editor_username',
      'sections_field' => 'field_inactive_sections',
      'sections_separator' => ', ',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public static function externallyAvailable(): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $queue = Queue::load($this->configuration['queue']);
    if (!$queue) {
      $this->logger->error('Cannot queue product owner notifications because the queue "@queue" does not exist.', [
        '@queue' => $this->configuration['queue'],
      ]);
      return;
    }

    $recipients = $this->notificationService->getRecipientsForSectionsWithoutActiveUsers();
    $summary = [
      'recipient_count' => count($recipients),
      'job_count' => 0,
      'section_count' => 0,
      'recipient_emails' => [],
    ];

    if (empty($recipients)) {
      $this->storeSummaryToken($summary);
      $this->logger->info('No product owner notifications were queued because no matching recipients were found.');
      return;
    }

    foreach ($recipients as $recipient) {
      $payload = $this->buildPayload($recipient);
      $job = Job::create($this->configuration['job_type'], $payload);
      $queue->enqueueJob($job);

      $summary['job_count']++;
      $summary['section_count'] += count($recipient['sections']);
      $summary['recipient_emails'][] = $recipient['recipient_email'];
    }

    $summary['recipient_emails'] = array_values(array_unique($summary['recipient_emails']));
    $this->storeSummaryToken($summary);

    $this->logger->info('Queued @jobs no-active-editor notification jobs for @recipients product owner recipients.', [
      '@jobs' => $summary['job_count'],
      '@recipients' => $summary['recipient_count'],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['token_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Summary token name'),
      '#default_value' => $this->configuration['token_name'],
      '#description' => $this->t('Optional token name that will hold a summary of queued notifications.'),
    ];
    $form['job_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Job type'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['job_type'],
      '#options' => $this->buildJobTypeOptions(),
      '#description' => $this->t('Advanced Queue job type used to send the notification.'),
    ];
    $form['queue'] = [
      '#type' => 'select',
      '#title' => $this->t('Queue'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['queue'],
      '#options' => $this->buildQueueOptions(),
      '#empty_option' => $this->t('- Select -'),
      '#empty_value' => '',
      '#description' => $this->t('Advanced Queue queue that should receive one job per recipient.'),
    ];
    $form['template'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Message template'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['template'],
      '#description' => $this->t('Machine name of the Message template to send.'),
    ];
    $form['owner_uid'] = [
      '#type' => 'number',
      '#title' => $this->t('Message owner UID'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['owner_uid'],
      '#min' => 1,
      '#description' => $this->t('User ID to own the Message entity. The actual recipient address is still taken from the product owner contact email.'),
    ];
    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email subject'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['subject'],
      '#description' => $this->t('Subject text stored in the configured subject field.'),
    ];
    $form['subject_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject field name'),
      '#default_value' => $this->configuration['subject_field'],
      '#description' => $this->t('Optional Message field that will receive the configured subject. Example: field_subject.'),
    ];
    $form['webhost_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Webhost field name'),
      '#default_value' => $this->configuration['webhost_field'],
      '#description' => $this->t('Optional Message field that will receive the current webhost. Example: field_webhost.'),
    ];
    $form['recipient_name_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Recipient name field name'),
      '#default_value' => $this->configuration['recipient_name_field'],
      '#description' => $this->t('Optional Message field that will receive the product owner contact label. Example: field_editor_username.'),
    ];
    $form['sections_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sections field name'),
      '#default_value' => $this->configuration['sections_field'],
      '#description' => $this->t('Optional Message field that will receive the joined section names for this recipient.'),
    ];
    $form['sections_separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sections separator'),
      '#default_value' => $this->configuration['sections_separator'],
      '#description' => $this->t('Separator used when joining section names into the sections field.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $job_type = (string) $form_state->getValue('job_type');
    if (!isset($this->jobTypeManager->getDefinitions()[$job_type])) {
      $form_state->setError($form['job_type'], $this->t('The selected job type does not exist.'));
    }

    if (!Queue::load((string) $form_state->getValue('queue'))) {
      $form_state->setError($form['queue'], $this->t('The selected queue does not exist.'));
    }

    if ((int) $form_state->getValue('owner_uid') < 1) {
      $form_state->setError($form['owner_uid'], $this->t('Message owner UID must be at least 1.'));
    }

    $message_field_definitions = $this->entityFieldManager->getFieldStorageDefinitions('message');
    foreach (['subject_field', 'webhost_field', 'recipient_name_field', 'sections_field'] as $field_key) {
      $field_name = trim((string) $form_state->getValue($field_key));
      if ($field_name !== '' && !isset($message_field_definitions[$field_name])) {
        $form_state->setError($form[$field_key], $this->t('The Message field %field does not exist.', [
          '%field' => $field_name,
        ]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['token_name'] = trim((string) $form_state->getValue('token_name'));
    $this->configuration['job_type'] = (string) $form_state->getValue('job_type');
    $this->configuration['queue'] = (string) $form_state->getValue('queue');
    $this->configuration['template'] = trim((string) $form_state->getValue('template'));
    $this->configuration['owner_uid'] = (int) $form_state->getValue('owner_uid');
    $this->configuration['subject'] = trim((string) $form_state->getValue('subject'));
    $this->configuration['subject_field'] = trim((string) $form_state->getValue('subject_field'));
    $this->configuration['webhost_field'] = trim((string) $form_state->getValue('webhost_field'));
    $this->configuration['recipient_name_field'] = trim((string) $form_state->getValue('recipient_name_field'));
    $this->configuration['sections_field'] = trim((string) $form_state->getValue('sections_field'));
    $this->configuration['sections_separator'] = (string) $form_state->getValue('sections_separator');
  }

  /**
   * Builds a job payload for a product owner recipient.
   *
   * @param array<string, mixed> $recipient
   *   Recipient and section summary data.
   *
   * @return array<string, mixed>
   *   A job payload compatible with JobTypeMessageNotifyBase.
   */
  protected function buildPayload(array $recipient): array {
    $values = [];

    if ($this->configuration['subject_field'] !== '') {
      $values[$this->configuration['subject_field']] = $this->configuration['subject'];
    }
    if ($this->configuration['webhost_field'] !== '') {
      $values[$this->configuration['webhost_field']] = Settings::get('webhost', 'https://prod.cms.va.gov');
    }
    if ($this->configuration['recipient_name_field'] !== '') {
      $values[$this->configuration['recipient_name_field']] = (string) ($recipient['recipient_name'] ?? '');
    }
    if ($this->configuration['sections_field'] !== '') {
      $values[$this->configuration['sections_field']] = $this->buildSectionSummary((array) ($recipient['sections'] ?? []));
    }

    return [
      'template_values' => [
        'template' => $this->configuration['template'],
        'uid' => $this->configuration['owner_uid'],
      ],
      'values' => $values,
      'mail' => (string) ($recipient['recipient_email'] ?? ''),
    ];
  }

  /**
   * Builds a joined section-name summary for a recipient.
   *
   * @param array<int, array<string, mixed>> $sections
   *   Sections belonging to the recipient.
   *
   * @return string
   *   Section names joined with the configured separator.
   */
  protected function buildSectionSummary(array $sections): string {
    $section_names = [];
    foreach ($sections as $section) {
      if (!empty($section['section_name'])) {
        $section_names[] = (string) $section['section_name'];
      }
    }
    return implode((string) $this->configuration['sections_separator'], $section_names);
  }

  /**
   * Stores a summary token for downstream ECA actions.
   *
   * @param array<string, mixed> $summary
   *   Queue summary data.
   */
  protected function storeSummaryToken(array $summary): void {
    if ($this->configuration['token_name'] === '') {
      return;
    }

    $this->tokenService->addTokenData(
      $this->configuration['token_name'],
      DataTransferObject::create($summary)
    );
  }

  /**
   * Builds available job type options.
   *
   * @return array<string, string>
   *   Labels keyed by job type ID.
   */
  protected function buildJobTypeOptions(): array {
    $options = [];
    foreach ($this->jobTypeManager->getDefinitions() as $id => $definition) {
      $options[$id] = $id . ' : ' . $definition['label'];
    }
    return $options;
  }

  /**
   * Builds available queue options.
   *
   * @return array<string, string>
   *   Queue labels keyed by queue ID.
   */
  protected function buildQueueOptions(): array {
    $options = [];
    $storage = $this->entityTypeManager->getStorage('advancedqueue_queue');
    foreach ($storage->loadMultiple() as $queue) {
      $options[$queue->id()] = $queue->label();
    }
    return $options;
  }

}
