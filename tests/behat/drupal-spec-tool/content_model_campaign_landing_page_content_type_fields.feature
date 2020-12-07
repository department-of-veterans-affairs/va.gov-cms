@api
Feature: Content model: Campaign Landing Page Content Type fields
  In order to enter structured content into my site
  As a content editor
  I want to have content type fields that reflect my content model.

  @dst @field_type @content_type_fields @dstfields @campaign_landing_page
     Scenario: Fields
       Then exactly the following fields should exist for bundles "campaign_landing_page" of entity type node
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Content type | Campaign Landing Page | Add a Spotlight panel | field_clp_spotlight_panel | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | Campaign Landing Page | Add a Stories panel | field_clp_stories_panel | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | Campaign Landing Page | Add a link to an external blog or other list of stories | field_clp_stories_cta | Link |  | 1 | Link |  |
| Content type | Campaign Landing Page | Add a link to more videos | field_clp_video_panel_more_video | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL |  |
| Content type | Campaign Landing Page | Add a video panel | field_clp_video_panel | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | Campaign Landing Page | Add up to two stories | field_clp_stories_teasers | Entity reference revisions |  | 2 | Paragraphs Classic |  |
| Content type | Campaign Landing Page | Additional links | field_clp_spotlight_link_teasers | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL |  |
| Content type | Campaign Landing Page | Hero Image (Optional) | field_hero_image | Entity reference |  | 1 | Media library |  |
| Content type | Campaign Landing Page | Hero blurb | field_hero_blurb | Text (plain) |  | 1 | Textfield with counter |  |
| Content type | Campaign Landing Page | Optional Spotlight cta | field_clp_spotlight_cta | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL |  |
| Content type | Campaign Landing Page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Campaign Landing Page | Panel Header | field_clp_video_panel_header | Text (plain) |  | 1 | Textfield with counter |  |
| Content type | Campaign Landing Page | Primary call to action | field_primary_call_to_action | Entity reference revisions | Required | 1 | Paragraphs EXPERIMENTAL |  |
| Content type | Campaign Landing Page | Related benefits | field_benefit_categories | Entity reference | Required | Unlimited | Check boxes/radio buttons | Translatable |
| Content type | Campaign Landing Page | Secondary call to action | field_secondary_call_to_action | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL |  |
| Content type | Campaign Landing Page | Spotlight header | field_clp_spotlight_header | Text (plain) |  | 1 | Textfield with counter |  |
| Content type | Campaign Landing Page | Spotlight intro text | field_clp_spotlight_intro_text | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter |  |
| Content type | Campaign Landing Page | Stories header | field_clp_stories_header | Text (plain) |  | 1 | Textfield with counter |  |
| Content type | Campaign Landing Page | Stories intro | field_clp_stories_intro | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter |  |
| Content type | Campaign Landing Page | Video | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | Campaign Landing Page | What you can do header | field_clp_what_you_can_do_header | Text (plain) | Required | 1 | Textfield with counter |  |
| Content type | Campaign Landing Page | What you can do intro text | field_clp_what_you_can_do_intro | Text (plain, long) | Required | 1 | Textarea (multiple rows) with counter |  |
| Content type | Campaign Landing Page | What you can do promos | field_clp_what_you_can_do_promos | Entity reference |  | 3 | Entity Browser - Table |  |
| Content type | Campaign Landing Page | Why this matters to you | field_clp_why_this_matters | Text (plain, long) | Required | 1 | Textarea (multiple rows) with counter |  |
| Content type | Campaign Landing Page | Add a Resources panel | field_clp_resources_panel | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | Campaign Landing Page | Downloadable resources cta | field_clp_resources_cta | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL |  |
| Content type | Campaign Landing Page | Downloadable resources header | field_clp_resources_header | Text (plain) |  | 1 | Textfield with counter |  |
| Content type | Campaign Landing Page | Downloadable resources intro text | field_clp_resources_intro_text | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter |  |
| Content type | Campaign Landing Page | Media resources | field_clp_resources | Entity reference |  | 3 | Entity browser |  |
