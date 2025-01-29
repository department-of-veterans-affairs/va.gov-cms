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
- [ ] 1. Check that the title change in name field on the VAMC node has shown up in Drupal.
- [ ] 2. Create a [URL change request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E), changing the entry from the old facility URL to the new facility URL. (**Note: The URL change request ticket blocks the completion of this ticket.**)

<insert_url_change_request_link>


#### CMS Engineer steps
- [ ] 3. Execute the steps of the URL change request ticket from step 2 above.

(Redirects deploy daily except Friday at 10am ET, or by requesting OOB deploy (of the revproxy job to prod) in #vfs-platform-support. Coordinate the following items below and canonical URL change after URL change ticket is merged, deployed, and verified in prod.)

#### Drupal Admin steps (CMS Engineer or Help desk)
_Help desk will complete these steps or escalate to request help from CMS engineering._
- [ ] 4. Locate the newly renamed VAMC Facility (https://prod.cms.va.gov/admin/content/bulk) Search by new name
- [ ] 5. Updates URL alias for this facility
- [ ] 6. Resave this facility
- [ ] 7. Make bulk alias changes to facility service nodes. (https://prod.cms.va.gov/admin/content/bulk?type=health_care_local_health_service)
- [ ] 8. Bulk save fixed titles to facility service nodes. (https://prod.cms.va.gov/admin/content/bulk?type=health_care_local_health_service)
- [ ] 9. Update menu title for facility
- [ ] 10. May also need to directly edit the VAMC System menu to alpha sort the menu item after the title changes
- [ ] 11. Update Alt text for facility image, if relevant
- [ ] 12. Update Meta description (TBD: some backwards compatibility for SEM, by including something like ", formerly known as [previous name]".
- [ ] 13. Edit facility node and remove flag `Changed name` then save node (with moderation state = published)

#### CMS Help desk (wrap up)
- [ ] 14. Notify editor and any other stakeholders. Ask editor to validate that the photo of the facility does not contain the old name in any signage, etc. and to replace if necessary.
