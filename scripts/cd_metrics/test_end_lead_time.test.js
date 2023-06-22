/* eslint-disable max-nested-callbacks */
// eslint-disable-next-line import/no-extraneous-dependencies, no-unused-vars
import { jest } from "@jest/globals";
const actualCommon = await import("./common.js");

describe("test_end_lead_time.lib.js", () => {
  const PREVIOUS_ENV = process.env;

  beforeEach(() => {
    jest.resetModules();
    process.env = { ...PREVIOUS_ENV };
  });

  // eslint-disable-next-line no-undef
  afterAll(() => {
    process.env = PREVIOUS_ENV;
  });

  describe("buildTestEndLeadTimeMetricSeries", () => {
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
      const { buildTestEndLeadTimeMetricSeries } = await import("./test_end_lead_time.lib.js");
      const result = buildTestEndLeadTimeMetricSeries(now);
      const expectedOutput = {
        metric: "cms_test.product_delivery.test_end_lead_time",
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

  describe("buildTestResultLeadTimeMetricSeries", () => {
    it("should return a metric series with the correct name and data point when tests pass", async () => {
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
      const { buildTestResultLeadTimeMetricSeries } = await import("./test_end_lead_time.lib.js");
      const result = buildTestResultLeadTimeMetricSeries(now, false);

      const expectedOutput = {
        metric: "cms_test.product_delivery.test_success_lead_time",
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

    it("should return a metric series with the correct name and data point when tests fail", async () => {
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
      const { buildTestResultLeadTimeMetricSeries } = await import("./test_end_lead_time.lib.js");
      const result = buildTestResultLeadTimeMetricSeries(now, true);

      const expectedOutput = {
        metric: "cms_test.product_delivery.test_failure_lead_time",
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
