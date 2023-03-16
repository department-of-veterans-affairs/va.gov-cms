---
name: Runbook - Vet Center Facility closed
about: Steps for archiving a Vet Center facility in VA.gov CMS.
title: 'Vet Center Facility closed: <insert_name>'
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

- [ ] If the submitter is an editor, send them a link to the operating status KB article and have them change the status to Facility notice and provide a description of the facility closure so that Veterans are aware of the future closure.
KB articles: <insert_kb_article_links>

- [ ] Link to facility in production:
Facility link: <insert_facility_link>

## Acceptance criteria

### Vet Center facility closure

#### CMS help desk steps
- [ ] 1. CMS team becomes aware that the facility is no longer on the Facility API.
- [ ] 2. CMS team submits [Redirect request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&labels=Redirect+request&template=redirect-request-facility-url.md&title=Redirect+Request+for%3A+%3Cinsert+facility+name%3E), cc'ing Facilities team, and referencing this issue.

<insert_redirect_request_link>

- [ ] 3. Once timing of Redirect going live is known, alert CMS engineers to carry out the other steps

#### CMS engineer steps
- [ ] 4. CMS engineer bulk unpublishes the nodes.
- [ ] 5. CMS engineer removes the Section.
- [ ] 6. CMS engineer edits facility node by removing the flag `Removed from source`, sets moderation state to archived, and saves the node.

#### CMS Help desk (wrap up)
- [ ] 7. Help desk notifies editor and any other stakeholders.

### Team
Please check the team(s) that will do this work.

- [ ] `CMS Team`
- [ ] `Public Websites`
- [x] `Facilities`
- [x] `User support`
