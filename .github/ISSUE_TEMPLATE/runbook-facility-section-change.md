---
name: Runbook - VAMC facility section change
about: How to update the section of a VAMC.
title: 'VAMC Facility section name change: <insert_name_of_vamc>'
labels: Change request
assignees: ''

---

# DRAFT/PLACEHOLDER

## What triggered this runbook?
_eg Help desk ticket, Product team, VHA Digital Media_


## Steps before proceeding

- [ ] Check with Facilities team Product Owner to get approval of section change.
- [ ] Check with VHA Digital Media.

## Who are the stakeholders for this changes
_eg Editor at VAMC

## Steps (Rough)

Timing around these is critical and we may need more detail here.

Redirect request:
- [ ] Submit a [redirect request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&labels=Redirect+request&template=redirect-request-facility-url.md&title=Redirect+Request+for%3A+%3Cinsert+facility+name%3E) to the devops repo from the original URL for the Facility to the newly updated URL once it changes systems

(These are usually released Wednesday afternoons so you should coordinate the remaining items below around that timeframe)

Update the original facility node:
- [ ] Change the Facility Locator API ID on the old facility, to the Facility Locator API ID for new one
- [ ] Change the section to the new one
- [ ] Change the VAMC system to the new one
- [ ] Update the Menu link (Parent link) to the new system
- [ ] Save new revision with good log message

Update the new facility node:
- [ ] Change the Facility Locator API ID on the new facility, to the Facility Locator API ID for the old one.
- [ ] Change the section to the old one
- [ ] Change the VAMC system to the old one
- [ ] Unset the Menu link
- [ ] Save new revision with good log message

Update related content (VAMC Facility Health Services, VAMC Non-clinical Services, Events, Stories, etc)
- [ ] Bulk edit the VAMC Facility Health Services for the original Facility (update the section to the new section)
- [ ] Manually edit each VAMC Facility Health Service (updating the VAMC system health service to use the new system)
- [ ] Be sure the urls for each update to reflect the change in the URL for the referenced facility
- [ ] Publish all
- [ ] Archive the new facility node.

In the production DB:
- [ ] Update the migrate_map entry to swap the api ids. (replace old with new) (sql command in drush)

In [Lighthouse Facilties](https://github.com/department-of-veterans-affairs/lighthouse-facilities)
- [ ] CMS engineer updates the [CSV in Lighthouse](https://github.com/department-of-veterans-affairs/lighthouse-facilities/blob/master/facilities/src/main/resources/websites.csv) with the changed URL, creating a PR, tagging the Lighthouse team and linking to it in Slack with an @mention to a Lighthouse team member 

- [ ] HD notifies editor and any other stakeholders.

## CMS Team
Please check the team(s) that will do this work.

- [ ] `Program`
- [ ] `Platform CMS Team`
- [ ] `Sitewide Crew`
- [ ] `⭐️ Sitewide CMS`
- [ ] `⭐️ Public Websites`
- [x] `⭐️ Facilities`
- [x] `⭐️ User support`
