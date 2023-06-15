/* eslint-disable no-console */
import { metricsApi, startTime } from "./common.js";
import {
  buildTestEndLeadTimeMetricSeries,
  buildTestResultLeadTimeMetricSeries,
} from "./test_end_lead_time.lib.js";

const testsFailed = process.env.CMS_TESTS_FAILED === "true";

const params = {
  body: {
    series: [
      buildTestEndLeadTimeMetricSeries(startTime),
      buildTestResultLeadTimeMetricSeries(startTime, testsFailed),
    ],
  },
};

console.log(JSON.stringify(params, null, 2));
console.log(`Test End Lead Time: ${params.body.series[0].points[0].value}`);
const result = testsFailed ? "Failure" : "Success";
console.log(
  `Test ${result} Lead Time: ${params.body.series[1].points[0].value}`
);

metricsApi.submitMetrics(params).then((data) => {
  console.log(
    `API called successfully. Returned data: ${JSON.stringify(data)}`
  );
});
