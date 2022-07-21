---
name: (PW) Injected Header/Footer - publish to prod
about: Submit a request to publish the injected header/footer to prod.
title: 'Injected header/footer: Publish to prod: <domain(s)>'
labels: ⭐️ Public Websites, VA.gov frontend, Injected header, Needs refining
assignees: jilladams

---

## Description
Original request to set up new site for injected header/footer: 
_Updates in this ticket should be made to the domain(s) listed / code updated in the original ticket._

This ticket adds new domain(s) to allowlists that gate the injected header. When the requsting team confirms their testing is complete, Public Websites will 

- [ ] update [proxy-rewrite-whitelist.json](https://github.com/department-of-veterans-affairs/vets-website/blob/main/src/applications/proxy-rewrite/proxy-rewrite-whitelist.json) code to `cookieOnly: false ` to permanently display the header 


## Acceptance Criteria
- [ ] Devops PRs to add www & non-www domain(s) have merged
- [ ] Requesting team has signed off that they're ready to publish
- [ ] On requested domain(s) the global header is injected on page load, with no cookie updates required

### CMS Team
Please check the team(s) that will do this work.

- [ ] `Program`
- [ ] `Platform CMS Team`
- [ ] `Sitewide Crew`
- [ ] `⭐️ Sitewide CMS`
- [X] `⭐️ Public Websites`
- [ ] `⭐️ Facilities`
- [ ] `⭐️ User support`
