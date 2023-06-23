/* eslint-disable no-console */
import { metricsApi } from "./common.js";
import {
  shouldSubmitMetrics,
  calculateTimeToRestore,
  buildTimeToRestoreMetricSeries,
} from "./time_to_restore.lib.js";

const testsFailed = process.env.CMS_TESTS_FAILED === "true";

shouldSubmitMetrics(testsFailed).then((shouldSubmit) => {
  if (!shouldSubmit) {
    if (testsFailed) {
      console.log("Not submitting metrics because tests failed.");
    } else {
      console.log("Not submitting metrics because previous commit passed.");
    }
    return;
  }
  return calculateTimeToRestore()
    .then((timeToRestore) => {
      console.log("Submitting metrics.");
      const metricSeries = buildTimeToRestoreMetricSeries(timeToRestore);
      const params = {
        body: {
          series: [metricSeries],
        },
      };
      console.log(JSON.stringify(params, null, 2));
      console.log(`Time to Restore: ${params.body.series[0].points[0].value}`);
      return params;
    })
    .then((params) => metricsApi.submitMetrics(params))
    .then((data) => {
      console.log(
        `API called successfully. Returned data: ${JSON.stringify(data)}`
      );
    });
});
