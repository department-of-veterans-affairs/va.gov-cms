---
name: Runbook - Facility URL Change
about: Submit a request to change the URL of a facility
title: 'URL Change for: <insert facility name>'
labels: Drupal engineering, Facilities, Flagged Facilities, Redirect request, User
  support, sitewide
assignees: ''

---

Parent ticket: #number-of-GH-ticket

### Implementation date
When does this request need to be live:
[MM/DD/YYYY]

### Instructions for URL redirect
(Note: This issue will be used from initial request through implementation to ensure all individuals working on this are notified of status updates.  Please do not create multiple issues to track different steps.)
- [ ] Notify VA stakeholders as appropriate.
- [ ] Link the related facility closure / rename issue.
- [ ] Create a URL redirect in the [vsp-platform-revproxy](https://github.com/department-of-veterans-affairs/vsp-platform-revproxy) repo in `template-rendering/revproxy-vagov/vars/redirects.yml`
- [ ] Add the "Awaiting redirect" flag to the facility node with a revision log message that includes a link to this ticket, preserving the node's current moderation state.
- [ ] Redirects deploy daily except Friday at 10am ET, or by requesting OOB deploy (of the revproxy job to prod) in #vfs-platform-support. After deploy, validate that the URL redirect is deployed. 
- [ ] Update this ticket with a comment that the redirect has been deployed.
- [ ] Remove the "Awaiting redirect" flag on the facility node with a revision log message that includes a link to this ticket, preserving the node's current moderation state.
- [ ] Notify helpdesk via comment on ticket or Slack message in #cms-support that changes are ready for review.

#### URL Redirect
| Current URL  |  Redirect Destination or New URL |
| ---  |  --- |
| current URL | new URL |

## Use one of the following:
### 1. Canonical URL change

**Note: Canonical URL changes do not block the completion of the parent ticket. Once the URL redirect above has been deployed, the value to the Veteran is delivered. This ticket should be kept open until the URL change is verified, except in the case of a removal (as described below).**

### Instructions for canonical URL change
- [ ] Verify that the new URL for the facility is published and accessible on VA.gov.

#### After next nightly Facilities migration to Lighthouse
- [ ] Validate that the change has deployed by checking that the Facility Locator has been updated with the new url.

#### URL change example (update with actual ID and URL)
| Facility API ID  |  Full VA.gov URL |
| ---  |  --- |
| vha_691GM | https://www.va.gov/greater-los-angeles-health-care/locations/oxnard-va-clinic/ |

### 2. Canonical URL removal (if removed from VAST)
### Instructions for canonical URL removal
- [ ] Try to find the facility via the Facility Locator, using the Facility API ID (e.g. https://va.gov/find-locations/facility/"facility_api_id"). If it is not available, close this ticket.

**Note: there's no check to see if it's not returning anything, as it should already be not showing anything in the Facility Locator.**

