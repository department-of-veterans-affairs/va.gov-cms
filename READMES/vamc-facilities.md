# VAMC Facilities

1. [Facility Migrations](migrations-facility.md#facility-migrations)
2. [Status Updates to Lighthouse](#status-changes-to-lighthouse)
3. [Facility Service Updates to Lighthouse](#facility-service-changes-to-lighthouse)
4. [System Banner Alerts and Situation Updates to GovDelivery](#system-banner-alerts-and-situation-updates-to-govdelivery)



## Status Changes to Lighthouse
Whenever any NCA facility, VAMC facility, VBA faciltiy, Vet Center, or Vet
Center Outstation has a change of "Operating status", "Operating status -
more info", or "Supplemental status" saved in VACMS (whether by an editor, or migration), a change 
is added to the "post API queue" by module:va_gov_post_api. When cron runs, any
items in the queue are posted to the Lighthouse API.

## Facility Service Changes to Lighthouse
To meet the need for getting timely information related to COVID-19 Vaccines out
to the Facility Locator, we are pushing this data to Lighthouse any time a
"COVID-19 Vaccines" facility service is saved under the following criteria:
 - New and published
 - New and draft
 - Draft of unpublished
 - Archived

 Drafts of a published service are intentionally not pushed.  A change post is
added to the "post API queue" by module:va_gov_post_api. When cron runs, any
items in the queue are posted to the Lighthouse API.  Queueing logic is all
handled in [PostFacilityService.php](../docroot/modules/custom/va_gov_post_api/src/Service/PostFacilityService.php).  This service is currently only using Covid-19
Vaccines but is expandable to handle more or eventually all services.


## System Banner Alerts and Situation Updates to GovDelivery

When a system creates a system banner alert (node: full_width_banner_alert) or an accompanying situation update (paragraph: situation_update), they have an option to send a notification through GovDelivery Bulletins (A gov specific mail service.)  However since there is a delay between when one is published and when it actually hits the website, it was a business requirement that the bulletin not go out before the actual content landed on the VA.gov. To make sure that content stays in sync, we queue all the items that are supposed to go to GovDelivery along with the timestamp of when each one was queued. The content build records the timestamp from when they make the graphQL query, and when the FE build is complete and the new content is out, the last thing in the build step is to ping an endpoint (/api/govdelivery_bulletins/queue?EndTime=[unix timestamp]) in the CMS with the timestamp from the query, at which point the CMS processes all the items in the queue from *before* that timestamp.

The handling is performed in:
  * custom module [VA Gov GovDelivery](https://github.com/department-of-veterans-affairs/va.gov-cms/tree/main/docroot/modules/custom/va_gov_govdelivery)
  * which leverages contrib [GovDelivery Bulletins](https://www.drupal.org/project/govdelivery_bulletins)

  The queue and related settings can be seen here [/admin/config/services/govdelivery-bulletins](https://prod.cms.va.gov/admin/config/services/govdelivery-bulletins)

----

[Table of Contents](../README.md)
