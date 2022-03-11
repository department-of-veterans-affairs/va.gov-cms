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
- [ ] Can any of the associated content  (eg services, facility map?) be reused? If so
  - [ ] is there a new facility in VAST/Facility API that content should be moved to?
- [ ] Create [redirect request](https://github.com/department-of-veterans-affairs/va.gov-team/issues/new?assignees=mnorthuis&labels=ia&template=redirect-request.md&title=Redirect+Request) to point to URL of new facility.
- [ ] When redirect is ready to go out, plan to make these changes immediately after redirect is released. Practice first on staging or a demo environment. 
  - [ ] In certain, rare situations: CMS engineer bulk moves any content to new facility.
  - [ ] CMS engineer finds the menu for the system https://prod.cms.va.gov/admin/structure/menu and deletes the menu item for the merged facility.

#### 2b. If not 
- [ ] Determine where should redirect go? to the system? or to the nearest clinic?
- [ ] Create [redirect request](https://github.com/department-of-veterans-affairs/va.gov-team/issues/new?assignees=mnorthuis&labels=ia&template=redirect-request.md&title=Redirect+Request) accordingly. 
- [ ] When redirect is ready to go out, plan to make these changes immediately after redirect is released. Practice first on staging or a demo environment. 
  - [ ] CMS engineer bulk archives the facility service nodes. (https://prod.cms.va.gov/admin/content/bulk?type=health_care_local_health_service).
  - [ ] CMS engineer bulk archives the facility non-clinical service nodes. (https://prod.cms.va.gov/admin/content/bulk?type=vha_facility_nonclinical_service).
  - [ ] CMS engineer finds the menu for the system https://prod.cms.va.gov/admin/structure/menu and deletes the menu item for the closed facility.
  - [ ] CMS engineer filters content by the health care system and scans for any events that might be taking place at that facility. Archive if any are found.
  - [ ] CMS engineer edits the facility node, removes  flag `Removed from source`, add a revision log that explains the change, with a link to github issue, and change moderation state to archive.
- [ ] Let HD know this is complete. 

#### 3. CMS Help desk
- [ ] Notifies editor and any other stakeholders that this is complete. 

### Vet Center – facility closure [this needs work]
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
