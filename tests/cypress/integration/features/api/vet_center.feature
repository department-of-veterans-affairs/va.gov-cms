Feature: API

  Scenario: JSON API consumer can access route translation for a given vet_center nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/router/translate-path?path=%2Ftacoma-vet-center"
  Scenario: JSON API consumer can access vet_center nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/vet_center?page[limit]=1&filter[status]=1&include=field_media,field_media.image,field_administration,field_prepare_for_visit,field_vet_center_feature_content,field_vet_center_feature_content.field_cta,field_health_services,field_health_services.field_service_name_and_descripti"