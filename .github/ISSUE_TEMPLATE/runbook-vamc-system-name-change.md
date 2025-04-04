---
name: Runbook - VAMC system name change
about: How to update the name of a VAMC.
title: 'VAMC system name change: <insert_name_of_vamc>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  VAMC, sitewide
assignees: ''

---

## Intake
VAMC Systems only change name via acts of Congress. These sorts of changes are not automatically flagged, and this runbook must be manually identified to run.

- [ ] Link to associated help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] Link to system in production:
System CMS link: <insert_facility_link>
System API ID: <insert_facility_API_ID>

## Steps before proceeding
**Note: If the help desk is waiting on information from the facility staff or editor, add the "Awaiting editor" flag to the facility with a log message that includes a link to this ticket. Remove the flag when the ticket is ready to be worked by the Facilities team. Be sure to preserve the current moderation state of the node when adding or removing the flag.**
- [ ] Check with Facilities team Product Owner to get approval of name change.
- [ ] Check with VHA Digital Media to confirm approval

## VAMC system name change
Timing around these is critical and VHA DM / VA PO will help determine timing and priority.  **It may be advisable to perform all the changes listed below in a Tugboat in order to get stakeholder signoff before making changes in production.**

### CMS help desk steps
- [ ] 1. Create a [URL change request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E), changing the entry from the old facility URL to the new facility URL. **Redirects no longer block remaining steps in this ticket.** Create the ticket, then proceed with remaining steps.

<insert_url_change_request_link>

### Drupal Admin steps (CMS Engineer or Help desk)
- [ ] Update the Section name to new VAMC System name.
- [ ] [Bulk edit](https://prod.cms.va.gov/admin/content/bulk), perform the Action: update URL alias: for all nodes within the system. (https://prod.cms.va.gov/admin/content/bulk)
- [ ] [Bulk edit](https://prod.cms.va.gov/admin/content/bulk), Action: Resave content, for all nodes within system.
- [ ] After next content release, verify that all content presents correctly in production.


### CMS engineer
- [ ] Create a PR to rename the menu for the system accordingly.  (In the future, they may need to rebuild the menu so that name and machine name match.)
- [ ] Build the front-end from a Tugboat and verify that all pages within the system still correctly use the menu and link correctly, before merge.
- [ ] Merge & verify after deploy in production

### CMS Help desk (wrap up)
- [ ] Notify editor and any other stakeholders that changes are complete
