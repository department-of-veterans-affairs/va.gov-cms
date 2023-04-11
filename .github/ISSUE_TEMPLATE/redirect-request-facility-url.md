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
- [ ] Validate that the URL redirect is deployed.

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
