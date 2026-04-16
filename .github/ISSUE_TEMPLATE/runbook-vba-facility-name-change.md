---
name: Runbook - VBA Facility name change
about: Steps for updating names and URLs
title: 'VBA Facility name change: <insert_name>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  VBA, sitewide
assignees: ''

---

## Intake
- [ ] What triggered this runbook? (Flag in CMS via Lighthouse migration)
Trigger: <insert_trigger>

  - [ ] If name change was requested by an Editor and is not the result of a CMS Flag, provide the Editor with a link to [VBA location and contact information: How to Edit](https://prod.cms.va.gov/help/veterans-benefits-administration-vba/location-and-contact-information) for instructions on how to properly get Facility name change recorded, so it will flow through Lighthouse to CMS flag.
     
- [ ] Link to facility in production:
Facility CMS link: <insert_facility_link>
Facility API ID: <insert_facility_API_ID>

## Acceptance criteria

## VBA Facility name change

### CMS help desk steps
**Note: If the help desk is waiting on information from the facility staff or editor, add the "Awaiting editor" flag to the facility with a log message that includes a link to this ticket. Remove the flag when the ticket is ready to be worked by the Facilities team. Be sure to preserve the current moderation state of the node when adding or removing the flag.**
What happens: The name change is made in Sandy's DB, that syncs to Lighthouse which syncs to Drupal.
- [ ] Check that the title change in name field on the VBA node has shown up in Drupal.
- [ ] If the node is published: Create a [URL change request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E), changing the entry from the old facility URL to the new facility URL. **URL changes no longer block the remaining steps in this ticket.**

<insert_url_change_request_link>

#### Drupal Admin steps (CMS Engineer or Help desk)
_Help desk will complete these steps or escalate to request help from CMS engineering._
- [ ] Locate the newly renamed VBA Facility (https://prod.cms.va.gov/admin/content/bulk) Search by new name
- [ ] Change all data for the facility, in order to ensure changes ship together in the same content release:
    - [ ] Edit facility:
        - [ ] Update URL alias for this facility to match the new facility name, lowercase with dashes.
        - [ ] Remove flag `Changed name`
        - [ ] Save node (and preserve moderation state)
    - [ ] [Bulk edit](https://prod.cms.va.gov/admin/content/bulk), perform the Action: update URL alias for all facility service nodes on this facility. (https://prod.cms.va.gov/admin/content/bulk?type=health_care_local_health_service)
    - [ ] [Bulk edit](https://prod.cms.va.gov/admin/content/bulk), Action: Resave content, on all facility service nodes for this facility, to fix the title. (https://prod.cms.va.gov/admin/content/bulk?type=health_care_local_health_service)
     - [ ] Under [Media](https://prod.cms.va.gov/admin/content/media/images): Find the image for this facility (using the old facility name or Section), and update Alt text and name for facility image, if relevant
     - [ ] If this facility is a Regional Office: Go to [Sections taxonomy]( https://prod.cms.va.gov/admin/structure/taxonomy/manage/administration/overview), VBA > Rename the term that matches the old Facility name to use the new Facility name
- [ ] After the next content release: verify that the new URL for the facility is published and accessible on VA.gov

#### CMS Help desk (wrap up)
- [ ] Notify editor and any other stakeholders. Ask editor to validate that the photo of the facility does not contain the old name in any signage, etc. and to replace if necessary.
