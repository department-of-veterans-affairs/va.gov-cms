/* eslint-disable import/prefer-default-export */
/* eslint-disable no-console */
import {
  buildLeadTimeDataPoint,
  buildMetricsObject,
  getStandardizedMetricName,
  getCommitTimestamp,
} from "./common.js";

/**
 * Build a metric series for the test_start_lead_time metric.
 *
 * @param {number} now The current timestamp.
 * @return {object} The metric series for this metric.
 */
export function buildTestStartLeadTimeMetricSeries(now) {
  const name = getStandardizedMetricName("test_start_lead_time");
  return buildMetricsObject(name, [
    buildLeadTimeDataPoint(now, getCommitTimestamp()),
  ]);
}
