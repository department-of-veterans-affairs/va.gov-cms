# CMS Collaboration Cycle Accessibility Discovery and Recomendations

## Discovery

Currently, the Platform Collaboration Cycle incorporates Accessibility throughout all touch points from Design Intent, Midpoint Review, and Staging Review. At both the Midpoint Review and Staging Review they have the following testing and artifacts:

1. Midpoint Review

   1. **Finalized design prototype - Required**

      - Desktop and mobile prototypes including accessibility annotations.

1. Staging Review

   1. **Foundational accessibility tests - Required**
      - [Use of color and color contrast](https://depo-platform-documentation.scrollhelp.site/collaboration-cycle/prepare-for-an-accessibility-staging-review#use-of-color)
      - [Automated testing with axe by Deque](https://depo-platform-documentation.scrollhelp.site/collaboration-cycle/prepare-for-an-accessibility-staging-review#axe)
      - [Content zoom and reflow](https://depo-platform-documentation.scrollhelp.site/collaboration-cycle/prepare-for-an-accessibility-staging-review#content-zoom)
      - [Keyboard navigation](https://depo-platform-documentation.scrollhelp.site/collaboration-cycle/prepare-for-an-accessibility-staging-review#keyboard-nav)

   2. **Foundational testing artifact - Required**
      - The [accessibility testing artifact](https://depo-platform-documentation.scrollhelp.site/collaboration-cycle/prepare-for-an-accessibility-staging-review#artifact) is required and must be submitted prior to each staging review. It documents the results of their foundational testing.

   3. **Advanced accessibility testing - Recommended**
      - [WAVE spot checks](https://depo-platform-documentation.scrollhelp.site/collaboration-cycle/prepare-for-an-accessibility-staging-review#wave)
      - [Code quality review](https://depo-platform-documentation.scrollhelp.site/collaboration-cycle/prepare-for-an-accessibility-staging-review#code-quality)
      - [Mouse-only and touchscreen](https://depo-platform-documentation.scrollhelp.site/collaboration-cycle/prepare-for-an-accessibility-staging-review#mouse-and-touch)
      - [Screen readers](https://depo-platform-documentation.scrollhelp.site/collaboration-cycle/prepare-for-an-accessibility-staging-review#screen-readers)
      - [Voice commands](https://depo-platform-documentation.scrollhelp.site/collaboration-cycle/prepare-for-an-accessibility-staging-review#voice)

Talking with Laura, sitewide teams are only building cypress tests when it's appropriate, but not accessibility specific tests.

## Recommendations for Collab cycle changes

1. **Mouse-only and touchscreen checks**
   - For CMS specifically, since Drupal is not accessible via mobile devices unless they have a GFE phone, we need a secondary way of completing the step of `Using a mobile device, make sure you can use the full functionality of a feature with only one finger to gesture (tapping, swiping, etc.)` by being able to `change the view in developer tools to be mobile`. The test should still be done for those users who want to use GFE tablets or phones to access Drupal.

## Recommendations for future work

1. **Automated testing with axe by Deque**
   1. Tasks for CMS team: 
      - Investigate why axe-core is being used instead of the perfered Attest mentioned in the [508-ds-process memo](https://github.com/department-of-veterans-affairs/va.gov-team-sensitive/blob/master/Administrative/memos/508-ds-process.md#automated-and-integrated-508-compliance-tests-and-digital-service-reviews) (dated 2016 and added to github in 2019) found under the [axe scans in end-to-end tests heading](https://depo-platform-documentation.scrollhelp.site/collaboration-cycle/prepare-for-an-accessibility-staging-review#Prepareforanaccessibilitystagingreview-axescansinend-to-endtests) on the Prepare for an accessibility staging review page.
        - Currently Platform teams are only using axe-core and all documentation points to axe-core despite the memo. Speaking with Brian DeConinck that's the only reference he knows of to Attest.
      - Better understand what automated tests are running in the code. This will need to be a collaboration between Accessibility and QA. Once this is understood, devs will need to augment their required QA tests to make sure they are testing their builds for accessibility using the [axe checks](https://depo-platform-documentation.scrollhelp.site/developer-docs/accessibility-testing-helper-functions).
      - Currently in the [accessibility.js](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/main/tests/cypress/support/accessibility.js) file we're only testing for `values: ["wcag2a", "wcag2aa", "wcag21a", "wcag21aa"]`. This should be expanded to the 22a and 22aa rules like the Platform team is planning on doing for the [conten-build testing rules](https://github.com/department-of-veterans-affairs/va.gov-team/issues/45693).
   2. Tasks for teams in collab cycle:
      - Teams need to start adding accessibility checks into each cypress test written.
