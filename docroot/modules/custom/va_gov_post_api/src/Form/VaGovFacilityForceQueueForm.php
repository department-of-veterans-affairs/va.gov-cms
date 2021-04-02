<?php

namespace Drupal\va_gov_post_api\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to force queuing content for posting to Lighthouse.
 */
class VaGovFacilityForceQueueForm extends FormBase {

  /**
   * Config settings.
   *
   * @var string Config settings
   */
  const SETTINGS = 'va_gov_post_api.settings';

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'facility_force_queue_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Queries to get total number of nodes for each type for reference.
    $health_care_local_facility = \Drupal::entityQuery('node')
      ->condition('type', 'health_care_local_facility')
      ->execute();

    $health_care_facility_service = \Drupal::entityQuery('node')
      ->condition('type', 'health_care_local_health_service')
      ->execute();

    $nca_facility = \Drupal::entityQuery('node')
      ->condition('type', 'nca_facility')
      ->execute();

    $vba_facility = \Drupal::entityQuery('node')
      ->condition('type', 'vba_facility')
      ->execute();

    $vet_center = \Drupal::entityQuery('node')
      ->condition('type', 'vet_center')
      ->execute();

    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t('This form queues ALL items of a selected type for sync to Lighthouse.'),
    ];

    $form['facility_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Content type'),
      '#description' => t('Select a content type to queue.'),
      '#options' => [
        'health_care_local_facility' => $this->t('VAMC facilities') . ' (' . count($health_care_local_facility) . ')',
        'health_care_local_health_service' => ' - ' . $this->t('VAMC facility services') . ' (' . count($health_care_facility_service) . ')',
        'nca_facility' => $this->t('NCA facilities') . ' (' . count($nca_facility) . ')',
        'vba_facility' => $this->t('VBA facilities') . ' (' . count($vba_facility) . ')',
        'vet_center' => $this->t('Vet Centers') . ' (' . count($vet_center) . ')',
      ],
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Force-Queue Selected Items'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $bundle = $form_state->getValue('facility_type');
    $queued_count = 0;

    // Enable force update.
    $this->configFactory()
      ->getEditable(static::SETTINGS)
      ->set('bypass_data_check', 1)
      ->save();

    $sandbox['nids'] = \Drupal::entityQuery('node')
      ->condition('type', $bundle)
      ->execute();

    if (!empty($sandbox['nids'])) {
      try {
        $sandbox['total'] = count($sandbox['nids']);
        $sandbox['current'] = 0;

        while ($sandbox['current'] < $sandbox['total']) {
          // Run through a batch of 50.
          $nids = array_slice($sandbox['nids'], $sandbox['current'], 50, FALSE);

          $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
          foreach ($nodes as $node) {
            if ($bundle === 'health_care_local_health_service') {
              $queued_count += _post_api_add_facility_service_to_queue($node);
            }
            else {
              $queued_count += _post_api_add_facility_to_queue($node);
            }
          }

          $sandbox['current'] = $sandbox['current'] + count($nids);

          $this->logger('va_gov_post_api')
            ->log(LogLevel::INFO, 'VA.gov Post API: %current of %total %type nodes processed. Queued %queued_count for sync to Lighthouse.', [
              '%type' => $bundle,
              '%current' => $sandbox['current'],
              '%total' => count($sandbox['nids']),
              '%queued_count' => $queued_count,
            ]);
        }

        $this->messenger()->addStatus(sprintf('%d %s nodes processed. Queued %d for sync to Lighthouse.', count($sandbox['nids']), $bundle, $queued_count));
      }
      catch (\Exception $e) {
        $this->logger('va_gov_post_api')
          ->log(LogLevel::ERROR, 'VA.gov Post API: Failed queuing items of type %type. %e', [
            '%type' => $bundle,
            '%e' => $e->getMessage(),
          ]);

        $this->messenger()->addError(sprintf('Failed queuing items of type %s. Check log and try again.', $bundle));
      }
    }
    else {
      // Didn't find facilities to process. Houston, we have a problem!
      $this->logger('va_gov_post_api')
        ->log(LogLevel::ERROR, 'VA.gov Post API: Found 0 items of type %type! This is a serious issue that requires immediate attention!', [
          '%type' => $bundle,
        ]);

      $this->messenger()->addError(sprintf('Found 0 items of type %s! This is a serious issue that requires immediate attention!', $bundle));
    }

    // Disable force update.
    $this->configFactory()
      ->getEditable(static::SETTINGS)
      ->set('bypass_data_check', 0)
      ->save();
  }

}
