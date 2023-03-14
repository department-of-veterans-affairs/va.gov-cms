---
name: Runbook - New Vet Center Facility
about: changing facility information in the CMS for Vet Center facilities
title: 'New Vet Center Facility: <insert_name_of_facility>'
labels: Change request
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

- [ ] Link to new facility in production:
Facility link: <insert_facility_link>

## Acceptance criteria

### New Vet Center
[@TODO: KB ARTICLE FOR ADDING VET CENTERS - SEE runbook-vamc-facility-new]

#### CMS help desk steps
- [ ] 1. Become aware that the new facility is now on the Facility API (typically, via a Flag).
- [ ] 2. Check with RCS(?) what district it belongs to.
- [ ] 3. Move the section to the appropriate district.
- [ ] 4. Communicate with editor (do they need to be onboarded) 

[@TODO write sample email - SEE runbook-vamc-facility-new]

- [ ] 5. When editor has prepared content and let help desk know, reassign this issue to appropriate CMS engineer on Product Support team, for bulk publishing.

#### CMS engineer steps
- [ ] 6. CMS engineer bulk publishes the nodes and facility.
- [ ] 7. CMS engineer edits facility node by removing the `New facility` flag and saves the node.
- [ ] 8. Let help desk know this has been done.
- [ ] 9. CMS engineer updates the [CSV in Lighthouse](https://github.com/department-of-veterans-affairs/lighthouse-facilities/blob/master/facilities/src/main/resources/websites.csv) with the changed URL, creating a PR, tagging the Lighthouse team and linking to it in Slack with an @mention to a Lighthouse team member 

#### CMS Help desk (wrap up)
- [ ] 10. Help desk notifies editor and any other stakeholders.

### Team
Please check the team(s) that will do this work.

- [ ] `CMS Team`
- [ ] `Public Websites`
- [x] `Facilities`
- [x] `User support`