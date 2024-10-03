---
name: Runbook - New VAMC Facility
about: changing facility information in the CMS for VAMC facilities
title: 'New VAMC Facility: <insert_name_of_facility>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  VAMC
assignees: ''

---

## Intake
- [ ] What triggered this runbook? (Flag in CMS, Help desk ticket, Product team, VHA Digital Media)
Trigger: <insert_trigger>

- [ ] Link to associated help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] If the submitter is an editor, send them links to any relevate KB articles for the VAMC Facility product.
KB articles: <insert_kb_article_links>

- [ ] Link to facility in production:
Facility CMS link: <insert_facility_link>
Facility API ID: <insert_facility_API_ID>

## Acceptance criteria

### New VAMC Facility
Please refer to the Knowledge Base article titled "How do I add a facility to my health care system?" for more information: https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-do-i-add-a-facility-to-my-health-care-system

#### CMS help desk steps
**Note: If the help desk is waiting on information from the facility staff or editor, add the "Awaiting editor" flag to the facility with a log message that includes a link to this ticket. Remove the flag when the ticket is ready to be worked by the Facilities team. Be sure to preserve the current moderation state of the node when adding or removing the flag.**
- [ ] Become aware that the new facility is now on the Facility API (typically, via a Flag, but this may come in as a helpdesk ticket).
- [ ] **If the facility is a VA Mobile clinic, the "New facility" flag can be removed and the page archived with no further work needed. (Note, there are required fields that will need to be filled in before doing so.)**
- [ ] If the editor has followed the steps from the above Knowledge Base article and included which section and VAMC the facility belongs to (i.e. VA Pittsburgh), great!
  - If not, please check with the editor or VHA digital media regarding what section and VAMC it belongs to.
    - [ ] Update the Section (default is "VAMC facilities", but it should be a VAMC system in a VISN) and VAMC system field accordingly.
- [ ] Communicate with editor (cc VHA Digital Media) to give them go-ahead to complete the content, with this [KB article](https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-do-i-add-a-facility-to-my-health-care-system). (See sample notification email below)

<details><summary>Email template </summary>

```

Hello! You should now be able to edit the draft page for this facility, located at [LINK TO NEW FACILITY DRAFT PAGE ON PROD]

Important: Please make sure that all relevant steps listed within the “How do I add a facility to my health care system?” Knowledge Base article have been completed: https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-do-i-add-a-facility-to-my-health-care-system

Once finished, please save this page (and all related VAMC Facility Health Service pages) in the moderation state “Draft." Please do not save them as “Published.”

Please let us know when your draft content is complete, so that we can wrap up the technical process from our end before publishing the new facility to VA.gov. Thanks!

```

</details>

- [ ] When editor has prepared content and let help desk know, proceed to the remaining steps.


#### Drupal Admin steps (CMS Engineer or Help desk) _Help desk will complete these steps or escalate to request help from CMS engineering._
- [ ] Update the facility **Meta description** field, using the following format: "Get address and hours, parking and transportation information, and health services offered at [facility name]."
- [ ] Move the facility link in the health care system menu to its place in the alphabetized list (medical centers first, then clinics).
- [ ] Drupal Admin bulk publishes nodes and facility.
- [ ] Contact Lighthouse via Slack at #cms-lighthouse channel that this facility requires a canonical link in the following format (replacing the placeholder data with the actual API Id and VA.gov URL):
  - `vha_691GM,https://www.va.gov/greater-los-angeles-health-care/locations/oxnard-va-clinic/`
- [ ] Add the "Awaiting CSV" flag to the facility node with a revision log message that includes a link to this ticket.
- [ ] Let Help desk know this has been done, if not done by Help desk.

#### Wait (days or weeks, potentially)
- [ ] After the canonical link has been added to the websites.csv and you have confirmation from Lighthouse that the CSV has been deployed, validate that the change has deployed by checking that the Facility Locator has been updated with the new url.
- [ ] Update this ticket with a comment that the CSV change has been deployed.
- [ ] Edit facility node and remove `New facility` and "Awaiting CSV" flags with a revision log message that includes a link to this ticket.

#### CMS Help desk (wrap up)
- [ ] Notify editor and any other stakeholders.
