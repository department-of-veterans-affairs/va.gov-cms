---
name: Runbook - New facility
about: changing facility information in the CMS for VHA facilities
title: 'New Facility: <insert_name_of_facility>'
labels: Change request
assignees: ''

---

## Background

*What triggered this runbook?*
_eg Flag in CMS, Help desk ticket, Product team, VHA Digital Media_

*Please attach Facility Locator link and https://prod.cms.va.gov/ link to this ticket*

*Please also include link to Jira help desk ticket, if applicable*

## Acceptance criteria

### VAMC
Please refer to the Knowledge Base article titled "How do I add a facility to my health care system?" for more information: https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-do-i-add-a-facility-to-my-health-care-system

#### CMS help desk steps
- [ ] 1. Become aware that the new facility is now on the Facility API (typically, via a Flag, but this may come in as a helpdesk ticket).
- [ ] 2. If the editor has followed the steps from the above Knowledge Base article and included which section and VAMC the facility belongs to (i.e. VA Pittsburgh), great! **Proceed to step 3.** If not, please check with the editor or VHA digital media regarding what section and VAMC it belongs to.
- [ ] 3. Updates the Section (default is "VAMC facilities", but it should be a VAMC system in a VISN) and VAMC system field accordingly.
- [ ] 4. Communicate with editor (cc VHA Digital Media) to give them go-ahead to complete the content, with this [KB article](https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-do-i-add-a-facility-to-my-health-care-system).
- [ ] 5. When editor has prepared content and let help desk know, reassign this issue to appropriate CMS engineer on Product Support team, for bulk publishing.

    #### Sample notification email:
    Hello! You should now both be able to edit the currently un-published draft page for this facility, located at [LINK TO NEW FACILITY DRAFT PAGE ON PROD]

    Important: Please make sure that all relevant steps listed within the “How do I add a facility to my health care system?” Knowledge Base article have been completed: https://prod.cms.va.gov/help/vamc/about-locations-content-for-vamcs/how-do-i-add-a-facility-to-my-health-care-system
    
    Once finished, please save this page as a “Draft”, and do not save it as “Published.”

    Please let us know once your draft is complete, so that we can wrap up the technical process from our end and ensure that the new facility publishes to the live site as intended. Thanks!

#### CMS engineer
- [ ] 6. CMS engineer bulk publishes nodes and facility.
- [ ] 7. CMS engineer edit facility node and remove `New facility` flag and save node.
- [ ] 8. Let help desk know this has been done.

#### CMS Help desk
- [ ] 9. HD notifies editor and any other stakeholders.


### Vet Center

CMS help desk steps
- [ ] 1. Become aware that the new facility is now on the Facility API (typically, via a Flag).
- [ ] 2. Check with RCS(?) what district it belongs to.
- [ ] 3. Move the section to the appropriate district.
- [ ] 4. Communicate with editor (do they need to be onboarded) @todo write sample email.
- [ ] 5. When editor has prepared content and let help desk know, reassign this issue to appropriate CMS engineer on Product Support team, for bulk publishing.

CMS engineer
- [ ] 6. CMS engineer bulk publishes nodes and facility.
- [ ] 7. CMS engineer edit facility node and remove `New facility` flag and save node.
- [ ] 9. Let help desk know this has been done.

CMS Help desk
- [ ] 9. HD notifies editor and any other stakeholders.

## CMS Team
Please check the team(s) that will do this work.

- [ ] `Platform CMS Team`
- [ ] `Sitewide program`
- [ ] `⭐️ Sitewide CMS`
- [ ] `⭐️ Public Websites`
- [x] `⭐️ Facilities`
- [x] `⭐️ User support`
