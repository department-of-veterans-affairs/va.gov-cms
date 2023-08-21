@facilities_api
Feature: CMS Users may not unintentionally change information in fields populated by the Facilities API
  In order to confirm that cms users cannot change Facilities API data
  As anyone involved in the project
  I need to have certain fields locked down

# Content Admin
Scenario: Log in and edit a VAMC facility
  When I am logged in as a user with the roles "content_admin"
  And my workbench access sections are set to "2,12,1013,1104"
  # 2 = NCA (Louisiana National Cemetery)
  # 12 = VA Pittsburgh health care (H. John Heinz III Department of Veterans Affairs Medical Center)
  # 190 = Vet Centers
  # 1104 = Cheyenne VA Regional Benefit Office (Cheyenne VA Regional Benefit Office)

  # NCA (Louisiana National Cemetery)
  Then I am at "/node/4610/edit"
  Then an element with the selector "#locations-and-contact-information .node__content > .not-editable" should exist
  And an element with the selector '[data-drupal-selector="edit-field-facility-locator-api-id-0-value"]' should not exist

  # VAMC Facility (H. John Heinz III Department of Veterans Affairs Medical Center)
  Then I am at "/node/175/edit"
  Then an element with the selector "#locations-and-contact-information .node__content > .not-editable" should exist
  And an element with the selector '[data-drupal-selector="edit-field-facility-locator-api-id-0-value"]' should not exist

  # Vet Center Outstation (Clarksville Vet Center Outstation)
  Then I am at "/node/17533/edit"
  Then an element with the selector "#locations-and-contact-information .node__content > .not-editable" should exist
  And an element with the selector '[data-drupal-selector="edit-field-facility-locator-api-id-0-value"]' should not exist

  # Vet Center (Cheyenne Vet Center)
  Then I am at "/node/3769/edit"
  Then an element with the selector "#locations-and-contact-information .node__content > .not-editable" should exist
  And an element with the selector '[data-drupal-selector="edit-field-facility-locator-api-id-0-value"]' should not exist

  # Mobile Vet Center (Evanston Mobile Vet Center)
  Then I am at "/node/17503/edit"
  Then an element with the selector "#locations-and-contact-information .node__content > .not-editable" should exist
  And an element with the selector '[data-drupal-selector="edit-field-facility-locator-api-id-0-value"]' should not exist

  # Mobile Vet Center (Cheyenne VA Regional Benefit Office)
  Then I am at "/node/4338/edit"
  Then an element with the selector ".node__content > #locations-and-contact-information .not-editable" should exist
  And an element with the selector '[data-drupal-selector="edit-field-facility-locator-api-id-0-value"]' should not exist

# Content Editor (VAMC)
Scenario: Log in and edit a VAMC facility
  When I am logged in as a user with the roles "vamc_content_creator, content_publisher"
  And my workbench access sections are set to "12"

  # VAMC Facility (H. John Heinz III Department of Veterans Affairs Medical Center)
  Then I am at "/node/175/edit"
  Then an element with the selector "#locations-and-contact-information .node__content > .not-editable" should exist
  And an element with the selector '[data-drupal-selector="edit-field-facility-locator-api-id-0-value"]' should not exist

# Content Editor (Vet Centers)
Scenario: Log in and edit a Vet Center facility
  When I am logged in as a user with the roles "content_creator_vet_center, content_publisher"
  And my workbench access sections are set to "190"

  # Vet Center Outstation (Clarksville Vet Center Outstation)
  Then I am at "/node/17533/edit"
  Then an element with the selector "#locations-and-contact-information .node__content > .not-editable" should exist
  And an element with the selector '[data-drupal-selector="edit-field-facility-locator-api-id-0-value"]' should not exist

  # Vet Center (Cheyenne Vet Center)
  Then I am at "/node/3769/edit"
  Then an element with the selector "#locations-and-contact-information .node__content > .not-editable" should exist
  And an element with the selector '[data-drupal-selector="edit-field-facility-locator-api-id-0-value"]' should not exist

  # Mobile Vet Center (Evanston Mobile Vet Center)
  Then I am at "/node/17503/edit"
  Then an element with the selector "#locations-and-contact-information .node__content > .not-editable" should exist
  And an element with the selector '[data-drupal-selector="edit-field-facility-locator-api-id-0-value"]' should not exist

# Content Editor (VBA & NCA)
Scenario: Log in and edit a VBA facility and NCA facility
  When I am logged in as a user with the roles "content_publisher, content_editor"
  And my workbench access sections are set to "2,1104"

  # NCA (Louisiana National Cemetery)
  Then I am at "/node/4610/edit"
  Then an element with the selector "#locations-and-contact-information .node__content > .not-editable" should exist
  And an element with the selector '[data-drupal-selector="edit-field-facility-locator-api-id-0-value"]' should not exist

  # Mobile Vet Center (Cheyenne VA Regional Benefit Office)
  Then I am at "/node/4338/edit"
  Then an element with the selector ".node__content > #locations-and-contact-information .not-editable" should exist
  And an element with the selector '[data-drupal-selector="edit-field-facility-locator-api-id-0-value"]' should not exist

