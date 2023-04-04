---
name: Runbook - Facility URL Change
about: Submit a request to change the canonical URL of a facility
title: 'URL Change for: <insert facility name>'
labels: URL Change
assignees: ''

---

### Instructions
- [ ] Verify that the new URL for the facility is published and accessible on VA.gov
- [ ] Update the [CSV in Lighthouse](https://github.com/department-of-veterans-affairs/lighthouse-facilities/blob/master/facilities/src/main/resources/websites.csv) with the changed URL, maintaining the sorted order of the Facility API IDs
- [ ] Create a PR in the [lighthouse-facilities repo](https://github.com/department-of-veterans-affairs/lighthouse-facilities), tagging the Lighthouse team
- [ ] Post a message in the #api-facilities channel in Slack, with an @mention to a Lighthouse team member


### URL change example
| Facility API ID  |  Full VA.gov URL |
| ---  |  --- |
| vha_691GM | https://www.va.gov/greater-los-angeles-health-care/locations/oxnard-va-clinic/ |


### Team
Please check the team(s) that will do this work.

- [ ] `CMS Team`
- [ ] `Public Websites`
- [x] `Facilities`
- [x] `User support`
