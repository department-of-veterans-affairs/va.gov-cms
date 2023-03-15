---
name: Runbook - VAMC system name change
about: How to update the name of a VAMC.
title: 'VAMC system name change: <insert_name_of_vamc>'
labels: Change request
assignees: ''

---

## Intake
- [ ] What triggered this runbook? (Flag in CMS, Help desk ticket, Product team, VHA Digital Media)
Trigger: <insert_trigger>

- [ ] Link to associated help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] Link to system in production:
System link: <insert_facility_link>

## Steps before proceeding

- [ ] Check with Facilities team Product Owner to get approval of name change.
- [ ] Check with VHA Digital Media.

## VAMC system name change

Timing around these is critical and we may need more detail here.

#### CMS help desk steps
- [ ] 1. CMS team submits [Redirect request](https://github.com/department-of-veterans-affairs/va.gov-team/issues/new?assignees=mnorthuis&labels=ia&template=redirect-request.md&title=Redirect+Request) from old system URL to new system URL.
- [ ] 2. Once timing of Redirect going live is known, alert CMS engineers to carry out the other steps

<insert_redirect_request_link>

#### CMS engineer steps
- [ ] 3. CMS engineer updates the Section name
- [ ] 4. CMS engineer bulk alias changes all nodes within the system. (https://prod.cms.va.gov/admin/content/bulk)
- [ ] 5. CMS engineer bulk saves to fix titles for all nodes within system. (https://prod.cms.va.gov/admin/content/bulk?type=health_care_local_health_service)
- [ ] 6. CMS engineer creates a PR to rename the menu for the system accordingly.  (In the future, they may need to rebuild the menu so that name and machine name match.)
- [ ] 7. CMS engineer updates the [CSV in Lighthouse](https://github.com/department-of-veterans-affairs/lighthouse-facilities/blob/master/facilities/src/main/resources/websites.csv) with the changed URL, creating a PR, tagging the Lighthouse team and linking to it in Slack with an @mention to a Lighthouse team member

#### CMS Help desk (wrap up)
- [ ] Help desk notifies editor and any other stakeholders.

### Team
Please check the team(s) that will do this work.

- [ ] `CMS Team`
- [ ] `Public Websites`
- [x] `Facilities`
- [x] `User support`
