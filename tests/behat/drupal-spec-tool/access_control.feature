@api
Feature: Access control
  In order to protect my site and its content
  As a site owner
  I want to control access with user roles and permissions.

  @spec @access
  Scenario: User roles
    Then exactly the following roles should exist
      | Name | Machine name |
      | Administrator | administrator |
      | Anonymous user | anonymous |
      | Authenticated user | authenticated |
      | Content API Consumer | content_api_consumer |
      | Content editor | content_editor |
      | Content publisher | content_publisher |
      | Content reviewer | content_reviewer |
      | User access admin | admnistrator_users |
      | Redirect Administrator | redirect_administrator |
      | Documentation editor | documentation_editor |
