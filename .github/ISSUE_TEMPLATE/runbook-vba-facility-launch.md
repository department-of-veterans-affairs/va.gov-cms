---
name: Runbook - Launching an approved VBA Facility
about: Initial publishing of a VBA Facility
title: 'Launching VBA Facility: <insert_name_of_facility>'
labels: Drupal engineering, Facilities, sitewide, User support, VBA
assignees: ''

---

## Intake
- [ ] What triggered this runbook? (Slack notification, Helpdesk ticket, etc)
Trigger: <insert_trigger>

- [ ] Link to facility in production:
Facility CMS link: <insert_facility_link>

## Helpdesk Tasks

### Prerequisites to publish
- [ ] Verify with Michelle Middaugh that VBA leadership completed content review and approved publishing. When Michelle has acknowledged the request, JIRA may be closed.
- [ ] Verify that all VBA Facility Services for this Facility have been set to Published.
- [ ] Verify that no major changes have been made directly in Drupal to data from migrations, such as Facility name, location, or contact information.
  - [ ] If any changes of that kind have been made in Drupal, provide the Editor with a link to [VBA location and contact information: How to Edit](https://prod.cms.va.gov/help/veterans-benefits-administration-vba/location-and-contact-information#how-to-edit) for instructions on how to properly make those changes.

### Publishing
- [ ] Set the VBA Facility to Published, and note approvals in the revision log
- [ ] Ask Michelle Middaugh to update the facility URL in Sandy's Database to the modernized URL. This eliminates the need for a URL change request ([LH confirmed](https://dsva.slack.com/archives/C02BTJTDFTN/p1730395863667449?thread_ts=1730395217.135779&cid=C02BTJTDFTN)).
- [ ] Create a [redirect request](https://github.com/department-of-veterans-affairs/va.gov-team/issues/new?assignees=kristinoletmuskat%2C+strelich%2C+Agile6MSkinner&labels=sitewide+CAIA%2C+Sitewide+IA%2C+Facilities%2C+Regional+Office%2C+sitewide%2C+VA.gov+frontend%2C+Redirect+request&projects=&template=redirect-request.md&title=Redirect+Request) to redirect the previous TeamSite page to the modernized page.
  - [ ] **Indicate in ticket** that this is a [page level redirect for a Teamsite using the injected header](https://github.com/department-of-veterans-affairs/va.gov-team/blob/master/platform/engineering/redirect-implementation-strategy.md#3-subdomain--vagov-page-level-cross-domain-redirect-for-a-subdomain-that-loads-proxy-rewrite-js) -- it can be done within proxy rewrite as a client-side redirect

## Facilities PM / DM tasks
- [ ] Verify that TeamSite redirect is complete
- [ ] Verify that Facility Locator search results return the modernized URL for the facility
- [ ] Notify VBA leadership, with CMS Helpdesk in cc, of completed launch, including: link to live Regional Office page(s), [VBA KB landing page](https://prod.cms.va.gov/help/veterans-benefits-administration-vba)
