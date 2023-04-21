---
name: Runbook - VAMC facility duplicate record or section change
about: How to update the section of a VAMC.
title: 'VAMC Facility duplicate record or section change: <insert_name_of_vamc>'
labels: Change request, VAMC, Facilities, User support, VA.gov frontend, Drupal engineering
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
Facility CMS link: <insert_facility_link>
Facility API ID: <insert_facility_API_ID>

## Steps before proceeding

- [ ] Check with Facilities team Product Owner to get approval of section change.
- [ ] Check with VHA Digital Media.

## VAMC facility section change (and duplicate VAST records)

A section change is typically represented by two facility records in VAST for a single physical location. In most cases the only difference between the two records are the Facility API ID values for each facility.

Duplicate records can also show up in VAST as the result of a facility name change if VAST opted to create a new record with the new name (with a new Facility API ID) instead of simply updating the name field for the existing facility record.

In either instance we have two VAST records representing the same physical location and we need to make changes in the CMS so VAST data for the NEW VAST entry properly maps to our original CMS facility. We do this to preserve revision log information and other history stored in the CMS.

#### CMS help desk steps
If there has been a section change, which results in a change to the facility URL:
- [ ] 1. Create a [URL change request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E), changing the entry from the old facility URL to the new facility URL. (**Note: The URL change request ticket blocks the completion of this ticket.**)

<insert_url_change_request_link>


(Redirects deploy weekly on Wed. at 10am ET, or by requesting OOB deploy (of the revproxy job to prod) in #vfs-platform-support. Coordinate the items below and canonical URL change after URL change ticket is merged, deployed, and verified in prod.)


#### CMS engineer steps (the new facility)
- [ ] 2. Execute the steps of the URL change request ticket from step 1 above.
- [ ] 3. Change the Facility Locator API ID on the new facility to the Facility Locator API ID for the old one.
- [ ] 4. Change the section on the new facility to the old one
- [ ] 5. Change the VAMC system on the new facility to the old one
- [ ] 6. Update the Menu link (Parent link) on the new facility to the old system (unable to delete it)
- [ ] 7. Check the box under URL alias to **Generate automatic URL alias**
- [ ] 8. Archive the facility with a good log message
- [ ] 9. Manually disable the menu link for this item directly in the system menu

#### CMS engineer steps (the original facility)
- [ ] 10. Change the Facility Locator API ID on the old facility to the Facility Locator API ID for new one
- [ ] 11. Change the section on the old facility to the new one
- [ ] 12. Change the VAMC system on the old facility to the new one
- [ ] 13. Update the Menu link (Parent link) on the old facility to the new system
- [ ] 14. Check the box under URL alias to **Generate automatic URL alias**
- [ ] 15. Publish changes with good log message
- [ ] 16. Manually move the menu link for this item into alphabetical order in locations section of the menu for its new system

#### CMS engineer steps (bulk edit)
Update related content (VAMC Facility Health Services, VAMC Non-clinical Services, Events, Stories, etc)
- [ ] 17. Bulk edit the VAMC Facility Health Services for the original Facility (update the section to the new section)
- [ ] 18. Manually edit each VAMC Facility Health Service (updating the VAMC system health service to use the new system)
- [ ] 19. Bulk edit the VAMC Facility Health Services to **Update URL alias**
- [ ] 20. Bulk edit the VAMC Facility Health services to **Resave content** (now the urls will be correct)

#### CMS engineer (create script to be run against the production DB)

- [ ] 21. Update this script to be tested on stage and then run against the production database by a member of the platform team to accomplish the following goals:

- [ ] Remove the migrate_map entry for the old facility
- [ ] Update the migrate_map entry for the new facility, updating the destid1 from the new nid to the old nid (sql command in drush)

**Paste the script to be run here**

#### Example script
```
// remove old facility migration map
drush sql:query "DELETE FROM migrate_map_va_node_health_care_local_facility WHERE destid1 = '[old node id]'"

// update new facility migration map so that it updates the original node
drush sql:query "UPDATE migrate_map_va_node_health_care_local_facility SET destid1 = '[old node id]' WHERE destid1 = '[new node id]'"
```


#### CMS Help desk (wrap up)
- [ ] 22. Notify editor and any other stakeholders.
