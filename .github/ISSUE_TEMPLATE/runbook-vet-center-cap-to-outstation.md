---
name: Runbook - Vet Center CAP becomes an Outstation
about: Steps for upgrading a CAP to an Outstation
title: 'Vet Center CAP becomes an Outstation: <insert_name_of_facility>'
labels: Change request, Drupal engineering, Facilities, User support, VA.gov frontend, Vet Center
assignees: ''

---

# Vet Center Community Access Point becomes an Outstation
## Background

   **Note: This is likely not a thing, so we may be building a runbook for something that will never happen. A CAP is space in an existing location (e.g. YMCA, or VFW, etc). A Outstation is VA leased space. So the address will likely change anyhow. We likely need to revisit this runbook when we have an actual use-case to evaluate.  **

  A Vet Center - Community Access Point (CAP) is a lighter, more fluid version of
  an Outstation.  They are created on-demand by the Vet Center.  If they prove
  popular, the CAP may be upgraded to an Outstation.  CAPs are not in VAST, and
  originate in the CMS.  The CMS assigns an API id so they can be pushed from
  the CMS to the Facility API.  That API ID does not persist when
  the CAP becomes an Outstation and it appears for the first time in VAST.  When
   it appears in VAST, it will have a node created for it as part of the
   migration.
   
   Note: What we're trying to accomplish here is synchronizing the archive of the CAP and the initial publication of the Outstation so that we don't have 1/ a missing entry or 2/ duplicative entries.
  
   
   
## Intake

Note: I've updated this with how we learn about these things (Jessica or New Facility Flag in CMS)

- [ ] What triggered this runbook? (Flag in CMS, Notification from RCS Central Office)
Triggers:
  A)  A RCS Central Office notifies help desk that they are converting a CAP to an Outstation.
  B)  A new Vet Center Outstation is entered in VAST so appears in the CMS as New.

  <insert_trigger>

- [ ] Link to associated help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] Link to facility in production:
CAP CMS link (Published): <insert_facility_link>
Outstation CMS link (in draft): <insert_facility_link>
Outstation Facility API ID: <insert_facility_API_ID>

## Acceptance criteria
## CMS help desk steps

Note: We should always check to see if there is an existing facility at that address regardless. We can check that using Facility Locator and/or asking Jessica and/or asking the parent facility.

**Note: If the help desk is waiting on information from the facility staff or editor, add the "Awaiting editor" flag to the facility with a log message that includes a link to this ticket. Remove the flag when the ticket is ready to be worked by the Facilities team. Be sure to preserve the current moderation state of the node when adding or removing the flag.**
- [ ] 1. Become aware that the new Vet Center Outstation is now in the Facility
  API and in the CMS (typically, via a Flag, but this may come in as a help
  desk ticket).
- [ ] 2. Determine the node ID of the existing CAP that has become a new outstation.
- [ ] 3. The parent facility of the outstation can be derived from the API ID via removing the last 3 characters (e.g. "1OS") and adding a "V". For example the outstation "vc_3072OS" has the parent facility "vc_372V".
- [ ] 4. Update the Section (default is "Vet Center", but it should be a under
  a district) and Vet Center accordingly.
- [ ] 5. Add the photo from the CAP as the photo for the new Outstation. This will prevent duplicate photos from being uploaded into Drupal.
### Publish new Outstation
- [ ] 6. Publish the new Outstation 
- [ ] 7. Archive the old with a revision log that points to the new OS, set status to closed and clear out status description. CAP. NOTE: redirects are not necessary due to low traffic, same page as landing, and general overhead/maintenance.
- [ ] 6. Remove the `New facility` flag from the node.
- [ ] 7. Communicate with editor and RCS Central Office

[@TODO help desk write sample email - SEE runbook-vamc-facility-new]
