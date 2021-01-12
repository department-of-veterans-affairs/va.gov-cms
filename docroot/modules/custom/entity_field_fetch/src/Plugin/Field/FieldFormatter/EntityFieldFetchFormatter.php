<?php

namespace Drupal\entity_field_fetch\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'EntityFieldFetch' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_field_fetch",
 *   label = @Translation("Entity Field Fetch"),
 *   field_types = {
 *     "entity_field_fetch"
 *   }
 * )
 */
class EntityFieldFetchFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The field definition.
   *
   * @var q\Drupal\Core\Field\FieldDefinitionInterface
   */
  protected $fieldDefinition;

  /**
   * Construct a MyFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   Defines an interface for entity field definitions.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity manager service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity Repository.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->fieldDefinition = $field_definition;
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
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      // Add any services you want to inject here.
      $container->get('entity_type.manager'),
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays the fetched field/paragraph.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = $this->viewElement($item, $langcode);
    }

    return $elements;
  }

  /**
   * Builds a renderable array for fetched targets.
   *
   * @param object $fetchField
   *   The Fetch field.
   * @param string $langcode
   *   The language that should be used to render the field.
   *
   * @return array
   *   A renderable array.
   */
  protected function viewElement($fetchField, $langcode) {
    $field_name = $this->fieldDefinition->getName();
    $target_entity_type = $this->fieldDefinition->getSetting('target_entity_type');
    $target_entity_id = $this->fieldDefinition->getSetting('target_entity_id');
    $target_fieldname = $this->fieldDefinition->getSetting('field_to_fetch');
    $target_paragraph_uuid = $this->fieldDefinition->getSetting('target_paragraph_uuid');

    if (!empty($target_paragraph_uuid)) {
      // Ignore the node, the data we want is all in the related paragraph.
      $paragraph = $this->entityRepository->loadEntityByUuid('paragraph', $target_paragraph_uuid);
      $builder = $this->entityTypeManager->getViewBuilder('paragraph');
      $element = $builder->view($paragraph, $this->viewMode);
      $source = "{$target_entity_type} {$target_entity_id} paragraph {$target_paragraph_uuid}";
      // Paragraphs have no published state.
      $targetIsPublished = TRUE;
    }
    else {
      // We are looking for just the field to render.
      $entity = $this->entityTypeManager->getStorage($target_entity_type)->load($target_entity_id);
      $element = $entity->$target_fieldname->view($this->viewMode);
      $source = "{$target_entity_type} {$target_entity_id} field {$target_fieldname}";
      $targetIsPublished = $entity->isPublished();
    }

    $element['#theme'] = 'entity_fetch_field';
    // @todo refine this indication of where the data comes from.
    $element['#suffix'] = "<div>Source:{$source}</div>";

    $element['#cache'] = [
      'keys' => [
        $field_name,
        $target_entity_type,
        $target_entity_id,
        $target_fieldname,
        $target_paragraph_uuid,
      ],
      'tags' => [
        // The cache should be expired when the source entity is updated.
        "{$target_entity_type}:{$target_entity_id}",
      ],
    ];
    // Handle the unpublished indicator.
    if (!$targetIsPublished) {
      // @todo figure out why the #attributes are not working.
      $element['#attributes'] = [
        'class' => ['node--unpublished'],
      ];
    }

    return $element;
  }

}
