---
name: Runbook - VAMC Facility name change
about: Steps for updating names and URLs
title: 'VAMC Facility name change: <insert_name>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  VAMC, sitewide
assignees: ''

---

## Intake
- [ ] What triggered this runbook? (Flag in CMS, Help desk ticket, Product team, VHA Digital Media)
Trigger: <insert_trigger>

- [ ] Link to associated help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] If the submitter is an editor, send them link to KB article: ["How Do I Update My VAMC Facility's Basic Location Data?"](https://prod.cms.va.gov/help/vamc/how-do-i-update-my-vamc-facilitys-basic-location-data). Let them know that once VAST/Lighthouse has been updated 1/ the Facility Name will be automatically updated and 2/ we will use a Facility name change flag to update the facility's URL. We can notify them once we have been notified that VAST/Lighthouse has been updated.

- [ ] Link to facility in production:
Facility CMS link: <insert_facility_link>
Facility API ID: <insert_facility_API_ID>

## Acceptance criteria

## VAMC Facility name change

#### CMS help desk steps
**Note: If the help desk is waiting on information from the facility staff or editor, add the "Awaiting editor" flag to the facility with a log message that includes a link to this ticket. Remove the flag when the ticket is ready to be worked by the Facilities team. Be sure to preserve the current moderation state of the node when adding or removing the flag.**
What happens: The name change is made in VAST, that syncs to Lighthouse which syncs to Drupal.
- [ ] Check that the title change in name field on the VAMC node has shown up in Drupal.
- [ ] Create a [URL change request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E), changing the entry from the old facility URL to the new facility URL. **URL changes no longer block the remaining steps in this ticket.**

<insert_url_change_request_link>

#### Drupal Admin steps (CMS Engineer or Help desk)
_Help desk will complete these steps or escalate to request help from CMS engineering._
- [ ] Locate the newly renamed VAMC Facility (https://prod.cms.va.gov/admin/content/bulk) Search by new name
- [ ] Change all data for the facility, in order to ensure changes ship together in the same content release:
    - [ ] Edit facility:
        - [ ] Update URL alias for this facility
        - [ ] Update menu title for the facility to use the new name
        - [ ] Update Meta description use the new name after "at", and add ", formerly known as [previous name]".
        - [ ] Remove flag `Changed name`
        - [ ] Save node (and preserve moderation state)
    - [ ] [Bulk edit](https://prod.cms.va.gov/admin/content/bulk), perform the Action: update URL alias for all facility service nodes on this facility. (https://prod.cms.va.gov/admin/content/bulk?type=health_care_local_health_service)
    - [ ] [Bulk edit](https://prod.cms.va.gov/admin/content/bulk), Action: Resave content, on all facility service nodes for this facility, to fix the title. (https://prod.cms.va.gov/admin/content/bulk?type=health_care_local_health_service)
     - [ ] May also need to directly edit the VAMC System menu to alpha sort the menu item after the title changes
     - [ ] Under [Media](https://prod.cms.va.gov/admin/content/media/images): Find the image for this facility (using the old facility name or Section), and update Alt text and name for facility image, if relevant
- [ ] After the next content release: verify that the new URL for the facility is published and accessible on VA.gov

#### CMS Help desk (wrap up)
- [ ] 14. Notify editor and any other stakeholders. Ask editor to validate that the photo of the facility does not contain the old name in any signage, etc. and to replace if necessary.
