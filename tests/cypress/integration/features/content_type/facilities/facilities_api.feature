@facilities_api
Feature: CMS Users may not unintentionally change information in fields populated by the Facilities API
  In order to confirm that cms users cannot change Facilities API data
  As anyone involved in the project
  I need to have certain fields locked down

  # Content Admin
  Scenario: Log in and edit a <contentType>: <title> (<nid>) as <roleTitle>
    Given I am logged in as a user with the roles "<roles>"
    And my workbench access sections are set to "<sections>"
    When I unlock node <nid>
    And I am at "/node/<nid>/edit"
    Then the Facility Locator API ID field should not be editable

  Examples:
    | roles                                           | roleTitle                    | sections  | contentType           | nid     | title                                                             |
    | content_admin                                   | Content Admin                | 2         | NCA                   | 4610    | Louisiana National Cemetery                                       |
    | content_admin                                   | Content Admin                | 12        | VAMC facility         | 175     | H. John Heinz III Department of Veterans Affairs Medical Center   |
    | content_admin                                   | Content Admin                | 190       | Vet Center Outstation | 17533   | Clarksville Vet Center Outstation                                 |
    | content_admin                                   | Content Admin                | 190       | Vet Center            | 3769    | Cheyenne Vet Center                                               |
    | content_admin                                   | Content Admin                | 190       | Mobile Vet Center     | 17503   | Evanston Mobile Vet Center                                        |
    | content_admin                                   | Content Admin                | 190       | VBA Facility          | 4338    | Cheyenne VA Regional Benefit Office                               |
    | vamc_content_creator, content_publisher         | Content Editor (VAMC)        | 12        | VAMC facility         | 175     | H. John Heinz III Department of Veterans Affairs Medical Center   |
    | content_creator_vet_center, content_publisher   | Content Editor (Vet Centers) | 190       | Vet Center Outstation | 17533   | Clarksville Vet Center Outstation                                 |
    | content_creator_vet_center, content_publisher   | Content Editor (Vet Centers) | 190       | Vet Center            | 3769    | Cheyenne Vet Center                                               |
    | content_creator_vet_center, content_publisher   | Content Editor (Vet Centers) | 190       | Mobile Vet Center     | 17503   | Evanston Mobile Vet Center                                        |
    | content_publisher, content_editor               | Content Editor (NCA)         | 2         | NCA                   | 4610    | Louisiana National Cemetery                                       |
    | content_creator_vba, content_publisher          | Content Editor (VBA)         | 1104      | VBA Facility          | 4338    | Cheyenne VA Regional Benefit Office                               |
