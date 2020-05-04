<?php

namespace Drupal\va_gov_post_api\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Psr\Log\LogLevel;

/**
 * Class VaGovFacilityForceQueueForm.
 */
class VaGovFacilityForceQueueForm extends FormBase {

  /**
   * Config settings.
   *
   * @var string Config settings
   */
  const SETTINGS = 'va_gov_post_api.settings';

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
    // Queries to get total number of facilities for each type for reference.
    $health_care_local_facility = \Drupal::entityQuery('node')
      ->condition('type', 'health_care_local_facility')
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
      '#markup' => $this->t('This form queues ALL facilities of selected type for sync to Lighthouse.'),
    ];

    $form['facility_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Facility type'),
      '#description' => t('Select a facility type to queue.'),
      '#options' => [
        'health_care_local_facility' => $this->t('VAMC facilities') . ' (' . count($health_care_local_facility) . ')',
        'nca_facility' => $this->t('NCA facilities') . ' (' . count($nca_facility) . ')',
        'vba_facility' => $this->t('VBA facilities') . ' (' . count($vba_facility) . ')',
        'vet_center' => $this->t('Vet Centers') . ' (' . count($vet_center) . ')',
      ],
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Force-Queue Selected Facilities'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $facility_type = $form_state->getValue('facility_type');

    // Enable force update.
    $this->configFactory()
      ->getEditable(static::SETTINGS)
      ->set('bypass_data_check', 1)
      ->save();

    $sandbox['nids'] = \Drupal::entityQuery('node')
      ->condition('type', $facility_type)
      ->execute();

    if (!empty($sandbox['nids'])) {
      try {
        $sandbox['total'] = count($sandbox['nids']);
        $sandbox['current'] = 0;

        while ($sandbox['current'] < $sandbox['total']) {
          // Run through a batch of 50.
          $nids = array_slice($sandbox['nids'], $sandbox['current'], 50, FALSE);

          $nodes = Node::loadMultiple($nids);
          foreach ($nodes as $node) {
            _post_api_add_facility_to_queue($node);
          }

          $sandbox['current'] = $sandbox['current'] + count($nids);

          $this->logger('va_gov_post_api')
            ->log(LogLevel::INFO, 'VA.gov Post API: %current of %total %type nodes queued for sync to Lighthouse.', [
              '%type' => $facility_type,
              '%current' => $sandbox['current'],
              '%total' => count($sandbox['nids']),
            ]);
        }

        $this->messenger()->addStatus(sprintf('%d %s nodes queued for sync to Lighthouse.', count($sandbox['nids']), $facility_type));
      }
      catch (\Exception $e) {
        $this->logger('va_gov_post_api')
          ->log(LogLevel::ERROR, 'VA.gov Post API: Failed queuing facilities of type %type. %e', [
            '%type' => $facility_type,
            '%e' => $e->getMessage(),
          ]);

        $this->messenger()->addError(sprintf('Failed queuing facilities of type %s. Check log and try again.', $facility_type));
      }
    }
    else {
      // Didn't find facilities to process. Houston, we have a problem!
      $this->logger('va_gov_post_api')
        ->log(LogLevel::ERROR, 'VA.gov Post API: Found 0 facilities of type %type! This is a serious issue that requires immediate attention!', [
          '%type' => $facility_type,
        ]);

      $this->messenger()->addError(sprintf('Found 0 facilities of type %s! This is a serious issue that requires immediate attention!', $facility_type));
    }

    // Disable force update.
    $this->configFactory()
      ->getEditable(static::SETTINGS)
      ->set('bypass_data_check', 0)
      ->save();
  }

}
