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
- [ ] 2. If the Vet Center published: CMS team submits [redirect request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&labels=Redirect+request&template=redirect-request-facility-url.md&title=Redirect+Request+for%3A+%3Cinsert+facility+name%3E), cc'ing Facilities team NOT NEEDED for Outstation

<insert_redirect_request_link>

- [ ] 3. If the Vet Center is not published or once the redirect request has gone live alert CMS engineers to continue steps below 

#### CMS engineer steps
- [ ] 4. CMS engineer renames the section for this Vet Center to match its new name (Section taxonomy change)
- [ ] 5. CMS engineer: If the new official name matches the pattern "<city> Vet Center", update the common name to match
- [ ] 6. CMS engineer visits [bulk operations](https://prod.cms.va.gov/admin/content/bulk) page and filter by section = vet center name
- [ ] 7. CMS engineer updates URLs for all content in that section by bulk operations
- [ ] 8. CMS engineer resaves all content in that section by bulk operations
- [ ] 9. CMS engineer edits the Vet Center node by removing flag `Changed name`, and saves the node (with moderation state = published)
  
In [Lighthouse Facilties](https://github.com/department-of-veterans-affairs/lighthouse-facilities)
- [ ] 10. CMS engineer updates the [CSV in Lighthouse](https://github.com/department-of-veterans-affairs/lighthouse-facilities/blob/master/facilities/src/main/resources/websites.csv) with the changed URL, creating a PR, tagging the Lighthouse team and linking to it in Slack with an @mention to a Lighthouse team member  NOT NEEDED for Outstation

#### CMS Help desk (wrap up)
- [ ] 11. Help desk notifies editor and any other stakeholders.

### Team
Please check the team(s) that will do this work.

- [ ] `CMS Team`
- [ ] `Public Websites`
- [x] `Facilities`
- [x] `User support`
