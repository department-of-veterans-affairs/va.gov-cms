/* eslint-disable no-console */
import {
  buildLeadTimeDataPoint,
  buildMetricsObject,
  getStandardizedMetricName,
  getCommitTimestamp,
} from "./common.js";

/**
 * Build a metric series for the test_end_lead_time metric.
 *
 * @param {number} now The current timestamp.
 * @return {object} The metric series object.
 */
export function buildTestEndLeadTimeMetricSeries(now) {
  const name = getStandardizedMetricName("test_end_lead_time");
  return buildMetricsObject(name, [
    buildLeadTimeDataPoint(now, getCommitTimestamp()),
  ]);
}

/**
 * Build a metric series for the appropriate result metric as appropriate.
 *
 * @param {number} now The current timestamp.
 * @param {boolean} testsFailed Whether or not the tests failed.
 * @return {object} The metric series object.
 */
export function buildTestResultLeadTimeMetricSeries(now, testsFailed) {
  const infix = testsFailed ? "failure" : "success";
  const name = getStandardizedMetricName(`test_${infix}_lead_time`);
  return buildMetricsObject(name, [
    buildLeadTimeDataPoint(now, getCommitTimestamp()),
  ]);
}
