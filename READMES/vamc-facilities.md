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

Information related to this can be found in CMS Content Model Document for [Full Width Banner alerts with Situation Updates](https://prod.cms.va.gov/admin/structure/types/manage/full_width_banner_alert/document)

```mermaid
flowchart TD
  subgraph CMS 
    FWB[Full width banner alert created/updated] -->|optional| Q(add GovDelivery Bulletin to Queue)
    SU[Situation Update created] -->|optional| Q
    Q --> GDqueue

    GDqueue[(Bulletin Queue)]
    GDendpoint[CMS GovDelivery endpoint] --> PQ(Process all pre start-time items in Bulletin Queue)
    PQ ---> GDsend(Send each item to GovDelivery)
    GDqueue -.->PQ

  end 
  subgraph Content-build
    CB1[Content release start] -->|record start-time|CB2[GraphQL Queries]
    CB2 --> CB3[Content built and deployed]
    CB3 --> CBfinal[Ping CMS GovDelivery endpoint with start-time]-.->GDendpoint
  end
  GDsend -.->GovDelivery[/GovDelivery\]

```

----

[Table of Contents](../README.md)
