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
- [ ] 2. Create a [URL change request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E), changing the entry from the old facility URL to the new facility URL. (**Note: The URL change request ticket blocks the completion of this ticket.**)

<insert_url_change_request_link>

(Redirects are released Wednesday afternoons, so coordinate the following items below and canonical URL change around that timeframe.)

#### CMS engineer steps
- [ ] 3. Execute the steps of the URL change request ticket from step 2.
- [ ] 4. Locate the newly renamed VAMC Facility (https://prod.cms.va.gov/admin/content/bulk) Search by new name
- [ ] 5. Updates URL alias for this facility
- [ ] 6. Resave this facility
- [ ] 7. Make bulk alias changes to facility service nodes. (https://prod.cms.va.gov/admin/content/bulk?type=health_care_local_health_service)
- [ ] 8. Bulk save fixed titles to facility service nodes. (https://prod.cms.va.gov/admin/content/bulk?type=health_care_local_health_service)
- [ ] 9. Update menu title for facility
- [ ] 10. May also need to directly edit the VAMC System menu to alpha sort the menu item after the title changes
- [ ] 11. Update Alt text for facility image, if relevant
- [ ] 12. Update Meta description (TBD: some backwards compatibility for SEM, by including something like ", formerly known as [previous name]".
- [ ] 13. Edit facility node and remove flag `Changed name` then save node (with moderation state = published)

#### CMS Help desk (wrap up)
- [ ] 14. Notify editor and any other stakeholders

### Team
Please check the team(s) that will do this work.

- [ ] `CMS Team`
- [ ] `Public Websites`
- [x] `Facilities`
- [x] `User support`
