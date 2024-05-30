---
name: Runbook - NCA Facility closed
about: Steps for archiving a NCA facility in VA.gov CMS.
title: 'NCA Facility closed: <insert_name>'
labels: Change request, Drupal engineering, Facilities, User support, Flagged Facilities, NCA
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
None:  Since NCA does not have a FE presence, the facilities get auto-archived by the CMS when they are removed from the Facilty API.  There are no flags created, and a revision log is added to the facility that indicates why it was archived.  When the NCA Facility product launches, the autoarchiving and this runbook will need to be updated.
