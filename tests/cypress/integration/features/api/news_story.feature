Feature: API

  Scenario: JSON API consumer can access news_story nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/news_story?page[limit]=1&include=field_media%2Cfield_media.image%2Cfield_author%2Cfield_listing%2Cfield_administration"
