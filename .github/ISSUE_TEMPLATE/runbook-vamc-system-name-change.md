---
name: Runbook - VAMC system name change
about: How to update the name of a VAMC.
title: 'VAMC system name change: <insert_name_of_vamc>'
labels: Change request, VAMC, Facilities, User support, VA.gov frontend, Drupal engineering
assignees: ''

---

## Intake
- [ ] What triggered this runbook? (Flag in CMS, Help desk ticket, Product team, VHA Digital Media)
Trigger: <insert_trigger>

- [ ] Link to associated help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] Link to system in production:
System CMS link: <insert_facility_link>
System API ID: <insert_facility_API_ID>

## Steps before proceeding

- [ ] Check with Facilities team Product Owner to get approval of name change.
- [ ] Check with VHA Digital Media.

## VAMC system name change

Timing around these is critical and we may need more detail here.

#### CMS help desk steps
- [ ] 1. Create a [URL change request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E), changing the entry from the old facility URL to the new facility URL. (**Note: The URL change request ticket blocks the completion of this ticket.**)

<insert_url_change_request_link>

(Redirects deploy weekly on Wed. at 10am ET, or by requesting OOB deploy (of the revproxy job to prod) in #vfs-platform-support. Coordinate the items below and canonical URL change after URL change ticket is merged, deployed, and verified in prod.)

#### CMS engineer steps
- [ ] 2. Execute the steps of the URL change request ticket from step 1.
- [ ] 3. Update the Section name.
- [ ] 3. Bulk alias change all nodes within the system. (https://prod.cms.va.gov/admin/content/bulk)
- [ ] 4. Bulk save to fix titles for all nodes within system. (https://prod.cms.va.gov/admin/content/bulk?type=health_care_local_health_service)
- [ ] 5. Create a PR to rename the menu for the system accordingly.  (In the future, they may need to rebuild the menu so that name and machine name match.)

#### CMS Help desk (wrap up)
- [ ] 6. Notify editor and any other stakeholders.
