@api
Feature: Access control
  In order to protect my site and its content
  As a site owner
  I want to control access with user roles and permissions.

  @spec
  Scenario: User roles
    Then exactly the following roles should exist
      | Name                  | Machine name          |
      | Administrator         | administrator         |
      | Anonymous user        | anonymous             |
      | Authenticated user    | authenticated         |
      | Basic page creator    | page_creator          |
      | Basic page reviewer   | page_reviewer         |
      | Landing page creator  | landing_page_creator  |
      | Landing page reviewer | landing_page_reviewer |
      | Layout manager        | layout_manager        |
      | Media creator         | media_creator         |
      | Media manager         | media_manager         |
