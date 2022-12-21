---
name: Runbook - Facility name change
about: Steps for updating names and URLs
title: 'Facility name change: <insert_name>'
labels: Change request
assignees: ''

---

## Intake
- [ ] Submitter: <insert_name>
- [ ] If the submitter is an editor, send them the link to the CMS Knowledge Base (KB) article on facility basic data for their product (VAMC or Vet Center). Let them know that facility changes can take between 75 days and 4 months after submitting a request, according to VAST administrators.
- [ ] If the change is a facility closure, send the editor a link to the operating status KB article and have them change the status to Facility notice and provide a description of the facility closure so that Veterans are aware of the future closure.
- [ ] Other stakeholders to include on updates, if any: <insert name>

## Acceptance criteria

- [ ] The title change comes from Lighthouse to Drupal.
- [ ] Coordinate with Facilities team to create a [redirect request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&labels=Redirect+request&template=redirect-request-facility-url.md&title=Redirect+Request+for%3A+%3Cinsert+facility+name%3E)
- [ ] CMS engineer locates the newly renamed VAMC Facility (https://prod.cms.va.gov/admin/content/bulk) Search by new name
- [ ] CMS engineer updates URL alias for this facility
- [ ] CMS engineer resaves this facility
- [ ] CMS engineer makes bulk alias changes to facility service nodes. (https://prod.cms.va.gov/admin/content/bulk?type=health_care_local_health_service)
- [ ] CMS engineer bulk saves fixed titles to facility service nodes. (https://prod.cms.va.gov/admin/content/bulk?type=health_care_local_health_service)
- [ ] CMS engineer updates menu title for facility
- [ ] CMS engineer may also need to directly edit the VAMC System menu to alpha sort the menu item after the title changes
- [ ] CMS engineer updates Alt text for facility image, if relevant
- [ ] CMS engineer updates Meta description (TBD: some backwards compatibility for SEM, by including something like ", formerly known as [previous name]".
- [ ] CMS engineer edit facility node and remove flag `Changed name` then save node
- [ ] HD notifies editor and any other stakeholders
</details>

## Vet Center – facility name change

- [ ] The title change comes from Lighthouse to Drupal
- [ ] If the Vet Center published: CMS team submits [redirect request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&labels=Redirect+request&template=redirect-request-facility-url.md&title=Redirect+Request+for%3A+%3Cinsert+facility+name%3E), cc'ing Facilities team
- [ ] If the Vet Center is not published or once the redirect request has gone live alert CMS engineers to continue steps below
- [ ] CMS engineer renames the section for this Vet Center to match its new name (Section taxonomy change)
- [ ] CMS engineer: If the new official name matches the pattern "<city> Vet Center", update the common name to match
- [ ] CMS engineer visits bulk operations page and filter by section = vet center name
- [ ] CMS engineer updates URLs for all content in that section by bulk operations
- [ ] CMS engineer resaves all content in that section by bulk operations
- [ ] CMS engineer edits Vet Center node and removes flag `Changed name` then saves node
  
In [Lighthouse Facilties](https://github.com/department-of-veterans-affairs/lighthouse-facilities)
- [ ] CMS engineer updates the [CSV in Lighthouse](https://github.com/department-of-veterans-affairs/lighthouse-facilities/blob/master/facilities/src/main/resources/websites.csv) with the changed URL, creating a PR, tagging the Lighthouse team and linking to it in Slack with an @mention to a Lighthouse team member 
- [ ] HD notifies editor and any other stakeholders.
</details>

## CMS Team
Please check the team(s) that will do this work.

- [ ] `Program`
- [ ] `Platform CMS Team`
- [ ] `Sitewide Crew`
- [ ] `⭐️ Sitewide CMS`
- [ ] `⭐️ Public Websites`
- [x] `⭐️ Facilities`
- [x] `⭐️ User support`
