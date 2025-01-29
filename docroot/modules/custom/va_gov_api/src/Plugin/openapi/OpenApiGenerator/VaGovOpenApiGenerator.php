<?php

namespace Drupal\va_gov_api\Plugin\openapi\OpenApiGenerator;

use Drupal\openapi_jsonapi\Plugin\openapi\OpenApiGenerator\JsonApiGenerator;

/**
 * Defines an OpenApi Schema Generator for the VA.gov JSON:API.
 *
 * @OpenApiGenerator(
 *   id = "va_gov_json_api",
 *   label = @Translation("VA.gov JSON:API"),
 * )
 */
class VaGovOpenApiGenerator extends JsonApiGenerator {

  /**
   * {@inheritdoc}
   */
  public function getPaths() {
    $paths = parent::getPaths();
    $filteredPaths = [];

    // Keep only the GET operations.
    foreach ($paths as $path => $operations) {
      if (isset($operations['get'])) {
        $filteredPaths[$path] = ['get' => $operations['get']];
      }
    }

    return $filteredPaths;
  }

}
