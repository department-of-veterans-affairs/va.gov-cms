@api @mock_va_gov_urls
Feature: CMS Users may effectively create & edit content
  In order to confirm that cms users have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

  @content_editing
  Scenario: Log in and confirm that "Step by step" node edit has the correct field settings
    Given I am logged in as a user with the "administrator" role
    And I am at "node/add/step_by_step"
    Then I should see "Create Step-by-Step"
    And I should see "Add Step"

    # Confirm that the wysiwyg is present for the "Step" paragraph type.
    And the "#edit-field-steps-0-subform-field-step-0-subform-field-wysiwyg-wrapper" element should exist

    # Confirm that text format selection is not allowed for the "Step" paragraph type.
    And I should not see "Rich Text" in the "#edit-field-steps-0-subform-field-step-0-subform" element
    And I should not see "Plain text" in the "#edit-field-steps-0-subform-field-step-0-subform" element
    And the "#edit-field-steps-0-subform-field-step-0-subform-field-wysiwyg-0-format--2" element should not exist

    # Confirm that a user may add unlimited Step-by-step fields.
    And the "#edit-field-steps-add-more-add-more-button-step-by-step" element should exist

  @content_editing
  Scenario: Confirm that the EWA block URL is shown correctly.
    Given I am logged in as a user with the "administrator" role
    And I am at "node/add/office"
    Then I should see "Create Office"

    # Create an office node.
    And I fill in "Name" with "Test Office - BeHaT"
    And I fill in "Meta title tag" with "Test Office - BeHaT | Veterans Affairs"
    And I fill in "Section" with "5"
    And I press "Save"

    # Confirm that the va.gov url is not shown for nodes without a published revision.
    Then I should see "Content Type: Office" in the "#block-entitymetadisplay" element
    And I should not see "VA.gov URL" in the "#block-entitymetadisplay" element

    # Publish the node.
    Then I visit the "edit" page for a node with the title "Test Office - BeHaT"
    And I select "Published" from "edit-moderation-state-0-state"
    And I fill in "Revision log message" with "Test publishing"
    And I press "Save"

    # Confirm that the va.gov url is shown for nodes with a published revision.
    Then I should see "Published" in the ".view-right-sidebar-latest-revision" element
    And I should see "VA.gov URL" in the "#block-entitymetadisplay" element
    And I should not see "(pending)" in the "#block-entitymetadisplay" element

    # Confirm that the va.gov url is not clickable when updating a node.
    Then I visit the "edit" page for a node with the title "Test Office - BeHaT"
    And I fill in "Name" with "Test Office - BeHaT 404"
    And I press "Save"
    Then I should see "VA.gov URL" in the "#block-entitymetadisplay" element

    # (Re-)Publish the node.
    Then I visit the "edit" page for a node with the title "Test Office - BeHaT"
    And I select "Published" from "edit-moderation-state-0-state"
    And I fill in "Revision log message" with "Test publishing"
    And I fill in "URL alias" with "/test-office-behat-404"
    And I press "Save"
    And I should see "(pending)" in the "#block-entitymetadisplay" element

    # Archive the node.
    Then I visit the "edit" page for a node with the title "Test Office - BeHaT 404"
    And I select "Archived" from "edit-moderation-state-0-state"
    And I fill in "Revision log message" with "Test archiving"
    And I press "Save"
    Then I should see "Test Office - BeHaT 404\u003C\/a\u003E\u003C\/em\u003E has been updated."
    And I should see "Content Type: Office" in the "#block-entitymetadisplay" element
    And I should not see "VA.gov URL" in the "#block-entitymetadisplay" element

  @content_editing
  Scenario: Confirm that press release country fields are shown correctly
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/press_release"
    Then I should see "Create News Release"
    And I should see "Country" in the "#edit-field-address-0" element
    And I should see "City" in the "#edit-field-address-0" element
    And I should see "State" in the "#edit-field-address-0" element

  @content_editing
  Scenario Outline: Confirm that content cannot be published directly from the node view but can from the node edit form.
    Given I am logged in as a user with the "content_admin" role
    And I am viewing an <type> with the title <title>
    Then the "#edit-new-state" element should not exist
    And I visit the "edit" page for a node with the title <title>
    Then "#edit-moderation-state-0-state" should contain "published"
    Examples:
      | type                             | title                                 |
      | "page"                           | "page page"                           |
      | "landing_page"                   | "landing_page page"                   |
      | "basic_landing_page"             | "basic_landing_page page"             |
      | "checklist"                      | "checklist page"                      |
      | "documentation_page"             | "documentation_page page"             |
      | "faq_multiple_q_a"               | "faq_multiple_q_a page"               |
      | "health_services_listing"        | "health_services_listing page"        |
      | "health_care_region_detail_page" | "health_care_region_detail_page page" |
      | "event"                          | "event page"                          |
      | "event_listing"                  | "event_listing page"                  |
      | "health_care_local_facility"     | "health_care_local_facility page"     |
      | "health_care_region_page"        | "health_care_region_page page"        |
      | "press_release"                  | "press_release page"                  |
      | "outreach_asset"                 | "outreach_asset page"                 |
      | "publication_listing"            | "publication_listing page"            |
      | "news_story"                     | "news_story page"                     |
      | "leadership_listing"             | "leadership_listing page"             |
      | "locations_listing"              | "locations_listing page"              |
      | "media_list_images"              | "media_list_images page"              |
      | "media_list_videos"              | "media_list_videos page"              |
      | "nca_facility"                   | "nca_facility page"                   |
      | "office"                         | "office page"                         |
      | "press_releases_listing"         | "press_releases_listing page"         |
      | "q_a"                            | "q_a page"                            |
      | "step_by_step"                   | "step_by_step page"                   |
      | "story_listing"                  | "story_listing page"                  |
      | "support_resources_detail_page"  | "support_resources_detail_page page"  |
      | "support_service"                | "support_service page"                |
      | "va_form"                        | "va_form page"                        |
      | "vba_facility"                   | "vba_facility page"                   |
      | "vet_center"                     | "vet_center page"                     |

  @content_editing
  Scenario: Confirm that the default time zone when creating an event is set explicitly to Eastern.
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/event"
    Then I should see "New York" in the "#edit-field-datetime-range-timezone-0-timezone" element

  @content_editing
  Scenario: Confirm Generate automatic URL alias is unchecked after taxonomy term publish.
    Given I am logged in as a user with the "administrator" role
    And I am at "admin/structure/taxonomy/manage/health_care_service_taxonomy/add"
    And the "path[0][pathauto]" checkbox should be checked
    And I fill in "Name" with "BeHat URL Alias Term Title Published"
    And I press "Save"
    Then I should see "BeHat URL Alias Term Title Published"
    Then I visit the "edit" page for a term with the title "BeHat URL Alias Term Title Published"
    And the "path[0][pathauto]" checkbox should not be checked

  @content_editing
  Scenario: Confirm field group is opened and required field is presented to editor on CLP page.
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"
    And I fill in "Page title" with "BeHat Required Field"
    And I press "Add Call to action"
    And I fill in "Link" with "/node/2418"
    And I check "Enable this page segment"
    And I press "Save"
    Then I should see "Introduction field is required."
    And I should see "0 Video entries created. Minimum of 1 required."
