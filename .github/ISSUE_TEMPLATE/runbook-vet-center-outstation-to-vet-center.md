---
name: Runbook - Vet Center Outstation becomes a Vet Center
about: Steps for upgrading an outstation to a full Vet Center
title: 'Outstation beomces Vet Center: <insert_name_of_facility>'
labels: Change request
assignees: ''

---
# Vet Center Outstation becomes a Vet Center
## Background
  Outstations have entries in VAST. When an Outstation becomes a full Vet Center,
  it gets a **new entry in VAST** with a new facility API id. When this happens, it will have a node created
  for it as part of the migration.
## Intake
- [ ] What triggered this runbook? (Flag in CMS, Help desk ticket, Product team, VHA Digital Media)
Trigger: <insert_trigger>

- [ ] Link to associated help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] If the submitter is an editor, send them links to any relevant KB articles for this process.
KB articles: <insert_kb_article_links>

- [ ] Link to facility in production:
Facility link: <insert_facility_link>

## Acceptance criteria
### CMS help desk steps
#### Edit new Vet Center
- [ ] 1. Become aware that the new Vet Center is now in the Facility API and in the CMS (typically, via a Flag, but this may come in as a help desk ticket).
- [ ] 2. Check with RCS(?) what district it belongs to, or it may be pulled from the former Outstation.
- [ ] 3. Update the Section (default is "Vet Center", but it should be under a district).
- [ ] 4.  Communicate with editor (cc VHA Digital Media) to give them go-ahead to complete the content. An Outstation does not have any services, but a Vet Center does, so these must be added.
#### Publish new Vet Center
- [ ] 5. When editor has prepared content and let help desk know, publish the new Vet Center.
- [ ] 6. Remove the `New facility` flag from the node.
- [ ] 7. Communicate with editor (do they need to be onboarded)

#### Close old Outstation and Create Vet Center URL
- [ ] 8. Create a [URL change request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E), changing the entry from the old facility URL to the new facility URL. (**Note: The URL change request ticket blocks the completion of this ticket.**)

<insert_url_change_request_link>

(Redirects are released Wednesday afternoons, so coordinate the following items below and canonical URL change around that timeframe.)
### CMS engineer steps
- [ ] 9. Execute the steps of the URL change request ticket from step 8.
- [ ] 10. When the redirect has been made live, set the status of the Outstation node to 'closed'.
- [ ] 11. Archive the Outstation with a comment in the revision log that points to the new Vet Center.


- [ ] 12. Help desk notifies editor and any other stakeholders.

### Team
Please check the team(s) that will do this work.

- [ ] `CMS Team`
- [ ] `Public Websites`
- [x] `Facilities`
- [x] `User support`
