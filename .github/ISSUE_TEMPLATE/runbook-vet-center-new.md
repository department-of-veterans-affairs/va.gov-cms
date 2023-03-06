---
name: Runbook - New Vet Center Facility
about: changing facility information in the CMS for Vet Center facilities
title: 'New Vet Center Facility: <insert_name_of_facility>'
labels: Change request
assignees: ''

---

## Background

*What triggered this runbook?*
_eg Flag in CMS, Help desk ticket, Product team, VHA Digital Media_

*1. Please attach Facility Locator link to this ticket*

*2. Please attach prod link to this ticket*

*3. Please also include link to Jira help desk ticket, if applicable*

## Acceptance criteria

### Vet Center

#### CMS help desk steps
- [ ] 1. Become aware that the new facility is now on the Facility API (typically, via a Flag).
- [ ] 2. Check with RCS(?) what district it belongs to.
- [ ] 3. Move the section to the appropriate district.
- [ ] 4. Communicate with editor (do they need to be onboarded) @todo write sample email.
- [ ] 5. When editor has prepared content and let help desk know, reassign this issue to appropriate CMS engineer on Product Support team, for bulk publishing.

#### CMS engineer
- [ ] 6. CMS engineer bulk publishes nodes and facility.
- [ ] 7. CMS engineer edit facility node and remove `New facility` flag and save node.
- [ ] 9. Let help desk know this has been done.

#### CMS Help desk
- [ ] 9. HD notifies editor and any other stakeholders.

### Team
Please check the team(s) that will do this work.

- [ ] `CMS Team`
- [ ] `Public Websites`
- [x] `Facilities`
- [x] `User support`