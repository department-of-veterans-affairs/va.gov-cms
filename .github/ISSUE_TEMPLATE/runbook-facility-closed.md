---
name: Runbook - Facility closed
about: Steps for arching a facility in VA.gov CMS.
title: 'Facility closed: <insert_name>'
labels: Change request
assignees: ''

---

## Background

**Who are the stakeholders for this?**

- Editor(s):
- VISN web manager (optional):
- Product team member:
- CMS Help desk

**Associated HD issue, if any**

<insert_link>

## Acceptance criteria

### VAMC facility closure

#### 1. CMS Help desk
- [ ] Becomes aware that the facility is no longer on the Facility API, via the "Facility closed" flag, and create github issue  (Future state: Flag autogenerates github issue)
- [ ] Fill out the stakeholders in github issue.
- [ ] If we don't already have context (say, via a HD ticket submitted by an editor), check with editor to find out more about the status of the facility
- [ ] Find out if there are any services or events tied to the facility to be archived that should be moved to a new facility or otherwise preserved and updated

<details><summary>Email template </summary>

```
FROM: vacms email
SUBJECT: <facility name> removed from VAST
CC: Jeffrey.Grandon@va.gov, Steve.Tokar2@va.gov, Jennifer.Heiland-Luedtke@va.gov, David.Conlon@va.gov
BODY:

Hi [VAMC editor who owns the node in CMS ]

We see that [name of facility] has been removed from VAST. If this facility has been permanently closed or moved, you can now work with us to unpublish the facility from the CMS and remove it from VA.gov.

Because some Veterans may have bookmarked this facility, external sites may have linked to it, and because it can take a little time for search engines to catch up to web content, we want prevent errors and bad web experiences for our Veterans.

   In order to do that we have some questions about the nature of this closure so that we can help redirect Veterans to the right place and understand this change.

1. Was this facility replaced with another facility?
   If yes, which one?
2. Is there a news release or story about this published on your VAMC website?
3. Anything else we should know about this facility closure?

If this facility has been removed from VAST in error, please notify our Support Desk as well as your VAST coordinator.

[outro]

[CMS helpdesk signature]
```


</details>

#### 2a. If facility has moved to a new system or merged
- [ ] Can any of the associated content (eg services, facility map, future events?) be reused? If so
  - [ ] is there a new facility in VAST/Facility API that content should be moved to?
- [ ] Create [redirect request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&labels=Redirect+request&template=redirect-request-facility-url.md&title=Redirect+Request+for%3A+%3Cinsert+facility+name%3E) to point to URL of new facility.
- [ ] When redirect is ready to go out, plan to make these changes immediately after redirect is released. Practice first on staging or a demo environment.
  - [ ] In certain, rare situations: CMS engineer bulk moves any content to new facility.
  - [ ] CMS engineer finds the menu for the system https://prod.cms.va.gov/admin/structure/menu and deletes the menu item for the merged facility.

#### 2b. If facility has NOT moved to a new system or merged
- [ ] Determine where should redirect go? to the system? or to the nearest clinic?
- [ ] Create [redirect request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&labels=Redirect+request&template=redirect-request-facility-url.md&title=Redirect+Request+for%3A+%3Cinsert+facility+name%3E) accordingly.
- [ ] When redirect is ready to go out, plan to make these changes immediately after redirect is released. Practice first on staging or a demo environment.
  - [ ] Are there any events tied to this facility that have yet to occur and if so should any of them be updated to a new location? If yes, update these events accordingly.
  - [ ] CMS engineer edits the facility node, removes flag `Removed from source`, add a revision log that explains the change, with a link to github issue, and change moderation state to archive. (Note: any related health services, non-clinical services and events for the given facility will be archived automatically when these changes are saved.)
  - [ ] CMS engineer finds the menu for the system https://prod.cms.va.gov/admin/structure/menu and deletes the menu item for the closed facility.
- [ ] Let HD know this is complete.

#### 3. CMS Help desk
- [ ] Notifies editor and any other stakeholders that this is complete.

### Vet Center – facility closure [this needs work]
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
