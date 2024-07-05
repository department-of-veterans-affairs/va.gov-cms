---
name: Runbook - New Vet Center Facility
about: changing facility information in the CMS for Vet Center facilities
title: 'New Vet Center Facility: <insert_name_of_facility>'
labels: Change request, Vet Center, Facilities, Flagged Facilities, User support, Drupal engineering
assignees: ''

---

## Description
Use this runbook if: a Vet Center, Mobile Vet Center, or Vet Center Outstation is flagged as New, OR if an existing facility is flagged as New with a new Facility API ID.  
- This may also occur when an Outstation is promoted to main Vet Center or if an existing Mobile Vet Center is reallocated to a different Vet Center 

## Intake
- [ ] What triggered this runbook? (Flag in CMS, Help desk ticket, Product team, VHA Digital Media)
Trigger: <insert_trigger>

- [ ] Link to associated help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] If the submitter is an editor, send them links to any relevant KB articles for the Vet Center product.
KB articles: <insert_kb_article_links>

- [ ] Link to facility in production:
Facility CMS link: <insert_facility_link>
Facility API ID: <insert_facility_API_ID>

## Acceptance criteria

### New Vet Center
[@TODO: KB ARTICLE FOR ADDING VET CENTERS - SEE runbook-vamc-facility-new]

#### CMS help desk steps
**Note: If the help desk is waiting on information from the facility staff or editor, add the `Awaiting editor` flag to the facility** with a log message that includes a link to this ticket. Remove the flag when the ticket is ready to be worked by the Facilities team. **Be sure to preserve the current moderation state of the node when adding or removing the flag.**

**If a Mobile Vet Center:**
- [ ] Confirm the Vet Center to which it belongs and and set the "Main Vet Center location" field. The parent location may be derived by the Facility ID.
- [ ] Follow up with Barb Kuhn/RCS Central office to let her know we've identified this new Mobile unit and confirm the District, Vet Center Director, and Outreach Specialist (names/email addresses) who will be responsible for updates
- [ ] Contact the Vet Center editor to remind them to (1) add a photo of the Mobile Vet Center and then they can publish when ready and (2) remind them that if this Mobile Vet Center is used by any other facilities to communicate with those Vet Center editors

**If a Outstation:**
- [ ] Become aware that the new facility is now on the Facility API (typically, via a Flag).
- [ ] Confirm the Vet Center to which it belongs and set the "Main Vet Center location" field. The parent location may be derived by the Facility ID.
- [ ] Follow up with Barb Kuhn/RCS Central office to let her know we've identified the new Outstation and confirm the District, Vet Center Director, and Outreach Specialist (names/email addresses) who will be responsible for updates
- [ ] Contact the Vet Center editor to remind them to (1) add a photo of the Outstation
- [ ] If the new Outstation replaces a CAP, the editor should consider updating the operating status for the CAP to direct Veterans to the new location with “as of” date and set a reminder on the calendar to archive the CAP 30 days after the new location has opened   

**If a Vet Center:**
- [ ] Become aware that the new facility is now on the Facility API (typically, via a Flag).
- [ ] Check with Readjustment Counseling Services to (1) confirm what district the Vet Center belongs, (2) identify the Vet Center Director and Outreach Specialist (names/email addresses), (3) confirm the new location isn't a replacement for an existing Outstation
- [ ] In [Sections taxonomy](https://prod.cms.va.gov/admin/structure/taxonomy/manage/administration/overview), move the Vet Center Section to the appropriate district.
- [ ] Create account access as directed by RCS. If editors are new to Drupal, create accounts with editor rights only for Vet Center Director and Outreach Specialist so that they cannot publish on their own.
- [ ] Contact Vet Center Director and Outreach specialist to onboard for training [@TODO write sample email - SEE runbook-vamc-facility-new] **Note: this should include instructions for adding content and preparing for publishing and RCS Central Office should be included as CC**
- [ ] Add flag `Awaiting editor` to this facility. Note: This is now blocked until RCS Central office approves.
- [ ] **Once approved by RCS Central Office as complete, proceed to Drupal Admin publishing steps**


**Drupal Admin steps**
- [ ] Bulk publish the nodes and facility.
- [ ] Contact Lighthouse via Slack at #cms-lighthouse channel that this facility requires a canonical link in the following format (replacing the placeholder data with the actual API Id and VA.gov URL):
  - `vha_691GM,https://www.va.gov/greater-los-angeles-health-care/locations/oxnard-va-clinic/`
- [ ] Add the "Awaiting CSV" flag to the facility node with a revision log message that includes a link to this ticket.
- [ ] Let Help desk know this has been done, if not done by Help desk.

#### Wait (days or weeks, potentially)
- [ ] After the canonical link has been added to the websites.csv and you have confirmation from Lighthouse that the CSV has been deployed, validate that the change has deployed by checking that the Facility Locator has been updated with the new url.
- [ ] Update this ticket with a comment that the CSV change has been deployed.
- [ ] Edit facility node and remove `New facility` and "Awaiting CSV" flags with a revision log message that includes a link to this ticket.
- [ ] Let Help desk know this has been done, if not done by Help desk.

#### CMS Help desk (wrap up)
- [ ] 11. Upgrade the Vet Center Director and Outreach Specialist accounts to the publisher role for that Vet Center.
- [ ] 12. Notify editor and any other stakeholders.
