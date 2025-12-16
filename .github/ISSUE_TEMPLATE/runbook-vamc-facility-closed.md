---
name: Runbook - VAMC Facility closed
about: Steps for fully archiving a VAMC facility from the VA.gov CMS.
title: 'VAMC Facility closed: <insert_name>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  VAMC, sitewide

---
#### Preliminary Info

Facility closure tickets are unique because they can be created _before_ the closed facility has been listed on the [Flagged Facilities page](https://prod.cms.va.gov/admin/content/facilities/flagged). 

This is because VAST updates may take up to 75 days, and we don't want anyone seeking care to drive to a closed location in the meantime. Fortunately, there are steps editors can take within the CMS to inform Veterans that the facility is closed.

**If flagged, but editor has not confirmed the facility closure:** 
Please send all active editors within the facility's section the "Facility Closure Confirmation" email template (see below) before proceeding.

**If notified via Jira or VA stakeholders, but closed facility has not been flagged yet:**
Confirm with editor whether facility closure has been reported to VAST.  For more information, please see the Knowledge Base article titled [How to Archive a Closed VAMC Facility](https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-to-archive-a-closed-vamc-facility) and send to editor if needed.

----------

# Facility and ticket info
- [ ] **Link to facility on production site:** `<insert_facility_link>`
- [ ] **Facility API ID:** `<insert_facility_API_ID>`
- [ ] **VAMC System/Section:** `<insert_vamc_section_name>`
- [ ] **Link to Jira ticket(s):** `<insert_jira_ticket_link>`
     - Embedded Support: Please search Jira and add links to any relevant tickets. If none found, please link once created.
- [ ] **Has the facility been added to the internal Flagged Facilities listing yet?** 
     - If no, please add it to the appropriate tab with the prod link, facility ID, and any relevant ticket links or details. ([Current spreadsheet.](https://docs.google.com/spreadsheets/d/1mqTRGkrnfysFMjC8xTHdFzQOMIQ7WrD2CLX83io5Z74/edit?gid=1358772674#gid=1358772674))

If the help desk is waiting on info or action from facility editor(s), please add the "Awaiting editor" flag to the facility node with a log message including any relevant Jira or Github ticket numbers. Please do not change the moderation state of the node (e.g. "Draft", "Published") when adding or removing flags unless otherwise noted.

----------

# Acceptance criteria for VAMC Facility closures

## Embedded Support team steps:

- [ ] **Step 1:** If facility was flagged as closed but there is no Jira correspondence confirming the closure, send editor "Facility Closure Confirmation" email to verify (See template below).
- [ ] **Step 2:** Ask editor(s) whether the closure has been reported to VAST. If not, please ask them to do so, and send a link to the following KB article: [How do I update my VAMC Facility's Basic Location Data?
](https://prod.cms.va.gov/help/vamc/how-do-i-update-my-vamc-facilitys-basic-location-data)
- [ ] **Step 3:** Whether or not the closure has been reported to VAST, if the closure is confirmed, please send the editor a link to the following KB article and ask them to complete the steps listed: [How to archive a closed VAMC facility](https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-to-archive-a-closed-facility)
- [ ] **Step 4:** Verify that the editor has completed the steps listed in the closed facility KB article before proceeding. 
- [ ] **Step 5:** Open the following link in a new tab to create a URL redirect request ticket: [Create a URL change ticket for the Facilities team](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E).
- [ ] **Step 6:** After creating the URL redirect ticket, please add the link to the top of this ticket and save your changes.

**Note:** Please make sure that the homepage was not archived by the editor, and is still saved as "Published." Otherwise, Veterans who've bookmarked the URL will encounter a "Page Not Found" error. The homepage must stay published until the URL redirect is complete.

## Facilities team steps:
- [ ] **Step 7:**  Execute the URL change request ticket linked above, and notify the Embedded Support team once completed.

## Drupal Administrator steps (Embedded Support team or Facilities team):
- [ ] **Step 8:** Go to the closed homepage's URL on the live site. If the redirect is complete, you'll be taken to the VAMC System's main "Locations" page.
- [ ] **Step 9:** Go to the VAMC Facility node on production (linked above). Click "Edit" and:
     - At the bottom of the page, remove the flag `Removed from source` and any others listed.
     - When adding a revision log message, please link to both Github tickets.
     - Save the facility homepage as "Archived."
- [ ] **Step 10:** The location should disappear from the corresponding VAMC system's "Locations" menu automatically. 
     - If not, please go to Content -> Menu, find the VAMC System the facility is assigned to, and disable the left-nav menu link: https://prod.cms.va.gov/admin/structure/menu

## Embedded Support team wrap-up:
- [ ] **Step 11:** CMS Help Desk team notifies editor and any other stakeholders that the facility closure and archival process is complete -- see "Facility Closure Complete" template below.

----------

# Email templates

## Facility Closure Confirmation
Send if facility closure was flagged but hasn't been confirmed by section editor(s):
```Hello,

The [FACILITY NAME] has been flagged as “closed” on VAST and the Facilities API: [LINK TO PROD HOMEPAGE]

Please confirm whether this location is closed or will be closing soon, or if this is mistaken info. Thank you!

**If the clinic is closed or closing soon:** Please keep Veterans informed by completing the steps listed in the following Knowledge Base article: https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-to-archive-a-closed-facility

**If this is a false report:** Please notify our team, and please contact your VAST administrator as soon as possible. For information on how to do so, please see the following KB article: https://prod.cms.va.gov/help/vamc/how-do-i-update-my-vamc-facilitys-basic-location-data

Once the steps listed in the above Knowledge Base article have been completed, please let us know, and our engineers will finish the archival process from our end. Thank you! 
```

## Facility Closure Complete

```Hello,

The [CLINIC NAME] is now fully archived from [VA.gov](http://va.gov/) and is no longer listed on the Facility Locator.

Site visitors who bookmarked the previous clinic homepage URL will now be re-directed back to the main [SYSTEM NAME] “Locations” page.

Please let us know if you have any questions or concerns, and thank you for your assistance.
```
