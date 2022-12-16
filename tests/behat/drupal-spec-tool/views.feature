
@api
Feature: Views
  In order to present and expose content and configuration
  As a site owner
  I want to have views for various contexts and applications.

  @dst @views
     Scenario: Views
       Then exactly the following views should exist
       | Name | Machine name | Base table | Status | Description |
| Administration section | administration_section | Taxonomy terms | Enabled | Top-level items in the Section taxonomy |
| Advanced Queue jobs | advancedqueue_jobs | Jobs | Enabled |  |
| Archive | archive | Content | Disabled | All content, by month. |
| Benefit Hub Contact Information | benefit_hub_contact_information | Content | Enabled |  |
| Benefit Hubs Categories | benefits_hub_categories | Content | Enabled |  |
| Benefits hub list | benefits_hub_list | Content | Enabled |  |
| Blocks listing | va_blocks_admin | Custom Block | Enabled | Shows existing blocks on the site. |
| Build info | build_info | Content | Enabled |  |
| Centralized content paragraphs | centralized_content_paragraphs | Content | Enabled |  |
| Child terms | child_terms | Taxonomy terms | Enabled |  |
| CMS Knowledge Base search results | knowledge_base_search_results | Index Knowledge base search | Enabled |  |
| Content | content | Content | Enabled | Find and manage content. |
| Content entity browsers | content_entity_browsers | Content | Enabled | Collection of Entity Browsers to use for field widgets configuration in form displays. |
| Content Entity Reference Source | content_entity_reference_source | Content | Enabled | Various views used to populate options on entity reference fields |
| Content release logs | content_release_logs | Log entries | Enabled | Shows content release job log entries |
| Content served from Drupal | content_served_from_drupal | Content | Enabled | An exportable list of all content served from Drupal |
| Custom block entity browsers | custom_block_entity_browsers | Custom Block | Enabled | For placing on content forms |
| Custom block library | block_content | Custom Block | Enabled | Find and manage custom blocks. |
| Date fields | date_fields | Content | Disabled |  |
| Detail page URL audit and bulk update | detail_page_url_audit_and_bulk_udpate | Content | Enabled | For bulk updating URL aliases for VAMC detail pages. |
| Facility Services | facility_services | Content | Enabled |  |
| Feedback | feedback | Admin feedback score | Enabled |  |
| File browsers | file_browsers | Media | Enabled |  |
| Files | files | Files | Enabled | Find and manage files. |
| Flagged Content | flagged_content | Content | Enabled |  |
| Flagged Content - VA Forms | flagged_content_va_forms | Content | Enabled |  |
| Frontpage | frontpage | Content | Enabled | All content promoted to frontpage |
| Glossary | glossary | Content | Disabled | All content, by letter. |
| Health care service names and descriptions | health_care_service_names_and_descriptions | Taxonomy terms | Enabled | A list of nationally-controlled health care service names and descriptions |
| Image Style Warmer Warmup Files | image_style_warmer_warmup_files | Files | Enabled | VBO for processing existing files with Image Style Warmer. |
| JSON data sources | json_data_sources | Taxonomy terms | Enabled | Do not alter - serves as api endpoint(s) |
| Knowledge Base Article administration | knowledge_base_article_administration | Content | Enabled | Audits and tools for managing Knowledge Base Articles |
| Listing page dashboard | listing_page_dashboard | Content | Disabled |  |
| Local facilities entity reference view | local_facilities_entity_reference_view | Content | Enabled | An entity reference view that determines options for the Local Health Service descriptions |
| Locked content | locked_content | Content | Enabled |  |
| Managed links | managed_links | Managed Link | Enabled | Managed links admin |
| Media | media | Media | Enabled |  |
| Media library | media_library | Media | Enabled | Find and manage media. |
| Message | message | Message | Enabled |  |
| Metatag Audit | metatag_audit | Content | Enabled |  |
| Moderated content | moderated_content | Content revisions | Enabled | Find and moderate content. |
| Moderation history | moderation_history | Content revisions | Enabled |  |
| Non-clinical services | non_clinical_services | Content | Enabled | Views of non-clinical services content placed within several "Top tasks" VAMC node forms. |
| Orphaned Paragraphs | orphaned_paragraphs | Paragraph | Enabled |  |
| PDF Audit | pdf_audit | Media | Enabled |  |
| People | user_admin_people | Users | Enabled | Find and manage people interacting with your site. |
| Recent content | content_recent | Content | Disabled | Recent content. |
| Redirect | redirect | Redirect | Enabled | List of redirects |
| Rich Text Field Audit | rich_text_field_audit | Content | Enabled |  |
| Right sidebar latest revision | right_sidebar_latest_revision | Content revisions | Enabled |  |
| Search | search | Index Content | Enabled |  |
| Section administration and export | section_export | Taxonomy terms | Enabled | Enables easier adminstration of Sections/Product relationship, and export tools for analysis outside Drupal |
| Services | services | Content | Enabled | Lists of services for facility pages, health services lists, etc |
| Subscribe node | subscribe_node | Content | Enabled |  |
| Subscribe node email | subscribe_node_email | Content | Enabled |  |
| Subscribe taxonomy term | subscribe_taxonomy_term | Taxonomy terms | Enabled |  |
| Subscribe taxonomy term email | subscribe_taxonomy_term_email | Taxonomy terms | Enabled |  |
| Subscribe user | subscribe_user | Users | Enabled |  |
| Subscribe user email | subscribe_user_email | Users | Enabled |  |
| Tables | tables | Paragraph | Enabled |  |
| Taxonomy entity browsers | taxonomy_entity_browsers | Taxonomy terms | Enabled |  |
| Taxonomy term | taxonomy_term | Content | Enabled | Content belonging to a certain taxonomy term. |
| User creation & editing activity | user_creation_editing_activity | Users | Enabled |  |
| User email csv | user_email_csv | Users | Enabled |  |
| User history | user_history | User history | Enabled |  |
| User history list | user_history_list | User history | Enabled |  |
| Users in section | users_in_section | Section association | Enabled | Views of users associated to a section |
| VA Forms | va_forms | Content | Enabled | VA forms dashboard |
| VA Services | vha_health_service_taxonomy | Taxonomy terms | Enabled |  |
| VAMC alerts and operating statuses | vamc_alerts_and_operating_statuses | Content | Enabled |  |
| VAMC operating statuses | vamc_operating_statuses | Content | Enabled |  |
| VAMC top task page revision histories | vamc_top_task_page_revisions | Content revisions | Enabled | An audit of VAMC top task pages for runbook planning |
| VAMCs | vamcs | Content | Enabled |  |
| Vet Center facility listing | vet_center_facility_listing | Content | Enabled |  |
| Vet centers | vet_centers | Content | Enabled |  |
| Watchdog | watchdog | Log entries | Enabled | Recent log messages |
| Who's new | who_s_new | Users | Disabled | Shows a list of the newest user accounts on the site. |
| Who's online block | who_s_online | Users | Disabled | Shows the user names of the most recently active users, and the total number of active users. |

  @dst @views_displays
     Scenario: Views displays
       Then exactly the following views displays should exist
       | View | Title | Machine name | Display plugin |
