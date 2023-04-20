---
name: Runbook - Vet Center Facility closed
about: Steps for archiving a Vet Center facility in VA.gov CMS.
title: 'Vet Center Facility closed: <insert_name>'
labels: Change request, Vet Center, Facilities, User support, VA.gov frontend, Drupal engineering
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
Facility CMS link: <insert_facility_link>
Facility API ID: <insert_facility_API_ID>

## Acceptance criteria

### Vet Center facility closure

#### CMS help desk steps
- [ ] 1. Become aware that the facility is no longer on the Facility API (typically, via a Flag, but this may come in as a helpdesk ticket).
- [ ] 2. CMS team submits [Redirect request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&labels=Redirect+request&template=redirect-request-facility-url.md&title=Redirect+Request+for%3A+%3Cinsert+facility+name%3E), cc'ing Facilities team, and referencing this issue.

<insert_redirect_request_link>

#### CMS engineer steps
- [ ] 3. Execute the steps of the URL change request ticket from step 2 above.

(Redirects deploy weekly on Wed. at 10am ET, or by requesting OOB deploy (of the revproxy job to prod) in #vfs-platform-support. Coordinate the items below and canonical URL change after URL change ticket is merged, deployed, and verified in prod.)

#### Drupal Admin steps (CMS Engineer or Help desk)
_Help desk will complete these steps or escalate to request help from CMS engineering._
- [ ] 4. Drupal Admin bulk unpublishes the nodes.
- [ ] 5. Drupal Admin removes the Section.
- [ ] 6. Drupal Admin edits facility node by removing the flag `Removed from source`, sets moderation state to archived, and saves the node.

#### CMS Help desk (wrap up)
- [ ] 7. Help desk notifies editor and any other stakeholders.
