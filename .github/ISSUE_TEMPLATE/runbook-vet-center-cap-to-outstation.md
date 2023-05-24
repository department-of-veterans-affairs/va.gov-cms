---
name: Runbook - Vet Center CAP becomes an Outstation
about: Steps for upgrading a CAP to an Outstation
title: 'Vet Center CAP becomes an Outstation: <insert_name_of_facility>'
labels: Change request, Drupal engineering, Facilities, User support, VA.gov frontend, Vet Center
assignees: ''

---

# Vet Center Community Access Point becomes an Outstation
## Background
  A Vet Center - Community Access Point (CAP) is a lighter, more fluid version of
  an Outstation.  They are created on-demand by the Vet Center.  If they prove
  popular, the CAP may be upgraded to an Outstation.  CAPs are not in VAST, and
  originate in the CMS.  The CMS assigns an API id so they can be pushed from
  the CMS to the Facility API.  That API ID does not persist when
  the CAP becomes an Outstation and it appears for the first time in VAST.  When
   it appears in VAST, it will have a node created for it as part of the
   migration.
## Intake
- [ ] What triggered this runbook? (Flag in CMS, Help desk ticket, Product team, VHA Digital Media)
Triggers:
  A)  A Vet Center notifies help desk that they are converting a CAP to an Outstation.
  B)  A new Vet Center Outstation is entered in VAST so appears in the CMS as New.

  <insert_trigger>

- [ ] Link to associated help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] If the submitter is an editor, send them links to any relevant KB articles for this process.
KB articles: <insert_kb_article_links>

- [ ] Link to facility in production:
Facility CMS link: <insert_facility_link>
Facility API ID: <insert_facility_API_ID>

## Acceptance criteria
## CMS help desk steps
**Note: If the help desk is waiting on information from the facility staff or editor, add the "Awaiting editor" flag to the facility with a log message that includes a link to this ticket. Remove the flag when the ticket is ready to be worked by the Facilities team. Be sure to preserve the current moderation state of the node when adding or removing the flag.**
- [ ] 1. Become aware that the new Vet Center Outstation is now in the Facility
  API and in the CMS (typically, via a Flag, but this may come in as a help
  desk ticket).
- [ ] 2. If the editor has followed the steps from the Knowledge Base
  article and included which district and Vet Center the facility belongs to, great!  If not, check
  with RCS(?) what district it belongs to.
- [ ] 3. Update the Section (default is "Vet Center", but it should be a under
  a district) and Vet Center accordingly.
- [ ] 4. Communicate with editor (cc VHA Digital Media) to give them go-ahead to
  complete the content.  An Outstation has no services, so typically all that
  is needed to publish is a photo.
### Publish new Outstation
- [ ] 5. When editor has prepared content and let help desk know, publish the
  new Outstation.
- [ ] 6. Remove the `New facility` flag from the node.
- [ ] 7. Communicate with editor (do they need to be onboarded)

[@TODO help desk write sample email - SEE runbook-vamc-facility-new]

### Close old CAP and Create new Outstation URL
- [ ] 8. Create a [URL change request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E), changing the entry from the old facility URL to the new facility URL, referencing this issue to redirect from the CAP URL to the Outstation URL. (**Note: The URL change request ticket blocks the completion of this ticket.**)

<insert_url_change_request_link>

(Redirects deploy weekly on Wed. at 10am ET, or by requesting OOB deploy (of the revproxy job to prod) in #vfs-platform-support. Coordinate the items below and canonical URL change after URL change ticket is merged, deployed, and verified in prod.)

### CMS engineer steps
- [ ] 9. Execute the steps of the URL change request ticket from step 8.
- [ ] 10. When the redirect has been made live, set the status of the CAP node to 'closed'.
- [ ] 11. Archive the CAP with a comment in the revision log that points to the new Vet Center.

### Help desk steps
- [ ] 12. Notify editor and any other stakeholders.
