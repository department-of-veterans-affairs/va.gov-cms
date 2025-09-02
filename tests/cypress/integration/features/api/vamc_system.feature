Feature: API

  Scenario: JSON API consumer can access route translation for a given vamc_system
    Given I am an anonymous user
    And I should receive status code 200 when I request "/router/translate-path?path=%2Fboston-health-care"
  Scenario: JSON API consumer can access vamc_system nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/health_care_region_page?page[limit]=1&filter[status]=1&include=field_media%2Cfield_media.image%2Cfield_administration%2Cfield_related_links%2Cfield_related_links.field_va_paragraphs"
