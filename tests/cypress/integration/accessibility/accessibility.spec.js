/* eslint-disable no-console */
/* eslint-disable max-nested-callbacks */
const routes = [
  // Home page
  "/",

  // Add/Edit and View versions for each content type:
  // Admin pages
  "/admin",
  "/admin/content",
  // Benefits Detail Page
  "/node/add/page",
  "/disability/compensation-rates/veteran-rates/past-rates-2020",
  // Benefits Hub Landing Page
  "/node/add/landing_page",
  "/pension",
  // CMS Knowledge Base Article
  "/node/add/documentation_page",
  "/help/vamc/about-locations-content-for-vamcs/about-alerts-and-operating-statuses",
  // Campaign Landing Page
  "/node/add/campaign_landing_page",
  "/initiatives/flu",
  // Centralized Content
  "/node/add/centralized_content",
  "/centralized-content/vamc-system-billing-and-insurance-page-content",
  // Checklist
  "/node/add/checklist",
  "/resources/test-registry-exam-checklist",
  // Event
  "/node/add/event",
  // Event List
  "/node/add/event_list",
  "/eastern-kansas-health-care/events",
  // FAQ - Multiple Q&A
  "/node/add/faq_multiple_q_a",
  "/resources/verifying-your-identity-on-vagov",
  // Full Width Alert
  "/node/add/banner",
  "/banner/homepage-banner",
  // Health Services List
  "/node/add/health_services_listing",
  "/eastern-colorado-health-care/health-services",
  // Landing Page
  "/node/add/basic_landing_page",
  "/resources",
  // Leadership Listing
  "/node/add/leadership_listing",
  "/dayton-health-care/about-us/leadership",
  // NCA Facility
  "/node/add/nca_facility",
  "/nca-facilities/locations/fargo-national-cemetery",
  // News Release
  "/node/add/press_release",
  "/orlando-health-care/news-releases/orlando-va-health-care-system-hosts-global-war-on-terrorism-wall-sept-6-15",
  // News Releases List
  "/central-ohio-health-care/news-releases",
  "/miami-health-care/news-releases",
  // Office
  "/node/add/health_care_local_facility",
  "/vba",
  // Promo Banner
  "/node/add/promo_banner",
  "/promo-banner/learn-what-the-pact-act-means-for-your-va-benefits",
  // Publication
  "/node/add/outreach_asset",
  "/outreach-and-events/va-employment-services",
  // Publication Listing
  "/node/add/publication_listing",
  "/outreach-and-events/outreach-materials",
  // Q&A
  "/node/add/q_a",
  "/resources/what-new-radiation-presumptive-locations-will-va-add",
  // Resources and Support Detail Page
  "/node/add/support_resources_detail_page",
  "/resources/getting-a-gi-bill-extension",
  // Staff Profile
  "/node/add/person_profile",
  "/north-texas-health-care/staff-profiles/nathalee-walker",
  // Step-by-Step
  "/node/add/step_by_step",
  "/resources/how-to-download-and-open-a-vagov-pdf-form",
  // Stories List
  "/node/add/story_listing",
  "/white-river-junction-health-care/stories",
  // Story
  "/node/add/news_story",
  "/minneapolis-health-care/stories/quit-tobacco-and-enjoy-what-matters-to-you",
  // Support Service
  "/node/add/support_service",
  "/va-central-office/ask-a-question-online",
  // VA Form
  "/node/add/va_form",
  "/find-forms/about-form-27-2008",
  // VAMC Detail Page
  "/node/add/health_care_region_detail_page",
  "/montana-health-care/work-with-us/internships-and-fellowships/psychology-internship-program",
  // VAMC Facility
  "/node/add/health_care_local_facility",
  "/san-diego-health-care/locations/oceanside-va-clinic",
  // VAMC Facility Health Service
  "/node/add/health_care_local_health_service",
  "/fayetteville-arkansas-health-care/locations/branson-va-clinic/mental-health-care",
  // VAMC Facility Non-Clinical Service
  "/node/add/vha_facility_nonclinical_service",
  "/boston-health-care/locations/jamaica-plain-va-medical-center/medical-records",
  // VAMC System
  "/node/add/health_care_region_page",
  "/bedford-health-care",
  // VAMC System Banner Alert with Situation Updates
  "/node/add/full_width_banner_alert",
  "/va-chillicothe-health-care/vamc-banner-alert/2022-08-10/face-mask-mandates-reinstated",
  // VAMC System Billing and Insurance
  "/node/add/vamc_system_billing_insurance",
  "/poplar-bluff-health-care/billing-and-insurance",
  // VAMC System Health Service
  "/node/add/regional_health_care_service_des",
  "/montana-health-care/health-services/radiology-at-va-montana-health-care",
  // VAMC System Locations List
  "/node/add/locations_listing",
  "/albany-health-care/locations",
  // VAMC System Medical Records Office
  "/node/add/vamc_system_medical_records_offi",
  "/miami-health-care/medical-records-office",
  // VAMC System Operating Status
  "/node/add/vamc_operating_status_and_alerts",
  "/illiana-health-care/operating-status",
  // VAMC System Policies Page
  "/node/add/vamc_system_policies_page",
  "/tuscaloosa-health-care/policies",
  // VAMC System Register for Care
  "/node/add/vamc_system_register_for_care",
  "/eastern-oklahoma-health-care/register-for-care",
  // VBA Facility
  "/node/add/vba_facility",
  "/vba-facilities/locations/sioux-falls-regional-office",
  // Vet Center
  "/node/add/vet_center",
  "/dubois-vet-center",
  // Vet Center - Community Access Point
  "/node/add/vet_center_cap",
  "/miami-vet-center/community-access-point/miami-vet-center-key-largo",
  // Vet Center - Facility Service
  "/node/add/vet_center_facility_health_servi",
  "/jacksonville-fl-vet-center/service/jacksonville-fl-vet-center-women-veteran-care",
  // Vet Center - Locations List
  "/node/add/vet_center_locations_list",
  "/macon-vet-center/locations",
  // Vet Center - Mobile Vet Center
  "/node/add/vet_center_mobile_vet_center",
  "/saint-george-vet-center/saint-george-mobile-vet-center",
  // Vet Center - Outstation
  "/node/add/vet_center_outstation",
  "/anchorage-vet-center/kenai-outstation",
  // Video List
  "/node/add/media_list_videos",

  // User Page
  "/user",
];

