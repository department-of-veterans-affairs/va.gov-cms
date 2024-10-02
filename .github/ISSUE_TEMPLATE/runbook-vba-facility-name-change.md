---
name: Runbook - VBA Facility name change
about: Steps for updating names and URLs
title: 'VBA Facility name change: <insert_name>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  VBA
assignees: ''

---

## Intake
- [ ] What triggered this runbook? (Flag in CMS via Lighthouse migration)
Trigger: <insert_trigger>

  - [ ] If name change was requested by an Editor and is not the result of a CMS Flag, provide the Editor with a link to [VBA location and contact information: How to Edit](https://prod.cms.va.gov/help/veterans-benefits-administration-vba/location-and-contact-information) for instructions on how to properly get Facility name change recorded, so it will flow through Lighthouse to CMS flag.
     
- [ ] Link to facility in production:
Facility CMS link: <insert_facility_link>
Facility API ID: <insert_facility_API_ID>

## Acceptance criteria

## VBA Facility name change


### Drupal Admin steps
- [ ] Edit the node and update the alias to match the new facility name, lowercase with dashes.
- [ ] Edit the facility node, remove the `Changed name` flag, save the node with revision log

If this facility is a Regional Office
- [ ] Go to [Sections taxonomy]( https://prod.cms.va.gov/admin/structure/taxonomy/manage/administration/overview), VBA > Rename the term that matches the old Facility name to use the new Facility name
    * If this process gets automated, this runbook can be retired.
