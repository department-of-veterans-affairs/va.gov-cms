---
name: Runbook - New Vet Center Facility (and Section)
about: changing facility information in the CMS for Vet Center facilities
title: 'New Vet Center Facility/Section: <new_vet_center_name>'
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
- [ ] **Vet Center facility name:** `<vet_center_facility>`
- [ ] **New Vet Center section name:** `<new_vet_center_section>`
- [ ] **Link to facility on production site:** `<facility_prod_link>`
- [ ] **Facility API ID:** `<facility_API_ID>`     
- [ ] **Has the new Vet Center been added to the internal Flagged Facilities listing yet?** 
     
     If no, please add it to the appropriate tab with the prod link, facility ID, and any relevant ticket links or details. ([Current spreadsheet.](https://docs.google.com/spreadsheets/d/1mqTRGkrnfysFMjC8xTHdFzQOMIQ7WrD2CLX83io5Z74/edit?gid=1358772674#gid=1358772674))
- [ ] **Link to Jira ticket(s):** `<jira_ticket_links>`
     
     **Embedded Support:** Please search Jira and add links to any relevant tickets. If none found, please link once created.

To find out which content editors should be assigned to this Vet Center, contact Barb Kuhn or the current Vet Centers VA point of contact once the new section has been created.

----

## Acceptance criteria for new Vet Center facilities

### Drupal Administrator steps (Embedded Support team or Facilities team):

#### STEP 1: Verify new section approval
- [ ] Please verify with the Facilities Team and/or VA Leadership that the new Vet Center is approved to become a new section within the CMS. 

     - Information may be provided within existing Jira tickets. Please compile all information and cross-link all tickets. You may also link to Slack threads within the comments of this ticket.

#### STEP 2: Verify new Vet Center district and points of contact
- [ ] Confirm the new Vet Center's district (1, 2, 3, 4, 5) and log the name and email address of the Vet Center Director and Outreach Specialist, if applicable, within internal notes.

------

### Facilities team steps:

#### STEP 3: Add new section
- [ ] Complete the following steps to add the new Vet Center section to the Sections taxonomy and assign it to the correct district. 

	Path: `/admin/structure/taxonomy/manage/administration/overview`

	- Product: "Vet Center." 
	- The name of the Vet Center is required but the "Description" field can be left blank.
	- Under "Relations" the parent term should be the District (i.e. District 1).
	- Click "Save."

#### STEP 4: Create new "Locations List" node
- [ ] From "Content -> Add Content" create a new "Vet Center - Locations List" node, assign it to the newly created Section, and save it as a Draft.

------

### Embedded Support team steps:
 
#### STEP 5: Review new homepage and section
- [ ] Double-check all URL aliases, breadcrumbs, and menu links for new Vet Center and section and report any issues to Facilities engineers.

#### STEP 6: Create Vet Center editor accounts
- [ ] Create new CMS accounts assigned to the new Vet Center section for the associated editors. If you do not know who should be assigned to this facility, please contact Barb Kuhn, Vet Centers VA leadership, or the Facilities team.

#### STEP 7: Ask editor to add necessary homepage elements
Link to Vet Center Editor Guide, if needed for content editors: `https://prod.cms.va.gov/help/vet-centers/vet-center-editor-guide`

- [ ] Send "Vet Center homepage edits needed" email to associated content editors (see Email Templates section at bottom of ticket).

#### STEP 8: Verify homepage elements
Please make sure the new Vet Center homepage has the following list added before publishing day:

   - [ ] A facility photo
   - [ ] Vet Center services
   - [ ] "Prepare For Your Visit" information
   - [ ] Satellite locations, if applicable (optional)
     
You may need to follow up with the associated editor(s) using the "Vet Center follow-up reminder" email template provided below.

#### STEP 9: Publishing day -- bulk publishing all linked nodes
- [ ] Go to the Bulk Edit page (`admin/content/bulk`), filter by the new Vet Center section name, moderation state = "Any".
- [ ] Select all nodes, then scroll to the "Action" menu at the bottom of the Bulk Edit page.
- [ ] Click "Publish latest revision" then click "Apply to selected items."
- [ ] After bulk-publishing, double-check all linked Vet Center nodes to ensure that they've successfully published.

#### STEP 10: After the next content release goes out
- [ ] Verify that the URL for the new Vet Center is working on the live site and that associated nodes such as the "Locations" page and any satellite locations are linked properly.

------

### Embedded Support team steps:

#### STEP 11: Remove flag
- [ ] Go to the main Vet Center homepage, click "Edit," then scroll to bottom to remove the `New facility` flag and any other flags. Click Save.

#### STEP 12: Wrap-up editor comms
- [ ] Help Desk team informs content editors and/or Barb Kuhn that process is complete.
     
------

## Email templates:

### New Vet Center reported
Send if a new Vet Center has been reported via the Flagged Facilities page but there is no existing communication confirming that this Vet Center vehicle is in use:

```
A new Vet Center, [NAME OF VET CENTER], has been added to the VA.gov CMS. 

The Facility ID associated with it is: [INSERT VET CENTER ID]

For your convenience, here is a direct link to this node: [INSERT PRODUCTION URL FOR Vet Center NODE]

Please confirm whether this information is correct, and please let our team know whether an opening date has been determined. Thank you!

This node is currently saved as a “Draft” and the info is not yet visible on the live site. Before the new Vet Center homepage is published, it will need Vet Center services, "Prepare For Your Visit" info, and a facility info.

For more information about required details, please see the following Knowledge Base article: https://prod.cms.va.gov/help/vet-centers/vet-center-editor-guide
```
### Vet Center homepage edits needed
Send if the Vet Center's content editors have not added Vet Center Services, a photo, "Prepare For Your Visit" info, and/or satellite locations (optional) to the homepage before opening day.

```

Hello! Before the new [INSERT NAME OF VET CENTER] homepage can be published to the live site, there is information that must be added within the CMS:

Please add:
- Vet Center Services
- A facility photo
- "Prepare For Your Visit" information
- Satellite locations such as Mobile Vet Centers or Outstations, if applicable (Optional)

For assistance, please refer to the Vet Center Editor Guide in the CMS Knowledge Base (We recommend bookmarking this page for future reference!): https://prod.cms.va.gov/help/vet-centers/vet-center-editor-guide

For your convenience, here is a direct link to this node: [INSERT PRODUCTION URL FOR Vet Center NODE]
```

### Vet Center follow-up reminder
Send if the Vet Center's content editors require a second reminder to add Vet Center Services, a photo, "Prepare For Your Visit" info, and/or satellite locations (optional) to the homepage before opening day.

```

Hello! The new [INSERT NAME OF VET CENTER] will be opening on [OPENING DATE]. However, the homepage is still missing some crucial components that are necessary for Veterans accessing care at this location.

Please add:
- Vet Center Services
- A facility photo
- "Prepare For Your Visit" information
- Satellite locations such as Mobile Vet Centers or Outstations, if applicable (Optional)

For assistance, please refer to the Vet Center Editor Guide in the CMS Knowledge Base (We recommend bookmarking this page for future reference!): https://prod.cms.va.gov/help/vet-centers/vet-center-editor-guide

For your convenience, here is a direct link to this node: [INSERT PRODUCTION URL FOR Vet Center NODE]
```
