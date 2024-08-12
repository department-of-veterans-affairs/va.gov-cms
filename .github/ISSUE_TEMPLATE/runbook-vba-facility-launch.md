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
- [ ] Notify Facilities team in #facilities-support that Regional Office is ready for redirect

### Facilities engineer  
- [ ] After Content release has run and completed successfully, send a request to Lighthouse in #cms-lighthouse, to update the canonical URL from the API ID facility locator detail page, to the published / modernized page. **This may take several days for LH to complete.**
- [ ] Create a redirect in vsp-platform-revproxy to point traffic from the facility locator detail page to the modernized page path
- [ ] Notify VBA leadership, with CMS Helpdesk in cc, of completed launch, including: link to live Regional Office page(s), [VBA KB landing page](https://prod.cms.va.gov/help/veterans-benefits-administration-vba), and estimated timing from LH for the CSV updates
