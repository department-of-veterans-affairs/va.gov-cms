/* eslint-disable no-console */
import { metricsApi, startTime } from "./common.js";
import { buildTestStartLeadTimeMetricSeries } from "./test_start_lead_time.lib.js";

const params = {
  body: {
    series: [buildTestStartLeadTimeMetricSeries(startTime)],
  },
};

console.log(JSON.stringify(params, null, 2));
console.log(`Test Start Lead Time: ${params.body.series[0].points[0].value}`);

metricsApi.submitMetrics(params).then((data) => {
  console.log(
    `API called successfully. Returned data: ${JSON.stringify(data)}`
  );
});
