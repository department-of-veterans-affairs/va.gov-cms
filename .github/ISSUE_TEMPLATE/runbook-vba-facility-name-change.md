---
name: Runbook - VBA Facility name change
about: Steps for updating names and URLs
title: 'VBA Facility name change: <insert_name>'
labels: Change request, Drupal engineering, Facilities, User support, VA.gov frontend, VBA
assignees: ''

---

## Intake
- [ ] What triggered this runbook? (Flag in CMS, Help desk ticket, Product team, VHA Digital Media)
Trigger: <insert_trigger>

- [ ] Link to associated help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] If the submitter is an editor, send them links to any relevate KB articles for the VBA product. Let them know that facility changes can take between 75 days and 4 months after submitting a request, according to VAST administrators.
KB articles: <insert_kb_article_links>

- [ ] Link to facility in production:
Facility CMS link: <insert_facility_link>
Facility API ID: <insert_facility_API_ID>

## Acceptance criteria

## VBA Facility name change
**Note: If the help desk is waiting on information from the facility staff or editor, add the "Awaiting editor" flag to the facility with a log message that includes a link to this ticket. Remove the flag when the ticket is ready to be worked by the Facilities team. Be sure to preserve the current moderation state of the node when adding or removing the flag.**
[@TODO: DRAFT FOR HELP DESK AND DEV STEPS]

### Drupal Admin steps
- [ ] Go to [Sections taxonomy]( https://prod.cms.va.gov/admin/structure/taxonomy/manage/administration/overview), VBA > Rename the term that matches the old Facility name to use the new Facility name
    * If this process gets automated, this runbook can be retired.
- [ ] Edit the facility node, remove the `Changed name` flag, save the node with revision log
