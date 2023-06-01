---
name: Runbook - NCA Facility closed
about: Steps for archiving a NCA facility in VA.gov CMS.
title: 'NCA Facility closed: <insert_name>'
labels: Change request, Drupal engineering, Facilities, User support, VA.gov frontend, NCA
assignees: ''

---

## Intake
- [ ] What triggered this runbook? (Flag in CMS triggered by Lighthouse migration)
Trigger: <insert_trigger>

- [ ] Link to associated help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] Link to facility in production:
Facility CMS link: <insert_facility_link>
Facility API ID: <insert_facility_API_ID>

## Acceptance criteria

### NCA facility closure

### Drupal Admin steps
- [ ] Move Node state to Archived
    * If this process gets automated, this runbook can be retired.
- [ ] Edit the facility node, remove the `Removed from source` flag, save the node with revision log
