---
name: Runbook - New Vet Center Facility
about: changing facility information in the CMS for Vet Center facilities
title: 'New Vet Center Facility: <insert_name_of_facility>'
labels: Change request, Vet Center, Facilities, User support, VA.gov frontend, Drupal engineering
assignees: ''

---

## Intake
- [ ] What triggered this runbook? (Flag in CMS, Help desk ticket, Product team, VHA Digital Media)
Trigger: <insert_trigger>

- [ ] Link to associated help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] If the submitter is an editor, send them links to any relevate KB articles for the Vet Center product.
KB articles: <insert_kb_article_links>

- [ ] Link to facility in production:
Facility CMS link: <insert_facility_link>
Facility API ID: <insert_facility_API_ID>

## Acceptance criteria

### New Vet Center
[@TODO: KB ARTICLE FOR ADDING VET CENTERS - SEE runbook-vamc-facility-new]

#### CMS help desk steps
- [ ] 1. Become aware that the new facility is now on the Facility API (typically, via a Flag).
- [ ] 2. Check with RCS(?) what district it belongs to.
- [ ] 3. Move the section to the appropriate district.
- [ ] 4. Communicate with editor (do they need to be onboarded)

[@TODO write sample email - SEE runbook-vamc-facility-new]

- [ ] 5. Create a [URL change request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E). (**Note: The URL change request ticket blocks the completion of this ticket.**)

<insert_url_change_request_link>

(Redirects deploy weekly on Wed. at 10am ET, or by requesting OOB deploy (of the revproxy job to prod) in #vfs-platform-support. Coordinate the items below and canonical URL change after URL change ticket is merged, deployed, and verified in prod.)

- [ ] 6. When editor has prepared content and let help desk know, reassign this issue to appropriate CMS engineer on Product Support team, for bulk publishing.

#### CMS engineer steps
- [ ] 7. Execute the steps of the URL change request ticket from step 5.
- [ ] 8. Bulk publish the nodes and facility.
- [ ] 9. Edits facility node by removing the `New facility` flag and saves the node.
- [ ] 10. Let help desk know this has been done.

#### CMS Help desk (wrap up)
- [ ] 11. Notify editor and any other stakeholders.
