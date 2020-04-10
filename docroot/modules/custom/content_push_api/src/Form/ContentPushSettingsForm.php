<?php

namespace Drupal\content_push_api\Form;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContentPushSettingsForm.
 */
class ContentPushSettingsForm extends FormBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  public $entityTypeBundleInfo;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityTypeBundleInfoInterface $entityTypeBundleInfo) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_push_api_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('content_push_api.settings');

    $form['endpoint_host'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Endpoint'),
      '#description' => $this->t(
        'Stores CMS Facility API endpoint host in database - THIS IS NOT RECOMMENDED.
        Preferred method is to store in settings.local.php - see README for
        instructions.'),
      '#default_value' => $config->get('endpoint_host'),
    ];

    $form['apikey'] = [
      '#type' => 'password',
      '#title' => $this->t('API Key'),
      '#description' => $this->t(
        'Stores API key in database - THIS IS NOT RECOMMENDED.
        Preferred method is to store in settings.local.php - see README for
        instructions.'),
      '#default_value' => $config->get('apikey'),
    ];

    $form['header_content_type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header: Content-type'),
      '#description' => $this->t(
        'Stores Content-type header to use in HTTP request. E.g.
        "application/json"'),
      '#default_value' => $config->get('header_content_type'),
    ];

    $form['target_bundles'] = [
      '#type' => 'details',
      '#title' => $this->t('Entity Types'),
      '#description' => $this->t('Select entity types that should be POSTed to endpoint when created new or updated.'),
      '#open' => TRUE,
    ];

    $entity_type_id = 'node';
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);

    if ($entity_type->hasKey('bundle')) {
      $bundle_options = [];
      foreach ($bundles as $bundle_name => $bundle_info) {
        $bundle_options[$bundle_name] = $bundle_info['label'];
      }
      natsort($bundle_options);

      $form['target_bundles']['content_types'] = [
        '#type' => 'checkboxes',
        '#title' => $entity_type->getBundleLabel(),
        '#options' => $bundle_options,
        '#default_value' => $config->get('content_types') ?: [],
        '#required' => TRUE,
        '#size' => 6,
        '#multiple' => TRUE,
      ];
    }

    $form['logging'] = [
      '#type' => 'details',
      '#title' => $this->t('Logging and Notifications'),
    ];

    $form['logging']['slack'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Slack Notifications'),
      '#description' => $this->t(
        'Enables calls to Slack webhook configured via environment variables (see README.md). If disabled, will still write to dblog.'
      ),
      '#default_value' => $config->get('slack'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#weight' => '20',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = self::configFactory()->getEditable('content_push_api.settings');
    $config
      ->set('endpoint_host', $form_state->getValue('endpoint_host'))
      ->set('apikey', $form_state->getValue('apikey'))
      ->set('header_content_type', $form_state->getValue('header_content_type'))
      ->set('content_types', $form_state->getValue('content_types'))
      ->set('slack', $form_state->getValue('slack'))
      ->save();
  }

}
