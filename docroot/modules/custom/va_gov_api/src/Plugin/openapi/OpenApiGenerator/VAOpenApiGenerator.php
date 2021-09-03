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
 *   id = "va_gov",
 *   label = @Translation("VAGOV"),
 * )
 */
class VAOpenApiGenerator extends JsonApiGenerator {

  /**
   * {@inheritDoc}
   */
  public function getTags() {
    // This is only to reduce the size of the json file for the lighthouse team.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getPaths() {
    $routes = $this->getJsonApiRoutes();
    // *** Start Includes the patch from:
    // https://www.drupal.org/project/openapi_jsonapi/issues/3079209
    // which did not apply correctly.
    // This needs to be pushed upstream.
    $read_only_mode_is_enabled = $this->configFactory->get('jsonapi.settings')->get('read_only');
    $read_only_methods = ['GET', 'HEAD', 'OPTIONS', 'TRACE'];

    $api_paths = [];
    foreach ($routes as $route_name => $route) {
      if ($read_only_mode_is_enabled === TRUE) {
        $supported_methods = $route->getMethods();
        assert(count($supported_methods) > 0, 'JSON:API routes always have a method specified.');
        $is_read_only_route = empty(array_diff($supported_methods, $read_only_methods));
        if ($is_read_only_route === FALSE) {
          continue;
        }
      }
      // *** End customizations
      /** @var \Drupal\jsonapi\ResourceType\ResourceType $resource_type */
      $resource_type = $this->getResourceType($route_name, $route);
      if (!$resource_type instanceof ResourceType) {
        continue;
      }
      $entity_type_id = $resource_type->getEntityTypeId();
      $bundle_name = $resource_type->getBundle();
      if (!$this->includeEntityTypeBundle($entity_type_id, $bundle_name)) {
        continue;
      }
      $api_path = [];
      $methods = $route->getMethods();
      foreach ($methods as $method) {
        $method = strtolower($method);
        $path_method = [
          'summary' => $this->getRouteMethodSummary($route, $route_name, $method),
          'description' => $this->getRouteMethodDescription($route, $route_name, $method, $resource_type->getTypeName()),
          'parameters' => $this->getMethodParameters($route, $route_name, $resource_type, $method),
          // *** Overridden only to reduce the size of the initial json file.
          'tags' => [],
          // Old 'tags' => $this->getBundleTag($entity_type_id, $bundle_name)
          'responses' => $this->getEntityResponsesJsonApi($entity_type_id, $method, $bundle_name, $route_name, $route),
        ];
        /*
         * @TODO: #2977109 - Calculate oauth scopes required.
         *
         * if (array_key_exists('oauth2', $path_method['security'])) {
         *   ...
         * }
         */

        $api_path[$method] = $path_method;
      }
      // Each path contains the "base path" from a OpenAPI perspective.
      $path = str_replace($this->getJsonApiBase(), '', $route->getPath());
      $api_paths[$path] = NestedArray::mergeDeep(empty($api_paths[$path]) ? [] : $api_paths[$path], $api_path);
    }
    return $api_paths;
  }

}
