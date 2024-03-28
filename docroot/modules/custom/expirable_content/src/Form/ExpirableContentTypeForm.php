<?php declare(strict_types = 1);

namespace Drupal\expirable_content\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\expirable_content\Entity\ExpirableContent;
use Drupal\expirable_content\Entity\ExpirableContentType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for expirable content type forms.
 */
final class ExpirableContentTypeForm extends BundleEntityFormBase {

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected EntityTypeBundleInfoInterface $entityTypeBundleInfo;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected EntityFieldManagerInterface $entityFieldManager;

  /**
   * Constructs a new ExpirableContentForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle info service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager service.
   */
  public function __construct(EntityTypeBundleInfoInterface $entityTypeBundleInfo, EntityFieldManagerInterface $entityFieldManager) {
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.bundle.info'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    if (!is_a($this->entity, ExpirableContentType::class)) {
      return $form;
    }
    $form = parent::form($form, $form_state);
    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $this->entity->status(),
    ];

    $form['entity_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Entity type'),
      '#description' => $this->t('Select the Entity Type for configuration of expiration.'),
      '#default_value' => $this->entity->entityType(),
      '#disabled' => !$this->entity->isNew(),
      '#empty_option' => $this->t('- Select a content entity -'),
      '#options' => $this->getAllContentEntityTypes(),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::updateBundle',
        'wrapper' => 'bundle-wrapper',
      ],
    ];

    $form['bundle_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'bundle-wrapper'],
    ];
    $entity_type = $this->entity->entityType();
    if (!empty($form_state->getValue('entity_type'))) {
      $entity_type = $form_state->getValue('entity_type');
    }
    if ($entity_type) {
      $form['bundle_wrapper']['entity_bundle'] = [
        '#type' => 'select',
        '#title' => $this->t('Entity bundle'),
        '#description' => $this->t('Select the bundle of the entity type you want to configure.'),
        '#default_value' => $this->entity->entityBundle(),
        '#options' => $this->getEntityBundleByType($entity_type),
        '#disabled' => !$this->entity->isNew(),
        '#required' => TRUE,
        '#empty_option' => $this->t('- Select a bundle -'),
        '#ajax' => [
          'callback' => '::updateFields',
          'wrapper' => 'field-wrapper',
        ],
      ];
    }
    $form['field_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'field-wrapper'],
    ];
    $entity_bundle = $this->entity->entityBundle();
    if (!empty($form_state->getValue('entity_bundle'))) {
      $entity_bundle = $form_state->getValue('entity_bundle');
    }
    if (!empty($entity_bundle) && !empty($entity_type)) {
      $form['field_wrapper']['field'] = [
        '#type' => 'select',
        '#title' => $this->t('Last updated field'),
        '#description' => $this->t('The field used to calculate when the entity was last updated. Must be a date field.'),
        '#default_value' => $this->entity->field(),
        '#options' => $this->getDateFieldsForBundle($entity_type, $entity_bundle),
        '#empty_option' => $this->t('- Select a field -'),
        '#required' => TRUE,
        '#ajax' => [
          'callback' => '::updateDays',
          'wrapper' => 'days-wrapper',
        ],
      ];
    }
    $form['days_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'days-wrapper'],
    ];
    $field = $this->entity->field();
    if ($form_state->getValue('field')) {
      $field = $form_state->getValue('field');
    }
    if (!empty($field) && !empty($entity_bundle) && !empty($entity_type)) {
      $form['days_wrapper']['days'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Days to expire'),
        '#description' => $this->t('Expire the entity this number of days since the last change to an entity.'),
        '#default_value' => $this->entity->days(),
        '#required' => TRUE,
      ];
      $form['days_wrapper']['warn'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Days to warn'),
        '#description' => $this->t('A number of days before an entity expires.'),
        '#default_value' => $this->entity->warn(),
        '#required' => TRUE,
      ];
    }
    return $form;
  }

  /**
   * Ajax callback for the bundle wrapper.
   */
  public function updateBundle(array $form, FormStateInterface $form_state) {
    return $form['bundle_wrapper'];
  }

  /**
   * Ajax callback for the field wrapper.
   */
  public function updateFields(array $form, FormStateInterface $form_state) {
    return $form['field_wrapper'];
  }

  /**
   * Ajax callback for the days wrapper.
   */
  public function updateDays(array $form, FormStateInterface $form_state) {
    return $form['days_wrapper'];
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setValue('id', $form_state->getValue('entity_type') . '.' . $form_state->getValue('entity_bundle'));
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);
    $message_args = ['%label' => $this->entity->label()];
    $this->messenger()->addStatus(
      match($result) {
        \SAVED_NEW => $this->t('Created new expirable content %label.', $message_args),
        \SAVED_UPDATED => $this->t('Updated expirable content %label.', $message_args),
      }
    );
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $result;
  }

  /**
   * Returns a list of content entity types.
   *
   * @return array
   *   An associative array of content entity types, suitable to use as form
   *   options.
   */
  public function getAllContentEntityTypes(): array {
    $allEntityTypes = $this->entityTypeManager->getDefinitions();
    $contentEntityTypes = [];

    foreach ($allEntityTypes as $entityTypeId => $entityType) {
      if ($entityType->entityClassImplements('\Drupal\Core\Entity\ContentEntityInterface')) {
        $contentEntityTypes[$entityTypeId] = $entityType->getLabel();
      }
    }

    return $contentEntityTypes;
  }

  /**
   * Returns bundles that correspond with the given entity type.
   *
   * @param string $entityType
   *   The entity type for which to return a list of colors.
   *
   * @return array
   *   An associative array of bundles that correspond to the given enityt
   *   type, suitable to use as form options.
   */
  protected function getEntityBundleByType($entityType): array {
    $bundles = $this->entityTypeBundleInfo->getBundleInfo($entityType);
    $bundleTypes = [];
    foreach ($bundles as $bundleId => $bundle) {
      $bundleTypes[$bundleId] = $bundle['label'];
    }
    return $bundleTypes;
  }

  /**
   * Gets all date fields for a specific entity bundle.
   *
   * @param string $entity_type_id
   *   The ID of the entity type (e.g., 'node', 'user').
   * @param string $bundle
   *   The machine name of the bundle (e.g., 'article' for nodes).
   *
   * @return array
   *   An associative array of field definitions for date fields in the
   *   specified bundle.
   */
  public function getDateFieldsForBundle($entity_type_id, $bundle) {
    $date_fields = [];
    $fields = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
    foreach ($fields as $field) {
      if (in_array($field->getType(), ['timestamp', 'changed', 'created'])) {
        $date_fields[$field->getName()] = $field->getName();
      }
    }

    return $date_fields;
  }

}
