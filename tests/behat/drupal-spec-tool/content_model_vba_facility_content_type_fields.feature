@api
Feature: Content model: VBA facility Content Type fields
  In order to enter structured content into my site
  As a content editor
  I want to have content type fields that reflect my content model.

  @dst @field_type @content_type_fields @dstfields
     Scenario: Fields
       Then exactly the following fields should exist for bundles "vba_facility" of entity type node
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Content type | VBA Facility | Hours additional info | field_additional_hours_info | Text (plain) |  | 1 | Textfield |  |
| Content type | VBA Facility | Address | field_address | Address | Required | 1 | Address |  |
| Content type | VBA Facility | Section | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VBA Facility | Facility Locator API ID | field_facility_locator_api_id | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | VBA Facility | Facility Type | field_facility_type | List (text) | Required | 1 | Select list |  |
| Content type | VBA Facility | Fax | field_fax_number | Telephone number |  | 1 | Telephone number |  |
| Content type | VBA Facility | Legacy URL | field_legacy_url | Link |  | 1 | Link |  |
| Content type | VBA Facility | Regional Office | field_office | Entity reference | Required | 1 | Select list |  |
| Content type | VBA Facility | Hours | field_office_hours | Office hours |  | 1 | Office hours (week) |  |
| Content type | VBA Facility | Operating status | field_operating_status_facility | List (text) | Required | 1 | Select list | Translatable |
| Content type | VBA Facility | Operating status - more info | field_operating_status_more_info | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | VBA Facility | Phone | field_phone_number | Telephone number |  | 1 | Telephone number |  |
| Content type | VBA Facility | Services | field_services | Entity reference |  | Unlimited | Check boxes/radio buttons |  |
