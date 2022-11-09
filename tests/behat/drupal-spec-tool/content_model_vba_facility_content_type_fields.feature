@api
Feature: Content model: VBA facility Content Type fields
  In order to enter structured content into my site
  As a content editor
  I want to have content type fields that reflect my content model.

  @dst @field_type @content_type_fields @dstfields
     Scenario: Fields
       Then exactly the following fields should exist for bundles "vba_facility,vba_facility_service" of entity type node
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Content type | VBA Facility | Address | field_address | Address |  | 1 | Address | Translatable |
| Content type | VBA Facility | Section | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VBA Facility | Facility Locator API ID | field_facility_locator_api_id | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | VBA Facility | Classification | field_facility_vba_classificatio | List (text) |  | 1 | Select list |  |
| Content type | VBA Facility | Geolocation | field_geolocation | Geofield |  | 1 | Latitude/Longitude | Translatable |
| Content type | VBA Facility | Page introduction | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | VBA Facility | Facility photo | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | VBA Facility | Non-VA location URL | field_non_va_location_url | Link |  | 1 | Link |  |
| Content type | VBA Facility | Non-VA location official name | field_non_va_official_name | Text (plain) |  | 1 | Textfield |  |
| Content type | VBA Facility | Parent office | field_office | Entity reference |  | 1 | Select list | Translatable |
| Content type | VBA Facility | Hours | field_office_hours | Office hours |  | Unlimited | Office hours (week) | Translatable |
| Content type | VBA Facility | Operating status | field_operating_status_facility | List (text) | Required | 1 | Select list | Translatable |
| Content type | VBA Facility | Operating status - more info | field_operating_status_more_info | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | VBA Facility | Phone number | field_phone_number | Telephone number |  | 1 | Telephone number | Translatable |
| Content type | VBA Facility | Shared VHA location | field_shared_vha_location | Entity reference |  | 1 | Autocomplete |  |
| Content type | VBA Facility | Services | field_vba_services | Entity reference |  | Unlimited | Inline entity form - Complex - Table View Mode |  |
| Content type | VBA Facility Service | Section | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VBA Facility Service | VBA service description | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | VBA Facility Service | Enforce unique combo | field_enforce_unique_combo | Allow Only One |  | 1 | Allow Only One widget | Translatable |
| Content type | VBA Facility Service | Facility | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VBA Facility Service | Service name | field_service_name_and_descripti | Entity reference | Required | 1 | Select list | Translatable |
