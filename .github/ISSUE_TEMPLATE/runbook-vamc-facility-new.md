---
name: Runbook - New VAMC Facility
about: changing facility information in the CMS for VAMC facilities
title: 'New VAMC Facility: <insert_name_of_facility>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  VAMC, sitewide

---
#### Preliminary Info

Please do not create this ticket until the new VAMC Facility name change has both:
1. Appeared on the CMS [Flagged Facilities page](https://prod.cms.va.gov/admin/content/facilities/flagged) **and** 
2. Has been confirmed by an editor or VA stakeholder.

**If notified via Jira but not flagged in the CMS:** Confirm with the editor that they have reported the new facility to VAST. Please refer to the KB article "[How do I add a facility to my health care system?](https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-do-i-add-a-facility-to-my-health-care-system)" for more info and send to editor if needed.

**If reported to VAST but not listed yet:** Changes made within VAST can take up to 75 days to appear within the Facilities API.

----------

# Facility and Ticket Info
- [ ] **Link to facility on production site:** `<insert_facility_link>`
- [ ] **Facility API ID:** `<insert_facility_API_ID>`
- [ ] **VAMC System/Section:** `<insert_vamc_section_name>`
- [ ] **Link to Jira ticket(s):** `<insert_jira_ticket_link>`
     Embedded Support: Please search Jira and add links to any relevant tickets. If none found, please link once created.
- [ ] **Has the facility been added to the internal Flagged Facilities listing yet?** 
     If no, please add it to the appropriate tab with the prod link, facility ID, and any relevant ticket links or details. ([Current spreadsheet.](https://docs.google.com/spreadsheets/d/1mqTRGkrnfysFMjC8xTHdFzQOMIQ7WrD2CLX83io5Z74/edit?gid=1358772674#gid=1358772674))

If the help desk is waiting on info or action from facility editor(s), please add the "Awaiting editor" flag to the facility node with a log message including any relevant Jira or Github ticket numbers. Please do not change the moderation state of the node (e.g. "Draft", "Published") when adding or removing flags unless otherwise noted.

### If the facility is a VA Mobile clinic:
"New facility" flag can be removed and the page archived with no further work needed. However, there are required fields within the CMS that will need to be filled in before doing so, such as meta text and the section and menu link assignments. (See below)

----

# Acceptance criteria for new VAMC facilities

## Embedded Support Team steps:
- [ ] **Step 1:** Confirm the new facility has been listed on the [CMS Flagged Facilities](https://prod.cms.va.gov/admin/content/facilities/flagged) page. 
- [ ] **Step 2:** Search for existing Jira tickets regarding this facility. If no tickets found, please send the "Editor confirmation email" (see below) to all Active users within the facility's section. If an opening day is mentioned, please add to ticket and escalate if opening soon.
- [ ] **Step 3:** Go to the facility homepage, click "Edit," and assign the facility homepage to the correct VAMC section and menu location. Facility homepages cannot be updated by content editors until they've been assigned to a section.
     - To find the correct VAMC section, add the first 3 digits of the facility ID to the ### portion of this URL: `https://www.va.gov/find-locations/facility/vha_###` This will direct you to the main location within the appropriate section.

     - For more information on how to assign a VAMC System and menu link, please see the associated Confluence guide: [How to Assign a New VAMC Facility to the Proper Section/Menus](https://vfs.atlassian.net/wiki/spaces/PCMS/pages/2947612716/How+to+Assign+a+New+VAMC+Facility+to+the+Proper+Section+Menus)
- [ ] **Step 4:** Add the following text to the "meta description" field: 
`Get address and hours, parking and transportation information, and health services offered at [CLINIC NAME]`
- [ ] **Step 5:** Check the menu link field (right-side panel) to ensure that it is listed under "Locations" for the correct section.
- [ ] **Step 6:** Save as "Draft."
- [ ] **Step 7:** Inform the editor(s) that they can now edit the homepage and add health services. (See "Editor next steps" email below)

#### After editor says homepage is ready to go:
- [ ] **Step 8:** Verify that the homepage has been updated from the original template. If not, this must be completed. ([KB article to send](https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-do-i-add-a-facility-to-my-health-care-system))
- [ ] **Step 9:** Go to the facility homepage on production to confirm that VAMC Facility Health Services have been added.
     - If there is nothing listed beneath the "Health Services" header at the bottom of the homepage, please send the "Missing VAMC Facility Health Services" email template below.
- [ ] **Step 10:** Confirm that the facility link within the health care system's "Locations" menu is placed correctly.
     - Medical centers are listed first, then clinics, both in alphabetical order. See "Left-Nav Menu Link Updates" section below for details.
     - If the facility was prematurely published by the editor instead of saved as a "Draft," the left-nav menu link will not be enabled, and will need to be enabled by a member of our team. (See "Left-Nav Menu Link Updates" section for how-to steps.)

## Drupal Administrator steps (Embedded Support team or Facilities team):
- [ ] **Step 11: Bulk publishing facility homepage and all linked nodes:** 
     - Go to the [Bulk Edit](https://prod.cms.va.gov/admin/content/bulk) page, filter by section, moderation state = "Any", and search for the facility name.
     - Select all relevant nodes, then scroll to the "Action" menu at the bottom of the Bulk Edit page.
     - Click "Publish latest revision" then click "Apply to selected items."
     - After bulk-publishing, double-check all linked VAMC Facility Health Services to ensure that they've successfully published.

#### After the next content release goes out:
- [ ] **Step 12:** Validate that the change has deployed by checking that the [Facility Locator](https://www.va.gov/find-locations/) directs to the newly published homepage.
- [ ] **Step 13:** Edit facility node and remove `New facility` flag with a revision log message that includes a link to this ticket.

## Embedded Support team:
- [ ] **Step 14:** Notify editor and any other stakeholders that the new homepage is now live.

------

## Additional guidance

**If this is a false "new facility" report, i.e. a duplication of an existing homepage, or one that simply doesn't exist.**
This is a rare case, but there are examples of it happening -- for example, a new facility was once reported that turned out to be a private, non-VA dental clinic unaffiliated with any VAMC system. If this happens, please let the editor know to contact their VAST administrator to make a correction.

**If editor has trouble editing the draft homepage:** 
Make sure they’re logged in properly and that the editor and the facility are assigned to the same section.

**If the editor publishes draft homepage without health services added:** 
Send "Missing VAMC Facility Health Services" email template below. If the editor does not add them after the CMS Help Desk team has reached out two or more times, CC [VHADigitalMedia@va.gov](mailto:VHADigitalMedia@va.gov) and add 'escalation' label in Jira.

## Left-Nav Menu Link Updates

- Go to Content -> [Menus](https://prod.cms.va.gov/admin/structure/menu) ( https://prod.cms.va.gov/admin/structure/menu ). 
- Find the associated VAMC System (e.g. VA Boston health care) and click "Edit menu."
- Scroll down until you find the "Locations" menu header. (Be careful to avoid accidentally moving menu items around!)
- To enable the left-nav menu link, find the facility name, click "Edit" and select "Enabled." Click Save.
- If the facility is listed in the wrong spot, click and drag the four-pointed arrow to move it. Per VA system design standards, medical centers are listed first, then clinics, with both in alphabetical order. 
- Once your changes have been made, go to the bottom of the VAMC System menu page and click "Save."

------

# Email templates:

## New VAMC facility editor confirmation email 
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
## New VAMC facility editor next steps email:

```Hello! You should now be able to edit the draft page for this facility, located at [LINK TO NEW FACILITY DRAFT PAGE ON PROD]

Important: Please make sure that all relevant steps listed within the “How do I add a facility to my health care system?” Knowledge Base article have been completed: https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-do-i-add-a-facility-to-my-health-care-system

The facility will also need VAMC Facility Health Services, otherwise Veterans will not be able to view what types of care are available. For more info, please see the associated Knowledge Base article: https://prod.cms.va.gov/help/vamc/about-vamc-health-services

Once finished, please save this page (and all related VAMC Facility Health Service pages) in the moderation state “Draft." Please do not save them as “Published.”

Please let us know when your draft content is complete, so that we can wrap up the technical process from our end before publishing the new facility to VA.gov. Thanks!
```
## Missing VAMC Facility Health Services email:
```
Thank you for your team's help updating the homepage for this facility; however, you have not added any VAMC Facility Health Services for this location.

Please add Facility Health Services as soon as possible since this may impact Veteran health care and patient safety. Thank you!

For more information on how to add VAMC Facility Health Services for this location, please see the related Knowledge Base article: https://prod.cms.va.gov/help/vamc/about-vamc-health-services

Please let us know as soon as health services have been added for this location. Thank you!
```
