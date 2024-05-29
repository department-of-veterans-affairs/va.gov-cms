---
name: Runbook - VBA Facility closed
about: Steps for archiving a VBA facility in VA.gov CMS.
title: 'VBA Facility closed: <insert_name>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support, VBA
assignees: ''

---

## Intake
- [ ] What triggered this runbook? (Flag in CMS via Lighthouse migration)
Trigger: <insert_trigger>

- [ ] Link to facility in production:
Facility CMS link: <insert_facility_link>
Facility API ID: <insert_facility_API_ID>

## Acceptance criteria


### Drupal Admin steps
- [ ] Archive the facility node, remove the `Removed from source` flag, save the node with revision log

IF the facility is a Regional Office then
- [ ] Contact Michelle/VBA Strategic Engagement to determine what happens to Satellite facilities.
- [ ] Reassign or archive Satellite facilities as needed (is this its own runbook?)
- [ ] Once we determine what happens with Satellite facilities and not before then go to [Sections taxonomy]( https://prod.cms.va.gov/admin/structure/taxonomy/manage/administration/overview), VBA > Delete the term that matches the Facility name
