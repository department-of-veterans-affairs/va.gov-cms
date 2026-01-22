---
name: Runbook - VAMC Facility closed
about: Steps for fully archiving a VAMC facility from the VA.gov CMS.
title: 'VAMC Facility closed: <insert_name>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  VAMC, sitewide

---
Facility closure tickets are unique because they can be created _before_ the closed facility has been listed on the [Flagged Facilities page](https://prod.cms.va.gov/admin/content/facilities/flagged). 

This is because VAST updates may take up to 75 days, and we don't want anyone visiting a closed location in the meantime. Fortunately, while the homepage remains published, there are steps editors can take to inform Veterans that the facility is closed.

**If flagged, but facility closure has not been confirmed / no Jira tickets found:** 
Please send all active editors within the facility's section the "Facility Closure Confirmation" email template before proceeding (see "Email Templates" section below).

**If notified via Jira or VA stakeholders, but closed facility has not been flagged yet:**
Confirm with editor whether facility closure has been reported to VAST.  For more information, please see the Knowledge Base article titled [How to Archive a Closed VAMC Facility](https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-to-archive-a-closed-vamc-facility) and send to editor if needed.

**If the help desk is waiting on info or actions from facility editor(s):** Please add the "Awaiting editor" flag to the facility node with a revision log message that includes Jira or Github ticket numbers. 

Do not change the moderation state of the node (e.g. "Draft", "Published") when adding or removing flags unless otherwise noted.

----------

## Facility and ticket info
- [ ] **VAMC facility name:** `<vamc_facility>`
- [ ] **VAMC System/Section:** `<vamc_system>`
- [ ] **Link to facility on production site:** `<facility_prod_link>`
- [ ] **Facility API ID:** `<facility_API_ID>`     
- [ ] **Has the facility been added to the internal Flagged Facilities listing yet?** 
     If no, please add it to the appropriate tab with the prod link, facility ID, and any relevant ticket links or details. ([Current spreadsheet.](https://docs.google.com/spreadsheets/d/1mqTRGkrnfysFMjC8xTHdFzQOMIQ7WrD2CLX83io5Z74/edit?gid=1358772674#gid=1358772674))
     
- [ ] **Link to Jira ticket(s):** `<jira_ticket_links>`
     
     **Embedded Support:** Please search Jira and add links to any relevant tickets. If none found, please link once created.
     
- [ ] **Link to URL redirect ticket:** `<url_redirect_link>`
     
     You will not need to insert the URL redirect link until it has been created.
     
**VAMC Facility Closure Knowledge Base article:** [How to archive a closed VAMC facility](https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-to-archive-a-closed-facility)

----------

## Acceptance criteria for VAMC Facility closures

### Embedded Support team steps:

#### STEP 1: Homepage elements to check
- [ ] **Operating Status:** Must be set to "Temporary Facility Closure" with closure message added.
- [ ] **Page Introduction:** Must also indicate facility is now closed.

If either of these homepage elements lack facility closure information, please send editor(s) the "Facility homepage updates needed email" template shown below.

#### STEP 2: Deactivate left-nav menu link
- [ ] From `admin/structure/menu` find the associated VAMC section and click "Edit menu." Scroll down to "Locations" and click "Edit" next to the location in question. From there, clear the checkbox to disable the menu link, and click "Save."

#### STEP 3: Bulk archive all linked VAMC Facility Health Service nodes (DO NOT ARCHIVE HOMEPAGE NODE)

Go to the Bulk Edit page (`admin/content/bulk`), filter by VAMC section, moderation state = "Any", and search for the facility name. Filter for all nodes with content type **VAMC Facility Health Service**, then scroll to the "Action" menu at the bottom of the Bulk Edit page.

- [ ] Click "Archive selected content (Unpublish)" then click "Apply to selected items."
- [ ] After bulk-unpublishing, review the "Health services" list at the bottom of the facility homepage to ensure that all linked VAMC Facility Health Services are archived.

----------

#### DO NOT PROCEED UNTIL EDITOR HAS MADE HOMEPAGE UPDATES LISTED IN STEP 1. 

----------

### Embedded Support team steps (Continued):

#### STEP 4: Create a URL redirect ticket 
- [ ] Open the following link in a new tab to create a URL redirect request ticket: [Create URL redirect request ticket for the Facilities team](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E)

#### STEP 5: Link to parent ticket
- [ ] After creating the URL redirect ticket, please add the link to the top of this ticket and save your changes.

**Important:** Please make sure that the homepage was not archived by an editor, and is still saved as "Published." (If this happens, please edit and re-save the homepage as "Published.")

Otherwise, Veterans who've bookmarked the URL will encounter a 404 "Page Not Found" error. The homepage must stay published until the URL redirect is complete.

----------

### Facilities team steps:
#### STEP 6: URL redirect completion
- [ ] Execute the URL change request ticket linked above, and notify the Embedded Support team once completed.

----------

### Drupal Administrator steps (Embedded Support team or Facilities team):

#### STEP 7: URL redirect verification
- [ ] Go to the closed homepage's URL on the live site. If the redirect is complete, you'll be taken to the VAMC System's main "Locations" page (or whichever page was specified). Do not proceed until redirect is in place and confirmed firsthand.

#### STEP 8: Remove flag and archive facility homepage

- [ ] Go to the VAMC Facility node on production (linked above). Click "Edit" and:
     - At the bottom of the page, remove the flag `Removed from source` and any others listed.
     - When adding a revision log message, please add any relevant Github or Jira ticket numbers.
     - Save the facility homepage as "Archived."

----------

### Embedded Support team final steps:

#### STEP 9: Wrap-up editor comms
- [ ] CMS Help Desk team notifies editor and any other stakeholders that the facility closure and archival process is complete -- see "Facility Closure Complete" template below.

----------

## Email templates

### Facility closure confirmation email
Send if facility closure was flagged but hasn't been confirmed by section editor(s):

```Hello,

The [FACILITY NAME] has been flagged as “closed” on VAST and the Facilities API: [LINK TO PROD HOMEPAGE]

Please confirm whether this location is closed or will be closing soon, or if this is mistaken info. Thank you!

**If the clinic is closed or closing soon:** Please keep Veterans informed by completing the steps listed in the following Knowledge Base article: https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-to-archive-a-closed-facility

**If this is a false report:** Please notify our team, and please contact your VAST administrator as soon as possible. For information on how to do so, please see the following KB article: https://prod.cms.va.gov/help/vamc/how-do-i-update-my-vamc-facilitys-basic-location-data

Once the steps listed in the above Knowledge Base article have been completed, please let us know, and our engineers will finish the archival process from our end. Thank you! 
```

### Facility homepage updates needed email
```
Thank you for your help with the [CLINIC NAME] homepage archival process!

Though the facility is closed, the homepage will remain published until a URL redirect is implemented that will automatically bring visitors to the main "Locations" page for your VAMC section.

This way, any Veterans who had previously bookmarked the URL will be able to quickly find an alternate facility instead of getting a 404 error. 

Please do not archive the facility homepage until we have notified your team that the redirect is in place. Thanks!

In the meantime, we would like to ask that your team make the following quick updates to the facility homepage. Thank you!

1. Operating Status: Please select "Temporary Facility Closure" and add details.

2. Page Introduction: Please add closure information and alternate location info (if available).

We appreciate your help,

```

### Facility closure complete email

```Hello,

The [CLINIC NAME] is now fully archived from [VA.gov](http://va.gov/) and is no longer listed on the Facility Locator.

Site visitors who bookmarked the previous clinic homepage URL will now be re-directed back to the main [SYSTEM NAME] “Locations” page.

Please let us know if you have any questions or concerns, and thank you for your assistance.
```
