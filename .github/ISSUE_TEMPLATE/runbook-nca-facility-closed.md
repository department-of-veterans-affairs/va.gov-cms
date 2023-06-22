---
name: Runbook - NCA Facility closed
about: Steps for archiving a NCA facility in VA.gov CMS.
title: 'NCA Facility closed: <insert_name>'
labels: Change request, Drupal engineering, Facilities, User support, VA.gov frontend, NCA
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

### NCA facility closure
**Note: If the help desk is waiting on information from the facility staff or editor, add the "Awaiting editor" flag to the facility with a log message that includes a link to this ticket. Remove the flag when the ticket is ready to be worked by the Facilities team. Be sure to preserve the current moderation state of the node when adding or removing the flag.**
[@TODO: DRAFT FOR HELP DESK AND DEV STEPS]

### Drupal Admin steps
None:  Since NCA does not have a FE presence, the facilities get auto-archived by the CMS when they are removed from the Facilty API.  There are no flags created, and a revision log is added to the facility that indicates why it was archived.  When the NCA Facility product launches, the autoarchiving and this runbook will need to be updated.
