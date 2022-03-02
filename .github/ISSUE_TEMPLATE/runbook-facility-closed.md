---
name: Runbook - Facility closed
about: Steps for arching a facility in VA.gov CMS.
title: 'Facility closed: <insert_name>'
labels: Change request
assignees: ''

---

## Acceptance criteria

VAMCs - facility closure
- [ ] CMS team becomes aware that the facility is no longer on the Facility API.
- [ ] CMS engineer bulk archives the facility service nodes. (https://prod.cms.va.gov/admin/content/bulk?type=health_care_local_health_service)
- [ ] CMS engineer finds the menu for the system https://prod.cms.va.gov/admin/structure/menu and deletes the menu item for the closed facility.
- [ ] CMS engineer filters content by the health care system and scans for any events that might be taking place at that facility. Archive if any are found.
- [ ] CMS engineer edits the facility node, removes  flag `Removed from source`, add a revision log to cover who requested the change and change moderation state to archive.
- [ ] HD notifies editor and any other stakeholders.


Vet Center – facility closure
- [ ] CMS team becomes aware that the facility is no longer on the Facility API.
- [ ] CMS team submits [Redirect request](https://github.com/department-of-veterans-affairs/va.gov-team/issues/new?assignees=mnorthuis&labels=ia&template=redirect-request.md&title=Redirect+Request), cc'ing Facilities team, and referencing this issue.
- [ ] Once timing of Redirect going live is known, alert CMS engineers to carry out the other steps
- [ ] CMS engineer bulk unpublishes the nodes.
- [ ] CMS engineer removes the Section.
- [ ] CMS engineer edit facility node and remove flag `Removed from source`, sets moderation state to archived, then save node.




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
