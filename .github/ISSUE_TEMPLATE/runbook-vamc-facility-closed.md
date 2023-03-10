---
name: Runbook - VAMC Facility closed
about: Steps for archiving a VAMC facility in VA.gov CMS.
title: 'VAMC Facility closed: <insert_name>'
labels: Change request
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
Facility link: <insert_facility_link>

## Acceptance criteria

### VAMC facility closure

#### CMS Help desk steps
- [ ] 1. CMS team becomes aware that the facility is no longer on the Facility API.
- [ ] 2. If we don't already have context (say, via a HD ticket submitted by an editor), check with editor to find out more about the status of the facility
- [ ] 3. Find out if there are any services or events tied to the facility to be archived that should be moved to a new facility or otherwise preserved and updated

[@TODO: Update email template]

<details><summary>Email template </summary>

```
FROM: vacms email
SUBJECT: <facility name> removed from VAST
CC: Jeffrey.Grandon@va.gov, Steve.Tokar2@va.gov, Jennifer.Heiland-Luedtke@va.gov, David.Conlon@va.gov
BODY:

Hi [VAMC editor who owns the node in CMS ]

We see that [name of facility] has been removed from VAST. If this facility has been permanently closed or moved, you can now work with us to unpublish the facility from the CMS and remove it from VA.gov.

Because some Veterans may have bookmarked this facility, external sites may have linked to it, and because it can take a little time for search engines to catch up to web content, we want prevent errors and bad web experiences for our Veterans.

   In order to do that we have some questions about the nature of this closure so that we can help redirect Veterans to the right place and understand this change.

1. Was this facility replaced with another facility?
   If yes, which one?
2. Is there a news release or story about this published on your VAMC website?
3. Anything else we should know about this facility closure?

If this facility has been removed from VAST in error, please notify our Support Desk as well as your VAST coordinator.

[outro]

[CMS helpdesk signature]
```

</details>

- [ ] 4. Are any of the services or upcoming events for the facility to be closed moving to a different facility?
  - [ ] 4a. If so, note the facility picking up the services and events here: <insert_target_facility>
  - [ ] 4b. If so, note the services and events here: <insert_services_to_be_moved>
- [ ] 5. Create a [redirect request](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/new?assignees=&labels=Redirect+request&template=redirect-request-facility-url.md&title=Redirect+Request+for%3A+%3Cinsert+facility+name%3E) from the URL of the facility to be closed to the URL of its parent system.

<insert_redirect_request_link>

(These are usually released Wednesday afternoons so you should coordinate the remaining items below around that timeframe)

- [ ] 6. When redirect has been deployed, make these changes. Practice first on staging or a demo environment.
  - [ ] 6a. CMS engineer bulk moves any content identified **4b** to new facility **4a**.
  - [ ] 6b. CMS engineer edits the facility node, removes flag `Removed from source`, add a revision log that explains the change, with a link to github issue, and change moderation state to archive. (Note: any remaining health services, non-clinical services and events for the given facility will be archived automatically when these changes are saved.)
  - [ ] 6c. CMS engineer finds the menu for the system https://prod.cms.va.gov/admin/structure/menu and deletes the menu item for the facility being closed.

#### CMS Help desk (wrap up)
- [ ] 7. Help desk notifies editor and any other stakeholders.

### Team
Please check the team(s) that will do this work.

- [ ] `CMS Team`
- [ ] `Public Websites`
- [x] `Facilities`
- [x] `User support`
