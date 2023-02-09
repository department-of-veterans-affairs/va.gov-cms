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

Update the new facility node:
- [ ] Change the Facility Locator API ID on the new facility, to the Facility Locator API ID for the old one.
- [ ] Change the section to the old one
- [ ] Change the VAMC system to the old one
- [ ] Update the Menu link (Parent link) to the old system (unable to delete it)
- [ ] Check the box under URL alias to **Generate automatic URL alias**
- [ ] Archive the facility with good log message
- [ ] Manually disable the menu link for this item directly in the system menu

Update the original facility node:
- [ ] Change the Facility Locator API ID on the old facility, to the Facility Locator API ID for new one
- [ ] Change the section to the new one
- [ ] Change the VAMC system to the new one
- [ ] Update the Menu link (Parent link) to the new system
- [ ] Check the box under URL alias to **Generate automatic URL alias**
- [ ] Publish changes with good log message
- [ ] Manually move the menu link for this item into alphabetical order in locations section of the menu for its new system

Update related content (VAMC Facility Health Services, VAMC Non-clinical Services, Events, Stories, etc)
- [ ] Bulk edit the VAMC Facility Health Services for the original Facility (update the section to the new section)
- [ ] Manually edit each VAMC Facility Health Service (updating the VAMC system health service to use the new system)
- [ ] Bulk edit the VAMC Facility Health Services to **Update URL alias**
- [ ] Bulk edit the VAMC Facility Health services to **Resave content** (now the urls will be correct)

In the production DB:
- [ ] Remove the migrate_map entry for the new facility
- [ ] Update the migrate_map entry for the old facility, updating the facility locator api id to the new one (sql command in drush)

**Paste the script to be run here**

In [Lighthouse Facilties](https://github.com/department-of-veterans-affairs/lighthouse-facilities)
- [ ] CMS engineer updates the [CSV in Lighthouse](https://github.com/department-of-veterans-affairs/lighthouse-facilities/blob/master/facilities/src/main/resources/websites.csv) with the changed URL, creating a PR, tagging the Lighthouse team and linking to it in Slack with an @mention to a Lighthouse team member 

- [ ] HD notifies editor and any other stakeholders.

### Team
Please check the team(s) that will do this work.

- [ ] `CMS Team`
- [ ] `Public Websites`
- [x] `Facilities`
- [x] `User support`
