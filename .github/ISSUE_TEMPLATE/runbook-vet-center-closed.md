---
name: Runbook - Vet Center closure and section removal
about: Steps for archiving a Vet Center facility and section from the VA.gov CMS.
title: 'Vet Center facility closure / section archival: <insert_name>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  Vet Center, sitewide
assignees: ''

---
Facility closure tickets are unique because they can be created _before_ the closed facility has been listed on the [Flagged Facilities page](https://prod.cms.va.gov/admin/content/facilities/flagged). 

This is because VAST updates may take up to 75 days, and we don't want anyone seeking care to drive to a closed location in the meantime. Fortunately, there are steps editors can take within the CMS to inform Veterans that the facility is closed.

**If flagged, but editor has not confirmed the Vet Center closure:** 
Please send all active editors within the facility's section the "Facility Closure Confirmation" email template (see below) before proceeding.

**If notified via Jira or VA stakeholders, but closed facility has not been flagged yet:**
Confirm with editor whether facility closure has been reported to VAST.

**If the help desk is waiting on info or actions from facility editor(s):** Please add the "Awaiting editor" flag to the facility node with a revision log message that includes Jira or Github ticket numbers. 

Do not change the moderation state of the node (e.g. "Draft", "Published") when adding or removing flags unless otherwise noted.

For any Vet Center sections lacking active editors, please contact Barb Kuhn or the current VA leadership point of contact.

----------

## Facility and Ticket Info
- [ ] **Vet Center facility name:** `<vet_center_facility>`
- [ ] **Vet Center section name:** `<vet_center_section>`
- [ ] **Link to facility on production site:** `<facility_prod_link>`
- [ ] **Facility API ID:** `<facility_API_ID>`
- [ ] **Has the Vet Center closure been added to the internal Flagged Facilities listing yet?** 
     
     If no, please add it to the appropriate tab with the prod link, facility ID, and any relevant ticket links or details. ([Current spreadsheet.](https://docs.google.com/spreadsheets/d/1mqTRGkrnfysFMjC8xTHdFzQOMIQ7WrD2CLX83io5Z74/edit?gid=1358772674#gid=1358772674))
     
- [ ] **Link to Jira ticket(s):** `<jira_ticket_links>`
     
  **Embedded Support:** Please search Jira and add links to any relevant tickets. If none found, please link once created.
     
- [ ] **Link to URL redirect ticket:** `<url_redirect_link>`
     
     You will not need to insert the URL redirect ticket link until the ticket has been created.
     
#### Satellite location closure tickets (i.e. Mobile Vet Centers, Outstations):
- [ ] Please link to any relevant Satellite Location facility closure GitHub ticket(s): `<satellite_location_ticket_links>`

----------

## Acceptance criteria for Vet Center facility closures

### Embedded Support team steps:

#### STEP 1: Homepage elements to check
- [ ] **Operating Status:** Must be set to "Temporary Facility Closure" with closure message added to the "Details" field.
- [ ] **Page Introduction:** Must also indicate facility is now closed.

If either of these homepage elements lack facility closure information, please send editor(s) the "Vet Center homepage updates needed email" template shown below.

#### STEP 2: Confirm URL redirect target

- [ ] Confirm with the content editor(s) and/or Barb Kuhn (or the current Vet Centers point of contact) where the homepage ought to redirect to, once a redirect is set up. You may need to email the editor once more.

#### STEP 3: Create a URL redirect ticket 
- [ ] Open the following link in a new tab to create a URL redirect request ticket: [Create URL redirect request ticket for the Facilities team](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E)

#### STEP 4: Link to parent ticket
- [ ] After creating the URL redirect ticket, please add the link to the top of this ticket and save your changes.

**Important:** Please make sure that the homepage was not archived by an editor, and is still saved as "Published." (If this happens, please edit and re-save the homepage as "Published.")

Otherwise, Veterans who've bookmarked the URL will encounter a 404 "Page Not Found" error. The homepage must stay published until the URL redirect is complete.

----------

### Facilities team steps (Single session):

#### STEP 5: Bulk-editing and re-saving all linked nodes
Go to the [Bulk Edit](https://prod.cms.va.gov/admin/content/bulk) page, filter by the closed Vet Center's section, and select moderation state = "Any."

Select all nodes within the section, then use the "Action" menu at the bottom of the Bulk Edit page to do the following:

   - [ ] **For linked nodes such as Mobile Vet Centers that may be assigned to a new Vet Center section:** If the MVC is still listed under the former Vet Center's section, please use the "Modify Values" action to update the node's associated section, then click "Apply to Selected Items." You will need to perform the URL alias update and re-saving steps listed below after doing this.

   - [ ] **For all other Vet Center nodes that will need to be archived:** Select the "archive selected content" option to archive all selected nodes, and click "Apply to selected items."
     	
   - [ ] **Update URL aliases for all linked nodes:** Click "Update URL alias" then "Apply to selected items."
          
   - [ ] **Re-save all nodes:** Select "Re-save content" then click "Apply to selected items".

#### STEP 6: Complete URL redirect request
- [ ] Execute the URL change request ticket linked above, and notify the Embedded Support team once completed.

----------

### Drupal Administrator steps (Embedded Support team or Facilities team):

#### STEP 7: Complete URL redirect request
- [ ] Go to the closed homepage's URL on the live site. If the redirect is complete, you will be taken to an alternate page instead of reaching a "Page Not Found" error. Do not proceed until redirect has been confirmed.

#### STEP 8: Reassign users to new section
Go to the "People" page and filter by Section = "old Vet Center name". Be sure to check for both Active and Blocked users.

- [ ] Update all users -- whether Active or Blocked -- to the new section and remove the closed Vet Center section from their accounts. 

- [ ] After updating each user's assigned section to the new Vet Center section, there should no longer be any listed under the old section on the "People" page.

If you do not know where to reassign each account, you may need to ask the editor(s) or ask Barb Kuhn / VA Leadership.

#### STEP 9: Verify all nodes have been archived and all users have been transferred
- [ ] Check the Content page to ensure that all nodes linked to this Vet Center have been archived.

#### STEP 10: Move media to alternate section (If requested)
Go to Content -> [Media](https://prod.cms.va.gov/admin/content/media/images) ( `admin/content/media/images` )

Filter the Media page by the closed Vet Center section's name to locate images associated with the facility.

- [ ] Update each image title and description to display the new Vet Center name, as well as any alt-text fields, and the Section (assign to different Vet Center section).

- [ ] After updating each item's assigned section to the new Vet Center section, there should no longer be any listed under the old section on the "Media" page.

#### PLEASE CONFIRM WITH FACILITIES TEAM BEFORE DOING THE FOLLOWING

#### STEP 11: Delete former Vet Center name taxonomy term
- [ ] Go to the Section taxonomy and delete the old Vet Center section name.

     Direct link: https://prod.cms.va.gov/admin/structure/taxonomy/manage/administration/overview 

#### STEP 12: Remove flag
Go to the Vet Center Facility node on production. Click "Edit" and:

- [ ] At the bottom of the page, remove the flag `Removed from source` and any others listed. When adding a revision log message, please list ticket numbers of any Github/Jira tickets.

- [ ] Save the facility homepage as "Archived" once more.

#### STEP 13: Verify section removal
- [ ] Confirm that the section is no longer visible within the dropdown menus on either the Content Page or People page.

#### STEP 14: Verify URL redirect
- [ ] Verify that the URL redirect is complete by going to the old URL. Do not proceed if not redirected to new URL.

----------

### Embedded Support team wrap-up:
#### STEP 15: Wrap up editor comms
- [ ] CMS Help Desk team notifies editor and any other stakeholders that the facility closure and archival process is complete -- see "Facility Closure Complete" template below.

----------

## Email templates

### Vet Center facility closure confirmation email
Send if facility closure was flagged but hasn't been confirmed by section editor(s):

```

The [VET CENTER NAME] has been flagged as “closed” on VAST and the Facilities API: [LINK TO PROD HOMEPAGE]

Please confirm whether this location is closed or will be closing soon, or if this is mistaken info. Thank you!

**If this Vet Center is closed or closing soon:** Please keep Veterans informed by editing the homepage introduction and Operating Status with closure info, as well as info about alternative locations (if available).

**If this is a false report:** Please notify our team, and please contact your VAST administrator as soon as possible. 

```
### Vet Center homepage updates needed email
```
Thank you for your help with the [VET CENTER NAME] homepage archival process!

Though the facility is closed, the homepage will remain published until a URL redirect is implemented that will bring visitors to [INSERT ALTERNATE PAGE]

This way, any Veterans who had previously bookmarked the homepage URL will be able to quickly find an alternate Vet Center instead of getting a 404 error. 

Please do not archive the Vet Center homepage until we have notified your team that the redirect is in place. Thanks!

In the meantime, we would like to ask that your team make the following quick updates to the Vet Center homepage. Thank you!

1. Operating Status: Please select "Temporary Facility Closure" and add details.

2. Page Introduction: Please add closure information and alternate location info (if available).

We appreciate your help,

```

### Vet Center facility closure complete

```Hello,

The [VET CENTER NAME] is now fully archived from [VA.gov](http://va.gov/) and is no longer listed on the Facility Locator.

Site visitors who bookmarked the previous homepage URL will now be re-directed to [INSERT REDIRECT URL].

Please let us know if you have any questions or concerns, and thank you for your assistance.
```
