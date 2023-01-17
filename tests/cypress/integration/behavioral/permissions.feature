Feature: Permissions

  Scenario: Content api consumer can access redirects and graphql api
    Given I am logged in as a user with the "content_api_consumer" role
    And I should receive status code 200 when I request "/graphql"
    And I should receive status code 200 when I request "/flags_list"
    And I should receive status code 200 when I request "/api/govdelivery_bulletins/queue?EndTime=1"

  Scenario: Redirect administrator can add/edit, administer redirects
    Given I am logged in as a user with the "redirect_administrator" role
    And I should receive status code 200 when I request "/admin/config/search/redirect/edit/261"
    And I should receive status code 200 when I request "/admin/config/search/redirect/add"
    And I should receive status code 200 when I request "/admin/config/search/redirect/migrate"
    And I should receive status code 200 when I request "/admin/config/search/redirect"

  Scenario: Administer user role can add/edit users
    Given I am logged in as a user with the "admnistrator_users" role
    And I should receive status code 200 when I request "/admin/people"
    And I should receive status code 200 when I request "/admin/people/create"

  Scenario: Content api consumer cannot alter existing menus
    Given I am logged in as a user with the "content_api_consumer" role
    And I should receive status code 403 when I request "/admin/structure/menu"

  Scenario: Content Admins should be able to browse sections from their profile even if none have been specifically assigned to them
    Given I am logged in as a user with the "content_admin" role
    And I am at "/user"
    Then I should see "You can edit content in the following VA.gov sections"
    And I should not see "You don't have permission to access content in any VA.gov sections yet"
