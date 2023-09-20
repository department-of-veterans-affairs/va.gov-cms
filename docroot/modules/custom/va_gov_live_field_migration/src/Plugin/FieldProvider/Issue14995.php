<?php

namespace Drupal\va_gov_live_field_migration\Plugin\FieldProvider;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\va_gov_live_field_migration\FieldProvider\Plugin\FieldProviderPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provider that returns a pre-defined list of fields.
 *
 * These are only the translatable fields; non-translatable fields may be dealt
 * with in a later issue.
 *
 * @see https://github.com/department-of-veterans-affairs/va.gov-cms/issues/14995
 *
 * @FieldProvider(
 *   id = "issue_14995",
 *   label = @Translation("Issue 14995")
 * )
 */
class Issue14995 extends FieldProviderPluginBase {

  const FIELDS = [
    'paragraph' => [
      'field_phone_label',
      'field_alert_heading',
      'field_text_expander',
      'field_error_message',
      'field_section_header',
      'field_question',
      'field_email_label',
      'field_button_label',
      'field_loading_message',
      'field_short_phrase_with_a_number',
      'field_title',
      'field_link_summary',
    ],
    'node' => [
      'field_teaser_text',
      'field_description',
      'field_home_page_hub_label',
    ],
  ];

  const VALID_FIELD_TYPES = [
    'string',
    'text',
  ];

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected EntityFieldManagerInterface $entityFieldManager;

  /**
   * {@inheritDoc}
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    TranslationInterface $stringTranslation,
    EntityFieldManagerInterface $entityFieldManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $stringTranslation);
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * {@inheritDoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('string_translation'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * Determine whether this field is still a valid target.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   *
   * @return bool
   *   Whether this field is still a valid target.
   */
  public function isStillValid(string $entityType, string $fieldName) : bool {
    $fieldStorage = $this->entityFieldManager->getFieldStorageDefinitions($entityType)[$fieldName];
    if ($fieldStorage === NULL) {
      return FALSE;
    }
    return in_array($fieldStorage->getType(), static::VALID_FIELD_TYPES);
  }

  /**
   * Determine whether this field exists on a specified bundle.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   * @param string $bundle
   *   The bundle.
   *
   * @return bool
   *   Whether this field exists on a specified bundle.
   */
  public function fieldExistsOnBundle(string $entityType, string $fieldName, string $bundle) : bool {
    $fieldConfig = $this->entityFieldManager->getFieldDefinitions($entityType, $bundle)[$fieldName];
    return $fieldConfig !== NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getFields(string $entityType, string $bundle = NULL) : array {
    $fields = static::FIELDS[$entityType] ?? [];
    if ($bundle !== NULL) {
      $fields = array_filter($fields, function ($field) use ($entityType, $bundle) {
        return $this->fieldExistsOnBundle($entityType, $field, $bundle);
      });
    }
    $fields = array_filter($fields, function ($field) use ($entityType) {
      return $this->isStillValid($entityType, $field);
    });
    return $fields;
  }

}
