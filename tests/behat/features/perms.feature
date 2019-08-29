@api
Feature: Permissions
      """
      Pattern for content type reuse.
      | title                              |
      | "page"                             |
      | "landing_page"                     |
      | "health_care_region_detail_page"   |
      | "documentation_page"               |
      | "event"                            |
      | "event_listing"                    |
      | "health_care_local_facility"       |
      | "health_care_region_page"          |
      | "health_care_local_health_service" |
      | "press_release"                    |
      | "office"                           |
      | "outreach_asset"                   |
      | "publication_listing"              |
      | "person_profile"                   |
      | "news_story"                       |
      """
  @perms @content_api_consumer
  Scenario Outline: Content api consumer can access redirects and graphql api
      """
      We may want to extend this test for other roles
      """
    Given I am logged in as a user with the <role> role
    And I am on <page>
    Then the response status code should be <code>
    Examples:
      | role                   | page          | code |
      | "content_api_consumer" | "/graphql"    | 200  |
      | "content_api_consumer" | "/flags_list" | 200  |


  @perms @content_editor
  Scenario Outline: Content editor can edit - edit test indicates save perm is true.
      """
      We may want to extend this test for other roles
      """
    Given I am logged in as a user with the <role> role
    Then I should be able to edit an <type>
    Examples:
      | role             | type                               |
      | "content_editor" | "page"                             |
      | "content_editor" | "landing_page"                     |
      | "content_editor" | "health_care_region_detail_page"   |
      | "content_editor" | "documentation_page"               |
      | "content_editor" | "event"                            |
      | "content_editor" | "event_listing"                    |
      | "content_editor" | "health_care_local_facility"       |
      | "content_editor" | "health_care_region_page"          |
      | "content_editor" | "health_care_local_health_service" |
      | "content_editor" | "press_release"                    |
      | "content_editor" | "office"                           |
      | "content_editor" | "outreach_asset"                   |
      | "content_editor" | "publication_listing"              |
      | "content_editor" | "person_profile"                   |
      | "content_editor" | "news_story"                       |


  @perms @content_reviewer @content_publisher
  Scenario Outline: Content reviewer can review content publisher can publish, authenticated can only create.
    Given I am logged in as a user with the "authenticated" role
    Then I am viewing an <type> with the title <title>
    Then I am logged in as a user with the "administrator" role
    And I visit the "edit" page for a node with the title <title>
    Then I should see "EDITORIAL WORKFLOW"
    And the "#edit-moderation-state-0-current" element should exist
    And I should see "Change to"
    And the "#edit-moderation-state-0-state" element should exist
    And I should see "Revision log message"

    Then I am logged in as a user with the "authenticated" role
    And I visit the "edit" page for a node with the title <title>
    Then the "#edit-moderation-state-0-state" element should not exist

    Then I am logged in as a user with the "content_reviewer" role
    And I visit the "edit" page for a node with the title <title>
    Then "#edit-moderation-state-0-state" should contain "review"

    Then I am logged in as a user with the "content_publisher" role
    And I visit the "edit" page for a node with the title <title>
    Then "#edit-moderation-state-0-state" should contain "published"
    Examples:
      | type                             | title                                 |
      | "page"                           | "page page"                           |
      | "landing_page"                   | "landing_page page"                   |
      | "health_care_region_detail_page" | "health_care_region_detail_page page" |
      | "documentation_page"             | "documentation_page page"             |
      | "event"                          | "event page"                          |
      | "event_listing"                  | "event_listing page"                  |
      | "health_care_local_facility"     | "health_care_local_facility page"     |
      | "health_care_region_page"        | "health_care_region_page page"        |
      | "press_release"                  | "press_release page"                  |
      | "outreach_asset"                 | "outreach_asset page"                 |
      | "publication_listing"            | "publication_listing page"            |
      | "news_story"                     | "news_story page"                     |


  @perms @field_administration
  Scenario Outline: Checkc for field_administration.
    Given I am logged in as a user with the "administrator" role
    Then I am viewing an <type> with the title <title>
    Then I visit the "edit" page for a node with the title <title>
    And the "#edit-field-administration" element should exist
    And "#edit-field-administration" should have the "required" with "required"
    Examples:
      | type                             | title                                  |
      | "page"                           | "page page2"                           |
      | "landing_page"                   | "landing_page page2"                   |
      | "health_care_region_detail_page" | "health_care_region_detail_page page2" |
      | "event"                          | "event page2"                          |
      | "event_listing"                  | "event_listing page2"                  |
      | "health_care_local_facility"     | "health_care_local_facility page2"     |
      | "health_care_region_page"        | "health_care_region_page page2"        |
      | "press_release"                  | "press_release page2"                  |
      | "outreach_asset"                 | "outreach_asset page2"                 |
      | "publication_listing"            | "publication_listing page2"            |
      | "news_story"                     | "news_story page2"                     |


  @perms @field_owner
  Scenario: Check for field_owner on media types.
    Given I am logged in as a user with the "administrator" role
    Then I visit "/media/add/image"
    And "#edit-field-owner" should have the "required" with "required"
    Then I visit "/media/add/document"
    And "#edit-field-owner" should have the "required" with "required"
    Then I visit "/media/add/video"
    And "#edit-field-owner" should have the "required" with "required"


  @perms @unnecessary_fields
  Scenario Outline: Check that node and media forms have Sticky, Promoted, Published, Author, or Created.
    Given I am logged in as a user with the "authenticated" role
    Then I am viewing an <type> with the title <title>
    And I should not see exactly "Sticky"
    Then I should not see exactly "Promoted"
    And I should not see exactly "Published"
    Then I should not see exactly "Author"
    And I should not see exactly "Created"
    Then I visit the "edit" page for a node with the title <title>
    And I should not see exactly "Sticky"
    Then I should not see exactly "Promoted"
    And I should not see exactly "Published"
    Then I should not see exactly "Author"
    And I should not see exactly "Created"
    Examples:
      | type                             | title                                  |
      | "page"                           | "page page3"                           |
      | "landing_page"                   | "landing_page page3"                   |
      | "health_care_region_detail_page" | "health_care_region_detail_page page3" |
      | "documentation_page"             | "documentation_page page3"             |
      | "event"                          | "event page3"                          |
      | "event_listing"                  | "event_listing page3"                  |
      | "health_care_local_facility"     | "health_care_local_facility page3"     |
      | "health_care_region_page"        | "health_care_region_page page3"        |
      | "press_release"                  | "press_release page3"                  |
      | "outreach_asset"                 | "outreach_asset page3"                 |
      | "office"                         | "office page3"                         |
      | "publication_listing"            | "publication_listing page3"            |
      | "news_story"                     | "news_story page3"                     |


  @perms @redirects
  Scenario Outline: Redirect administrator can add/edit, administer redirects
    Given I am logged in as a user with the "redirect_administrator" role
    And I am on <page>
    Then the response status code should be <code>
    Examples:
      | page                                     | code |
      | "/admin/config/search/redirect/edit/261" | 200  |
      | "/admin/config/search/redirect/add"      | 200  |
      | "/admin/config/search/redirect/import"   | 200  |
      | "/admin/config/search/redirect"          | 200  |


  @perms @administer_users
  Scenario Outline: Adminiser user role can add/edit users
    Given I am logged in as a user with the "admnistrator_users" role
    And I am on <page>
    Then the response status code should be <code>
    Examples:
      | page                   | code |
      | "/admin/people"        | 200  |
      | "/admin/people/create" | 200  |
