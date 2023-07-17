---
name: Runbook - Vet Center, Outstation, Mobile Vet Center Facility closed
about: Steps for archiving a Vet Center facility in VA.gov CMS.
title: 'Vet Center Facility closed: <insert_name>'
labels: Change request, Drupal engineering, Facilities, User support, VA.gov frontend, Vet Center
assignees: ''

---

## Intake
- [ ] What triggered this runbook? (Flag in CMS, Help desk ticket, Product team, RCS Central Office)
Trigger: <insert_trigger>

- [ ] Link to associated help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] Contact the Vet Center editor and send them a link to the operating status KB article. If the facility has not closed yet and we can provide lead-time, the editor should change the Facility status to "Facility notice" and provide information in the Facility Status additional information about when the facility will permanently close.  
- [ ] When the facility has already closed - including if we were unable to give the aforementioned lead time- the editor should change the Facility status to Closed and provide information in the Facility Status additional information that the facility has permanently closed as of DATE.
   - Vet Center Mobile autoarchive when they are removed from the facilty API.  No action required.
   - Vet Center Outstations autoarchive when they are removed from the facilty API.  No action required.
- [ ] After 30 days of the facility being closed AND that the facility has been removed from the Lighthouse Facilities API, we can then archive the facility.
KB articles: <insert_kb_article_links>

- [ ] Link to facility in production:
Facility CMS link: <insert_facility_link>
Facility API ID: <insert_facility_API_ID>

## Acceptance criteria

### Vet Center -> Talk to Michelle

### Mobile Vet Center closure -> Autoarchive since they have no FE page of their own.

### Outstation -> Autoarchive since they have no FE page of their own.



#### CMS help desk steps
**Note: If the help desk is waiting on information from the facility staff or editor, add the "Awaiting editor" flag to the facility with a log message that includes a link to this ticket. Remove the flag when the ticket is ready to be worked by the Facilities team. Be sure to preserve the current moderation state of the node when adding or removing the flag.**
- [ ] 1. Become aware that the facility is no longer on the Facility API (typically, via a Flag, but this may come in as a helpdesk ticket).
- [ ] 2. ~~CMS team submits [Redirect request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&labels=Redirect+request&template=redirect-request-facility-url.md&title=Redirect+Request+for%3A+%3Cinsert+facility+name%3E), cc'ing Facilities team, and referencing this issue.~~ redirects are not necessary for Outstations.

#### Drupal Admin steps (CMS Engineer or Help desk)
_Help desk will complete these steps or escalate to request help from CMS engineering._
- [ ] 4. Drupal Admin bulk unpublishes the nodes.
- [ ] 5. Drupal Admin removes the Section.
- [ ] 6. Drupal Admin edits facility node by removing the flag `Removed from source`, sets moderation state to archived, and saves the node.

#### CMS Help desk (wrap up)
- [ ] 7. Help desk notifies editor and any other stakeholders.
