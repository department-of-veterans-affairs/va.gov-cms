---
name: Runbook - VAMC Facility name change
about: Steps for updating names and URLs
title: 'VAMC Facility name change: <insert_name>'
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

- [ ] If the submitter is an editor, send them links to any relevate KB articles for the VAMC product. Let them know that facility changes can take between 75 days and 4 months after submitting a request, according to VAST administrators.
KB articles: <insert_kb_article_links>

- [ ] Link to facility in production:
Facility link: <insert_facility_link>

## Acceptance criteria

## VAMC Facility name change

#### CMS help desk steps
- [ ] 1. The title change comes from Lighthouse to Drupal.
- [ ] 2. Coordinate with Facilities team to create a [redirect request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&labels=Redirect+request&template=redirect-request-facility-url.md&title=Redirect+Request+for%3A+%3Cinsert+facility+name%3E) from the original URL for the facility to the new URL for the facility.

<insert_redirect_request_link>

#### CMS engineer steps
- [ ] 3. CMS engineer locates the newly renamed VAMC Facility (https://prod.cms.va.gov/admin/content/bulk) Search by new name
- [ ] 4. CMS engineer updates URL alias for this facility
- [ ] 5. CMS engineer resaves this facility
- [ ] 6. CMS engineer makes bulk alias changes to facility service nodes. (https://prod.cms.va.gov/admin/content/bulk?type=health_care_local_health_service)
- [ ] 7. CMS engineer bulk saves fixed titles to facility service nodes. (https://prod.cms.va.gov/admin/content/bulk?type=health_care_local_health_service)
- [ ] 8. CMS engineer updates menu title for facility
- [ ] 9. CMS engineer may also need to directly edit the VAMC System menu to alpha sort the menu item after the title changes
- [ ] 10. CMS engineer updates Alt text for facility image, if relevant
- [ ] 11. CMS engineer updates Meta description (TBD: some backwards compatibility for SEM, by including something like ", formerly known as [previous name]".
- [ ] 12. CMS engineer edit facility node and remove flag `Changed name` then save node (with moderation state = published)

In [Lighthouse Facilties](https://github.com/department-of-veterans-affairs/lighthouse-facilities)
- [ ] 13. CMS engineer updates the [CSV in Lighthouse](https://github.com/department-of-veterans-affairs/lighthouse-facilities/blob/master/facilities/src/main/resources/websites.csv) with the changed URL, creating a PR, tagging the Lighthouse team and linking to it in Slack with an @mention to a Lighthouse team member 

#### CMS Help desk (wrap up)
- [ ] Help desk notifies editor and any other stakeholders

### Team
Please check the team(s) that will do this work.

- [ ] `CMS Team`
- [ ] `Public Websites`
- [x] `Facilities`
- [x] `User support`
