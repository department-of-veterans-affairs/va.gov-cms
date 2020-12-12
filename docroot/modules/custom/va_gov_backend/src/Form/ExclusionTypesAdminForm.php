<?php

namespace Drupal\va_gov_backend\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExclusionTypesAdminForm.
 */
class ExclusionTypesAdminForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritDoc}
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
  public function getFormId() {
    return 'exclusion_types_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('exclusion_types_admin.settings');
    $options = [];
    $types = $this->entityTypeManager
      ->getStorage('node_type')
      ->loadMultiple();

    foreach ($types as $type) {
      $options[$type->get('type')] = $this->t(':name', [':name' => $type->get('name')]);
    }

    $form['types_to_exclude'] = [
      '#type' => 'checkboxes',
      '#options' => $options,
      '#title' => $this->t('Content types that are not individual pages on va.gov.'),
      '#description' => $this->t('Names of content types, that are not separate pages on va.gov'),
      '#weight' => '10',
      '#default_value' => $config->get('types_to_exclude') ?? [],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#weight' => '20',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = [];
    foreach ($form_state->getValue('types_to_exclude') as $key => $item) {
      if ($item !== 0) {
        $values[$key] = $item;
      }
    }
    $config = $this->configFactory()->getEditable('exclusion_types_admin.settings');
    $config
      ->set('types_to_exclude', $values)
      ->save();
  }

}
