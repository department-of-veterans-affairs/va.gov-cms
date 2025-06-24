Feature: API

  Scenario: JSON API consumer can access route translation for a given news_story
    Given I am an anonymous user
    And I should receive status code 200 when I request "/router/translate-path?path=%2Fboston-health-care%2Fstories%2F600-volunteers-45000-hours-one-inspiring-week"
  Scenario: JSON API consumer can access news_story nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/news_story?page[limit]=1&include=field_media%2Cfield_media.image%2Cfield_author%2Cfield_listing%2Cfield_administration"
