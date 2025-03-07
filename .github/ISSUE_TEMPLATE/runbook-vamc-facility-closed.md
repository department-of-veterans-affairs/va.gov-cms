---
name: Runbook - VAMC Facility closed
about: Steps for archiving a VAMC facility in VA.gov CMS.
title: 'VAMC Facility closed: <insert_name>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support,
  VAMC, sitewide
assignees: ''

---

## Intake
- [ ] What triggered this runbook? (Flag in CMS, Help desk ticket, Product team, VHA Digital Media)
Trigger: <insert_trigger>

- [ ] Link to associated JIRA help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- [ ] If the submitter is an editor, send them a link to the KB article: [How to archive a closed facility](https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-to-archive-a-closed-facility)

- [ ] Link to facility in production:
Facility CMS link: <insert_facility_link>
Facility API ID: <insert_facility_API_ID>

## Acceptance criteria

### VAMC facility closure

#### CMS Help desk steps
**Note: If the help desk is waiting on information from the facility staff or editor, add the "Awaiting editor" flag to the facility with a log message that includes a link to this ticket. Remove the flag when the ticket is ready to be worked by the Facilities team. Be sure to preserve the current moderation state of the node when adding or removing the flag.**
- [ ] 1. CMS team becomes aware that the facility is no longer on the Facility API.
- [ ] 2. If we don't already have context (say, via a HD ticket submitted by an editor), check with editor to find out more about the status of the facility
- [ ] 3. Find out if there are any services or events tied to the facility to be archived that should be moved to a new facility or otherwise preserved and updated

[@TODO: Update email template]

<details><summary>Email template </summary>

```
SUBJECT: <facility name> removed from VAST

Hi [VAMC editor(s) who own the node in CMS]

We see that [name of facility] has been removed from VAST. If this facility has been permanently closed or moved, you can work with us to unpublish the facility from the CMS and remove it from VA.gov.

Because some Veterans may have bookmarked this facility, external sites may have linked to it, and because it can take a little time for search engines to catch up to web content, we want prevent errors and bad web experiences for our Veterans.

In order to do that we have some questions about the nature of this closure so that we can help redirect Veterans to the right place and understand this change.

1. Was this facility replaced with another facility?
   If yes, which one?
2. Is there a news release or story about this published on your VAMC website?
3. Anything else we should know about this facility closure?

If this facility has been removed from VAST in error, please notify the VA Drupal CMS Help Desk Support Team by writing to support@va-gov.atlassian.net, and please also notify your VAST coordinator.

[outro]

[CMS helpdesk signature]

Alternative, for "Removed From Source" flag follow-up:
-----------------------
During a site-wide review, our team found a location within the [INSERT SYSTEM NAME] health care system that is no longer listed within the Facilities API, but still exists within Drupal.

Can you please tell us more about the status of the [CLINIC NAME]?  Thanks!

Here is a link to the clinic homepage, which is still published on the live site: [INSERT CLINIC HOMEPAGE LINK FROM LIVE SITE]

Here is a link to this page on the production site:  [INSERT CLINIC HOMEPAGE LINK FROM PRODUCTION SITE]

IF THIS FACILITY IS CLOSED:
Please follow the steps listed in the following Knowledge Base article in order to fully archive it from VA.gov: https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-to-archive-a-closed-facility

Important: Once these steps have been followed, please reach back out to the VA Drupal CMS Help Desk by replying to this email or by writing to support@va-gov.atlassian.net.

From there, our engineering team will proceed with next steps for archiving this facility.

If the clinic has not been closed, please also let our team know as soon as possible.

Thank you for your help!

[Signature]

```
</details>

- [ ] 4. Are any of the services or upcoming events for the facility to be closed moving to a different facility?
  - [ ] 4a. If so, note the facility picking up the services and events here: <insert_target_facility>
  - [ ] 4b. If so, note the services and events here: <insert_services_to_be_moved>
- [ ] 5. Create a [URL change](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&template=runbook-facility-url-change.md&title=URL+Change+for%3A+%3Cinsert+facility+name%3E) to redirect the from the URL of the facility to be closed to the URL of its parent system and to remove the old canonical link.

<insert_redirect_request_link>

#### CMS Engineer steps
- [ ] 6. Execute the steps of the URL change request ticket from step 5 above.

(Redirects deploy daily except Friday at 10am ET, or by requesting OOB deploy (of the revproxy job to prod) in #vfs-platform-support. Coordinate the items below and canonical URL change after URL change ticket is merged, deployed, and verified in prod.)

#### Drupal Admin steps (CMS Engineer or Helpdesk)
_Help desk will complete these steps or escalate to request help from CMS engineering._
- [ ] 7. When redirect has been deployed, make these changes. Practice first on staging or a demo environment.
  - [ ] 7a. Drupal Admin bulk moves any content identified **4b** to new facility **4a**.
  - [ ] 7b. Drupal Admin edits the facility node, removes flag `Removed from source`, add a revision log that explains the change, with a link to github issue, and change moderation state to archive. (Note: any remaining health services, non-clinical services and events for the given facility will be archived automatically when these changes are saved.)
  - [ ] 7c. Drupal Admin finds the menu for the system https://prod.cms.va.gov/admin/structure/menu and deletes the menu item for the facility being closed.

#### CMS Help desk (wrap up)
- [ ] 8. Help desk notifies editor and any other stakeholders.
