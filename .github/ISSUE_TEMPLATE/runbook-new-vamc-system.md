---
name: Runbook - New VAMC system website
about: Creating a new site for a VA healthcare system
title: 'New VAMC System: <insert_name_of_system>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support, VAMC, sitewide
assignees: ''

---

## Acceptance criteria

### Information needed from Product / VHA DM
- [ ] The plain language name for the VAMC system. This should follow [the pattern](https://github.com/department-of-veterans-affairs/va.gov-team/tree/master/products/facilities/naming-schema): VA [region name] health care
- [ ] Which VISN is this system in?

### Create a new menu through config PR [Drupal Engineer]
- [ ] [Add a new Menu](https://staging.cms.va.gov/admin/structure/menu/add) in a non-prod environment
   - [ ] Title = VAMC system plain language name
   - [ ] Administrative summary = VISN ## | va.gov/plain-language-name
- [ ] Enable the new menu in the Menu Settings for these Content types
   - [ ] event
   - [ ] event_listing
   - [ ] health service listing
   - [ ] leadership listing
   - [ ] locations listing
   - [ ] news release
   - [ ] news release listing
   - [ ] publication listing
   - [ ] staff profile
   - [ ] story
   - [ ] story listing
   - [ ] VAMC operating status
   - [ ] VAMC billing and insurance
   - [ ] VAMC facility
   - [ ] VAMC system
   - [ ] VAMC medical records office
   - [ ] VAMC system policies page
   - [ ] VAMC register for care
   - [ ] VAMC VA police
   - [ ] VAMC detail page
- [ ] Enable the menu in Menu Breadcrumbs module
- [ ] Export the config file and create a PR to merge it into the va.gov-cms repo

### Enable menu in Content Build / Next Build [FE Engineer]
- [ ] Update [facilitySidebar.nav.graphql.js](https://github.com/department-of-veterans-affairs/content-build/blob/main/src/site/stages/build/drupal/graphql/navigation-fragments/facilitySidebar.nav.graphql.js) with the machine name of the menu under FACILITY_MENU_NAMES and in the appropriate VISN

### Create initial VAMC System Drupal entities [CMS helpdesk or Sitewide team]
- [ ] Add a new Term in the [Sections taxonomy](https://prod.cms.va.gov/admin/structure/taxonomy/manage/administration/overview) 
   - [ ] Nest the term under VHA > VAMC Facilities > VISN ##
   - [ ] Name = VAMC system plain language name
   - [ ] As you create all the subsequent Drupal entities, assign them to this Section
- [ ] [Add a VAMC System](https://prod.cms.va.gov/node/add/health_care_region_page)
   - [ ] Fill out all required fields
      - [ ] Meta description 
      - [ ] Banner image 
      - [ ] GovDelivery ID(s)
      - [ ] Menu settings > Menu link title = VAMC system plain language name
      - [ ] Menu settings > Parent link = `<VAMC system plain language name>`
      - [ ] System menu = VAMC system plain language name

### Create one copy of each of these hardened VAMC content types [CMS helpdesk or Sitewide team]
 - [ ] Parent link = `<VAMC system plain language name>`
    - [ ] VAMC System Billing and Insurance
    - [ ] VAMC System Medical Records Office
    - [ ] VAMC System Operating Status
    - [ ] VAMC System Register for Care
    - [ ] Health Services List
    - [ ] VAMC System Locations List
    - [ ] Events List
    - [ ] News Releases List
    - [ ] Stories List
    - [ ] Leadership List
    - [ ] VAMC System Policies Page
    - [ ] VAMC System VA Police page

### Clone these semi-hardened VAMC detail pages [CMS helpdesk or Sitewide team]
- [ ] Find an existing VAMC in the same VISN as the new system and clone from there
- [ ] You will need to edit the cloned pages to update any references to the VAMC system, and fix links to other pages.
- [ ] Parent link = `<VAMC system plain language name>`
  - [ ] Make an appointment
  - [ ] Pharmacy
  - [ ] About us
  - [ ] Programs
  - [ ] Research
  - [ ] Work with us
  - [ ] Contact us
- [ ] Parent link = Health Services List
  - [ ] Caregiver support
  - [ ] Homeless Veteran care
  - [ ] LGBT Veteran care
  - [ ] Mental health care
  - [ ] Minority Veteran care
  - [ ] Patient advocates
  - [ ] Returning service members
  - [ ] Suicide prevention
  - [ ] Women Veteran care
- [ ] Parent link = About us
  - [ ] Mission and vision
  - [ ] History
  - [ ] Performance
- [ ] Parent link = Work with us
  - [ ] Jobs and careers
  - [ ] Internships and fellowships
  - [ ] Volunteer or donate
  - [ ] Doing business with us

### Find any existing VAMC facilities that belong to this system [CMS helpdesk or Sitewide team]
- [ ] Set them to the appropriate Section
- [ ] Set their Parent link settings to Locations

### Menu configuration and clean up [CMS helpdesk or Sitewide team]
- [ ] Go to [Content / Menus](https://prod.cms.va.gov/admin/structure/menu)
- [ ] Find the relevant menu and select edit menu
- [ ] Ensure all menu links are nested in the proper place
- [ ] Set some menu links to disabled 
- [ ] Consult [VAMC sitemap in sharepoint](https://dvagov.sharepoint.com/:x:/s/SitewideContract/EblgAS21OUtHloKK3a8ZvNIBHzV1S6uO2l4hj4dqYG0avQ?e=J8UVZh) for menu settings

### URL alias configuration [CMS helpdesk or Sitewide team]
- [ ] Check that each page has the correct URL alias matching the [VAMC sitemap in sharepoint](https://dvagov.sharepoint.com/:x:/s/SitewideContract/EblgAS21OUtHloKK3a8ZvNIBHzV1S6uO2l4hj4dqYG0avQ?e=J8UVZh)
- [ ] If pages dont have the correct URL Alias, change them from Auto to Manual and input the correct URL alias

### User set up [CMS helpdesk]
- [ ] Create users if needed
- [ ] Assign users to the appropriate section
- [ ] Follow guidance in [CMS User Administration documentation](https://github.com/department-of-veterans-affairs/va.gov-team/tree/master/platform/cms/user-administration)

### VAMC editor tasks
- [ ] Complete training if they haven't already
- [ ] Follow the instructions in [VAMC editor guide](https://prod.cms.va.gov/help/vamc)
- [ ] Confirm when ready to publish

### Launch tasks
- [ ] Lighthouse coordination for service push?
- [ ] Redirects? 
- [ ] Comms, change management?
