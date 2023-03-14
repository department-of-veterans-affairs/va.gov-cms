---
name: Runbook - VAMC facility section change
about: How to update the section of a VAMC.
title: 'VAMC Facility section name change: <insert_name_of_vamc>'
labels: Change request
assignees: ''

---

## Intake
- [ ] What triggered this runbook? (Flag in CMS, Help desk ticket, Product team, VHA Digital Media)
Trigger: <insert_trigger>

- [ ] Link to associated help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] Link to facility in production:
Facility link: <insert_facility_link>

## Steps before proceeding

- [ ] Check with Facilities team Product Owner to get approval of section change.
- [ ] Check with VHA Digital Media.

## VAMC facility section change (and duplicate VAST records)

A section change is typically represented by two facility records in VAST for a single physical location. In most cases the only difference between the two records are the Facility API ID values for each facility. 

Duplicate records can also show up in VAST as the result of a facility name change if VAST opted to create a new record with the new name (with a new Facility API ID) instead of simply updating the name field for the existing facility record. 

In either instance we have two VAST records representing the same physical location and we need to make changes in the CMS so VAST data for the NEW VAST entry properly maps to our original CMS facility. We do this to preserve revision log information and other history stored in the CMS.

#### CMS help desk steps
- [ ] 1. Submit a [redirect request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&labels=Redirect+request&template=redirect-request-facility-url.md&title=Redirect+Request+for%3A+%3Cinsert+facility+name%3E) from the original URL for the facility to the new URL for the facility.

<insert_redirect_request_link>

(These are usually released Wednesday afternoons so you should coordinate the remaining items below around that timeframe)

#### CMS engineer steps (the new facility)
- [ ] 2. Change the Facility Locator API ID on the new facility to the Facility Locator API ID for the old one.
- [ ] 3. Change the section on the new facility to the old one
- [ ] 4. Change the VAMC system on the new facility to the old one
- [ ] 5. Update the Menu link (Parent link) on the new facility to the old system (unable to delete it)
- [ ] 6. Check the box under URL alias to **Generate automatic URL alias**
- [ ] 7. Archive the facility with a good log message
- [ ] 8. Manually disable the menu link for this item directly in the system menu

#### CMS engineer steps (the original facility)
- [ ] 9. Change the Facility Locator API ID on the old facility to the Facility Locator API ID for new one
- [ ] 10. Change the section on the old facility to the new one
- [ ] 11. Change the VAMC system on the old facility to the new one
- [ ] 12. Update the Menu link (Parent link) on the old facility to the new system
- [ ] 13. Check the box under URL alias to **Generate automatic URL alias**
- [ ] 14. Publish changes with good log message
- [ ] 15. Manually move the menu link for this item into alphabetical order in locations section of the menu for its new system

#### CMS engineer steps (bulk edit)
Update related content (VAMC Facility Health Services, VAMC Non-clinical Services, Events, Stories, etc)
- [ ] 16. Bulk edit the VAMC Facility Health Services for the original Facility (update the section to the new section)
- [ ] 17. Manually edit each VAMC Facility Health Service (updating the VAMC system health service to use the new system)
- [ ] 18. Bulk edit the VAMC Facility Health Services to **Update URL alias**
- [ ] 19. Bulk edit the VAMC Facility Health services to **Resave content** (now the urls will be correct)

#### CMS engineer (create sript to be run against the production DB)

This script will need to be run against the production database by a member of the platform team and should accomplish the following goals:

- [ ] Remove the migrate_map entry for the new facility
- [ ] Update the migrate_map entry for the old facility, updating the facility locator api id to the new one (sql command in drush)

**Paste the script to be run here**

#### Example script
```
drush sql:query "DELETE FROM migrate_map_va_node_health_care_local_facility WHERE sourceid1 = 'vha_612GL'"

drush sql:query "UPDATE migrate_map_va_node_health_care_local_facility SET sourceid1 = 'vha_612GL' WHERE sourceid1 = 'vha_640GB'"
```

#### CMS engineer (update lighthouse)
In [Lighthouse Facilties](https://github.com/department-of-veterans-affairs/lighthouse-facilities)
- [ ] 20. CMS engineer updates the [CSV in Lighthouse](https://github.com/department-of-veterans-affairs/lighthouse-facilities/blob/master/facilities/src/main/resources/websites.csv) with the changed URL, creating a PR, tagging the Lighthouse team and linking to it in Slack with an @mention to a Lighthouse team member 

#### CMS Help desk (wrap up)
- [ ] Help desk notifies editor and any other stakeholders.

### Team
Please check the team(s) that will do this work.

- [ ] `CMS Team`
- [ ] `Public Websites`
- [x] `Facilities`
- [x] `User support`
