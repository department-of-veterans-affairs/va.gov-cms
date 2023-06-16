/* eslint-disable no-restricted-syntax */
/* eslint-disable import/no-extraneous-dependencies */
import { client, v2 } from "@datadog/datadog-api-client";
import { execSync } from "child_process";
import { Octokit } from "@octokit/rest";

const options = {
  authMethods: {
    apiKeyAuth: process.env.CMS_DATADOG_API_KEY,
    appKeyAuth: process.env.CMS_DATADOG_APP_KEY,
  },
};
const configuration = client.createConfiguration(options);

client.setServerVariables(configuration, {
  site: "ddog-gov.com", // GovCloud Datadog host
});

const inTestMode = process.env.CMS_APP_NAME === "cms-test" || process.env.CMS_DATADOG_TEST_MODE === "true";
const address =
  process.env.CMS_DRUPAL_ADDRESS || process.env.DRUPAL_ADDRESS || "unknown";
const environment = process.env.CMS_ENVIRONMENT_TYPE || "unknown";

export { v2 };
export const eventsApi = new v2.EventsApi(configuration);
export const metricsApi = new v2.MetricsApi(configuration);

export const octokit = new Octokit({
  auth: process.env.GITHUB_TOKEN,
});

export const startTime = Math.floor(Date.now() / 1000);

/**
 * Standardize metrics names so that they are easy to find in Datadog.
 *
 * @param {string} name The name of the metric.
 * @return {string} The standardized metric name.
 */
export function getStandardizedMetricName(name) {
  const prefix = `cms${inTestMode ? "_test" : ""}`;
  return `${prefix}.product_delivery.${name}`;
}

/**
 * Get the timestamp of the specified commit.
 *
 * @param {string} sha The SHA of the commit.
 * @return {number} The timestamp of the specified commit.
 */
export function getCommitTimestamp(sha = "HEAD") {
  const command = `git show -s --format=%ct ${sha}`;
  const output = execSync(command).toString().trim();
  const timestamp = parseInt(output, 10);
  return timestamp;
}

/**
 * Get the default resources for metrics.
 *
 * @return {array} The default resources for metrics.
 */
export function getDefaultResources() {
  return [
    {
      name: address,
      type: "host",
    },
    {
      name: environment,
      type: "env",
    },
  ];
}

/**
 * Build a metrics body object.
 *
 * @param {string} metric The name of the metric, standardized by getStandardizedMetricName.
 * @param {array} points The data points for the metric.
 * @param {number} type The type of metric (generally 3, for "gauge").
 * @param {array} addedResources The resources to add to the metric.
 * @return {object} The metrics body object.
 */
export function buildMetricsObject(
  metric,
  points,
  type = 3,
  addedResources = []
) {
  return {
    metric,
    type,
    points,
    resources: getDefaultResources().concat(addedResources),
  };
}

/**
 * Build a data point for a lead time metric.
 *
 * @param {number} now The current timestamp in seconds since the epoch.
 * @param {string} commitTimestamp The timestamp of the last commit.
 * @return {object} The data point for the metric.
 */
export function buildLeadTimeDataPoint(now, commitTimestamp) {
  return {
    timestamp: now,
    value: now - commitTimestamp,
  };
}

/**
 * Get the parent commit sha of the commit with the specified sha.
 *
 * @param {string} sha The SHA of the commit.
 * @return {string} The SHA of the parent commit.
 */
export function getParentCommitSha(sha) {
  const command = `git rev-parse ${sha}^`;
  return execSync(command).toString().trim();
}

/**
 * Get the combined status for a commit.
 *
 * The combined status should be one of the following:
 * - "success": All statuses have a state of "success".
 * - "pending": There is at least one status with a state of "pending"
 *   and no statuses with a state of "failure".
 * - "failure": There is at least one status with a state of "failure".
 *
 * @param {string} owner The owner of the repository.
 * @param {string} repo The name of the repository.
 * @param {string} sha The SHA of the commit.
 * @return {string} The combined status for the commit.
 */
export async function getCombinedStatusForCommit(owner, repo, sha) {
  const combinedStatus = await octokit.repos.getCombinedStatusForRef({
    owner,
    repo,
    ref: sha,
  });
  return combinedStatus.status;
}
