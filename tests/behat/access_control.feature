@api
Feature: Access control
  In order to protect my site and its content
  As a site owner
  I want to control access with user roles and permissions.

  @spec @access
  Scenario: User roles
    Then exactly the following roles should exist
      | Name               | Machine name      |
      | Administrator      | administrator     |
      | Anonymous user     | anonymous         |
      | Authenticated user | authenticated     |
      | Media creator      | media_creator     |
      | Media manager      | media_manager     |
      | Content creator    | content_creator   |
      | Content publisher  | content_publisher |
      | Content reviewer   | content_reviewer  |
