The JSON:API will provide an API from Drupal following the https://jsonapi.org/ spec.

Current test endpoint: 
* https://pr6265-lkoslgyagkodlmzq41fgzass01ozbvla.ci.cms.va.gov/jsonapi
* OpenAPI Spec: https://pr6265-lkoslgyagkodlmzq41fgzass01ozbvla.ci.cms.va.gov/openapi/jsonapi

## Modules Used

| Module            | Core/Contrib                                     | Purpose                                                                                |
|-------------------|--------------------------------------------------|----------------------------------------------------------------------------------------|
| jsonapi           | Core                                             | Provides default JSON:API for all entities in Drupal                                   |
| jsonapi_extras    | https://www.drupal.org/project/jsonapi_extras    | Provides configuration, control and UI for JSON:API. See the module page for examples. |
| jsonapi_resources | https://www.drupal.org/project/jsonapi_resources | Allows for Custom JSON:API routes.                                                     |
| openapi           | https://www.drupal.org/project/openapi           | A framework for generate OpenAPI compliant JSON endpoints.                             |
| openapi_jsonapi   | https://www.drupal.org/project/openapi_jsonapi   | Creates a OpenAPI endpoint for the JSON:API at `/openapi/jsonapi`                      |
| schemata          | https://www.drupal.org/project/schemata          | Provides a JSON:API spec document for Drupal entities.                                 |

## Other Modules to evaluate

Enriches Metadata in JSON:API
* https://www.drupal.org/project/jsonapi_include
* https://www.drupal.org/project/jsonapi_hypermedia
* https://www.drupal.org/project/jsonapi_cross_bundles

Other
* https://www.drupal.org/project/jsonapi_include (https://www.drupal.org/project/jsonapi_include/issues/3172870, https://www.drupal.org/project/jsonapi_include/issues/3172870)
* https://www.drupal.org/project/jsonapi_schema (this would replace `schemata`)
* https://www.drupal.org/project/consumers

Other projects to look at:

* https://next-drupal.org/docs
* https://www.contentacms.org/
* https://github.com/contentacms/contentajs
* https://www.drupal.org/project/jsonapi_include
