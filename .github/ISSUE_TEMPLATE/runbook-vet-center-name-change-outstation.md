---
name: Runbook - Vet Center Outstation Name Change
about: Runbook for Vet Center Outstation name changes on the VA Drupal CMS
title: 'Vet Center Outstation name change: FROM <old_outstation_name> TO <new_outstation_name>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  Vet Center, sitewide

---
**Before you begin:** Please do not create this ticket until the Vet Center Outstation name change has appeared on the CMS [Flagged Facilities page](https://prod.cms.va.gov/admin/content/facilities/flagged).

**If notified via Jira but not flagged in the CMS:**
Confirm with the editor that they reported the Outstation name change to VAST. Please refer to the KB article "[How do I update my Vet Center facility's basic location data?](https://prod.cms.va.gov/help/vet-centers/how-do-i-update-my-vet-center-facilitys-basic-location-data)" for more info and send to editor if needed.

**If reported to VAST but not listed yet:** Changes made within VAST can take up to 75 days to appear within the Facilities API.

**If the help desk is waiting on info or actions from facility editor(s):** Please add the "Awaiting editor" flag to the facility node with a revision log message that includes Jira or Github ticket numbers. 

Do not change the moderation state of the node (e.g. "Draft", "Published") when adding or removing flags unless otherwise noted.

For any Vet Center sections lacking active editors, please contact Barb Kuhn or the current VA leadership point of contact.

----------

## Facility and Ticket Info
- [ ] **Former Outstation name:** `<former_outstation_name>`
- [ ] **New Outstation name:** `<new_outstation_name>`
- [ ] **Link to facility on production site:** `<facility_prod_link>`
- [ ] **Facility API ID:** `<facility_API_ID>`

**If updating section:**

- [ ] **Former Vet Center section name:** `<former_vet_center_section_name>`
- [ ] **New Vet Center section name:** `<new_vet_center_section_name>`

**If linked to main Vet Center name change:**

- [ ] Link to parent Vet Center name change ticket: `<insert_vet_center_name_change_Github_ticket>`

- [ ] **Has the Outstation name change been added to the internal Flagged Facilities listing yet?** 
     
     If no, please add it to the appropriate tab with the prod link, facility ID, and any relevant ticket links or details. ([Current spreadsheet.](https://docs.google.com/spreadsheets/d/1mqTRGkrnfysFMjC8xTHdFzQOMIQ7WrD2CLX83io5Z74/edit?gid=1358772674#gid=1358772674))
     
- [ ] **Link to Jira ticket(s):** `<jira_ticket_links>`
     
     **Embedded Support:** Please search Jira and add links to any relevant tickets. If none found, please link once created. 

URL redirects are not needed for Mobile Vet Center and Vet Center Outstation tickets because they do not have outward-facing URLs. 

However, you will still need to update and re-save the URL alias for this Outstation node. Otherwise, it will not be linked to the main Vet Center properly, and will not show up on the Vet Center's "Locations" page. Instructions listed below.

----

## Acceptance criteria for Outstation (Outstation) facility name changes

### Embedded Support team steps:
#### STEP 1: Confirm name change on facility homepage
First, please note the H1 title of the Outstation node on the production site. From there, please inspect the node's URL and the "Main Vet Center" and "Section" fields.

Does the section listed in the node's H1 header match the Vet Center section listed in the URL, as well as the "Main Vet Center" and "Section" fields?

- [ ] **Yes**: No work needed, proceed to next steps.
		
- [ ] **No, but this is because the main Vet Center is currently undergoing a name change**: STOP! Do not proceed with Outstation name change until the main Vet Center name change ticket is complete.

- [ ] **No, because the Outstation may have been re-assigned from the City 1 Vet Center to the City 2 Vet Center**: The Embedded Support team will need to send the editor(s) associated with both the former section _and_ the new section the "Outstation section change verification email" template provided below. 

**If the URL ends in -0**: This node may be a duplicate of an existing Outstation. Please verify by removing the `-0` to visit the original Outstation URL. If both are listed under the same section, Embedded Support will need to email the associated Vet Center Section editor(s) to confirm which node has the correct ID (see "Duplicate Outstation email" below).

-------

### Drupal Administrator steps (Embedded Support team or Facilities team):

#### STEP 2: Update Section and Main Vet Center
- [ ] After verifying the correct section assignment, please edit the Outstation node and update the **Common name**, **"Main Vet Center"** and **"Section"** fields. Click Save.

#### STEP 3: Bulk-edit page updates
Go to the [Bulk Edit](https://prod.cms.va.gov/admin/content/bulk) page, filter by section, moderation state = "Any", and search for the Outstation.

Select the node, then use the "Action" menu at the bottom of the Bulk Edit page to do the following in one session:

   - [ ] **Update the Outstation's assigned Vet Center section:** If the Outstation is still listed under the former Vet Center's section, please use the "Modify Values" action to update the section, then click "Apply to Selected Items."

   - [ ] **Update the node's URL alias:** Click "Update URL alias" then "Apply to selected items"
          
   - [ ] **Re-save the node:** Select "Re-save content" then click "Apply to selected items".

#### STEP 4: Update facility media
Go to Content -> [Media](https://prod.cms.va.gov/admin/content/media/images) ( `admin/content/media/images` )

- [ ] Check the "Media" page to ensure that any images of this facility are updated with the new Section and name in their title and alt-text fields. ( https://prod.cms.va.gov/admin/content/media )

#### STEP 5: URL/Section Update Acceptance criteria:
- [ ] After the next content release goes out, please go to the new Vet Center's "Locations" page to ensure that the Outstation is properly linked. 
	   
The Outstation should be listed under the "Main and Satellite Locations" header on the new Vet Center's "Locations" node on the production site. If published, it will also be listed beneath the new Vet Center's "Satellite Locations" header on the live site.

------

## Embedded Support team steps:
#### STEP 6: Editor comms

- [ ] Reach out to the associated Vet Center editor(s) via Jira and send the "Section change completed" email template available below.

     - **If the Outstation lacks a photo:** Please ask the editor to add a photo as soon as possible.

     - **If the Outstation node is unpublished:** Please remind the editor that the Outstation will not be listed on their Vet Center's "Locations" page on the live site unless this node is saved as "Published."

#### STEP 7: Remove flag
- [ ] Once the editor has finished adding a photo and publishing the node, go to the Outstation homepage, click "Edit," then scroll to bottom to remove the `Changed name` flag and any other flags. Click Save.
     
------

## Email templates:

### Outstation section change verification email
Send if an Outstation node appears to have moved from one section to another:

```
The VA Drupal CMS Help Desk Support team received a notification that the former [OLD NAME OF OUTSTATION] has been re-named to the [NEW NAME OF OUTSTATION]. 

The facility ID for this Outstation is vc_####OS (Outstation ####). 

Here is a direct link to this node: [INSERT LINK TO Outstation ON PRODUCTION SITE]

If this Outstation was not re-assigned, please let us know at your earliest convenience. Thank you!

 -- IF THERE IS A SECOND OUTSTATION NODE NOW LINKED TO THIS OUTSTATION'S FORMER VET CENTER -- 

[FORMER VET CENTER] team: 

Please note that there is also a separate Outstation listed under facility ID vc_####OS and assigned to your section.

Is this Outstation assigned correctly? Thank you!

```

## Section change completed email

``` 
Thank you for letting us know that Outstation #### was re-assigned. This Outstation is now assigned to your Vet Center section within the CMS.

Here is a link to the Outstation’s homepage, where you can verify the info listed and update the photo: [INSERT LINK TO Outstation NODE ON PRODUCTION SITE]

```

## Duplicate Outstation email 
Send if there is a new Outstation that has been added to the VA.gov CMS that is a potential duplicate (i.e. same name/section as a different Outstation node and has `-0` at the end of the URL).

```Hello,

We hope you’re doing well!

It looks like there are two entries within our system for the Outstation associated with your location:

- vc_####OS (Outstation #___)

- vc_####OS (Outstation #___)

Can you please verify which of these two IDs matches the [NAME OF OUTSTATION]? Thank you!
```
