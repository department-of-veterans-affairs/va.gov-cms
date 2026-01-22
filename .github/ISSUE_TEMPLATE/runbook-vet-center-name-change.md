---
name: Runbook - Vet Center facility / section name change
about: Steps for updating Vet Center facility and section names and all linked URLs
title: 'Vet Center name change: FROM <former_name> TO <new_name>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support, Vet Center, sitewide

---
**Before you begin:** Please do not create this ticket until the Vet Center name change has appeared on the CMS [Flagged Facilities page](https://prod.cms.va.gov/admin/content/facilities/flagged) or we've been notified directly to go ahead by Barb Kuhn, Michelle Middaugh, or another VA leadership point of contact.

**If notified via Jira but not flagged in the CMS:**
Confirm with the editor that they reported the Vet Center name change to VAST. Please refer to the KB article "[How do I update my Vet Center facility's basic location data?](https://prod.cms.va.gov/help/vet-centers/how-do-i-update-my-vet-center-facilitys-basic-location-data)" for more info and send to editor if needed.

**If reported to VAST but not listed yet:** Changes made within VAST can take up to 75 days to appear within the Facilities API.

**If the help desk is waiting on info or actions from facility editor(s):** Please add the "Awaiting editor" flag to the facility node with a revision log message that includes Jira or Github ticket numbers. 

Do not change the moderation state of the node (e.g. "Draft", "Published") when adding or removing flags unless otherwise noted.

For any Vet Center sections lacking active editors, please contact Barb Kuhn or the current VA leadership point of contact.

----------

## Facility and Ticket Info
- [ ] **Former Vet Center facility name:** `<former_vet_center_facility_name>`
- [ ] **New Vet Center name:** `<new_vet_center_facility_name>`
- [ ] **Link to facility on production site:** `<facility_prod_link>`
- [ ] **Facility API ID:** `<facility_API_ID>`
- [ ] **Former Vet Center section name:** `<former_vet_center_section_name>`
- [ ] **New Vet Center section name:** `<new_vet_center_section_name>`
- [ ] **Has the Vet Center name change been added to the internal Flagged Facilities listing yet?** 
     
     If no, please add it to the appropriate tab with the prod link, facility ID, and any relevant ticket links or details. ([Current spreadsheet.](https://docs.google.com/spreadsheets/d/1mqTRGkrnfysFMjC8xTHdFzQOMIQ7WrD2CLX83io5Z74/edit?gid=1358772674#gid=1358772674))
     
- [ ] **Link to Jira ticket(s):** `<jira_ticket_links>`
     
  **Embedded Support:** Please search Jira and add links to any relevant tickets. If none found, please link once created.
     
- [ ] **Link to URL redirect ticket:** `<url_redirect_link>`
     
     You will not need to insert the URL redirect ticket link until the ticket has been created.
     
#### Satellite location name change tickets (i.e. Mobile Vet Centers, Outstations):
- [ ] Please link to any relevant Satellite Location name change GitHub ticket(s): `<satellite_location_ticket_links>`

----

## Acceptance criteria for Vet Center facility name changes

### Embedded Support team steps:
#### STEP 1: Confirm name change on facility homepage
- [ ] Confirm the new title is displayed as the main H1 header on the Vet Center Facility homepage in Drupal. 

#### STEP 2: Create a URL redirect ticket 
- [ ] Open the following link in a new tab to create a URL redirect request ticket: [Create URL redirect request ticket for the Facilities team](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E)

#### STEP 3: Link to parent ticket
- [ ] After creating the URL redirect ticket, please add the link to the top of this ticket and save your changes.

-------

### Facilities team:
#### STEP 4: Complete URL redirect
- [ ] Complete URL redirect request ticket, then notify the CMS Help Desk team once complete.

-------

The following steps are _not_ blocked by the URL redirect ticket and may be completed simultaneously:

### Drupal Administrator steps (Embedded Support team or Facilities team):

Please complete the following steps in one session to ensure all changes publish together in the next content release.

#### STEP 4: Edit Vet Center name on facility homepage
- [ ] Update the homepage node's "Common Name" field in accordance with the "[Location] Vet Center" naming convention. 

   - 	If the new section already exists within the taxonomy, update the "Main Vet Center" and "Section" fields accordingly.

#### STEP 5: Verify removal of old name from homepage
- [ ] Verify that the former name no longer appears within any fields on the homepage by running a CTRL+F browser search for the former name, and editing it where found. Save the node in its current moderation state.

**IMPORTANT:** You will need to add a new taxonomy term for the updated Vet Center section name. _DO NOT CHANGE THE NAME OF THE EXISTING SECTION LISTED UNDER THE OLD NAME!_

#### STEP 6: Add new Vet Center taxonomy term
- [ ] Add the new Vet Center section name to the Sections taxonomy and assign it to the correct district. 

	Path: `/admin/structure/taxonomy/manage/administration/overview`

	- Product: "Vet Center." 
	- The name of the Vet Center is required but the "Description" field can be left blank.
	- Under "Relations" the parent term should be the District (i.e. District 1).
	- Click "Save."

#### STEP 7: Add new "Vet Center - Locations List" node
- [ ] Create a new "Vet Center - Locations List" node, assign it to the newly created Section, and save it as a Draft.

#### STEP 8: Bulk edit action 1 - Reassigning content to the new Vet Center section

Go to the [Bulk Edit](https://prod.cms.va.gov/admin/content/bulk) page, filter by section = "[Former Vet Center section name]", and select moderation state = "Any".

Select all relevant nodes including the Vet Center homepage and all Vet Center services, then use the "Action" menu at the bottom of the Bulk Edit page to do the following in one session:

   - [ ] **Update the assigned Vet Center section:** Use the "Modify Values" action to update the section for all selected nodes to the newly created Vet Center section, then click "Apply to Selected Items."
     	
   - [ ] **Open the main "Content" page in a new tab to verify that there is no longer any content listed under the old Vet Center section before proceeding.**

#### STEP 9: Bulk edit action 2 - Updating URL aliases
Next, filter the list to view all nodes listed under the new Vet Center section and select all.

  - [ ] **Update all URL aliases:** Select "Update URL alias," then click "Apply to selected items."
          
  - [ ] **Re-save all nodes:** Select "Re-save content" then click "Apply to selected items".

#### STEP 10: Update facility media
Go to Content -> [Media](https://prod.cms.va.gov/admin/content/media/images) ( `admin/content/media/images` )

Filter the Media page by the existing section name to locate images associated with the facility.

- [ ] Update each image title and description to display the new Vet Center name, as well as any alt-text fields, and the Section (assign to new Vet Center section).
- [ ] After updating each item's assigned section to the new Vet Center section, there should no longer be any listed under the old section on the "Media" page.

#### STEP 11: Transfer users to new section
Go to the "People" page and filter by Section = "old Vet Center name". Be sure to check for both Active and Blocked users.

- [ ] Update all users -- whether Active or Blocked -- to the new section and remove the old Vet Center section from their accounts.
- [ ] After updating each user's assigned section to the new Vet Center section, there should no longer be any listed under the old section on the "People" page.

#### STEP 12: Final verification
- [ ] I have verified that there is no longer anything listed under the old Vet Center section on the **Content** page, the **People** page, and the **Media** page, and that all prior listings have been moved to the new Vet Center section.

#### PLEASE CONFIRM WITH FACILITIES TEAM BEFORE DOING THE FOLLOWING

#### STEP 13: Delete former Vet Center name taxonomy term
- [ ] Go to the Section taxonomy and delete the old Vet Center section name. **DO NOT DO THIS IF STEP 12 HAS NOT BEEN COMPLETED.**

     Direct link: https://prod.cms.va.gov/admin/structure/taxonomy/manage/administration/overview 

-----

### After URL re-direct ticket is complete (Embedded Support Team):

#### STEP 14: Final verification
- [ ] Verify that the URL redirect is complete by going to the old URL. Do not proceed if not redirected to new URL.
- [ ] Verify that the name change is visible across the Vet Center homepage, all URL aliases, and any linked nodes, and that the name change is complete.

#### STEP 15: Remove flag
- [ ] Go to facility homepage, click "Edit," then scroll to bottom to remove `Changed name` flag and any other flags. Click Save.

#### STEP 16: Wrap up editor comms
- [ ] Notify associated Vet Center system editor(s) and any other stakeholders that the work is complete by sending the "Vet Center Name Change Complete" email template below.
     
------

## Email templates:

### Vet Center name and section name change verification email

```
The VA Drupal CMS Help Desk Support team has received a notification that the former [OLD NAME OF VET CENTER] has been re-named to the [NEW NAME OF VET CENTER]. 

The facility ID for this Vet Center is [INSERT VET CENTER FACILITY ID]

Here is a direct link to this node: [INSERT LINK TO VET CENTER ON PRODUCTION SITE]

If this Vet Center was not renamed, please let us know at your earliest convenience. Thank you!

```

### Vet Center name change complete
Send once all steps listed above and the URL redirect ticket have been completed:

```
Hello,

The facility previously known as the [OLD NAME OF VET CENTER] has been updated within the CMS to the current title, [NEW NAME OF VET CENTER].

Former URL: [PASTE FORMER URL HERE]
New URL: [PASTE NEW URL HERE]

The previous URL re-directs to the new URL listed above, so anyone who has the old page bookmarked will be automatically taken to the new location.

Please let us know if everything looks good on your end. We recommend reviewing the facility homepage photo in case the outdoor signage has changed.
```
