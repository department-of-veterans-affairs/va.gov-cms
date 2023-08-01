---
name: Runbook - New Vet Center Facility
about: changing facility information in the CMS for Vet Center facilities
title: 'New Vet Center Facility: <insert_name_of_facility>'
labels: Change request, Vet Center, Facilities, User support, VA.gov frontend, Drupal engineering
assignees: ''

---

## Description
Use this runbook if: a Vet Centers, Mobile Vet Centers, Vet Center Outstations is flagged as New, OR if an existing facility is flagged as New with a new Facility API ID. (This may happen  if a Vet Center moves districts, or a Mobile Vet Center is reallocated to a different Vet Center.)

## Intake
- [ ] What triggered this runbook? (Flag in CMS, Help desk ticket, Product team, VHA Digital Media)
Trigger: <insert_trigger>

- [ ] Link to associated help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] If the submitter is an editor, send them links to any relevate KB articles for the Vet Center product.
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
- [ ] Confirm what Vet Center it belongs to, and set the "Main Vet Center location" field. The parent location may be derived by the Facility ID.
- [ ] Contact the Vet Center editor to remind them to (1) add a photo of the Mobile Vet Center and then they can publish when ready and (2) remind them that if this Mobile Vet Center is used by any other facilities to communicate with those Vet Center editors
- [ ] Consider: Following up with Barb/RCS Central office to be sure that she's aware that the mobile Vet Center is in-flight

**If a Outstation:**
- [ ] Confirm what Vet Center it belongs to, and set the "Main Vet Center location" field. The parent location may be derived by the Facility ID.
- [ ] Contact the Vet Center editor to remind them to (1) add a photo of the Outstation and then they can publish when ready
- [ ] Consider: Following up with Barb/RCS Central office to be sure that she's aware that the Outstation is in-flight

**If a Vet Center:**
- [ ] 1. Become aware that the new facility is now on the Facility API (typically, via a Flag).
- [ ] 2. Check with Readjustment Counseling Services to (1) confirm what district the Vet Center belongs and (2) Identify the Vet Center Director and Outreach Specialist (names/email addresses)
- [ ] 3. In [Sections taxonomy](https://prod.cms.va.gov/admin/structure/taxonomy/manage/administration/overview), move the Vet Center Section to the appropriate district.
- [ ] 4. Create accounts (or restrict existing accounts) with editor rights only for Vet Center Director and Outreach Specialist so that they cannot publish on their own.
- [ ] 5. Contact Vet Center Director and Outreach specialist to onboard for training [@TODO write sample email - SEE runbook-vamc-facility-new] **Note: this should include instructions for adding content and preparing for publishing and RCS Central Office should be included as CC**
- [ ] 6. Add flag `Awaiting editor` to this facility. Note: This is now blocked until RCS Central office approves.
- [ ] 7. **Once approved by RCS Central Office as complete, proceed to Drupal Admin publishing steps**


**Drupal Admin steps**
- [ ] 8. Bulk publish the nodes and facility.
- [ ] 9. Edit the facility node: remove the `New facility` and `Awaiting editor` flags, save the node.
- [ ] 10. Let Help desk know this has been done.

#### CMS Help desk (wrap up)
- [ ] 11. Upgrade the Vet Center Director and Outreach Specialist accounts to the publisher role for that Vet Center.
- [ ] 12. Notify editor and any other stakeholders.
