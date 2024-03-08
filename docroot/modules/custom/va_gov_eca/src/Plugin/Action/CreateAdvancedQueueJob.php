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
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\eca\EcaState;
use Drupal\eca\Plugin\Action\ActionBase;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca\Service\YamlParser;
use Drupal\eca\Token\TokenInterface;
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
   * Constructs a CreateAdvancedQueueJob object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity Type Manager service.
   * @param \Drupal\eca\Token\TokenInterface $token_services
   *   The ECA Token Services service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The Time service.
   * @param \Drupal\eca\EcaState $state
   *   The ECA state service.
   * @param \Drupal\advancedqueue\JobTypeManager $job_type_manager
   *   The AdvancedQueue Job Type plugin manager.
   * @param \Drupal\eca\Service\YamlParser $yaml_parser
   *   The ECA Yaml parser service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The Logger Channel Factory service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    TokenInterface $token_services,
    AccountProxyInterface $current_user,
    TimeInterface $time,
    EcaState $state,
    JobTypeManager $job_type_manager,
    YamlParser $yaml_parser,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $token_services, $current_user, $time, $state);
    $this->entityTypeManager = $entity_type_manager;
    $this->tokenServices = $token_services;
    $this->currentUser = $current_user;
    $this->time = $time;
    $this->state = $state;
    $this->jobTypeManager = $job_type_manager;
    $this->yamlParser = $yaml_parser;
    $this->loggerFactory = $logger_factory;
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
      $container->get('eca.service.yaml_parser'),
      $container->get('logger.factory')
    );
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
    $this->tokenServices->addTokenData($this->configuration['token_name'], $job);
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
