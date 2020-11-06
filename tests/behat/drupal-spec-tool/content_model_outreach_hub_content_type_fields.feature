@api
Feature: Content model: Outreach hub Content Type fields
  In order to enter structured content into my site
  As a content editor
  I want to have content type fields that reflect my content model.

  @dst @field_type @content_type_fields @dstfields                                                                                                                                                               
     Scenario: Fields
       Then exactly the following fields should exist for bundles "office,outreach_asset,publication_listing" of entity type node
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Content type | Office | Body | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Office | Meta description | field_description | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | Office | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Office | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Office | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Publication | File or video | field_media | Entity reference |  | 1 | Media library |  |
| Content type | Publication | Format | field_format | List (text) | Required | 1 | Select list |  |
| Content type | Publication | Meta description | field_description | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | Publication | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Publication | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Publication | Publication listing | field_listing | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Publication | Related Benefits | field_benefits | List (text) |  | 1 | Select list |  |
| Content type | Publication Listing Page | Intro text | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Publication Listing Page | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Publication Listing Page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Publication Listing Page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Publication Listing Page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Publication Listing Page | Related office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