| Administration section | CLP Entity Reference | clp_entity_reference | Entity Reference |
| Administration section | Entity Reference | entity_reference_1 | Entity Reference |
| Administration section | Master | default | Default |
| Advanced Queue jobs | Master | default | Default |
| Advanced Queue jobs | Page | page_1 | Page |
| Archive | Block | block_1 | Block |
| Archive | Master | default | Default |
| Archive | Page | page_1 | Page |
| Benefit Hub Contact Information | Entity browser | benefit_hub_contact_information | Entity browser |
| Benefit Hub Contact Information | Master | default | Default |
| Benefit Hubs Categories | Entity browser | benefits_hub_categories | Entity browser |
| Benefit Hubs Categories | Master | default | Default |
| Benefits hub list | Entity Reference | entity_reference_1 | Entity Reference |
| Benefits hub list | Master | default | Default |
| Blocks listing | Alert Blocks | page_2 | Page |
| Blocks listing | Master | default | Default |
| Blocks listing | Promo blocks | page_1 | Page |
| Build info | Master | default | Default |
| Build info | REST export | rest_export_1 | REST export |
| Centralized content paragraphs | Centralized Content paragraphs | centralized_content_paragraphs | Page |
| Centralized content paragraphs | Default | default | Default |
| Child terms | Block | block_1 | Block |
| Child terms | Master | default | Default |
| CMS Knowledge Base search results | Master | default | Default |
| CMS Knowledge Base search results | Page | knowledge_base_search_page | Page |
| Content | All content | page_1 | Page |
| Content | Bulk edit content | page_2 | Page |
| Content | Bulk edit events | events_page | Page |
| Content | Content audit CSV export | content_audit_csv_export | Data export |
| Content | Content audit tools | content_audit_page | Page |
| Content | Master | default | Default |
| Content | Outdated content | outdated_content | Page |
| Content | Outdated Content CSV export | outdated_content_data_export | Data export |
| Content | Resources and support | resources_support_dashboard | Page |
| Content entity browsers | Event entity browser | event_entity_browser | Entity browser |
| Content entity browsers | Master | default | Default |
| Content entity browsers | Q&A entity browser | entity_browser_1 | Entity browser |
| Content Entity Reference Source | Entity Reference: Event Listing | entity_reference_1 | Entity Reference |
| Content Entity Reference Source | Entity Reference: Main Offices | entity_reference_7 | Entity Reference |
| Content Entity Reference Source | Entity Reference: News Release Listing | entity_reference_4 | Entity Reference |
| Content Entity Reference Source | Entity Reference: Publication Listing | entity_reference_2 | Entity Reference |
| Content Entity Reference Source | Entity Reference: Staff profiles | entity_reference_5 | Entity Reference |
| Content Entity Reference Source | Entity Reference: Story Listing | entity_reference_3 | Entity Reference |
| Content Entity Reference Source | Entity Reference: Systems | entity_reference_6 | Entity Reference |
| Content Entity Reference Source | Master | default | Default |
| Content release logs | Master | default | Default |
| Content release logs | Page | page_1 | Page |
| Content served from Drupal | Data export | data_export_1 | Data export |
| Content served from Drupal | Master | default | Default |
| Content served from Drupal | Page | page_1 | Page |
| Custom block entity browsers | Alert block entity browsers | entity_browser_1 | Entity browser |
| Custom block entity browsers | Master | default | Default |
| Custom block entity browsers | Promo block entity browsers | entity_browser_2 | Entity browser |
| Custom block library | Master | default | Default |
| Custom block library | Page | page_1 | Page |
| Date fields | Master | default | Default |
| Date fields | Page | page_1 | Page |
| Detail page URL audit and bulk update | Audit page | audit_page | Page |
| Detail page URL audit and bulk update | CSV export | data_export_1 | Data export |
| Detail page URL audit and bulk update | Master | default | Default |
| Facility Services | Accordion audit | accordion_audit | Page |
| Facility Services | Accordion audit export | accordion_audit_export | Data export |
| Facility Services | Addresses | addresses | Page |
| Facility Services | Addresses export | addresses_export | Data export |
| Facility Services | Facilities | content_audit_facilities | Page |
| Facility Services | Facilities export | content_audit_facilities_export | Data export |
| Facility Services | Facility status | facility_status_page | Page |
| Facility Services | Facility status export | facility_status_export | Data export |
| Facility Services | Facility Urls | data_export_facility_urls | Data export |
| Facility Services | Master | default | Default |
| Facility Services | VAMC facility health services | vamc_facility_health_services_page | Page |
| Facility Services | VAMC facility health services export | vamc_facility_health_services_export | Data export |
| Facility Services | VAMC facility non-clinical services | vamc_facility_non_clinical_services_page | Page |
| Facility Services | VAMC facility non-clinical services export | vamc_facility_non_clinical_services_export | Data export |
| Facility Services | VAMC System Service Audit | vamc_system_service_audit_page | Page |
| Facility Services | VAMC System Service Audit Export | vamc_system_service_audit_export | Data export |
| Facility Services | VAMC systems | vamc_systems | Page |
| Facility Services | Vet Center services | vet_center_services_page | Page |
| Facility Services | Vet Center services export | vet_center_services_export | Data export |
| Feedback | Master | default | Default |
| Feedback | Nodes list | nodes_list | Page |
| Feedback | Nodes score | nodes_score | Page |
| File browsers | Block | file_browser_block | Block |
| File browsers | Entity browser | file_entity_browser | Entity browser |
| File browsers | Master | default | Default |
| Files | File usage | page_2 | Page |
| Files | Files overview | page_1 | Page |
| Files | Master | default | Default |
| Flagged Content | Default | default | Default |
| Flagged Content | Flagged Content | flagged_content | Page |
| Flagged Content - VA Forms | Changed Filename | changed_filename | Page |
| Flagged Content - VA Forms | Changed Title | changed_title | Page |
| Flagged Content - VA Forms | Default | default | Default |
| Flagged Content - VA Forms | New/Deleted Forms | new_deleted | Page |
| Frontpage | Feed | feed_1 | Feed |
| Frontpage | Master | default | Default |
| Frontpage | Page | page_1 | Page |
| Glossary | Attachment | attachment_1 | Attachment |
| Glossary | Master | default | Default |
| Glossary | Page | page_1 | Page |
| Health care service names and descriptions | Facility Supplemental Status - entity reference | facility_supplemental_status | Entity Reference |
| Health care service names and descriptions | Master | default | Default |
| Health care service names and descriptions | Non clinical service | entity_reference_non_clinical_services | Entity Reference |
| Health care service names and descriptions | VAMC health service and type of care - entity reference | entity_reference_vamc_services | Entity Reference |
| Health care service names and descriptions | VBA services | entity_reference_vba_services | Entity Reference |
| Health care service names and descriptions | Vet Center health service and type of care - entity reference | entity_reference_vet_center_services | Entity Reference |
| Image Style Warmer Warmup Files | Files overview  | page_1 | Page |
| Image Style Warmer Warmup Files | Master | default | Default |
| JSON data sources | Default | default | Default |
| JSON data sources | Facility supplemental status | facility_supplemental_status | REST export |
| Knowledge Base Article administration | Master | default | Default |
| Knowledge Base Article administration | Page | knowledge_base_admin | Page |
| Listing page dashboard | Master | default | Default |
| Listing page dashboard | Past events | block_2 | Block |
| Listing page dashboard | Upcoming events | block_1 | Block |
| Local facilities entity reference view | Entity Reference | entity_reference_1 | Entity Reference |
| Local facilities entity reference view | Master | default | Default |
| Locked content | Master | default | Default |
| Locked content | Page | page_1 | Page |
| Managed links | External Link Status | external_link_status_page | Page |
| Managed links | Master | default | Default |
| Managed links | Page | page_1 | Page |
| Media | Browser | entity_browser_1 | Entity browser |
| Media | Data export | images_export | Data export |
| Media | Downloadable document browser | entity_browser_3 | Entity browser |
| Media | Image Browser | entity_browser_2 | Entity browser |
| Media | Images | media_images | Page |
| Media | Master | default | Default |
| Media | Media | media_page_list | Page |
| Media | Media bulk edit | page_1 | Page |
| Media library | Master | default | Default |
| Media library | Page | page | Page |
| Media library | Widget | widget | Page |
| Media library | Widget (table) | widget_table | Page |
| Message | Master | default | Default |
| Message | Page | page_1 | Page |
| Metatag Audit | Default | default | Default |
| Metatag Audit | Metatag Audit | metatag_audit | Page |
| Moderated content | Master | default | Default |
| Moderated content | Moderated content | moderated_content | Page |
| Moderation history | Master | default | Default |
| Moderation history | Page | page | Page |
| Non-clinical services | Admissions offices | admissions_offices | Block |
| Non-clinical services | Billing and insurance offices | billing_and_insurance | Block |
| Non-clinical services | Default | default | Default |
| Non-clinical services | Medical records offices | medical_records_offices | Block |
| Orphaned Paragraphs | Default | default | Default |
| Orphaned Paragraphs | Orphaned Paragraphs | orphaned_para_page | Page |
| PDF Audit | Data export | pdf_audit_export | Data export |
| PDF Audit | Default | default | Default |
| PDF Audit | PDF Audit | pdf_audit | Page |
| People | Data export | data_export_1 | Data export |
| People | Data export: All users | data_export_2 | Data export |
| People | Master | default | Default |
| People | Page | page_1 | Page |
| Recent content | Block | block_1 | Block |
| Recent content | Master | default | Default |
| Redirect | Master | default | Default |
| Redirect | Non admin Page | page_2 | Page |
| Redirect | Page | page_1 | Page |
| Rich Text Field Audit | Content Audit - Buttons | content_audit_buttons | Page |
| Rich Text Field Audit | Content Audit - Phone numbers | content_audit_phone_numbers | Page |
| Rich Text Field Audit | Data export - buttons | buttons_export | Data export |
| Rich Text Field Audit | Data export - Phone numbers | phone_numbers_export | Data export |
| Rich Text Field Audit | Default | default | Default |
| Right sidebar latest revision | All revisions | block_1 | Block |
| Right sidebar latest revision | Latest revision | attachment_1 | Attachment |
| Right sidebar latest revision | Master | default | Default |
| Search | Master | default | Default |
| Search | Search | content_search | Page |
| Search | Search data export | search_data_export | Data export |
| Section administration and export | Data export | csv_export | Data export |
| Section administration and export | Master | default | Default |
| Section administration and export | Page | page_1 | Page |
| Services | Facility health services | block_1 | Block |
| Services | Master | default | Default |
| Services | VAMC system health services | block_2 | Block |
| Subscribe node | Master | default | Default |
| Subscribe node email | Master | default | Default |
| Subscribe taxonomy term | Master | default | Default |
| Subscribe taxonomy term email | Master | default | Default |
| Subscribe user | Master | default | Default |
| Subscribe user email | Master | default | Default |
| Tables | Data export | table_audit_export | Data export |
| Tables | Default | default | Default |
| Tables | Tables | table_audit_page | Page |
| Taxonomy entity browsers | Audiences vocabularies | audiences_vocabularies | Entity browser |
| Taxonomy entity browsers | Block | block_1 | Block |
| Taxonomy entity browsers | Master | default | Default |
| Taxonomy entity browsers | Resources and support vocabulary | entity_browser_1 | Entity browser |
| Taxonomy term | Attachment | attachment_1 | Attachment |
| Taxonomy term | Block | taxonomy_term_listing_block | Block |
| Taxonomy term | Master | default | Default |
| Taxonomy term | Page | page_1 | Page |
| User creation & editing activity | Master | default | Default |
| User creation & editing activity | Page | page_1 | Page |
| User email csv | Data export | data_export_1 | Data export |
| User email csv | Master | default | Default |
| User email csv | Page | page_1 | Page |
| User history | Master | default | Default |
| User history | Page | page_1 | Page |
| User history list | Master | default | Default |
| User history list | Page | page_1 | Page |
| Users in section | Master | default | Default |
| Users in section | Page | section_member_page | Page |
| VA Forms | Audit | audit | Page |
| VA Forms | Master | default | Default |
| VA Forms | Page | page_1 | Page |
| VA Forms | CSV export | csv_export | Data export |
| VA Services | Data export | data_export_1 | Data export |
| VA Services | Master | default | Default |
| VA Services | Page | page_1 | Page |
| VAMC alerts and operating statuses | Master | default | Default |
| VAMC alerts and operating statuses | Page | page_1 | Page |
| VAMC operating statuses | Entity Reference | entity_reference_1 | Entity Reference |
| VAMC operating statuses | Master | default | Default |
| VAMC top task page revision histories | Master | default | Default |
| VAMC top task page revision histories | Page | page_1 | Page |
| VAMCs | Master | default | Default |
| Vet Center facility listing | Block | vc_facility_listing | Block |
| Vet Center facility listing | Master | default | Default |
| Vet Center facility listing | MVC listing for node form | mvc_listing_for_node_form | Block |
| Vet Center facility listing | VC listing for node form | vc_listing_for_node_form | Block |
| Vet centers | Master | default | Default |
| Vet centers | Mobile Vet Centers Entity Browser | mvc_entity_browser | Entity browser |
| Watchdog | Master | default | Default |
| Watchdog | Page | page | Page |
| Who's new | Master | default | Default |
| Who's new | Who's new | block_1 | Block |
| Who's online block | Master | default | Default |
| Who's online block | Who's online | who_s_online_block | Block |
