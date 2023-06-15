/**
 * @file Set environment variables for automated tests.
 */

process.env.CMS_DATADOG_TEST_MODE = "true";
process.env.CMS_TESTS_FAILED = "false";
process.env.CMS_ENVIRONMENT_TYPE = "local";
process.env.DRUPAL_ADDRESS = "https://va-gov-cms.ddev.site/";
process.env.GITHUB_TOKEN = "not-even-a-real-token";
