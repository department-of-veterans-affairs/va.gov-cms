---
name: Runbook - Facility URL Change
about: Submit a request to change the URL of a facility
title: 'URL Change for: <insert facility name>'
labels: URL Change
assignees: ''

---
### Implementation date
When does this request need to be live:
[MM/DD/YYYY]

### Instructions for URL redirect
(Note: This issue will be used from initial request through implementation to ensure all individuals working on this are notified of status updates.  Please do not create multiple issues to track different steps.)
- [ ] Notify VA stakeholders as appropriate.
- [ ] Link the related facility closure / rename issue.
- [ ] Create a URL redirect in the [devops](https://github.com/department-of-veterans-affairs/devops) repo in `ansible/deployment/config/revproxy-vagov/vars/redirects.yml`
- [ ]  Redirects deploy weekly on Wed. at 10am ET, or by requesting OOB deploy (of the revproxy job to prod) in #vfs-platform-support. After deploy, validate that the URL redirect is deployed. (Note: In the event of a facility closure or a name change,  validate that this occurs before making the Lighthouse csv changes.)
- [ ]  Notify helpdesk via comment on ticket or Slack message in #cms-support that changes are ready for review.  

#### URL Redirect
| Current URL  |  Redirect Destination or New URL |
| ---  |  --- |
| current URL | new URL |

### Instructions for canonical URL change
- [ ] Verify that the new URL for the facility is published and accessible on VA.gov.
- [ ] Update the [CSV in Lighthouse](https://github.com/department-of-veterans-affairs/lighthouse-facilities/blob/master/facilities/src/main/resources/websites.csv) with the changed URL, maintaining the sorted order of the Facility API IDs.
- [ ] Create a PR in the [lighthouse-facilities repo](https://github.com/department-of-veterans-affairs/lighthouse-facilities), tagging the Lighthouse team.
- [ ] Post a message in the #api-facilities channel in Slack, with an @mention to a Lighthouse team member.
- [ ] Validate that the change has deployed by checking that the Facility Locator has been updated with the new url.


#### URL change example (update with actual ID and URL)
| Facility API ID  |  Full VA.gov URL |
| ---  |  --- |
| vha_691GM | https://www.va.gov/greater-los-angeles-health-care/locations/oxnard-va-clinic/ |


### Team
Please check the team(s) that will do this work.

- [ ] `CMS Team`
- [ ] `Public Websites`
- [x] `Facilities`
- [x] `User support`
