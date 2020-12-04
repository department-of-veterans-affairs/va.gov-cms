<?php

namespace Drupal\va_gov_backend\Plugin\media\Source;

use Drupal\media\MediaSourceBase;

/**
 * External image entity media source.
 *
 * @see \Drupal\file\FileInterface
 *
 * @MediaSource(
 *   id = "external_file",
 *   label = @Translation("External File"),
 *   description = @Translation("Use remote files."),
 *   allowed_field_types = {"link"},
 * )
 */
class ExternalFile extends MediaSourceBase {

  /**
   * Necessary to create a new file type.
   *
   * @inheritdoc
   */
  public function getMetadataAttributes() {
    return [
      'name' => $this->t('Name'),
      'title' => $this->t('Title'),
      'mimetype' => $this->t('Mime type'),
    ];
  }

}
