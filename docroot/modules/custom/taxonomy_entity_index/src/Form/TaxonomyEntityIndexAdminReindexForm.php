<?php

namespace Drupal\taxonomy_entity_index\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the reindex form.
 */
class TaxonomyEntityIndexAdminReindexForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Creates a TaxonomyEntityIndexAdminReindexForm form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'taxonomy_entity_index_admin_reindex_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $entity_types = $this->entityTypeManager->getDefinitions();
    $entity_types_to_index = $this->configFactory->get('taxonomy_entity_index.settings')
      ->get('types');

    foreach ($entity_types as $entity_type_id => $entity_type) {
      if (!is_null($entity_type->getBaseTable())
        && $entity_type->isSubclassOf('\Drupal\Core\Entity\ContentEntityInterface')
        && in_array($entity_type_id, $entity_types_to_index)
      ) {
        $options[$entity_type_id] = $entity_type->getLabel() . " <em>($entity_type_id)</em>";
      }
    }
    asort($options);

    $form['description'] = [
      '#markup' => $this->t('<p>Use this form to reindex all terms for the selected entity types.</p>'),
    ];

    $form['types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Entity types'),
      '#options' => $options,
      '#description' => $this->t('Re-index all terms for the selected entity types.'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Rebuild index'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $types = array_filter($form_state->getValue(['types']));

    // Add an operation for each entity type.
    foreach ($types as $type) {
      $operations[] = ['taxonomy_entity_index_reindex_entity_type', [$type]];
    }

    // Set a batch operation for each selected entity type.
    $batch = [
      'operations' => $operations,
      'finished' => 'taxonomy_entity_index_reindex_finished',
    ];

    // Execute the batch.
    batch_set($batch);
  }

}
