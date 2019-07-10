@api
Feature: Menus
  In order to organize my content hierarchically
  As a content editor
  I want to have menus that reflect my information architecture.

  @spec @menus
  Scenario: Menus
    Then exactly the following menus should exist
      | Name | Machine name | Description |
      | Administration | admin | Administrative task links |
      | Footer | footer | Site information links |
      | Main navigation | main | Site section links |
      | Tools | tools | User tool links, often added by modules |
      | User account menu | account | Links related to the active user account |
      | Careers & Employment Benefits Hub | careers-employment-benefits | va.gov/careers-employment                                                       |
      | Health Care benefits hub          | health-care-benefits-hub    | va.gov/health-care                                                              |
      | Outreach and Events               | outreach-and-events         |                                                                                 |
      | Pension Benefits Hub              | pension-benefits-hub        | va.gov/pension                                                                  |
      | Pittsburgh Health Care            | pittsburgh-health-care      | va.gov/pittsburgh-health-care                                                   |
      | Records Benefits Hub              | records-benefits-hub        | va.gov/records                                                                  |
      | Sections                          | sections                    | Automatically updated from the Sections taxonomy, and appears in admin toolbar. |
      | VA.gov CMS documentation          | documentation               | How-to's for editing content in the VA.gov CMS                                  |
