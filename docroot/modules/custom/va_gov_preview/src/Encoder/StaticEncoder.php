<?php

namespace Drupal\va_gov_preview\Encoder;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\serialization\Encoder\JsonEncoder as SerializationJsonEncoder;
use Drupal\va_gov_preview\StaticServiceProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Encodes data in JSON.
 *
 * Simply respond to static_html format requests using the JSON encoder.
 */
class StaticEncoder extends SerializationJsonEncoder {
  use StringTranslationTrait;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $stringTranslation;

  /**
   * The formats that this Encoder supports.
   *
   * @var string
   */
  protected static $format = ['static_html'];

  /**
   * StaticEncoder constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(MessengerInterface $messenger, TranslationInterface $string_translation) {
    $this->messenger = $messenger;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Manipulate the output before it is rendered to the browser.
   *
   * This reads the static files right off the server and returns them.
   */
  public function encode($data, $format, array $context = []) {
    $requested_path = Url::fromRoute('<current>', [], ['absolute' => FALSE])->toString();
    $content_path = StaticServiceProvider::urlPathToServerPath($requested_path);

    if (file_exists($content_path)) {

      // We print here instead of returning because, right now, we inherit the
      // JSON encoder, which sets headers that prevent the HTML from being
      // rendered properly.
      print file_get_contents($content_path);
      exit;
    }

    $message = $this->t('Static content file does not yet exist at %path. Please wait for the rebuild process to complete.', [
      '%path' => $content_path,
    ]);
    $this->messenger->addWarning($message);

    (new RedirectResponse($requested_path))->send();
  }

}
