---
name: Runbook - VAMC Facility name change
about: Steps for updating names and URLs
title: 'VAMC Facility name change: FROM <former_name> TO <new_name>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  VAMC, sitewide

---
**Before you begin:** Please do not create this ticket until the VAMC Facility name change has appeared on the CMS [Flagged Facilities page](https://prod.cms.va.gov/admin/content/facilities/flagged) **and** has been confirmed by an editor or VA stakeholder.

**If flagged, but editor has not confirmed the name change:** 
Please send all active editors within the facility's section the "Name Change Confirmation" email template (see below) before proceeding.

**If confirmed by editor or VA stakeholders, but not yet flagged:** 
Ask editor whether they have reported the intended name change to VAST.  Once reported, changes made within VAST can take up to 75 days to appear within the Facilities API. 

Please refer to the KB article [How do I update my VAMC Facility's Basic Location Data?](https://prod.cms.va.gov/help/vamc/how-do-i-update-my-vamc-facilitys-basic-location-data) for more info, and send to editor if needed.

**If the help desk is waiting on info or actions from facility editor(s):** Please add the "Awaiting editor" flag to the facility node with a revision log message that includes Jira or Github ticket numbers. 

Do not change the moderation state of the node (e.g. "Draft", "Published") when adding or removing flags unless otherwise noted.

-------

## Facility and ticket info
- [ ] **VAMC System/Section:** `<vamc_system>`
- [ ] **Former VAMC facility name:** `<former_vamc_facility_name>`
- [ ] **New VAMC facility name:** `<new_vamc_facility_name>`
- [ ] **Link to facility on production site:** `<facility_prod_link>`
- [ ] **Facility API ID:** `<facility_API_ID>`
- [ ] **Has the facility been added to the internal Flagged Facilities listing yet?** 
     If no, please add it to the appropriate tab with the prod link, facility ID, and any relevant ticket links or details. ([Current spreadsheet.](https://docs.google.com/spreadsheets/d/1mqTRGkrnfysFMjC8xTHdFzQOMIQ7WrD2CLX83io5Z74/edit?gid=1358772674#gid=1358772674))
- [ ] **Link to Jira ticket(s):** `<jira_ticket_links>`
     
     **Embedded Support:** Please search Jira and add links to any relevant tickets. If none found, please link once created.
     
- [ ] **Link to URL redirect ticket:** `<url_redirect_link>`
     
     You will not need to insert the URL redirect link until it has been created.

----

## Acceptance criteria for VAMC Facility name changes

### Embedded Support team steps:
#### STEP 1: Confirm name change on facility homepage
- [ ] Confirm the new title is displayed as the H1 header on the VAMC Facility homepage on the production site.

#### STEP 2: Create a URL redirect ticket 
- [ ] Open the following link in a new tab to create a URL redirect request ticket: [Create URL redirect request ticket for the Facilities team](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E)

#### STEP 3: Link to parent ticket
- [ ] After creating the URL redirect ticket, please add the link to the top of this ticket and save your changes.

-------

The following steps are _not_ blocked by the URL redirect ticket and may be completed simultaneously:

### Drupal Administrator steps (Embedded Support team or Facilities team):
Please complete the following steps in one session to ensure all changes publish together in the next content release.

#### STEP 4: Facility homepage edits
Go to the VAMC Facility node on production (linked above). Click "Edit" and locate the following fields:

- [ ] **Introduction:** Update facility name if mentioned.
- [ ] **Meta Description:** (`"Get address and hours..."`) 

     - Scroll to the end of this field to update the facility name and add the text ```"formerly known as [previous name]."```

- [ ] **Menu Link Title:** Update the "Menu Link Title" field with the new facility name (Right-side panel of the page).

#### STEP 5: Update homepage URL alias
While editing the facility homepage, scroll down to the "URL alias" field  (Bottom of right-side panel).

- [ ] Delete the current URL alias displayed in this field, and check ✔️ "Generate automatic URL alias."

- [ ] Click "Publish" to save all changes from Steps 4 and 5. Do not change the moderation state of the page.


#### STEP 6: VAMC System "Locations" menu update 
Go to Content -> [Menus](https://prod.cms.va.gov/admin/structure/menu) ( `admin/structure/menu` )

- [ ] From the Menus page, find the associated VAMC section and click "Edit menu." Scroll down to "Locations" and use the four-pointed arrow to move the facility to the correct location in the menu. After editing the menu, click "Save."

     - Note: Per VA system design standards, medical centers are listed first, then clinics, both in alphabetical order.

#### STEP 7: Update Facility photo alt-text
Go to Content -> [Media](https://prod.cms.va.gov/admin/content/media/images) ( `admin/content/media/images` ) and search for the _former_ name of the facility to locate the facility's homepage photo.

- [ ] Update the title and alt text with the new facility name. Click Save.

#### STEP 8: Bulk editing all linked facility nodes
Go to the [Bulk Edit](https://prod.cms.va.gov/admin/content/bulk) page (`/admin/content/bulk`), filter by section, moderation state = "Any", and search for the _former_ name of the VAMC Facility.

Filtering by section helps to avoid selecting nodes linked to the wrong facility -- for example, there are 3 different "Springfield VA Clinic" facilities in 3 different sections.

Select all relevant nodes and use the "Action" menu at the bottom of the Bulk Edit page to do the following:

- [ ] **Bulk-edit action 1:** Update URL alias (Click "Apply to selected items")

- [ ] **Bulk-edit action 2:** Re-save content (Click "Apply to selected items")

#### STEP 9: After the next content release goes out:

- [ ] Verify that the URL alias, menu link breadcrumbs, left-nav "Locations" menu link, and VAMC Facility Health Services have been updated accordingly.

-------

### After URL re-direct ticket is completed (Embedded Support Team):

#### STEP 10: Verify URL redirect
- [ ] Verify that URL redirect is complete by going to the old URL. Do not proceed if not redirected to new URL.

#### STEP 11: Remove flag
- [ ] Go to facility homepage, click "Edit," then scroll to bottom to remove `Changed name` flag and any other flags. Click Save.

#### STEP 12: Wrap up editor comms
- [ ] Notify associated VAMC system editor(s) and any other stakeholders that the work is complete by sending the "Name Change Complete" email template below.

------

## Email templates

### Name Change Confirmation
Send if name change was flagged but hasn't been confirmed by section editor(s):

```
Hello,

Our team received a facility name change notification for a location in your system.

**Former name:** [INSERT FORMER NAME]
**New name:** [INSERT NEW NAME]

Can your team please let us know if the updated clinic name is correct? Thank you!

Once you’ve confirmed that the name change is correct, our engineering team can proceed with next steps such as updating the URL and menu links. 

If this name change is incorrect, please contact your VAST administrator. For more information, please see the following Knowledge Base article: https://prod.cms.va.gov/help/vamc/how-do-i-update-my-vamc-facilitys-basic-location-data
```
### Name Change Complete
Send once all steps listed above and the URL redirect ticket have been completed:

```
Hello,

The facility previously known as the [FORMER TITLE] has been updated within the CMS to the current title, [NEW TITLE].

Former URL: [PASTE FORMER URL HERE]
New URL: [PASTE NEW URL HERE]

The previous URL re-directs to the new URL listed above, so anyone who has the old page bookmarked will be automatically taken to the new location.

Please let us know if everything looks good on your end. We recommend reviewing the facility homepage photo in case the outdoor signage has changed.
```
