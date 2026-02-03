---
name: Runbook - New Vet Center Outstation
about: Adding a new Vet Center Outstation to an existing Vet Center
title: 'New Vet Center Outstation: <new_vet_center_outstation>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  Vet Center, sitewide
assignees: ''

---
**Before you begin:** Please do not create this ticket until the new Outstation has appeared on the CMS [Flagged Facilities page](https://prod.cms.va.gov/admin/content/facilities/flagged).

**If notified via Jira but not flagged in the CMS:**
Confirm with the editor that they reported the new facility to VAST. Please refer to the KB article "[How do I update my Vet Center facility's basic location data?](https://prod.cms.va.gov/help/vet-centers/how-do-i-update-my-vet-center-facilitys-basic-location-data)" for more info and send to editor if needed.

**If reported to VAST but not listed yet:** Changes made within VAST can take up to 75 days to appear within the Facilities API.

Do not change the moderation state of the node (e.g. "Draft", "Published") when adding or removing flags unless otherwise noted.

For any Vet Center sections lacking active editors, please contact Barb Kuhn or the current VA leadership point of contact.

----------
## Facility and Ticket Info
- [ ] **Vet Center Outstation name:** `<outstation_name>`
- [ ] **Vet Center section name:** `<vet_center_section>`
- [ ] **Link to facility on production site:** `<facility_prod_link>`
- [ ] **Facility API ID:** `<facility_API_ID>`     
- [ ] **Has the new Outstation been added to the internal Flagged Facilities listing yet?** 
     
     If no, please add it to the appropriate tab with the prod link, facility ID, and any relevant ticket links or details. ([Current spreadsheet.](https://docs.google.com/spreadsheets/d/1mqTRGkrnfysFMjC8xTHdFzQOMIQ7WrD2CLX83io5Z74/edit?gid=1358772674#gid=1358772674))
- [ ] **Link to Jira ticket(s):** `<jira_ticket_links>`
     
     **Embedded Support:** Please search Jira and add links to any relevant tickets. If none found, please link once created.
     
You will not need to worry about creating any redirect tickets for Outstation nodes, because they do not have direct URLs -- these nodes are nested within the linked Vet Center's "Locations" page.

----

## Acceptance criteria for new Outstation facilities

### Drupal Administrator steps (Embedded Support team or Facilities team):

#### STEP 1: Inspect URL
First, please inspect the new Outstation URL. Does it end in `-0` ?

   - [ ] **No**: No work needed, proceed to next steps.

   - [ ] **Yes**: This node may be a duplicate of an existing Outstation. In this case, please go to the original MVC URL by removing the `-0`.

      - **If there are two Outstations listed under the same name with two different nodes:** Embedded Support will need to email the associated Vet Center Section editor(s) to confirm which one has the correct ID (see "Duplicate Outstation email" below).

#### STEP 2: URL check
Does the section listed in the URL match the main Vet Center listed in the Outstation's H1 title?

  - [ ] **Yes**: No work needed, proceed to next steps.
     
  - [ ] **No**: You will need to use the 'bulk-edit' page to re-save the URL alias for this Outstation node. Otherwise, it will not be linked to the main Vet Center properly, and will not show up on the Vet Center's "Locations" page. Instructions listed below.

#### STEP 3: Section and naming check
Inspect the "Common Name," "Main Vet Center" and "Section" fields. Does the H1 title of the Outstation node match the name of the Vet Center section listed in these fields?

  - [ ] **Yes**: No work needed, proceed to next steps.

  - [ ] **No:** Please edit the node and update the incorrect field(s) after confirming the correct Vet Center section assignment.

#### STEP 4: URL alias update / Re-saving the node
(THIS STEP IS ONLY NECESSARY IF YOU MADE SECTION OR URL CHANGES IN STEPS 1/2/3. Otherwise, skip.)

Go to the [Bulk Edit](https://prod.cms.va.gov/admin/content/bulk) page, filter by section, moderation state = "Any", and search for the Outstation.

Select the node, then use the "Action" menu at the bottom of the Bulk Edit page to perform the steps listed below. You must click "Apply to selected items" after each action listed, or it will not be executed or saved.

   - [ ] If the Outstation is listed under an incorrect section, please use the "Modify Values" action to update the assigned section, then click "Apply to Selected Items."

   - [ ] **Update the node's URL alias:** Click "Update URL alias" then "Apply to selected items"
          
   - [ ] **Re-save the node:** Select "Re-save content" then click "Apply to selected items".

#### STEP 5: Verify URL alias update and section linking
(THIS STEP IS ONLY NECESSARY IF YOU MADE SECTION OR URL CHANGES IN STEPS 1/2/3. Otherwise, skip.)
     
- [ ] Refresh the Outstation node to verify the URL update, and go to the main Vet Center's "Locations" page to ensure that the Outstation is properly linked. 
	   
The Outstation should be listed under the "Main and Satellite Locations" header on the Vet Center "Locations" node on the production site. If published, it will also be listed beneath the "Satellite Locations" header on the live site.

### Embedded Support team steps:

#### STEP 6: Editor wrap-up
- [ ] Reach out to the associated Vet Center editor(s) via Jira and send the "New Outstation reported" email template available below.

     - **If the Outstation lacks a photo:** Please ask the editor to add a photo as soon as possible. (Included within the "New Outstation" email template).

     - **If the Outstation node is unpublished:** Please remind the editor that the Outstation will not be listed on their Vet Center's "Locations" page on the live site unless this node is saved as "Published." (Also included within the "New VMC" email template.)

#### STEP 7: Remove flags
- [ ] Once the editor has finished adding a photo and publishing the node, go to the Outstation homepage, click "Edit," then scroll to bottom to remove the `New facility` flag and any other flags. Click Save.
     
------

## Email templates:

### New Outstation reported
Send if a new Outstation has been reported via the Flagged Facilities page but there is no existing communication confirming that this Outstation is in use:

```
A new Outstation, [NAME OF OUTSTATION], has been added to the section [MAIN VET CENTER].

Please review the information listed for this location, and please add a photo at your earliest convenience: [INSERT PRODUCTION URL FOR OUTSTATION NODE]

If there is any incorrect information listed on this page, please let us know. Thank you!

This node is currently saved as a “Draft” and the info is not yet visible on the live site. Once it is saved as “Published,” it will be visible from the "Locations" page on your Vet Center site beneath the “Satellite Locations” header.
```

### Duplicate Outstation email 
Send if there is a new Outstation that has been added to the VA.gov CMS that is a potential duplicate (i.e. same name/section as a different Outstation node and has `-0` at the end of the URL).

```Hello,

We hope you’re doing well!

It looks like there are two entries within our system for the Outstation associated with your location:

- vc_####OS (Outstation #___)

- vc_####OS (Outstation #___)

Can you please verify which of these two IDs matches the [NAME OF OUTSTATION]? Thank you!
```
