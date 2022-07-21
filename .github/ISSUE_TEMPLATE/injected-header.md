---
name: (PW) Injected Header/Footer - prep for testing
about: Submit a request to add the injected header/footer an existing site.
title: 'Injected header/footer: <domain(s)>'
labels: ⭐️ Public Websites, VA.gov frontend, Injected header, Needs refining
assignees: jilladams

---

## Description
A new site is requesting the injected header/footer: 

**Production url:** 
**Staging url (if any):**

Please handle both www and non-www versions of the URL.

## Tasks
Public Websites team will need to:

- [ ] add the domain with **both www and non-www** to the [proxy-rewrite-whitelist.json](https://github.com/department-of-veterans-affairs/vets-website/blob/main/src/applications/proxy-rewrite/proxy-rewrite-whitelist.json) allow list, with `"cookieOnly": true` for staging testing. - .5hr

- [ ] add the domain to Devops code that handles the TeamSite CORS allowed origins for `bucket-prod-va-gov-assets` and `bucket-preview-va-gov-assets`. The buckets are now part of the vets-api module, so you just need to make sure any needed origins are in the local allowed_origins list: https://github.com/department-of-veterans-affairs/devops/blob/master/terraform/environments/dsva-vagov-prod/main.tf#L274 - .5hr

- [ ] PR shepherding - .5-1hr

2-2.5hrs

## Additional context 
[DEPO teamsite overview](https://depo-platform-documentation.scrollhelp.site/developer-docs/teamsite-overview) - explains the mechanisms, and has notes on testing, for the implementing team.  (And has an incorrect code pointer, ticketed here: https://github.com/department-of-veterans-affairs/va.gov-team/issues/43364)

After PW updates allowlists, the requesting team will need to test on Staging by setting a cookie in browser, per the TeamSite docs. The main issues are styling related, where styles from the site may affect the presentation of the header/footer. Those issues can/should be fixed by updating the CSS of the site / app (rather than by modifying styles for the header / footer globally). This is the bulk of their work, and if no style issues occur, it could potentially be a no-op. Once requesting team confirms that the injected header/footer work and they're ready to publish, PW will manage a separate issue to update code to `cookieOnly: false ` in order to permanently expose the header/footer on the site. 
- Production cookie update ticket: 

## To test

- Load the requested URL
- Open developer tools, Console
- Type document.cookie = "proxyRewrite=true;", and hit Enter. This creates a cookie that you will then find under Application cookies in dev tools until you clear cookies.
- Refresh the page, and injected header should load.

## Acceptance Criteria
- [ ] On the requested domains, when setting cookie in the console the global header is injected
- [ ] Let DM know when your changes have deployed to production, so they can notify the requesting team

### CMS Team
Please check the team(s) that will do this work.

- [ ] `Program`
- [ ] `Platform CMS Team`
- [ ] `Sitewide Crew`
- [ ] `⭐️ Sitewide CMS`
- [X] `⭐️ Public Websites`
- [ ] `⭐️ Facilities`
- [ ] `⭐️ User support`
