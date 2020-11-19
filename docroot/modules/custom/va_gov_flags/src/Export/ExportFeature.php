<?php

namespace Drupal\va_gov_flags\Export;

use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Site\Settings;
use Drupal\va_gov_flags\FeatureFlagDataBuilderInterface;

/**
 * A service to export feature flags to a file.
 */
class ExportFeature implements ExportFeatureInterface {

  /**
   * Feature Flag builder service.
   *
   * @var \Drupal\va_gov_flags\FeatureFlagDataBuilderInterface
   */
  protected $featureFlagDataBuilder;

  /**
   * FileSystem service.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * Serializer.
   *
   * @var \Drupal\Component\Serialization\SerializationInterface
   */
  protected $serializer;

  /**
   * ExportFeature constructor.
   *
   * @param \Drupal\va_gov_flags\FeatureFlagDataBuilderInterface $featureFlagDataBuilder
   *   THe feature data builder class.
   * @param \Drupal\Core\File\FileSystem $fileSystem
   *   The file system class.
   * @param \Drupal\Component\Serialization\SerializationInterface $serializer
   *   The serialization class to use.
   */
  public function __construct(
    FeatureFlagDataBuilderInterface $featureFlagDataBuilder,
    FileSystem $fileSystem,
    SerializationInterface $serializer
  ) {
    $this->featureFlagDataBuilder = $featureFlagDataBuilder;
    $this->fileSystem = $fileSystem;
    $this->serializer = $serializer;
  }

  /**
   * {@inheritDoc}
   */
  public function export() : void {
    $data = $this->featureFlagDataBuilder->buildData();
    $encoded = $this->serializer::encode($data);

    $this->fileSystem->saveData($encoded, static::getPath());
  }

  /**
   * {@inheritDoc}
   */
  public static function getPath() : string {
    return Settings::get('va_gov_flags_export_path', 'public://va_gov_flags.json');
  }

}
