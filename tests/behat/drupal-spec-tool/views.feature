@api
Feature: Views
  In order to present and expose content and configuration
  As a site owner
  I want to have views for various contexts and applications.

  @dst @views
     Scenario: Views
       Then exactly the following views should exist
       | Name | Machine name | Base table | Status | Description |
| Archive | archive | Content | Disabled | All content, by month. |
| Custom block library | block_content | Custom Block | Enabled | Find and manage custom blocks. |
| Build info | build_info | Content | Enabled |  |
| Child terms | child_terms | Taxonomy terms | Enabled |  |
| Content | content | Content | Enabled | Find and manage content. |
| Recent content | content_recent | Content | Disabled | Recent content. |
| Content served from Drupal | content_served_from_drupal | Content | Enabled | An exportable list of all content served from Drupal |
| Files | files | Files | Enabled | Find and manage files. |
| Frontpage | frontpage | Content | Enabled | All content promoted to frontpage |
| Glossary | glossary | Content | Disabled | All content, by letter. |
| Health care service names and descriptions | health_care_service_names_and_descriptions | Taxonomy terms | Enabled | A list of nationally-controlled health care service names and descriptions |
| Health service offerings | health_service_offerings | Content | Enabled |  |
| Local facilities entity reference view | local_facilities_entity_reference_view | Content | Enabled | An entity reference view that determines options for the Local Health Service descriptions |
| Locked content  | locked_content | Content | Enabled |  |
| Media | media | Media | Enabled |  |
| Media library widget | media_library | Media | Enabled |  |
| Moderated content | moderated_content | Content revisions | Enabled | Find and moderate content. |
| Moderation history | moderation_history | Content revisions | Enabled |  |
| My Workflow  | my_workflow | Content | Enabled | Content a user has access to that is ready for moderation  |
| Redirect | redirect | Redirect | Enabled | List of redirects |
| Right sidebar latest revision | right_sidebar_latest_revision | Content revisions | Enabled |  |
| Search | search | Index Content | Disabled |  |
| Sections tree | sections_tree | Taxonomy terms | Enabled |  |
| Taxonomy term | taxonomy_term | Content | Enabled | Content belonging to a certain taxonomy term. |
| People | user_admin_people | Users | Enabled | Find and manage people interacting with your site. |
| Blocks listing | va_blocks_admin | Custom Block | Enabled | Shows existing blocks on the site. |
| VAMC alerts and operating statuses | vamc_alerts_and_operating_statuses | Content | Enabled |  |
| VAMC operating statuses | vamc_operating_statuses | Content | Enabled |  |
| VAMCs | vamcs | Content | Enabled |  |
| VHA Health service taxonomy | vha_health_service_taxonomy | Taxonomy terms | Enabled |  |
| Watchdog | watchdog | Log entries | Enabled | Recent log messages |
| Who's new | who_s_new | Users | Disabled | Shows a list of the newest user accounts on the site. |
| Who's online block | who_s_online | Users | Disabled | Shows the user names of the most recently active users, and the total number of active users. |

  @dst @views_displays
     Scenario: Views displays
       Then exactly the following views displays should exist
       | View | Title | Machine name | Display plugin |
| Archive | Block | block_1 | Block |
| Archive | Master | default | Master |
| Archive | Page | page_1 | Page |
| Blocks listing | Master | default | Master |
| Blocks listing | Promo blocks | page_1 | Page |
| Blocks listing | Alert Blocks | page_2 | Page |
| Build info | Master | default | Master |
| Build info | REST export | rest_export_1 | REST export |
| Child terms | Block | block_1 | Block |
| Child terms | Master | default | Master |
| Content | Master | default | Master |
| Content | All content | page_1 | Page |
| Content | Bulk edit content | page_2 | Page |
| Content served from Drupal | Page | page_1 | Page |
| Content served from Drupal | Data export | data_export_1 | Data export |
| Content served from Drupal | Master | default | Master |
| Custom block library | Master | default | Master |
| Custom block library | Page | page_1 | Page |
| Files | Master | default | Master |
| Files | Files overview | page_1 | Page |
| Files | File usage | page_2 | Page |
| Frontpage | Master | default | Master |
| Frontpage | Feed | feed_1 | Feed |
| Frontpage | Page | page_1 | Page |
| Glossary | Attachment | attachment_1 | Attachment |
| Glossary | Master | default | Master |
| Glossary | Page | page_1 | Page |
| Health care service names and descriptions | Master | default | Master |
| Health care service names and descriptions |  Entity Reference   | entity_reference_1 | Entity Reference |
| Health service offerings | Master | default | Master |
| Health service offerings | Page | page_1 | Page |
| Local facilities entity reference view | Master | default | Master |
| Local facilities entity reference view | Entity Reference | entity_reference_1 | Entity Reference |
| Locked content  | Master | default | Master |
| Locked content  | Page | page_1 | Page |
| Media | Master | default | Master |
| Media | Browser | entity_browser_1 | Entity browser |
| Media | Image Browser | entity_browser_2 | Entity browser |
| Media | Media | media_page_list | Page |
| Media | Downloadable document browser | entity_browser_3 | Entity browser |
| Media | Media bulk edit | page_1 | Page |
| Media library widget | Master | default | Master |
| Media library widget | Deprecated (to follow D8.7 approach) | page | Page |
| Media library widget | Widget | widget | Page |
| Moderated content | Master | default | Master |
| Moderated content | Moderated content | moderated_content | Page |
| Moderation history | Master | default | Master |
| Moderation history | Page | page | Page |
| My Workflow  | Master | default | Master |
| My Workflow  | My workflow | my_workflow_page | Page |
| People | Master | default | Master |
| People | Page | page_1 | Page |
| Recent content | Block | block_1 | Block |
| Recent content | Master | default | Master |
| Redirect | Master | default | Master |
| Redirect | Page | page_1 | Page |
| Redirect | Non admin Page | page_2 | Page |
| Right sidebar latest revision | All revisions | block_1 | Block |
| Right sidebar latest revision | Latest revision | attachment_1 | Attachment |
| Right sidebar latest revision | Master | default | Master |
| Right sidebar latest revision | Meta tags | attachment_2 | Attachment |
| Right sidebar latest revision | Owner | attachment_0 | Attachment |
| Right sidebar latest revision | Posted to | attachment_0b | Attachment |
| Search | Master | default | Master |
| Search | Page | page | Page |
| Sections tree | Block | block_1 | Block |
| Sections tree | Master | default | Master |
| Sections tree | Page | page_1 | Page |
| Taxonomy term | Master | default | Master |
| Taxonomy term | Feed | feed_1 | Feed |
| Taxonomy term | Page | page_1 | Page |
| VAMC alerts and operating statuses | Master | default | Master |
| VAMC alerts and operating statuses | Page | page_1 | Page |
| VAMC operating statuses | Master | default | Master |
| VAMC operating statuses | Entity Reference | entity_reference_1 | Entity Reference |
| VAMCs | Master | default | Master |
| VHA Health service taxonomy | Page | page_1 | Page |
| VHA Health service taxonomy | Data export | data_export_1 | Data export |
| VHA Health service taxonomy | Master | default | Master |
| Watchdog | Master | default | Master |
| Watchdog | Page | page | Page |
| Who's new | Who's new | block_1 | Block |
| Who's new | Master | default | Master |
| Who's online block | Master | default | Master |
| Who's online block | Who's online | who_s_online_block | Block |
