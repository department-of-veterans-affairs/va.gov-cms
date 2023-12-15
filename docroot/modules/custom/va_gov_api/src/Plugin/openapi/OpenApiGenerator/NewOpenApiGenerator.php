<?php

namespace Drupal\va_gov_api\Plugin\openapi\OpenApiGenerator;

use Drupal\Component\Utility\NestedArray;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\openapi_jsonapi\Plugin\openapi\OpenApiGenerator\JsonApiGenerator;

/**
 * Defines an OpenApi Schema Generator for the VA.
 *
 * This is a temporary class that should either be removed or patched upstream
 * to openapi_json.
 *
 * @OpenApiGenerator(
 *   id = "newp",
 *   label = @Translation("VA.gov JSON:API"),
 * )
 */
class NewOpenApiGenerator extends JsonApiGenerator {

  /**
   * {@inheritDoc}
   */
  public function getTags() {
    // This is only to reduce the size of the json file for the lighthouse team.
//    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getPaths() {
    $paths = parent::getPaths();
    $filteredPaths = [];

    foreach ($paths as $path => $operations) {
      if (isset($operations['get'])) {
        // Keep only the 'get' operation
        $filteredPaths[$path] = ['get' => $operations['get']];
      }
    }

    return $filteredPaths;
  }

}
