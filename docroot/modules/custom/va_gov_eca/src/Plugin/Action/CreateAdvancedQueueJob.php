<?php

namespace Drupal\va_gov_eca\Plugin\Action;

use Drupal\advancedqueue\Entity\Queue;
use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobTypeManager;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca\Plugin\DataType\DataTransferObject;
use Drupal\eca\Service\YamlParser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Create an Advanced Queue Job.
 */
#[Action(
  id: 'create_advancedqueue_job',
  label: new TranslatableMarkup("Create an AdvancedQueue Job and optionally enqueue it.")
)]
class CreateAdvancedQueueJob extends ConfigurableActionBase {

  /**
   * The job type manager service.
   *
   * @var \Drupal\advancedqueue\JobTypeManager
   */
  protected JobTypeManager $jobTypeManager;

  /**
   * The ECA Yaml parser service.
   *
   * @var \Drupal\eca\Service\YamlParser
   */
  protected YamlParser $yamlParser;

  /**
   * The Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $loggerFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->jobTypeManager = $container->get('plugin.manager.advancedqueue_job_type');
    $instance->yamlParser = $container->get('eca.service.yaml_parser');
    $instance->loggerFactory = $container->get('logger.factory');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'token_name' => '',
      'type' => '',
      'payload' => '',
      'queue' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritDoc}
   */
  public function execute() {
    $job = Job::create($this->configuration['type'], $this->buildPayload());
    if ($this->configuration['queue']) {
      Queue::load($this->configuration['queue'])?->enqueueJob($job);
    }
    $this->tokenService->addTokenData($this->configuration['token_name'], DataTransferObject::create($job->toArray()));
  }

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['token_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of token'),
      '#default_value' => $this->configuration['token_name'],
      '#description' => $this->t('Provide the name of a token that holds the new Job.'),
    ];
    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Job Type'),
      '#description' => $this->t('The Advanced Queue Job Type to create.'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['type'],
      '#options' => $this->buildJobTypeOptions(),
    ];
    $form['payload'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Payload'),
      '#description' => $this->t('The value of the payload. Enter data as Yaml. Tokens are supported.'),
      '#default_value' => $this->configuration['payload'],
    ];
    $form['queue'] = [
      '#type' => 'select',
      '#title' => $this->t('Job Queue'),
      '#description' => $this->t('Send the job to this queue.'),
      '#default_value' => $this->configuration['queue'],
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
    catch (InvalidPluginDefinitionException | PluginNotFoundException) {
      $this->loggerFactory->get('va_gov_eca')->error('Attempt to build option values for queue in action "create_advancedqueue_job" failed. Is Advanced Queue installed?');
    }

    return $options;
  }

  /**
   * Builds a payload for a Job.
   *
   * @return array
   *   The Job's payload.
   */
  private function buildPayload() {
    $payload = [];
    $value = $this->configuration['payload'];
    try {
      $payload = $this->yamlParser->parse($value);
    }
    catch (ParseException $e) {
      $this->loggerFactory->get('va_gov_eca')->error('Tried parsing a Token value in action "create_advancedqueue_job" as YAML format, but parsing failed. Error was: <pre>@message</pre>', ['@message' => $e->getMessage()]);
    }
    return $payload;
  }

  /**
   * {@inheritDoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {
    try {
      $this->yamlParser->parse($form_state->getValue('payload'));
    }
    catch (ParseException $e) {
      $form_state->setError($form['subform']['payload'], $this->t('Payload contains invalid yaml. Error: <pre>@message</pre>', ['@message' => $e->getMessage()]));
    }
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
