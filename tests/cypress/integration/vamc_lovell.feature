Feature: CMS Facility editors are able to interact with Lovell.

  @lovell @lovell_menus
  Scenario: Anonymous users can see the Lovell sidebar menu on Lovell VA
    Given I am at "/lovell-federal-health-care-va"
    Then the URL for the link with text "Lovell Federal health care - VA" should contain "lovell-federal-health-care-va"
  Scenario: Anonymous users can see the Lovell sidebar menu on Lovell TRICARE
    Given I am at "/lovell-federal-health-care-tricare"
    Then the URL for the link with text "Lovell Federal health care - TRICARE" should contain "lovell-federal-health-care-tricare"
