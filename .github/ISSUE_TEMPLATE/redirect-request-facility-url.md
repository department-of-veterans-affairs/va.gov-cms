---
name: Redirect Request - Facility URL
about: Submit a request to change a URL and/or implement a redirect for a URL.
title: 'Redirect Request for: <insert facility name>'
labels: Redirect request
assignees: ''

---

### Instructions
(Note: This issue will be used from initial request through implementation to ensure all individuals working on this are notified of status updates.  Please do not create multiple issues to track different steps.)
- [ ] Notify VA stakeholders as appropriate.
- [ ] Link the related facility closure / rename issue.
- [ ] Create a URL redirect in the [devops](https://github.com/department-of-veterans-affairs/devops) repo in `ansible/deployment/config/revproxy-vagov/vars/redirects.yml`
- [ ] Add the "Awaiting redirect" flag to the facility node with a revision log message that includes a link to this ticket, preserving the node's current moderation state.
- [ ] Redirects deploy weekly on Wed. at 10am ET, or by requesting OOB deploy (of the revproxy job to prod) in #vfs-platform-support. After deploy, validate that the URL redirect is deployed. (Note: In the event of a facility closure or a name change,  validate that this occurs before making the Lighthouse csv changes.)
- [ ] Update this ticket with a comment that the redirect has been deployed.
- [ ] Remove the "Awaiting redirect" flag on the facility node with a revision log message that includes a link to this ticket, preserving the node's current moderation state.

### Implementation date
When does this request need to be live:

### Redirects
| Current URL  |  Redirect Destination or New URL |
| ---  |  --- |
| current URL | new URL |


### Team
Please check the team(s) that will do this work.

- [ ] `CMS Team`
- [ ] `Public Websites`
- [x] `Facilities`
- [x] `User support`
