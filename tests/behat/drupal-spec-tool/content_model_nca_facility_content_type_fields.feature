@api
Feature: Content model: NCA facility Content Type fields
  In order to enter structured content into my site
  As a content editor
  I want to have content type fields that reflect my content model.

  @dst @field_type @content_type_fields @dstfields                                                                                                                                                               
     Scenario: Fields
       Then exactly the following fields should exist for bundles "nca_facility" of entity type node
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Content type | NCA Facility | Facility Locator API ID | field_facility_locator_api_id | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | NCA Facility | Operating status | field_operating_status_facility | List (text) | Required | 1 | Select list | Translatable |
| Content type | NCA Facility | Operating status - more info | field_operating_status_more_info | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | NCA Facility | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
