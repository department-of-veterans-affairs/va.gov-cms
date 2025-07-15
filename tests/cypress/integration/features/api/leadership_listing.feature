Feature: API

  Scenario: JSON API consumer can access leadership_listing nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/leadership_listing?page[limit]=1&include=field_administration%2Cfield_facility%2Cfield_office%2Cfield_primary_cta%2Cfield_related_links"
