# Downstream Dependencies

## Background

This document lists known services which depend on the CMS.

## Services

| ServiceName                                                                  | Monitoring                                                          | Mode                                                                                                                                                                                                                                                                                                          | Data                                                                                                                               |
|------------------------------------------------------------------------------|---------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------|
| Forms API                                                                    | [Datadog](https://app.datadoghq.com/synthetics/details/2fc-eae-4zx) | Lighthouse pulls data with GraphQL ([README](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/migrations-forms.md#cms-forms-data-to-lighthouse))                                                                                                                              | form data (manually edited auxiliary fields) from “VA Form” nodes                                                                  |
| [GraphQL (Content API)](#graphql-content-api-notes)                          | [Datadog](https://app.datadoghq.com/synthetics/details/2fc-eae-4zx) | GraphQL contrib module provides endpoint and explorer ([README](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/graph_ql.md))                                                                                                                                                | All Drupal entities (content & config)                                                                                             |
| Facility Statuses (push to lighthouse                                        | Alerts to slack (#cms-notifications channel) on failure             | post_api contrib module is used to POST updates to lighthouse’s API every 10-15 minutes (when updates are available in queue) ([README](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/vamc-facilities.md#status-changes-to-lighthouse))                                    | Facility statuses (certain fields on VAMC statuses, operating status, additional status info, facility API locator ID used as GID) |
| Health service descriptions                                                  | Alerts to slack (#cms-notifications channel) on failure             | post_api contrib module is used to POST updates to the Lighthouse API on cron every 10-15 minutes (when updates are available in queue) ([README](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/vamc-facilities.md#status-changes-to-lighthouse))                          | Only Covid 19 vaccine service descriptions at this time, but soon to be all health service descriptions.                           |
| Post content release webhook endpoint (Jenkins calls after content releases) | [Datadog](https://app.datadoghq.com/synthetics/details/ei9-6u7-c44) | Webhook endpoint (GET) at /api/govdelivery_bulletins/queue - used to trigger sending of notifications to govdelivery.                                                                                                                                                                                         | `?EndTime=<unix timestamp>` of last successful GQL content query                                                                   |
| Feature flags endpoint (/flags_list)                                         | [Datadog](https://app.datadoghq.com/synthetics/details/tvy-z92-4qd) | GET endpoint at `/flags_list` that provides a list of feature flags for the content build (http://jenkins.vfs.va.gov/job/builds/job/content-build-content-only-vagovprod/) to consume ([README](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/interfaces.md#featureflags)) | Feature flags that control whether certain products are enabled                                                                    |                                                                 |

### GraphQL (Content API) Notes

 * Use Cases
     * Content build
         * Frequency
             * hourly+ 8-5 ET
     * Forms API = form data (manually edited auxiliary fields) from “VA Form” nodes ([README](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/migrations-forms.md#cms-forms-data-to-lighthouse))
         * Frequency
             * Nightly (0100 ET)

