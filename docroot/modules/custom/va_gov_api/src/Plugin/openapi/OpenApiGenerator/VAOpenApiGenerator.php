<?php

namespace Drupal\va_gov_api\Plugin\openapi\OpenApiGenerator;

use Drupal\Component\Utility\NestedArray;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\openapi_jsonapi\Plugin\openapi\OpenApiGenerator\JsonApiGenerator;
use Drupal\Core\Entity\ContentEntityTypeInterface;

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
   * Undocumented function.
   */
  public function getSpecification() {
    $spec = parent::getSpecification();
    // openapi_jsonapi does not provide any reasonable way to override its
    // methods.
    $desiredDefinitions = [
      'node' => [
        'banner',
        'q_a',
        'full_width_banner_alert',
        'checklist',
        'media_list_images',
        'media_list_videos',
        'support_resources_detail_page',
        'faq_multiple_q_a',
        'step_by_step',
      ],
    ];
    $definitions = $this->getSpecificDefinitions($desiredDefinitions);
    ksort($definitions);
    $spec['definitions'] = $definitions;
    return $spec;
  }

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
    // Pull the objects we're getting so we can get the routes that match.
    $desiredDefinitions = [
      'node' => [
        'banner',
        'q_a',
        'full_width_banner_alert',
        'checklist',
        'media_list_images',
        'media_list_videos',
        'support_resources_detail_page',
        'faq_multiple_q_a',
        'step_by_step',
      ],
    ];
    $definition_keys = array_keys($this->getSpecificDefinitions($desiredDefinitions));
    foreach ($routes as $route_name => $route) {
      // Filter out routes that don't match our definition keys.
      $route_matches = FALSE;
      foreach ($definition_keys as $key) {
        if (strpos($route_name, "$key.")) {
          $route_matches = TRUE;
        }
      }
      if (!$route_matches) {
        continue;
      }

      $route_type = $this->getRouteTypeByName($route_name);
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
      // Custom.
      // Deflect relationship routes.
      if (in_array($route_type, ['relationship', 'related'])) {
        continue;
      }
      // Menu link relationships are not correctly linked to a schema.
      if (strpos($route_name, 'menu_link') !== FALSE) {
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

  /**
   * Get requested schema defs as well as relationship schemas.
   *
   * @param array $desiredDefinitions
   *   Definitions in the form 'entitytype' => ['bundle names'].
   *
   * @return array
   *   Definition structures.
   */
  public function getSpecificDefinitions(array $desiredDefinitions) {
    static $definitions = [];
    foreach ($desiredDefinitions as $entity_type_id => $bundles) {
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      if ($entity_type instanceof ContentEntityTypeInterface) {
        foreach ($bundles as $bundle_name) {
          if ($bundle_name == 'banner') {
            $foo = 'bar';
          }
          // Should check that bundle exists on entity type.
          if ($this->includeEntityTypeBundle($entity_type_id, $bundle_name)) {
            $definition_key = $this->getEntityDefinitionKey($entity_type_id, $bundle_name);
            // Continue if we already have this definition.
            if (array_key_exists($definition_key, $definitions)) {
              continue;
            }
            $json_schema = $this->getJsonSchema('api_json', $entity_type_id, $bundle_name);
            $json_schema = $this->fixReferences($json_schema, '#/definitions/' . $definition_key);
            $definitions[$definition_key] = $json_schema;
            // Extract relationships from $json_schema and get definitions.
            // The following is trash, fix it.
            $relationships = $json_schema['properties']['data']['properties']['relationships'] ?: NULL;
            if (count($relationships['properties'])) {
              foreach ($relationships['properties'] as $relationship) {
                $keys = $relationship['properties']['data']['properties']['type']['enum'] ?: [];
                foreach ($keys as $key) {
                  list($entity_type, $bundle_name) = explode("--", $key);
                  if (strpos($entity_type, 'menu') !== FALSE) {
                    $foo = 'bar';
                  }
                  $desiredDefinitions = [$entity_type => [$bundle_name]];
                  $added_definitions = $this->getSpecificDefinitions($desiredDefinitions);
                  $definitions = array_merge($definitions, $added_definitions);
                }
              }
            }
          }
        }
      }
    }
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  private function fixReferences(array $schema, string $prefix) {
    foreach ($schema as $name => $item) {
      if (is_array($item)) {
        $schema[$name] = $this->fixReferences($item, $prefix);
      }
      if ($name === '$ref' && is_string($item) && strpos($item, '#/') !== FALSE) {
        $schema[$name] = preg_replace('/#\//', $prefix . '/', $item);
      }
    }
    return $schema;
  }

}
