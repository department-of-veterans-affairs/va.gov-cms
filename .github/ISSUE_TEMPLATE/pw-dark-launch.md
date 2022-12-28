---
name: "(PW) Dark launch request"
about: Request to dark launch CMS content that includes React widget, owned by Public
  Websites team
title: 'CMS/React content dark launch request: <content info>'
labels: Drupal engineering, Needs refining, ⭐️ Public Websites, VA.gov frontend
assignees: jilladams, wesrowe

---

## Description
Use this issue to request dark launch of a CMS page that includes a React widget. 

**Review documentation about the process** prior to submitting your ticket: https://github.com/department-of-veterans-affairs/va.gov-team/tree/master/products/public-websites#3-publish-a-cms-page-only-to-staging-using-entityqueue-in-order-to-stage-cms--content-build--vets-website-for-viewing-off-of-va-network
  
  
### Staging/dark launch
* **CMS link to content**:
* **Target dark launch date**:
* **Is this date flexible?** If not, please explain: 
* **Technical point of contact** for vets-website React code: 
* **Strategic point of contact** for timing & approval to publish the node to Staging: 

### Production launch
CMS team does not want to get in the habit of having staged nodes that will not publish, so this method should only be used for business cases that have a line of sight on production launch. 

* **Target production launch date:** 
* **What is driving and/or blocking prod launch date?** 
  
# For PW use
## Tasks
Please tick the boxes for completed steps as we go, for cross-team visibility.
- [ ] Technical POC has merged vets-website code to main & confirmed deploy to prod
- [ ] Add Node ID to [Staged Content entity subqueue](https://prod.cms.va.gov/admin/structure/entityqueue/staged_content/staged_content?destination=/admin/structure/entityqueue)
- [ ] Strategic POC has given approval to publish node to Staging, or has published node
- [ ] Nightly CMS build has run which pushes prod mirror to the staging site, or PW must request an out of band deployment.
- [ ] Content-release has run. 
- [ ] Verify on staging / prod

## Acceptance Criteria
- [ ] Page is verified: Live on Staging
- [ ] Page is **not** live on Prod
- [ ] Ticket is cut for production launch planning, e.g. #10627 

### CMS Team
Please check the team(s) that will do this work.

- [ ] `Program`
- [ ] `Platform CMS Team`
- [ ] `Sitewide Crew`
- [ ] `⭐️ Sitewide CMS`
- [X] `⭐️ Public Websites`
- [ ] `⭐️ Facilities`
- [ ] `⭐️ User support`
