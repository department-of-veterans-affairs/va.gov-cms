---
name: Runbook - Mobile Vet Center Name Change
about: Runbook for Mobile Vet Center name changes on the VA Drupal CMS
title: 'Mobile Vet Center name change: FROM <old_mvc_name> TO <new_mvc_name>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  Vet Center, sitewide

---
**Before you begin:** Please do not create this ticket until the Mobile Vet Center name change has appeared on the CMS [Flagged Facilities page](https://prod.cms.va.gov/admin/content/facilities/flagged).

**If notified via Jira but not flagged in the CMS:**
Confirm with the editor that they reported the MVC name change to VAST. Please refer to the KB article "[How do I update my Vet Center facility's basic location data?](https://prod.cms.va.gov/help/vet-centers/how-do-i-update-my-vet-center-facilitys-basic-location-data)" for more info and send to editor if needed.

**If reported to VAST but not listed yet:** Changes made within VAST can take up to 75 days to appear within the Facilities API.

**If the help desk is waiting on info or actions from facility editor(s):** Please add the "Awaiting editor" flag to the facility node with a revision log message that includes Jira or Github ticket numbers. 

Do not change the moderation state of the node (e.g. "Draft", "Published") when adding or removing flags unless otherwise noted.

For any Vet Center sections lacking active editors, please contact Barb Kuhn or the current VA leadership point of contact.

----------

## Facility and Ticket Info
- [ ] **Former Mobile Vet Center name:** `<former_mvc_name>`
- [ ] **New Mobile Vet Center name:** `<new_mvc_name>`
- [ ] **Link to facility on production site:** `<facility_prod_link>`
- [ ] **Facility API ID:** `<facility_API_ID>`

**If updating section:**

- [ ] **Former Vet Center section name:** `<former_vet_center_section_name>`
- [ ] **New Vet Center section name:** `<new_vet_center_section_name>`

**If linked to main Vet Center name change:**

- [ ] Link to parent Vet Center name change ticket: `<insert_vet_center_name_change_Github_ticket>`

- [ ] **Has the Vet Center name change been added to the internal Flagged Facilities listing yet?** 
     
     If no, please add it to the appropriate tab with the prod link, facility ID, and any relevant ticket links or details. ([Current spreadsheet.](https://docs.google.com/spreadsheets/d/1mqTRGkrnfysFMjC8xTHdFzQOMIQ7WrD2CLX83io5Z74/edit?gid=1358772674#gid=1358772674))
     
- [ ] **Link to Jira ticket(s):** `<jira_ticket_links>`
     
  **Embedded Support:** Please search Jira and add links to any relevant tickets. If none found, please link once created. 

URL redirects are not needed for Mobile Vet Center and Vet Center Outstation tickets because they do not have outward-facing URLs. 

However, you will still need to update and re-save the URL alias for this MVC node. Otherwise, it will not be linked to the main Vet Center properly, and will not show up on the Vet Center's "Locations" page. Instructions listed below.

----

## Acceptance criteria for Mobile Vet Center facility name changes

### Embedded Support team steps:
#### STEP 1: Confirm name change on facility homepage
First, please note the H1 title of the Mobile Vet Center node on the production site. From there, please inspect the node's URL and the "Main Vet Center" and "Section" fields.

Does the section listed in the node's H1 header match the Vet Center section listed in the URL, as well as the "Main Vet Center" and "Section" fields?

- [ ] **Yes**: No work needed, proceed to next steps.
		
- [ ] **No, but this is because the main Vet Center is currently undergoing a name change:** STOP! Do not proceed with Mobile Vet Center name change until the main Vet Center name change ticket is complete.

- [ ] **No, because the MVC may have been re-assigned from the City 1 Vet Center to the City 2 Vet Center:** The Embedded Support team will need to send the editor(s) associated with both the former section _and_ the new section the "Mobile Vet Center section change verification email" template provided below. 

**Context:** Each MVC ID within the CMS is associated with a specific vehicle, not a specific node. When vehicles are swapped between Vet Centers, the info tied to the vehicle in the CMS will need to be updated. **DO NOT PROCEED WITHOUT VERIFYING THE SECTION CHANGE.**

**If the URL ends in -0**: This node may be a duplicate of an existing MVC. Please verify by removing the `-0` to visit the original MVC URL. If both are listed under the same section, Embedded Support will need to email the associated Vet Center Section editor(s) to confirm which node has the correct  MVC ID (see "Duplicate Mobile Vet Center email" below).

-------

### Drupal Administrator steps (Embedded Support team or Facilities team):

#### STEP 2: Update Section and Main Vet Center
- [ ] After verifying the correct section assignment, please edit the MVC node and update the **"Main Vet Center"** and **"Section"** fields. Click Save.

#### STEP 3: Bulk-edit updates
Go to the [Bulk Edit](https://prod.cms.va.gov/admin/content/bulk) page, filter by section, moderation state = "Any", and search for the Mobile Vet Center.

Select the node, then use the "Action" menu at the bottom of the Bulk Edit page to do the following in one session:

- [ ] **Update the MVC's assigned Vet Center section:** If the MVC is still listed under the former Vet Center's section, please use the "Modify Values" action to update the section, then click "Apply to Selected Items."

- [ ] **Update the node's URL alias:** Click "Update URL alias" then "Apply to selected items"
          
- [ ] **Re-save the node:** Select "Re-save content" then click "Apply to selected items".

#### STEP 4: Update facility media
Go to Content -> [Media](https://prod.cms.va.gov/admin/content/media/images) ( `admin/content/media/images` )

- [ ] Check the "Media" page to ensure that any images of this facility are updated with the new Section and name in their title and alt-text fields. ( https://prod.cms.va.gov/admin/content/media )

#### STEP 5: URL/Section Update Acceptance criteria:
	   
- [ ] After the next content release goes out, please go to the new Vet Center's "Locations" page to ensure that the MVC is properly linked. 
	   
The MVC should be listed under the "Main and Satellite Locations" header on the new Vet Center's "Locations" node on the production site. If published, it will also be listed beneath the new Vet Center's "Satellite Locations" header on the live site.

------

## Embedded Support team steps:
#### STEP 6: Editor comms
- [ ] Reach out to the associated Vet Center editor(s) via Jira and send the "Section change completed" email template available below.

     - **If the Mobile Vet Center lacks a photo:** Please ask the editor to add a photo as soon as possible.

     - **If the Mobile Vet Center node is unpublished:** Please remind the editor that the MVC will not be listed on their Vet Center's "Locations" page on the live site unless this node is saved as "Published."

#### STEP 7: Remove flag
- [ ] Once the editor has finished adding a photo and publishing the node, go to the MVC homepage, click "Edit," then scroll to bottom to remove the `Changed name` flag and any other flags. Click Save.
     
------

## Email templates:

### Mobile Vet Center section change verification email
Send if an MVC node appears to have moved from one section to another -- such as the real-life MVC that moved from the Salt Lake City Vet Center to the Austin Vet Center:

```
The VA Drupal CMS Help Desk Support team received a notification that the former [OLD NAME OF MOBILE VET CENTER] has been re-named to the [NEW NAME OF MOBILE VET CENTER]. 

The facility ID for this MVC is vc_0###MVC (Mobile Vet Center ###). 

Here is a direct link to this node: [INSERT LINK TO MVC ON PRODUCTION SITE]

If this Mobile Vet Center was not re-assigned, please let us know at your earliest convenience. Thank you!

 -- IF THERE IS A SECOND MVC NODE NOW LINKED TO THIS MVC'S FORMER VET CENTER -- 

[FORMER VET CENTER] team: 

Please note that there is also a separate Mobile Vet Center listed under facility ID vc_0###MVC and assigned to your section.

Is this MVC assigned correctly, and is it in use? Thank you!

```

### Section change completed email

``` 
Thank you for letting us know that Mobile Vet Center ### was re-assigned. This MVC is now assigned to your Vet Center section within the CMS.

Here is a link to the MVC’s homepage, where you can verify the info listed and update the photo: [INSERT LINK TO MVC NODE ON PRODUCTION SITE]

```

### Duplicate Mobile Vet Center email 
Send if there is a new MVC that has been added to the VA.gov CMS that is a potential duplicate (i.e. same name/section as a different MVC node and has `-0` at the end of the URL).

```Hello,

We hope you’re doing well!

It looks like there are two entries within our system for the Mobile Vet Center associated with your location:

vc_0###MVC (Mobile Vet Center #___)

vc_0###MVC (Mobile Vet Center #___)

Can you please verify which of these two IDs matches the MVC currently in use? Thank you!```
