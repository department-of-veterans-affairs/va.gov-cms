---
name: Runbook - Vet Center name change
about: Steps for updating names and URLs
title: 'Vet Center name change: <insert_name>'
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

- [ ] If the submitter is an editor, send them links to any relevate KB articles for the Vet Center product. Let them know that facility changes can take between 75 days and 4 months after submitting a request, according to VAST administrators.
KB articles: <insert_kb_article_links>

- [ ] Link to facility in production:
Facility link: <insert_facility_link>

## Acceptance criteria

## Vet Center facility name change

#### CMS help desk steps
- [ ] 1. The title (Name of Vet Center field) change comes from Lighthouse to Drupal
- [ ] 2. If the Vet Center published and NOT an Outstation, create a [URL change request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E), changing the entry from the old facility URL to the new facility URL. (**Note: The URL change request ticket blocks the completion of this ticket.**)

<insert_redirect_request_link>

(Redirects are released Wednesday afternoons, so coordinate the following items below and canonical URL change around that timeframe.)

#### CMS engineer steps
- [ ] 3. Execute the steps of the URL change request ticket from step 2.
- [ ] 4. Rename the section for this Vet Center to match its new name (Section taxonomy change)
- [ ] 5. If the new official name matches the pattern "<city> Vet Center", update the common name to match
- [ ] 6. Visit [bulk operations](https://prod.cms.va.gov/admin/content/bulk) page and filter by section = vet center name
- [ ] 7. Update URLs for all content in that section by bulk operations
- [ ] 8. Resave all content in that section by bulk operations
- [ ] 9. Edit the Vet Center node by removing flag `Changed name`, and saves the node (with moderation state = published)

#### CMS Help desk (wrap up)
- [ ] 10. Notify editor and any other stakeholders.

### Team
Please check the team(s) that will do this work.

- [ ] `CMS Team`
- [ ] `Public Websites`
- [x] `Facilities`
- [x] `User support`
