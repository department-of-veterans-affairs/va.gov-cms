<?php

namespace Drupal\va_gov_eca\Plugin\Action;

use Drupal\advancedqueue\Entity\Queue;
use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobTypeManager;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\eca\EcaState;
use Drupal\eca\Plugin\Action\ActionBase;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca\Token\TokenInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create an Advanced Queue Job.
 */
#[Action(
  id: 'create_advancedqueue_job',
  label: new TranslatableMarkup("Create an AdvancedQueue Job.")
)]
class CreateAdvancedQueueJob extends ConfigurableActionBase {

  /**
   * The job type manager service.
   *
   * @var \Drupal\advancedqueue\JobTypeManager
   */
  protected JobTypeManager $jobTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, TokenInterface $token_services, AccountProxyInterface $current_user, TimeInterface $time, EcaState $state, JobTypeManager $job_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $token_services, $current_user, $time, $state);
    $this->entityTypeManager = $entity_type_manager;
    $this->tokenServices = $token_services;
    $this->currentUser = $current_user;
    $this->time = $time;
    $this->state = $state;
    $this->jobTypeManager = $job_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ActionBase {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('eca.token_services'),
      $container->get('current_user'),
      $container->get('datetime.time'),
      $container->get('eca.state'),
      $container->get('plugin.manager.advancedqueue_job_type'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function execute() {
    $job = Job::create($this->configuration['job_type']);
    $this->setPayload($job);
    if ($this->configuration['queue']) {
      $queue = Queue::load($this->configuration['queue']);
      $queue->enqueueJob($job);
    }
    $this->tokenServices->addTokenData($this->configuration['token_name'], $job);
  }

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['token_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of token'),
      '#default_value' => $this->configuration['token_name'] ?? '',
      '#description' => $this->t('Provide the name of a token that holds the new Job.'),
    ];
    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Job Type'),
      '#description' => $this->t('The Advanced Queue Job Type to create.'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['type'] ?? '',
      '#options' => $this->buildJobTypeOptions(),
    ];
    $form['payload'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Payload'),
      '#description' => $this->t('Add payload values to the Job as key|value. Enter one value per line.'),
      '#default_value' => $this->configuration['payload'] ?? '',
    ];
    $form['queue'] = [
      '#type' => 'select',
      '#title' => $this->t('Job Queue'),
      '#description' => $this->t('Send the job to this queue.'),
      '#default_value' => $this->configuration['queue'] ?? '',
      '#options' => $this->buildQueueOptions(),
      "#empty_option" => "- None -",
      '#empty_value' => "",

    ];
    return $form;
  }

  /**
   * Build the Job Type option values, suitable for a select Form element.
   *
   * @return array
   *   An array of Job Type labels, keyed by type.
   */
  private function buildJobTypeOptions() {
    $options = [];
    foreach ($this->jobTypeManager->getDefinitions() as $id => $definition) {
      $options[$id] = $id . ' : ' . $definition['label'];
    }
    return $options;
  }

  /**
   * Build the Queue option values, suitable for a select Form element.
   *
   * @return array
   *   An array of queue's, keyed by id.
   */
  private function buildQueueOptions() {
    $options = [];
    try {
      $storage = $this->entityTypeManager->getStorage('advancedqueue_queue');
      $ids = $storage->getQuery()->execute();
      foreach ($ids as $id) {
        $q = Queue::load($id);
        $options[$id] = $q->label();
      }
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException) {}

    return $options;
  }

  /**
   * Builds a Job's payload.
   *
   * @param \Drupal\advancedqueue\Job $job
   *   An Advanced Queue Job.
   */
  private function buildPayload(Job $job) {
    // todo; implement.
    $payload = [];
    $payload['allowed_recipients'] = 'test@user.com,steve@blah.foo';
    $payload['uid'] = 1;
    // etc.
    return $payload;
  }

  /**
   * Set a Job's payload.
   *
   * @param \Drupal\advancedqueue\Job $job
   *   An Advanced Queue Job.
   *
   * @return void
   */
  private function setPayload(Job $job): void {
    $job->setPayload($this->buildPayload($job));
  }

  /**
   * {@inheritDoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['token_name'] = $form_state->getValue('token_name');
    $this->configuration['type'] = $form_state->getValue('type');
    $this->configuration['payload'] = $form_state->getValue('payload');
    $this->configuration['queue'] = $form_state->getValue('queue');
  }

}
