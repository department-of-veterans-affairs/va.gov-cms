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
- [ ] Verify that the new URL for the facility is published and accessible on VA.gov.
- [ ] Create a URL redirect in the [vsp-platform-revproxy](https://github.com/department-of-veterans-affairs/vsp-platform-revproxy) repo in `template-rendering/revproxy-vagov/vars/redirects.yml`
- [ ] Add the "Awaiting redirect" flag to the facility node with a revision log message that includes a link to this ticket, preserving the node's current moderation state. (may not be necessary if redirects deploy quickly)
- [ ] Redirects deploy daily except Friday at 10am ET, or by requesting OOB deploy (of the revproxy job to prod) in #vfs-platform-support. After deploy, validate that the URL redirect is deployed. 
- [ ] Update this ticket with a comment that the redirect has been deployed.
- [ ] Remove the "Awaiting redirect" flag if it was added on the facility node, with a revision log message that includes a link to this ticket, preserving the node's current moderation state.
- [ ] Notify helpdesk via comment on ticket that redirect has deployed.

#### URL Redirect
| Current URL  |  Redirect Destination or New URL |
| ---  |  --- |
| `old URL` | `new URL` |

#### If the Facility is an unpublished NCA facility
After next nightly Facilities migration to Lighthouse:
- [ ] Validate that the change has deployed by checking that the Facility Locator search result for the facility has been updated and uses the new url
