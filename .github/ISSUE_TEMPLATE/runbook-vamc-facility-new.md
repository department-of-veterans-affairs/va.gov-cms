---
name: Runbook - New VAMC Facility
about: changing facility information in the CMS for VAMC facilities
title: 'New VAMC Facility: <insert_name_of_facility>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  VAMC, sitewide

---
**Before you begin:** Please do not create this ticket until the new VAMC Facility has appeared on the CMS [Flagged Facilities page](https://prod.cms.va.gov/admin/content/facilities/flagged) **and** has been confirmed by an editor or VA stakeholder.

**If notified via Jira but not flagged in the CMS:** Confirm with the editor that they have reported the new facility to VAST. Please refer to the KB article "[How do I add a facility to my health care system?](https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-do-i-add-a-facility-to-my-health-care-system)" for more info.

**If reported to VAST but not listed yet:** Changes made in VAST may take up to 75 days to appear on the Facilities API.

**If the help desk is waiting on info or actions from facility editor(s):** Please add the "Awaiting editor" flag to the facility node with a revision log message that includes Jira or Github ticket numbers. 

Do not change the moderation state of the node (e.g. "Draft", "Published") when adding or removing flags unless otherwise noted.

----------

## Facility and Ticket Info
- [ ] **VAMC facility name:** `<vamc_facility>`
- [ ] **VAMC System/Section:** `<vamc_system>`
- [ ] **Link to facility on production site:** `<facility_prod_link>`
- [ ] **Facility API ID:** `<facility_API_ID>`     
- [ ] **Has the facility been added to the internal Flagged Facilities listing yet?** 
     If no, please add it to the appropriate tab with the prod link, facility ID, and any relevant ticket links or details. ([Current spreadsheet.](https://docs.google.com/spreadsheets/d/1mqTRGkrnfysFMjC8xTHdFzQOMIQ7WrD2CLX83io5Z74/edit?gid=1358772674#gid=1358772674))
- [ ] **Link to Jira ticket(s):** `<jira_ticket_links>`
     
     **Embedded Support:** Please search Jira and add links to any relevant tickets. If none found, please link once created.

### VA Mobile Medical Unit facilities:
Please check with the VAMC section's editors whether the MMU is currently in use. If the MMU is in use, please treat it as a typical VAMC Facility homepage using the steps below.

If it is not in use, the "new facility" flag can be removed and the node can be archived. However, there are required fields within the CMS that will need to be filled in before doing so, such as meta text and the section and menu link assignments. (See below)

----

## Acceptance criteria for new VAMC facilities

### Embedded Support Team steps on the VAMC Facility homepage:

#### STEP 1: Section assignment

Note that VAMC Facility homepages cannot be updated by content editors until they've been assigned to a section.

- [ ] While editing the homepage, scroll down to the field **"What health care system does the facility belong to?"** and assign the facility's VAMC system from the dropdown menu.

- [ ] You will need to assign the VAMC system once more on the right-side menu beneath "Section settings," which governs the VAMC menu location. Facility homepages cannot be updated by content editors until they've been assigned to a section.

#### STEP 2: Menu settings

- [ ] On the right-side menu under "Menu settings" add the full name of the VAMC Facility to the "Menu link title" field.

- [ ] Use the dropdown titled "Parent link" to select the "Locations" menu for the associated VAMC section. _(Be careful! This is an extremely long dropdown menu, and it is easy to select the wrong section.)_
      
#### STEP 3: Meta text

- [ ] Add the following text to the "meta description" field: `Get address and hours, parking and transportation information, and health services offered at [CLINIC NAME]`

#### STEP 4: Save as "Draft"

- [ ] Save as "Draft," then double-check to ensure the node was assigned to the correct section.

#### STEP 5: Update menu link location (but do not enable link yet)
- [ ] From `admin/structure/menu` find the associated VAMC section and click "Edit menu." Scroll down to "Locations" and use the four-pointed arrow to move the facility to the correct location in the menu. After editing the menu, click "Save."

     - Note: Per VA system design standards, medical centers are listed first, then clinics, both in alphabetical order.

#### STEP 6: Update editor/stakeholders

- [ ] Let the VAMC section's assigned editor(s) know they can now edit the draft facility homepage and add health services. (Email template: "Editor next steps")

### After editor says homepage is ready for publishing:
      
#### STEP 7: Make sure the facility homepage has the following:
- [ ] An introduction
- [ ] A facility photo
- [ ] "Prepare For Your Visit" info
- [ ] VAMC Facility Health Services
      
Health services will be listed at the bottom of the facility homepage on the production site. If there is nothing listed beneath the "Health Services" header at the bottom of the homepage, please send the "Missing VAMC Facility Health Services" email template below.

### Drupal Administrator steps (Embedded Support team or Facilities team):

#### STEP 8: Enable left-nav menu link
- [ ] From `admin/structure/menu` find the associated VAMC section and click "Edit menu." Scroll down to "Locations" and click "Edit" next to the location in question. From there, enable the menu link, and click "Save."

#### STEP 9: Bulk publishing facility homepage and all linked nodes:
- [ ] Go to the Bulk Edit page (`admin/content/bulk`), filter by section, moderation state = "Any", and search for the facility name.
- [ ] Select all relevant nodes, then scroll to the "Action" menu at the bottom of the Bulk Edit page.
- [ ] Click "Publish latest revision" then click "Apply to selected items."
- [ ] After bulk-publishing, double-check all linked VAMC Facility Health Services to ensure that they've successfully published.
      
### After the next content release goes out:

#### STEP 10: Facility Locator check ( https://www.va.gov/find-locations/ )

- [ ] Validate that the change has deployed by checking that the Facility Locator directs to the newly published homepage.

#### STEP 11: Remove flag
- [ ] Edit facility node and remove `New facility` flag with a revision log message that includes a link to this ticket.
      
### Embedded Support team:

#### STEP 12: Wrap-up editor comms.
- [ ] Notify editor and any other stakeholders that the new homepage is now live.

------

## Additional guidance

**If this is a false "new facility" report, i.e. a duplication of an existing homepage, or one that simply doesn't exist.**
This is a rare case, but if this happens, please let the editor know to contact their VAST administrator to make a correction.

**If editor has trouble editing the draft homepage:** 
Make sure they’re logged in properly and that the editor and the facility are assigned to the same section.

**If the editor publishes draft homepage without health services added:** 
Send "Missing VAMC Facility Health Services" email template below. If the editor does not add them after the CMS Help Desk team has reached out two or more times, CC [VHADigitalMedia@va.gov](mailto:VHADigitalMedia@va.gov) and add 'escalation' label in Jira.

------

## Email templates:

### New VAMC facility editor confirmation email 
Send if facility spotted on Flagged Facilities page but details haven't been confirmed
```
Hello!

The [CLINIC NAME] has been flagged as a “new” location by the Facilities API.

Questions:

What is the status of this facility?

Should the homepage for this location be added to the live site?
- If so, great! We can proceed with next steps.
- If not, is this an existing location that was previously closed, renamed, or relocated? Was it previously known by any other name?

Please provide details for this location as well as an opening date if one has been determined – thank you!
```
### New VAMC facility editor next steps email:

```Hello! You should now be able to edit the draft page for this facility, located at [LINK TO NEW FACILITY DRAFT PAGE ON PROD]

Important: Please make sure that all relevant steps listed within the “How do I add a facility to my health care system?” Knowledge Base article have been completed: https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-do-i-add-a-facility-to-my-health-care-system

The facility will also need VAMC Facility Health Services, otherwise Veterans will not be able to view what types of care are available. For more info, please see the associated Knowledge Base article: https://prod.cms.va.gov/help/vamc/about-vamc-health-services

Once finished, please save this page (and all related VAMC Facility Health Service pages) in the moderation state “Draft." Please do not save them as “Published.”

Please let us know when your draft content is complete, so that we can wrap up the technical process from our end before publishing the new facility to VA.gov. Thanks!
```
### Missing VAMC Facility Health Services email:
```
Thank you for your team's help updating the homepage for this facility; however, you have not added any VAMC Facility Health Services for this location.

Please add Facility Health Services as soon as possible since this may impact Veteran health care and patient safety. Thank you!

For more information on how to add VAMC Facility Health Services for this location, please see the related Knowledge Base article: https://prod.cms.va.gov/help/vamc/about-vamc-health-services

Please let us know as soon as health services have been added for this location. Thank you!
```
