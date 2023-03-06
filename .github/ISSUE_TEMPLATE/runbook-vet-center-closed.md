---
name: Runbook - Vet Center Facility closed
about: Steps for archiving a Vet Center facility in VA.gov CMS.
title: 'Vet Center Facility closed: <insert_name>'
labels: Change request
assignees: ''

---

## Background

**Who are the stakeholders for this?**

- Editor(s):
- District web manager (optional):
- Product team member:
- CMS Help desk

**Associated HD issue, if any**

<insert_link>

## Acceptance criteria

### Vet Center facility closure
- [ ] CMS team becomes aware that the facility is no longer on the Facility API.
- [ ] CMS team submits [Redirect request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&labels=Redirect+request&template=redirect-request-facility-url.md&title=Redirect+Request+for%3A+%3Cinsert+facility+name%3E), cc'ing Facilities team, and referencing this issue.
- [ ] Once timing of Redirect going live is known, alert CMS engineers to carry out the other steps
- [ ] CMS engineer bulk unpublishes the nodes.
- [ ] CMS engineer removes the Section.
- [ ] CMS engineer edit facility node and remove flag `Removed from source`, sets moderation state to archived, then save node.

### Team
Please check the team(s) that will do this work.

- [ ] `CMS Team`
- [ ] `Public Websites`
- [x] `Facilities`
- [x] `User support`
