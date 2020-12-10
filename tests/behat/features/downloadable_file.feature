@api @d8 @downloadable_file
Feature: Downloadable File paragraphs are displayed correctly corresponding to their content type.
  As anyone involved in the project
  I need to see correctly formatted files.

  Scenario: Document files should, on any content type, contain a specially formatted link.
    Given I am logged in as a user with the "content_admin" role
    And I am on "/node/9421"
    Then I should see "Download slide deck for Session 1 - CMS Orientation (PDF)" in the "a.downloadable-file-link--document" element
    Then "a.downloadable-file-link--document" should have the attribute "target" with value "_blank"
    Then "a.downloadable-file-link--document" should have the attribute "aria-label" with value "Download"
    Then "a.downloadable-file-link--document" should have the attribute "href" matching pattern "/(.*)\/files\/2020\-10\/CMS%20Orientation%20\-%20Session%201_0\.pdf$/"

  Scenario: Image files should, on any content type, contain a specially formatted link.
    Given I am logged in as a user with the "content_admin" role
    And I am on "/node/7166"
    Then I should see "Download Salem Visitor's Guide (PNG)" in the "a.downloadable-file-link--image" element
    Then "a.downloadable-file-link--image" should have the attribute "target" with value "_blank"
    Then "a.downloadable-file-link--image" should have the attribute "aria-label" with value "Download"
    Then "a.downloadable-file-link--image" should have the attribute "href" matching pattern "/(.*)\/files\/2020\-09\/Salem%20VA%20Medical%20Center%20campus%20map\.PNG$/"

  Scenario: Video files should, on any non-documentation_page content type, contain a specially formatted link.
    Given I am logged in as a user with the "content_admin" role
    And I am on "/node/6172"
    Then I should see "Go to video" in the "a.downloadable-file-link--video" element
    Then "a.downloadable-file-link--video" should have the attribute "target" with value "_blank"
    Then "a.downloadable-file-link--video" should have the attribute "href" with value "https://www.youtube.com/watch?v=6TKRU0nm8qo&feature=youtu.be"
