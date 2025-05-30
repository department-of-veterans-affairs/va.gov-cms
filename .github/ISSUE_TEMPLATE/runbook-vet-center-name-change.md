---
name: Runbook - Vet Center, Outstation, Mobile Vet Center name change
about: Steps for updating names and URLs
title: 'Vet Center name change: <insert_name>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support, Vet Center, sitewide
assignees: ''

---

## Vet Center, Outstation, Mobile Vet Center facility name change - Description
Vet Centers, Mobile Vet Centers, and Vet Center Oustations may all be subject to name changes in VAST. Not all steps apply to each type of facility -- please pay attention and make sure you've done the relevant steps based on facility type.

## Intake
- [ ] What triggered this runbook? (Flag in CMS, Help desk ticket, Product team, VHA Digital Media)
Trigger: <insert_trigger>

- [ ] Link to associated help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] If the submitter is an editor, send them links to any relevate KB articles for the Vet Center product. Let them know that facility changes can take between 75 days and 4 months after submitting a request, according to VAST administrators.
KB articles: <insert_kb_article_links>

- [ ] Link to facility in production:
Facility CMS link: <insert_facility_link>
Facility API ID: <insert_facility_API_ID>

## Acceptance criteria

### CMS help desk steps
**Note: If the help desk is waiting on information from the facility staff or editor, add the "Awaiting editor" flag to the facility** with a log message that includes a link to this ticket. Remove the flag when the ticket is ready to be worked by the Facilities team. **Be sure to preserve the current moderation state of the node when adding or removing the flag.**
- [ ] The title (Name of Vet Center field) change comes from Lighthouse to Drupal & is flagged
- [ ] If the Vet Center published and is NOT an Outstation/MVC, create a [URL change request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E), changing the entry from the old facility URL to the new facility URL. **URL changes no longer block the remaining steps in this ticket.**

<insert_redirect_request_link>

### Drupal Admin steps (CMS Engineer or Help desk)
_Help desk will complete these steps or escalate to request help from CMS engineering._

**If a Mobile Vet Center or Outstation**
- [ ] Verify which Vet Center the Outstation belongs to, and confirm that the "Main Vet Center Location" field is set correctly.

**If a Vet Center**

***CASE: Renamed, but named after a person, not a location***

- [ ] The new official name no longer matches the pattern "<location> Vet Center" so you need to change the common name to align with the <location> naming convention if it does not already.
- [ ] Confirm on the Front-end that the <location> naming convention is the h1 followed by a "also known as" with the named-after-person name.

***CASE: Renamed, but still location-based naming convention***

- [ ] If the new official name matches the pattern "<city> Vet Center", update the common name to match.
- [ ] Visit [bulk operations](https://prod.cms.va.gov/admin/content/bulk) page and filter by section = old vet center name
- [ ] Update URLs for all content in that section by bulk operations
- [ ] Resave all content in that section by bulk operations

***CASE: Renamed***
- [ ] Visit [bulk operations](https://prod.cms.va.gov/admin/content/bulk) page and filter by section = old vet center name
- [ ] Select all
- [ ] Choose "Modify values"
- [ ] Change Section to new Section name
- [ ] Execute
- [ ] Visit [Users](https://prod.cms.va.gov/admin/people)
- [ ] Filter by Section = old vet center name
- [ ] Update users to new Section
- [ ] Go to [Section](https://prod.cms.va.gov/admin/structure/taxonomy/manage/administration/overview) taxonomy
- [ ] Delete old vet center name term
 
**For all types**
- [ ] Edit the Vet Center node by removing flag `Changed name`, and saves the node (with moderation state = published)

#### CMS Help desk (wrap up)
- [ ] After the next content release, verify that your changes took place on VA.gov:
    - [ ] Vet center name is correct
    - [ ] Vet center URLs are correct
- [ ] Notify editor and any other stakeholders.
