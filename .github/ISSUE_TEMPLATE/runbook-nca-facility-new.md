---
name: Runbook - New NCA Facility
about: changing facility information in the CMS for NCA facilities
title: 'New NCA Facility: <insert_name_of_facility>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  VBA, sitewide
assignees: ''

---

## Intake
- [ ] What triggered this runbook? (Flag in CMS, Help desk ticket, Product team, VHA Digital Media)
Trigger: <insert_trigger>

- [ ] Link to associated help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] If the submitter is an editor, send them links to any relevate KB articles for the NCA Facility product.
KB articles: <insert_kb_article_links>

- [ ] Link to facility in production:
Facility CMS link: <insert_facility_link>
Facility API ID: <insert_facility_API_ID>

## Acceptance criteria

### New NCA Facility
**Note: If the help desk is waiting on information from the facility staff or editor, add the "Awaiting editor" flag to the facility with a log message that includes a link to this ticket. Remove the flag when the ticket is ready to be worked by the Facilities team. Be sure to preserve the current moderation state of the node when adding or removing the flag.**
[@TODO: DRAFT FOR HELP DESK AND DEV STEPS]

#### Help desk steps
- [ ] Notify an editor to set an operating status so the facility will get pushed to the Facility API
- [ ] Verify when the status is added            

#### Drupal Admin steps
- [ ] Edit the facility node: remove the `New facility` flag, save the node
