# Downstream Dependencies

## Background

This document lists known services which depend on the CMS.

## Services

* Forms API
    * Mode
        * Lighthouse pulls data with GraphQL ([README](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/migrations-forms.md#cms-forms-data-to-lighthouse))
    * Data
        * form data (manually edited auxiliary fields) from “VA Form” nodes
    * Monitoring
        * [Datadog](https://app.datadoghq.com/synthetics/details/2fc-eae-4zx)
* GraphQL (Content API)
    * Mode
        * GraphQL contrib module provides endpoint and explorer ([README](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/graph_ql.md))
    * Use Cases
        * Content build
            * Frequency
                * hourly+ 8-5 ET
        * Forms API = form data (manually edited auxiliary fields) from “VA Form” nodes ([README](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/migrations-forms.md#cms-forms-data-to-lighthouse))
            * Frequency
                * Nightly (0100 ET)
    * Data
        * All Drupal entities (content & config)
    * Monitoring
        * [Datadog](https://app.datadoghq.com/synthetics/details/2fc-eae-4zx)
* Facility Statuses (push to lighthouse)
    * Mode
        * post_api contrib module is used to POST updates to lighthouse’s API every 10-15 minutes (when updates are available in queue) ([README](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/vamc-facilities.md#status-changes-to-lighthouse))
        * Data
            * Facility statuses (certain fields on VAMC statuses, operating status, additional status info, facility API locator ID used as GID)
        * Monitoring
            * Alerts to slack (#cms-notifications channel) on failure
* Health service descriptions
    * Mode
        * post_api contrib module is used to POST updates to the Lighthouse API on cron every 10-15 minutes (when updates are available in queue) ([README](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/vamc-facilities.md#status-changes-to-lighthouse))
    * Data
        *  Only Covid 19 vaccine service descriptions at this time, but soon to be all health service descriptions.
    * Monitoring
        * Alerts to slack (#cms-notifications channel) on failure
* Post content release webhook endpoint (Jenkins calls after content releases)
    * Mode
        * Webhook endpoint (GET) at /api/govdelivery_bulletins/queue - used to trigger sending of notifications to govdelivery.
    * Data
        * `?EndTime=<unix timestamp>` of last successful GQL content query
    * Monitoring
        * [Datadog](https://app.datadoghq.com/synthetics/details/ei9-6u7-c44)
* Feature flags endpoint (/flags_list)
    * Mode
        * GET endpoint at `/flags_list` that provides a list of feature flags for the content build (http://jenkins.vfs.va.gov/job/builds/job/content-build-content-only-vagovprod/) to consume ([README](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/interfaces.md#featureflags))
    * Data
        * Feature flags that control whether certain products are enabled
    * Monitoring
        * [Datadog](https://app.datadoghq.com/synthetics/details/tvy-z92-4qd)
