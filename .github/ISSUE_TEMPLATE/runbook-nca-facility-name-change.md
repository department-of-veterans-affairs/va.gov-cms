---
name: Runbook - NCA Facility name change
about: Steps for updating names and URLs
title: 'NCA Facility name change: <insert_name>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, NCA, User
  support, sitewide
assignees: ''

---

## Intake
- [ ] What triggered this runbook? (Flag in CMS via Lighthouse migration)

- [ ] Link to facility in production:
Facility CMS link: <insert_facility_link>
Facility API ID: <insert_facility_API_ID>

## Acceptance criteria

## NCA Facility name change

### Drupal Admin steps
- [ ] Edit the node and update the alias to match the new facility name, lowercase with dashes.
 Note: Low priority because these are not currently published on the FE. We should, however, not let these build up and overwhelm us later. High likelihood that we may want to automate this prior to first publish.
- [ ] Remove the `Changed name` flag, save the node with revision log