before(() => {
  // @TODO Use Cypress.env variables for user/pass.
  // @TODO Use a content admin role.
  // Ensure there is no active user session.
  cy.drupalLogout();
  cy.drupalLogin("axcsd452ksey", "drupal8");

  // Preserve the Drupal session cookie to avoid having to login
  // before testing each page.
  const cookies = cy.getCookies();
  cookies.each((cookie) => {
    if (cookie.name.match(/SS?ESS/)) {
      Cypress.Cookies.defaults({
        preserve: cookie.name,
      });
    }
  });
});

const axeContext = {
  include: [["body"]],
  exclude: [
    // 8700-item select elements apparently break accessibility tests.
    ["#edit-menu-menu-parent"],
    // Not our widget, not our problem.
    ["img.leaflet-marker-icon"],
    // Not our widget, not our problem.
    ["iframe#jsd-widget"],
  ],
};

const axeRuntimeOptions = {
  runOnly: {
    type: "tag",
    values: ["wcag2a", "wcag2aa"],
  },
};

const allViolations = [];

describe("Component accessibility test", () => {
  routes.forEach((route) => {
    const testName = `${route} has no detectable accessibility violations on load.`;
    it(testName, () => {
      cy.visit(route);
      cy.injectAxe();
      cy.wait(1000);
      cy.checkA11y(axeContext, axeRuntimeOptions, (violations) => {
        cy.accessibilityLog(violations);
        const violationData = violations.map((violation) => ({
          route,
          ...violation,
        }));
        allViolations.push(...violationData);
      });
    });
  });
});

after(() => {
  cy.writeFile(
    "cypress_accessibility_errors.json",
    JSON.stringify(allViolations)
  );
});
