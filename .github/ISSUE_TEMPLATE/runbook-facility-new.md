---
name: Runbook - New facility
about: changing facility information in the CMS for VHA facilities
title: 'New Facility: <insert_name_of_facility>'
labels: Change request
assignees: ''

---

## Background

*What triggered this runbook?*
_eg Flag in CMS, Help desk ticket, Product team, VHA Digital Media_


## Acceptance criteria

### VAMC

CMS help desk steps
- [ ] 1. Become aware that the new facility is now on the Facility API (typically, via a Flag).
- [ ] 2. Check with VHA digital media what section and VAMC it belongs to. [@todo write sample email.]
- [ ] 3. Updates the Section (default is "VAMC facilities", but it should be a VAMC system in a VISN) and VAMC system field accordingly.
- [ ] 4. Communicate with editor (cc VHA Digital Media) to give them go-ahead to complete the content, with this [KB article](https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-do-i-add-a-facility-to-my-health-care-system). [@todo write sample email.]
- [ ] 5. When editor has prepared content and let help desk know, reassign this issue to appropriate CMS engineer on Product Support team, for bulk publishing.

CMS engineer
- [ ] 6. CMS engineer bulk publishes nodes and facility.
- [ ] 7. CMS engineer edit facility node and remove `New facility` flag and save node.
- [ ] 8. Let help desk know this has been done.

CMS Help desk
- [ ] 9. HD notifies editor and any other stakeholders.


### Vet Center

CMS help desk steps
- [ ] 1. Become aware that the new facility is now on the Facility API (typically, via a Flag).
- [ ] 2. Check with RCS(?) what district it belongs to.
- [ ] 3. Move the section to the appropriate district.
- [ ] 4. Communicate with editor (do they need to be onboarded) @todo write sample email.
- [ ] 5. When editor has prepared content and let help desk know, reassign this issue to appropriate CMS engineer on Product Support team, for bulk publishing.

CMS engineer
- [ ] 6. CMS engineer bulk publishes nodes and facility.
- [ ] 7. CMS engineer edit facility node and remove `New facility` flag and save node.
- [ ] 9. Let help desk know this has been done.

CMS Help desk
- [ ] 9. HD notifies editor and any other stakeholders.

## CMS Team
Please check the team(s) that will do this work.

- [ ] `CMS Program`
- [ ] `Platform CMS Team`
- [ ] `Sitewide CMS Team ` (leave Sitewide unchecked and check the specific team instead)
  - [ ] `⭐️ Content ops`
  - [ ] `⭐️ CMS experience`
  - [ ] `⭐️ Offices`
  - [x] `⭐️ Product support`
  - [x] `⭐️ User support`
