---
name: Runbook - New VAMC system website
about: Creating a new site for a VA healthcare system
title: 'New VAMC System: <insert_name_of_system>'
labels: Change request, Drupal engineering, Facilities, Flagged Facilities, User support, VAMC
assignees: ''

---

## Acceptance criteria

### Information needed from Product / VHA DM
- [ ] The plain language name for the VAMC system. This should follow [the pattern](https://github.com/department-of-veterans-affairs/va.gov-team/tree/master/products/facilities/naming-schema): VA [region name] health care
- [ ] Which VISN is this system in?

### Create initial VAMC System Drupal entities [CMS helpdesk or Sitewide team]
- [ ] Add a new Term in the [Sections taxonomy](https://prod.cms.va.gov/admin/structure/taxonomy/manage/administration/overview) 
   - [ ] Nest the term under VHA > VAMC Facilities > VISN ##
   - [ ] Name = VAMC system plain language name
   - [ ] As you create all the subsequent Drupal entities, assign them to this Section
- [ ] [Add a new Menu](https://prod.cms.va.gov/admin/structure/menu/add)
   - [ ] Title = VAMC system plain language name
   - [ ] Administrative summary = VISN ## | va.gov/plain-language-name
   - [ ] Add 3 menu link items
      - [ ] SERVICES AND LOCATIONS 
      - [ ] NEWS AND EVENTS 
      - [ ] ABOUT VA [REGION NAME]
      - [ ] Link = `<nolink>`
      - [ ] Parent link = `<VAMC system plain language name>`
- [ ] [Add a VAMC System](https://prod.cms.va.gov/node/add/health_care_region_page)
   - [ ] Fill out all required fields
      - [ ] Meta description = placeholder?
      - [ ] Banner image = placeholder?
      - [ ] GovDelivery ID(s) = placeholder?
      - [ ] Menu settings > Menu link title = VAMC system plain language name
      - [ ] Menu settings > Parent link = `<VAMC system plain language name>`
      - [ ] System menu = VAMC system plain language name

### Create one copy of each of these hardened VAMC content types [CMS helpdesk or Sitewide team]
 - [ ] Parent link = `<VAMC system plain language name>`
    - [ ] VAMC System Billing and Insurance
    - [ ] VAMC System Medical Records Office
    - [ ] VAMC System Operating Status
    - [ ] VAMC System Register for Care
 - [ ] Parent link = SERVICES AND LOCATIONS
    - [ ] Health Services List
    - [ ] VAMC System Locations List
 - [ ] Parent link = NEWS AND EVENTS 
    - [ ] Events List
    - [ ] News Releases List
    - [ ] Stories List
 - [ ] Parent link = ABOUT VA [REGION NAME]
    - [ ] Leadership List (will need to be moved later)
    - [ ] VAMC System Policies Page
    - [ ] VAMC System VA Police page

### Clone (from where?) these semi-hardened VAMC detail pages [CMS helpdesk or Sitewide team]
- [ ] Parent link = `<VAMC system plain language name>`
  - [ ] Make an appointment
  - [ ] Pharmacy
- [ ] Parent link = SERVICES AND LOCATIONS / Health Services List
  - [ ] Caregiver support
  - [ ] Homeless Veteran care
  - [ ] LGBT Veteran care
  - [ ] Mental health care
  - [ ] Minority Veteran care
  - [ ] Patient advocates
  - [ ] Returning service members
  - [ ] Suicide prevention
  - [ ] Women Veteran care
- [ ] Parent link = ABOUT VA [REGION NAME]
  - [ ] About us
  - [ ] Programs
  - [ ] Research
  - [ ] Work with us
  - [ ] Contact us
- [ ] Parent link = ABOUT VA [REGION NAME] / About us
  - [ ] Mission and vision
  - [ ] History
  - [ ] Performance
- [ ] Parent link = ABOUT VA [REGION NAME] / Work with us
  - [ ] Jobs and careers
  - [ ] Internships and fellowships
  - [ ] Volunteer or donate
  - [ ] Doing business with us

### Find any existing VAMC facilities that belong to this system [CMS helpdesk or Sitewide team]
- [ ] Set them to the appropriate Section
- [ ] Set their Parent link settings to SERVICES AND LOCATIONS / Locations

### Menu configuration and clean up [CMS helpdesk or Sitewide team]
- [ ] Go to [Content / Menus](https://prod.cms.va.gov/admin/structure/menu)
- [ ] Find the relevant menu and select edit menu
- [ ] Ensure all menu links are nested in the proper place
- [ ] Set some menu links to disabled 
- [ ] Consult [VAMC sitemap in sharepoint](https://dvagov.sharepoint.com/:x:/s/SitewideContract/EblgAS21OUtHloKK3a8ZvNIBHzV1S6uO2l4hj4dqYG0avQ?e=J8UVZh) for menu settings 
- [ ] Enable the menu in Menu Breadcrumbs module
- [ ] Update CONTENT BUILD FILE with the menu

### User set up [CMS helpdesk]
- [ ] Create users if need / assign users to the appropriate section [link to KB article]

### VAMC editor tasks
- [ ] Complete training if they haven't already
- [ ] Do all the things listed [here](https://prod.cms.va.gov/help/vamc)
- [ ] Do we have a checklist like we do for VBA?
- [ ] Confirm when ready to publish

### Launch tasks
- [ ] Lighthouse coordination for service push?
- [ ] Redirects? 
- [ ] Comms, change management?
