<?php

namespace Drupal\entity_field_fetch\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A widget for EntityFieldFetch fields.
 *
 * @FieldWidget(
 *   id = "entity_field_fetch_widget",
 *   label = @Translation("Entity Field Fetch widget"),
 *   field_types = {
 *     "entity_field_fetch"
 *   }
 * )
 */
class EntityFieldFetchWidget extends WidgetBase implements WidgetInterface {

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a EntityFieldFetchWidget object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity manager service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity Repository.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      // Add any services we want to inject.
      $container->get('entity_type.manager'),
      $container->get('entity.repository')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $target_entity_type = $this->fieldDefinition->getSetting('target_entity_type');
    $target_entity_id = $this->fieldDefinition->getSetting('target_entity_id');
    $target_fieldname = $this->fieldDefinition->getSetting('field_to_fetch');
    $target_paragraph_uuid = $this->fieldDefinition->getSetting('target_paragraph_uuid');
    if (empty($target_entity_type) || empty($target_entity_id)) {
      // The field has no data yet so create a preview notice.
      return [
        '#type' => 'markup',
        '#markup' => $this->t('[ No preview exists yet as the field is not configured. ]'),
      ];
    }

    $element['#type'] = 'item';
    $source_entity_type = (empty($target_paragraph_uuid)) ? $target_entity_type : 'paragraph';
    $field_name = $this->fieldDefinition->getName();
    if ($source_entity_type === 'paragraph') {
      // The data is in a paragraph so load the paragraph directly.
      $paragraph = $this->entityRepository->loadEntityByUuid('paragraph', $target_paragraph_uuid);
      $target_render = $this->entityTypeManager->getViewBuilder('paragraph')->view($paragraph, 'full');
      $source_uuid = $target_paragraph_uuid;
    }
    else {
      // The data is in the source entity so load it.
      $entity = $this->entityTypeManager->getStorage($target_entity_type)->load($target_entity_id);
      $target_render = $entity->$target_fieldname->view('full');
      $source_uuid = $entity->uuid();
    }

    // Enable caching even though caching for speed and uniformity.
    $element['#cache'] = [
      'keys' => [
        $field_name,
        $target_entity_type,
        $target_entity_id,
        $target_fieldname,
        $target_paragraph_uuid,
        'edit',
      ],
      'tags' => [
        // The cache should be expired when the source entity is updated.
        "{$target_entity_type}:{$target_entity_id}",
      ],
    ];

    // The data saved in the forms are used for content export only.  It is not
    // used for any retrieval of the source data.  That all comes from the
    // field configuration.
    $element['target_type'] = [
      '#type' => 'hidden',
      '#disabled' => TRUE,
      '#size' => 36,
      '#value' => $source_entity_type,
      '#weight' => 2,
    ];

    $element['target_uuid'] = [
      '#type' => 'hidden',
      '#disabled' => TRUE,
      '#size' => 36,
      '#value' => $source_uuid,
      '#weight' => 3,
    ];

    $element['target_field'] = [
      '#type' => 'hidden',
      '#disabled' => TRUE,
      '#size' => 36,
      // If the target is a paragraph, the field name should be nullified.
      '#value' => (empty($target_paragraph_uuid)) ? $target_fieldname : NULL,
      '#weight' => 4,
    ];

    // Include the render array for the fetched content.
    $element['fetched_source'] = $target_render;
    $element['fetched_source']['#weight'] = 10;

    // @todo Add a markup that shows the last updated date.
    return $element;
  }

}
