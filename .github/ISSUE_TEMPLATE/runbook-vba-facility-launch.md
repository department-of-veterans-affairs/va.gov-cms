---
name: Runbook - Launching an approved VBA Facility
about: Initial publishing of a VBA Facility
title: 'Launching VBA Facility: <insert_name_of_facility>'
labels: Drupal engineering, Facilities, User support, VBA
assignees: ''

---

## Intake
- [ ] What triggered this runbook? (Slack notification, Helpdesk ticket, etc)
Trigger: <insert_trigger>

- [ ] Link to facility in production:
Facility CMS link: <insert_facility_link>

## Helpdesk Tasks

### Prerequisites to publish
- [ ] Verify with [who / how?] that VBA leadership completed content review and approved publishing
- [ ] Verify that all VBA Facility Health Services for this Facility have been set to Published.

### Publishing
- [ ] Set the VBA Facility to Published, and note approvals in the revision log
- [ ] Create a [URL change ticket](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&labels=Facilities%2C+Drupal+engineering%2C+Flagged+Facilities%2C+Redirect+request%2C+URL+Change%2C+User+support&projects=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E) for the URL update from facility locator detail page URL (using API ID) to the modernized URL. If you're unsure about the original URL, request help from Facilities team in #facilities-support.

## Facilities engineer tasks
- [ ] After Content release has run and completed successfully, send a request to Lighthouse in #cms-lighthouse, to update the canonical URL from the API ID facility locator detail page, to the published / modernized page. **This may take several days for LH to complete.**
- [ ] Create a redirect in vsp-platform-revproxy to point traffic from the facility locator detail page to the modernized page path
- [ ] Notify VBA leadership, with CMS Helpdesk in cc, of completed launch, including: link to live Regional Office page(s), [VBA KB landing page](https://prod.cms.va.gov/help/veterans-benefits-administration-vba), and estimated timing from LH for the CSV updates
