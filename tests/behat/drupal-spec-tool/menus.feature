@api
Feature: Menus
  In order to organize my content hierarchically
  As a content editor
  I want to have menus that reflect my information architecture.

  @dst @menus                                                                                                                                                                             
     Scenario: Menus
       Then exactly the following menus should exist
       | Name | Machine name | Description |
| Administration | admin | Administrative task links |
| Footer | footer | Site information links |
| Main navigation | main | Site section links |
| Tools | tools | User tool links, often added by modules |
| User account menu | account | Links related to the active user account |
| Health Care benefits hub | health-care-benefits-hub | va.gov/health-care |
| Outreach and events | outreach-and-events |  |
| VA Pittsburgh health care | pittsburgh-health-care | va.gov/pittsburgh-health-care |
| Sections | sections | Automatically updated from the Sections taxonomy, and appears in admin toolbar. |
| VA.gov CMS documentation | documentation | How-to's for editing content in the VA.gov CMS |
| Burials and memorials benefits hub | burials-and-memorials-benef | For pages in the /burials-and-memorials benefits hub |
| Careers & employment benefits hub | careers-employment-benefits | va.gov/careers-employment |
| Decision reviews benefits hub | decision-reviews-benefits-h |  |
| Development | devel | Links related to Devel module. |
| Disability benefits hub | disability-benefits-hub | For pages in the /disability benefits hub |
| Education benefits hub | education-benefits-hub | For pages in the /education benefits hub |
| Housing Assistance benefits hub | housing-assistance-benefits | va.gov/housing-assistance |
| Life insurance benefits hub | life-insurance-benefits-hub | va.gov/life-insurance |
| Pension benefits hub | pension-benefits-hub | va.gov/pension |
| Records benefits hub | records-benefits-hub | va.gov/records |
| Root pages | root-benefits-hub | For various pages that live at the top level of the URL structure. |
| Homepage top tasks blocks | homepage-top-tasks-blocks |  |
| Header megamenu | header-megamenu | Links and promos for the site's header menu. |
| Footer Bottom Rail | footer-bottom-rail | Horizontal list of links at the bottom of the page |
