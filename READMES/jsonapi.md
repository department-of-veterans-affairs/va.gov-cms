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

