---
name: Runbook - New Mobile Vet Center
about: changing facility information in the CMS for Vet Center facilities
title: 'New Mobile Vet Center: <new_mobile_vet_center>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  Vet Center, sitewide
assignees: ''

---
**Before you begin:** Please do not create this ticket until the new Vet Center has appeared on the CMS [Flagged Facilities page](https://prod.cms.va.gov/admin/content/facilities/flagged) or until either the Facilities team or VA Leadership have instructed us to create the new Vet Center homepage and section.

**If notified via Jira but not flagged in the CMS:**
Confirm with the editor that they reported the new facility to VAST. Please refer to the KB article "[How do I update my Vet Center facility's basic location data?](https://prod.cms.va.gov/help/vet-centers/how-do-i-update-my-vet-center-facilitys-basic-location-data)" for more info and send to editor if needed.

**If reported to VAST but not listed yet:** Changes made within VAST can take up to 75 days to appear within the Facilities API.

**If the help desk is waiting on info or actions from facility editor(s):** Please add the "Awaiting editor" flag to the facility node with a revision log message that includes Jira or Github ticket numbers. 

Do not change the moderation state of the node (e.g. "Draft", "Published") when adding or removing flags unless otherwise noted.

----------
## Facility and Ticket Info
- [ ] **Mobile Vet Center name:** `<mobile_vet_center_name>`
- [ ] **Vet Center section name:** `<vet_center_section>`
- [ ] **Link to facility on production site:** `<facility_prod_link>`
- [ ] **Facility API ID:** `<facility_API_ID>`     
- [ ] **Has the new Mobile Vet Center been added to the internal Flagged Facilities listing yet?** 
     
     If no, please add it to the appropriate tab with the prod link, facility ID, and any relevant ticket links or details. ([Current spreadsheet.](https://docs.google.com/spreadsheets/d/1mqTRGkrnfysFMjC8xTHdFzQOMIQ7WrD2CLX83io5Z74/edit?gid=1358772674#gid=1358772674))
- [ ] **Link to Jira ticket(s):** `<jira_ticket_links>`
     
     **Embedded Support:** Please search Jira and add links to any relevant tickets. If none found, please link once created.
     
You will not need to worry about creating any redirect tickets for Mobile Vet Center nodes, because they do not have direct URLs -- these nodes are nested within the linked Vet Center's "Locations" page.

----

## Acceptance criteria for new Mobile Vet Center (MVC) facilities

### Drupal Administrator steps (Embedded Support team or Facilities team):

#### STEP 1: Inspect URL
- [ ] First, please inspect the new Mobile Vet Center URL. Does it end in `-0` ?

     - [ ] **No**: No work needed, proceed to next steps.

     - [ ] **Yes**: This node may be a duplicate of an existing MVC. In this case, please go to the original MVC URL by removing the `-0`.

          - **If there are two MVCs listed under the same name with two different nodes:** Embedded Support will need to email the associated Vet Center Section editor(s) to confirm which one has the correct ID (see "Duplicate Mobile Vet Center email" below).

          - **Context:** Duplicate node creation may happen when MVC vehicles are swapped between Vet Centers, or a new vehicle is put into use. This is because each MVC ID within the CMS is associated with a specific vehicle, not a specific section or node.
         
#### STEP 2: URL check
- Does the section listed in the URL match the main Vet Center listed in the Mobile Vet Center's H1 title?

     - [ ] **Yes**: No work needed, proceed to next steps.
     
     - [ ] **No**: You will need to use the 'bulk-edit' page to re-save the URL alias for this MVC node. Otherwise, it will not be linked to the main Vet Center properly, and will not show up on the Vet Center's "Locations" page. Instructions listed below.

#### STEP 3: Section check
- [ ] Inspect the "Main Vet Center" and "Section" fields. Does the H1 title of the Mobile Vet Center node match the name of the Vet Center section listed in these fields?

     - [ ] **Yes**: No work needed, proceed to next steps.

     - [ ] **No:** Please edit the node and update the incorrect field(s) after confirming the correct Vet Center section assignment.

#### STEP 4: URL alias update / Re-saving the node
(THIS STEP IS ONLY NECESSARY IF YOU MADE SECTION OR URL CHANGES IN STEPS 1/2/3. Otherwise, skip.)

Go to the [Bulk Edit](https://prod.cms.va.gov/admin/content/bulk) page, filter by section, moderation state = "Any", and search for the Mobile Vet Center.

Select the node, then use the "Action" menu at the bottom of the Bulk Edit page to perform the steps listed below. You must click "Apply to selected items" after each action listed, or it will not be executed or saved.

   - [ ] If the MVC node is currently listed under an incorrect section, please use the "Modify Values" action to update the assigned section, then click "Apply to Selected Items."

   - [ ] **Update the node's URL alias:** Click "Update URL alias" then "Apply to selected items"
          
   - [ ] **Re-save the node:** Select "Re-save content" then click "Apply to selected items".

#### STEP 5: Verify URL alias update and section linking
(THIS STEP IS ONLY NECESSARY IF YOU MADE SECTION OR URL CHANGES IN STEPS 1/2/3. Otherwise, skip.)
     
   - [ ] Refresh the MVC node to verify that the URL alias matches the associated Vet Center section.
	   
   - [ ] Visit the associated Vet Center's "Locations" page on the production site to ensure that the MVC is properly linked. 
	   
The MVC should be listed under the "Main and Satellite Locations" header on the Vet Center "Locations" node on the production site. If published, it will be listed beneath the "Satellite Locations" header on the live site.

----

### Embedded Support team steps:

#### STEP 6: Verify URL alias update and section linking
- [ ] Reach out to the associated Vet Center editor(s) via Jira and send the "New Mobile Vet Center reported" email template available below.

     - **If the Mobile Vet Center lacks a photo:** Please ask the editor to add a photo as soon as possible. (Included within the "New MVC" email template).

     - **If the Mobile Vet Center node is unpublished:** Please remind the editor that the MVC will not be listed on their Vet Center's "Locations" page on the live site unless this node is saved as "Published." (Also included within the "New VMC" email template.)

#### STEP 7: Remove flags
- [ ] Once the editor has finished adding a photo and publishing the node, go to the MVC homepage, click "Edit," then scroll to bottom to remove the `New facility` flag and any other flags. Click Save.
     
------

## Email templates:

### New Mobile Vet Center reported
Send if a new MVC has been reported via the Flagged Facilities page but there is no existing communication confirming that this Mobile Vet Center vehicle is in use:

```
A new Mobile Vet Center, [NAME OF MOBILE VET CENTER], has been added to the section [MAIN VET CENTER].

Please review the information listed for this location, and please add a photo at your earliest convenience: [INSERT PRODUCTION URL FOR MOBILE VET CENTER NODE]

If there is any incorrect information listed on this page, please let us know. Thank you!

This node is currently saved as a “Draft” and the info is not yet visible on the live site. Once it is saved as “Published,” it will be visible from the "Locations" page on your Vet Center site beneath the “Satellite Locations” header.
```

### Duplicate Mobile Vet Center email 
Send if there is a new MVC that has been added to the VA.gov CMS that is a potential duplicate (i.e. same name/section as a different MVC node and has `-0` at the end of the URL).

```Hello,

We hope you’re doing well!

It looks like there are two entries within our system for the Mobile Vet Center associated with your location:

vc_0###MVC (Mobile Vet Center #___)

vc_0###MVC (Mobile Vet Center #___)

Can you please verify which of these two IDs matches the MVC currently in use? Thank you!```
