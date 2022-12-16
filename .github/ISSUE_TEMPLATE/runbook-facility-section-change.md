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

On the original facility node:
- [ ] Change the old  API id on the old facility, to the new one.
- [ ] Change the section to the new one.
- [ ] save draft with good log message

On the new facility node:
- [ ] Change the API id to the to the old one.
- [ ] Change the section to the new old one.
- [ ] save draft with good log message


- [ ] Edit all the facility service nodes at that facility to point to the new systems service nodes. (save as draft, with good log)
- [ ] Publish all.
- [ ] Archive the new facility node.
- [ ] *May need to bulk op to update aliases.*
  - These probably need a redirect created, but not certain.

In the production DB:
- [ ] Update the migrate_map entry to swap the api ids. (replace old with new) (sql command in drush)

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
