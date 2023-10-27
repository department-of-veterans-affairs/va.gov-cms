<?php

namespace Drupal\va_gov_media\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\va_gov_media\EventSubscriber\MediaEventSubscriber;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for VA.gov Media routes.
 */
class AltTextValidationController extends ControllerBase {

  /**
   * Validate the alt text.
   */
  public function validate(Request $req) {
    $logger = \Drupal::logger('va_gov_media');
    $value = $req->request->get('value');
    $value_length = MediaEventSubscriber::getLengthOfSubmittedValue($value);
    $res = TRUE;
    if ($value_length > 150) {
      $logger->error("[CC] Alternative text ({$value}) cannot be longer than 150 characters. {$value_length} characters were submitted.");
      $res = $this->t('Alternative text cannot be longer than 150 characters.');
    }
    if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $value)) {
      $logger->error("[FN] Alternative text cannot contain file names. {$value} was submitted.");
      $res = $this->t('Alternative text cannot contain file names.');
    }
    if (preg_match('/(image|photo|graphic|picture) of/i', $value)) {
      $logger->error("[RP] Alternative text cannot contain repetitive phrases. {$value} was submitted.");
      $res = $this->t('Alternative text cannot contain phrases like “image of”, “photo of”, “graphic of”, “picture of”, etc.');
    }
    return new JsonResponse($res);
  }

}
