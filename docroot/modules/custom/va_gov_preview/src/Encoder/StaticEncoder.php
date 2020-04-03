<?php

namespace Drupal\va_gov_preview\Encoder;

use Drupal\serialization\Encoder\JsonEncoder as SerializationJsonEncoder;
use Drupal\va_gov_preview\StaticServiceProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;

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

  /**
   * Manipulate the output before it is rendered to the browser.
   *
   * This reads the static files right off the server and returns them.
   */
  public function encode($data, $format, array $context = []) {

    $requested_path = \Drupal::url('<current>', [], ['absolute' => FALSE]);

    $content_path = StaticServiceProvider::urlPathToServerPath($requested_path);
    if (file_exists($content_path)) {

      // We print here instead of returning because, right now, we inherit the
      // JSON encoder, which sets headers that prevent the HTML from being
      // rendered properly.
      print file_get_contents($content_path);
      exit;
    }
    else {
      // Drupal: Goto!
      drupal_set_message(t("Static content file does not yet exist at %path. Please wait for rebuild process to complete.", [
        '%path' => $content_path,
      ]), 'warning');

      // Yup. drupal_goto() is gone.
      (new RedirectResponse($requested_path))->send();
      return;
    }
  }

}
