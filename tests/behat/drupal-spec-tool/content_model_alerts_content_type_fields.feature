@api
Feature: Content model: Alerts Content Type fields
  In order to enter structured content into my site
  As a content editor
  I want to have content type fields that reflect my content model.

  @dst @field_type @content_type_fields @dstfields
     Scenario: Fields
       Then exactly the following fields should exist for bundles "banner" of entity type node
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Content type | Banner | Body | body | Text (formatted, long, with summary) | Required | 1 | Textarea with a summary and counter | Translatable |
| Content type | Banner | Alert type | field_alert_type | List (text) | Required | 1 | Select list | Translatable |
| Content type | Banner | Section | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Banner | Paths | field_target_paths | Text (plain) |  | Unlimited | Textfield |  |
| Content type | Banner | Persistence | field_dismissible_option | List (text) | Required | 1 | Check boxes/radio buttons |  |
