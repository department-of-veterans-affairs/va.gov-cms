/* eslint-disable max-nested-callbacks */
// eslint-disable-next-line import/no-extraneous-dependencies, no-unused-vars
import { jest } from "@jest/globals";

const actualCommon = await import("./common.js");

describe("test_start_lead_time.lib.js", () => {
  const PREVIOUS_ENV = process.env;

  beforeEach(() => {
    jest.resetModules();
    process.env = { ...PREVIOUS_ENV };
  });

  // eslint-disable-next-line no-undef
  afterAll(() => {
    process.env = PREVIOUS_ENV;
  });

  describe("buildTestStartLeadTimeMetricSeries", () => {
    it("should return a metric series with the correct name and data point", async () => {
      const now = 1700000000;
      const commitTimestamp = 1680000000;
      const expectedLeadTime = 20000000;
      jest.unstable_mockModule("./common.js", async () => {
        return {
          ...actualCommon,
          startTime: now,
          getCommitTimestamp: () => commitTimestamp,
        };
      });
      const { buildTestStartLeadTimeMetricSeries } = await import("./test_start_lead_time.lib.js");
      const result = buildTestStartLeadTimeMetricSeries(now);
      const expectedOutput = {
        metric: "cms_test.product_delivery.test_start_lead_time",
        points: [
          {
            timestamp: now,
            value: expectedLeadTime,
          },
        ],
        resources: [
          {
            name: "https://va-gov-cms.ddev.site/",
            type: "host",
          },
          {
            name: "local",
            type: "env",
          },
        ],
        type: 3,
      };
      expect(result).toEqual(expectedOutput);
    });
  });
});
