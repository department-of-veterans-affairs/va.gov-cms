# JSON:API

The site uses [JSON:API](https://www.drupal.org/docs/core-modules-and-themes/core-modules/jsonapi-module/api-overview) (from core) and some additional related modules as the mechanism for delivering JSON to Next.js for page data (see https://github.com/department-of-veterans-affairs/next-build for info and setup).

The key point to know is that:

> The API that the JSON:API module makes available is centered on Drupal's entity types and bundles. Every bundle receives its own, unique URL path, which all follow a shared pattern.
>
> Unlike the Drupal Core REST module, these paths are not configurable and are **all enabled by default**.

The main JSONAPI endpoint is at `/jsonapi`.

We are using the [jsonapi_extras](https://www.drupal.org/project/jsonapi_extras) to tighten up what information is included for various entity responses.
This pruning of available information reduces the response size served over the wire on each request to JSONAPI, and decreases the time it takes to build the site.
Much of the default information included is not necessary for FE consumers, so it has (or will be) disabled.

See `/admin/config/services/jsonapi/resource_types` for a full list of information Drupal can serve through JSON:API.

If you expect to see a field's data included in the response and it is not there, check the overwrites, if any, for that resource and adjust appropriately.

See the config at `admin/config/services/jsonapi/resource_types/node--news_story/edit?destination=/admin/config/services/jsonapi/resource_types` for an example.

**Breadcrumbs and JSON:API**
See in [CMS Breadcrumb documentation](https://prod.cms.va.gov/admin/structure/cm_document/note/126/breadcrumbs)

## UI Explorer

You can explore the JSON:API endpoints via a Swagger UI to see what's available and to test out requests and responses.

1. Login to the site as a user with an "administrator" or "content_api_consumer" role.
2. Go to "/admin/config/services/openapi/swagger/jsonapi".
3. You should only see one UI option "VA.gov JSON:API". Click on the link to explore.
3. Read a [Swagger UI tutorial](https://idratherbewriting.com/learnapidoc/pubapis_swagger.html) to familiarize
   yourself with the UI features, if you aren't already.

Via this UI, you can:

1. View resource information - Each endpoint has a description, list of parameters, and response codes. We limit the
   operations to GET requests only.
2. Try it out - You can test each endpoint with a "Try it out" button in the top right section of "Parameters".
3. Filter by tag - Use the search to narrow down the endpoints displayed.
4. Deep Link - As you click to gather information about the API, the URL automatically updates so you can share that
   specific documentation with others.

## Tests

You can find JSON:API tests in the following places:

- 'tests/phpunit/API/JsonApiRequestTest' - Tests GET requests and associated configuration.
- 'tests/phpunit/API/JsonApiExplorerUITest' - Tests Swagger UI for OpenAPI documentation.

## Field Type Enhancers

JSON:API Extras allows "field enhancers" to change the normalized output sent back in a response. These are set in
configuration on "/admin/config/services/jsonapi/resource_types", but there is no way to use current configuration
and code to set a default field enhancer for all field instances of the same field type. We are calling this feature
"field type enhancers" to contrast with the current field instance enhancers tied to resources.

On "/admin/config/services/jsonapi/field_types", we added a configuration form where you can add a field type
enhancer that will apply to all fields of that type. However, if you set a field instance enhancer, that will
override the field type enhancer. So, users can set an enhancer for most of the field instances of one field type
but then customize each field instance as needed.

This custom code is in a patch, but we are trying to incorporate it into the main `jsonapi_extras` module in this issue:
https://www.drupal.org/project/jsonapi_extras/issues/3025283

To use the field instance enhancers:

1. Go to "/admin/config/services/jsonapi/resource_types" and click to edit the resource type you are working with.
2. Check the fields you want alter output for to see if they already have an enhancer applied.
3. If there is an enhancer configured for that field instance, then that is what will be used for JSON:API responses.
4. If you want to use a field type enhancer, remove the configured field instance enhancer.

To use the field type enhancers:

1. Go to "/admin/config/services/jsonapi/field_types" and check the "Included Field Types" text area.
2. If you see your field added and have an enhancer configuration form visible, then skip to step #4.
3. If the field isn't included, then add it to the textarea and save the form.
4. Adjust the enhancer type and settings to achieve the output you want for that field type and save the form.
5. All field types should now be transformed by the selected enhancer for JSON:API responses, unless a field
   instance enhancer is overriding the field type enhancer.
