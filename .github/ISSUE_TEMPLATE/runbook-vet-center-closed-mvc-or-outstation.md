---
name: Runbook - Closed Mobile Vet Center or Outstation
about: Runbook for closed Mobile Vet Center and Vet Center Outstation facilities on the VA Drupal CMS
title: 'Closed Vet Center MVC/Outstation: <closed_facility_name>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  Vet Center, sitewide

---

### STOP! DO NOT CREATE THIS TICKET IF THE LINKED VET CENTER HAS CLOSED! Simply archive the MVC or Outstation homepage node and remove the flag. Afterward, please note this in the main Vet Center closure ticket.

**Before you begin:** If the linked Vet Center is still open, please do not create this ticket until the Mobile Vet Center or Vet Center Outstation closure has appeared on the CMS [Flagged Facilities page](https://prod.cms.va.gov/admin/content/facilities/flagged).

**If notified via Jira but not flagged in the CMS:**
Confirm with the editor that they reported the closure to VAST. Please refer to the KB article "[How do I update my Vet Center facility's basic location data?](https://prod.cms.va.gov/help/vet-centers/how-do-i-update-my-vet-center-facilitys-basic-location-data)" for more info and send to editor if needed.

**If reported to VAST but not listed yet:** Changes made within VAST can take up to 75 days to appear within the Facilities API.

**If the help desk is waiting on info or actions from facility editor(s):** Please add the "Awaiting editor" flag to the facility node with a revision log message that includes Jira or Github ticket numbers. 

Do not change the moderation state of the node (e.g. "Draft", "Published") when adding or removing flags unless otherwise noted.

For any Vet Center sections lacking active editors, please contact Barb Kuhn or the current VA leadership point of contact.

----------

# Facility and Ticket Info
- [ ] **MVC or Outstation name:** `<mvc_or_outstation_name>`
- [ ] **Vet Center section name:** `<vet_center_section>`
- [ ] **Link to facility on production site:** `<facility_prod_link>`
- [ ] **Facility API ID:** `<facility_API_ID>`

- [ ] **Link to Jira ticket(s):** `<jira_ticket_links>`
     
 **Embedded Support:** Please search Jira and add links to any relevant tickets. If none found, please link once created.
     
You do not need to create any redirect tickets for Mobile Vet Center nodes, because they do not have direct URLs -- these nodes are nested within the linked Vet Center's "Locations" page.

----

## Acceptance criteria for closed Mobile Vet Center or Vet Center Outstation facilities

### Drupal Administrator steps (Embedded Support team or Facilities team):

#### STEP 1: Confirm closure
- [ ] Confirm with the main Vet Center's editor(s) or with Barb Kuhn/VA Leadership that the MVC or Outstation is now closed. (See "Facility closure confirmation" email template below.)

#### STEP 2: Archive node
- [ ] When editing the facility node, select the moderation state "Archived." Click "Save."

#### STEP 3: Verify archival
- [ ] After the next content release goes out, go to the main Vet Center's "Locations" page to ensure that the archived location is no longer displayed beneath the "Satellite Locations" header.

#### STEP 4: Remove flag
- [ ] Go back to the Mobile Vet Center or Outstation node, click "Edit," then scroll to bottom to remove the `Removed from source` flag and any other flags. Click Save.

----------
 
### Embedded Support team wrap-up:
#### STEP 5: Editor comms
- [ ] CMS Help Desk team notifies editor and any other stakeholders that the facility closure and archival process is complete -- see "Facility Closure Complete" template below.

----------

## Email templates

### Facility closure confirmation
Send if facility closure was flagged but hasn't been confirmed by section editor(s):

```
The [MVC OR OUTSTATION NAME] has been flagged as “closed” on VAST and the Facilities API: [LINK TO PROD HOMEPAGE]

Please confirm whether this location is closed, or if this is incorrect. Thank you!

```

### Facility closure complete

```Hello,

The [MVC OR OUTSTATION NAME] is now fully archived from [VA.gov](http://va.gov/).

Site visitors will no longer see this facility listed on your Vet Center's “Locations” page.

Please let us know if you have any questions or concerns, and thank you for your assistance.
```
