---
name: Runbook - VAMC system name change
about: How to update the name of a VAMC.
title: 'VAMC system name change: <insert_name_of_vamc>'
labels: Change request
assignees: ''

---

## What triggered this runbook?
_eg Help desk ticket, Product team, VHA Digital Media_


## Steps before proceeding

- [ ] Check with Facilities team Product Owner to get approval of name change.
- [ ] Check with VHA Digital Media.

## Who are the stakeholders for this changes
_eg Editor at VAMC

## Steps

Timing around these is critical and we may need more detail here.

- [ ] CMS team submits [Redirect request](https://github.com/department-of-veterans-affairs/va.gov-team/issues/new?assignees=mnorthuis&labels=ia&template=redirect-request.md&title=Redirect+Request), cc'ing Facilities team
- [ ] Once timing of Redirect going live is known, alert CMS engineers to carry out the other steps
- [ ] CMS engineer updates the Section name
- [ ] CMS engineer bulk alias changes all nodes within the system. (https://prod.cms.va.gov/admin/content/bulk)
- [ ] CMS engineer bulk saves to fix titles for all nodes within system. (https://prod.cms.va.gov/admin/content/bulk?type=health_care_local_health_service)
- [ ] CMS engineer creates PR to rename the menu for the system accordingly.  (in the future, may need to rebuild the menu so that name and machine name match)
- [ ] CMS engineer updates the [CSV in Lighthouse](https://github.com/department-of-veterans-affairs/lighthouse-facilities/blob/master/facilities/src/main/resources/websites.csv) with the changed URL, creating a PR, tagging the Lighthouse team and linking to it in Slack with an @mention to a Lighthouse team member 
- [ ] HD notifies editor and any other stakeholders.



### Team
Please check the team(s) that will do this work.

- [ ] `CMS Team`
- [ ] `Public Websites`
- [x] `Facilities`
- [x] `User support`
