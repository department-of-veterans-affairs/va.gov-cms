<?php

namespace Drupal\va_gov_preview\Encoder;

use Drupal\serialization\Encoder\JsonEncoder as SerializationJsonEncoder;

/**
 * Encodes data in JSON.
 *
 * Simply respond to static_html format requests using the JSON encoder.
 */
class StaticEncoder extends SerializationJsonEncoder {

  /**
   * The formats that this Encoder supports.
   *
   * @var string
   */
  protected static $format = ['static_html'];

  const STATIC_DIRECTORY_NAME = 'static';
  const INDEX_FILE_NAME = 'index.html';

  /**
   * Manipulate the output before it is rendered to the browser.
   */
  public function encode($data, $format, array $context = []) {
    $encoded = parent::encode($data, $format, $context);

    $requested_path = \Drupal::url('<current>', [], ['absolute' => FALSE]);
    $content_path = implode(DIRECTORY_SEPARATOR, [
      DRUPAL_ROOT,
      self::STATIC_DIRECTORY_NAME,
      $requested_path,
      self::INDEX_FILE_NAME,
    ]);

    if (file_exists($content_path)) {

      // We print here instead of returning because, right now, we inherit the
      // JSON encoder, which sets headers that prevent the HTML from being
      // rendered properly.
      print file_get_contents($content_path);
      exit;
    }
    else {
      throw new \Exception("Unable to load content from $content_path. Run `composer va:web:build` command or press `Rebuild VA.gov Front=End` button.");
    }
  }

}
