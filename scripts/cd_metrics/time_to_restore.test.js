/* eslint-disable max-nested-callbacks */
// eslint-disable-next-line import/no-extraneous-dependencies, no-unused-vars
import { jest } from "@jest/globals";
const actualCommon = await import("./common.js");

describe("time_to_restore.lib.js", () => {
  const PREVIOUS_ENV = process.env;

  beforeEach(() => {
    jest.resetModules();
    process.env = { ...PREVIOUS_ENV };
  });

  // eslint-disable-next-line no-undef
  afterAll(() => {
    process.env = PREVIOUS_ENV;
  });

  describe("shouldSubmitMetrics", () => {
    it("should refuse to submit metrics when in a failure state", async () => {
      const testsFailed = true;
      jest.unstable_mockModule("./common.js", () => {
        return {
          ...actualCommon,
          getParentCommitSha: jest.fn(() => "some-irrelevant-sha"),
          getCombinedStatusForCommit: jest.fn(async () => "not-needed"),
        };
      });
      const { shouldSubmitMetrics } = await import("./time_to_restore.lib.js");
      const result = await shouldSubmitMetrics(testsFailed);
      const expectedOutput = false;
      expect(result).toEqual(expectedOutput);
    });

    it("should agree to submit metrics when the current commit succeeded and the last commit failed tests", async () => {
      const testsFailed = false;
      jest.unstable_mockModule("./common.js", () => {
        return {
          ...actualCommon,
          getParentCommitSha: jest.fn(() => "some-failing-sha"),
          getCombinedStatusForCommit: jest.fn(async () => "failure"),
        };
      });
      const { shouldSubmitMetrics } = await import("./time_to_restore.lib.js");
      const result = await shouldSubmitMetrics(testsFailed);
      const expectedOutput = true;
      expect(result).toEqual(expectedOutput);
    });

    it("should refuse to submit metrics when the current commit succeeded and the last commit passed tests", async () => {
      const testsFailed = false;
      jest.unstable_mockModule("./common.js", async () => {
        return {
          ...actualCommon,
          getParentCommitSha: jest.fn(() => "some-passing-sha"),
          getCombinedStatusForCommit: jest.fn(() => "success"),
        };
      });
      const { shouldSubmitMetrics } = await import("./time_to_restore.lib.js");
      const result = await shouldSubmitMetrics(testsFailed);
      const expectedOutput = false;
      expect(result).toEqual(expectedOutput);
    });

    it("should refuse to submit metrics when the current commit succeeded and the last commit is marked pending", async () => {
      const testsFailed = false;
      jest.unstable_mockModule("./common.js", () => {
        return {
          ...actualCommon,
          getParentCommitSha: () => "some-pending-sha",
          getCombinedStatusForCommit: () => "pending",
        };
      });
      const { shouldSubmitMetrics } = await import("./time_to_restore.lib.js");
      const result = await shouldSubmitMetrics(testsFailed);
      const expectedOutput = false;
      expect(result).toEqual(expectedOutput);
    });

  });


  describe("buildTimeToRestoreMetricSeries", () => {
    const PREVIOUS_ENV = process.env;
  
    beforeEach(() => {
      jest.resetModules();
      process.env = { ...PREVIOUS_ENV };
    });
  
    // eslint-disable-next-line no-undef
    afterAll(() => {
      process.env = PREVIOUS_ENV;
    });
  
    it("should return a metric series with the correct name and data point", async () => {
      const now = 1700000000;
      const restoreTimestamp = 1690000000;
      const expectedRestoreTime = 10000000;
  
      jest.unstable_mockModule("./common.js", async () => {
        return {
          ...actualCommon,
          startTime: now,
        };
      });
  
      const { buildTimeToRestoreMetricSeries } = await import("./time_to_restore.lib.js");
      const result = buildTimeToRestoreMetricSeries(expectedRestoreTime);
  
      const expectedOutput = {
        metric: "cms_test.product_delivery.time_to_restore",
        points: [
          {
            timestamp: now,
            value: expectedRestoreTime,
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
  
  describe("calculateAccruedTimeInFailure", () => {
    beforeEach(() => {
      jest.resetModules();
    });
  
    it("should calculate the accrued time in failure correctly", async () => {
      jest.unstable_mockModule("./common.js", () => {
        return {
          ...actualCommon,
          getCommitTimestamp: jest.fn().mockImplementation((sha) => {
            if (sha === "failing-sha-2") {
              return 3000;
            }
            if (sha === "failing-sha-1") {
              return 2000;
            }
            if (sha === "passing-sha") {
              return 1000;
            }
            throw new Error("Unexpected sha");
          }),
          getParentCommitSha: jest.fn().mockImplementation((sha) => {
            if (sha === "failing-sha-2") {
              return "failing-sha-1";
            }
            if (sha === "failing-sha-1") {
              return "passing-sha";
            }
            if (sha === "passing-sha") {
              return "some-sha";
            }
            throw new Error("Unexpected sha");
          }),
          getCombinedStatusForCommit: jest.fn().mockImplementation(async (owner, repo, sha) => {
            if (sha.startsWith("failing-sha")) {
              return "failure";
            }
            if (sha.startsWith("passing-sha")) {
              return "success";
            }
          }),
        };
      });
  
      const { calculateAccruedTimeInFailure } = await import("./time_to_restore.lib.js");
      const result = await calculateAccruedTimeInFailure("failing-sha-2");
      const expectedOutput = 1000;
      expect(result).toEqual(expectedOutput);
    });
  
    it("should calculate the accrued time in failure correctly for three failing commits", async () => {
      jest.unstable_mockModule("./common.js", () => {
        return {
          ...actualCommon,
          getCommitTimestamp: jest.fn().mockImplementation((sha) => {
            if (sha === "failing-sha-3") {
              return 4000;
            }
            if (sha === "failing-sha-2") {
              return 3000;
            }
            if (sha === "failing-sha-1") {
              return 2000;
            }
            if (sha === "passing-sha") {
              return 1000;
            }
            throw new Error("Unexpected sha");
          }),
          getParentCommitSha: jest.fn().mockImplementation((sha) => {
            if (sha === "failing-sha-3") {
              return "failing-sha-2";
            }
            if (sha === "failing-sha-2") {
              return "failing-sha-1";
            }
            if (sha === "failing-sha-1") {
              return "passing-sha";
            }
            if (sha === "passing-sha") {
              return "some-sha";
            }
            throw new Error("Unexpected sha");
          }),
          getCombinedStatusForCommit: jest.fn().mockImplementation(async (owner, repo, sha) => {
            if (sha.startsWith("failing-sha")) {
              return "failure";
            }
            if (sha.startsWith("passing-sha")) {
              return "success";
            }
          }),
        };
      });
    
      const { calculateAccruedTimeInFailure } = await import("./time_to_restore.lib.js");
      const result = await calculateAccruedTimeInFailure("failing-sha-3");
      const expectedOutput = 2000;
      expect(result).toEqual(expectedOutput);
    });
    
    it("should return zero if the previous commit was not a failure", async () => {
      jest.unstable_mockModule("./common.js", () => {
        return {
          ...actualCommon,
          getCommitTimestamp: jest.fn().mockImplementation(() => 3000),
          getParentCommitSha: jest.fn().mockImplementation(() => "passing-sha"),
          getCombinedStatusForCommit: jest.fn().mockImplementation(async (owner, repo, sha) => {
            return "success";
          }),
        };
      });
  
      const { calculateAccruedTimeInFailure } = await import("./time_to_restore.lib.js");
      const result = await calculateAccruedTimeInFailure("failing-sha-3");
      const expectedOutput = 0;
      expect(result).toEqual(expectedOutput);
    });
  });
  
  describe("calculateTimeToRestore", () => {
    beforeEach(() => {
      jest.resetModules();
    });
  
    it("should calculate the time to restore service correctly", async () => {
      jest.unstable_mockModule("./common.js", () => {
        return {
          ...actualCommon,
          getCommitTimestamp: jest.fn().mockImplementation((sha) => {
            if (sha === "HEAD") {
              return 4000;
            }
            if (sha === "failing-sha-2") {
              return 3000;
            }
            if (sha === "failing-sha-1") {
              return 2000;
            }
            if (sha === "passing-sha") {
              return 1000;
            }
            throw new Error("Unexpected sha");
          }),
          getParentCommitSha: jest.fn().mockImplementation((sha) => {
            if (sha === "HEAD") {
              return "failing-sha-2";
            }
            if (sha === "failing-sha-2") {
              return "failing-sha-1";
            }
            if (sha === "failing-sha-1") {
              return "passing-sha";
            }
            if (sha === "passing-sha") {
              return "some-sha";
            }
            throw new Error("Unexpected sha");
          }),
          getCombinedStatusForCommit: jest.fn().mockImplementation(async (owner, repo, sha) => {
            if (sha.startsWith("failing-sha")) {
              return "failure";
            }
            if (sha.startsWith("passing-sha") || sha === "HEAD") {
              return "success";
            }
          }),
        };
      });
  
      const { calculateTimeToRestore } = await import("./time_to_restore.lib.js");
      const result = await calculateTimeToRestore();
      const expectedOutput = 2000;
      expect(result).toEqual(expectedOutput);
    });
  
    it("should calculate the time to restore service correctly when there is no accrued failure time", async () => {
      jest.unstable_mockModule("./common.js", () => {
        return {
          ...actualCommon,
          getCommitTimestamp: jest.fn().mockImplementation((sha) => {
            if (sha === "HEAD") {
              return 2000;
            }
            if (sha === "failing-sha-1") {
              return 1000;
            }
            if (sha === "passing-sha") {
              return 0;
            }
            throw new Error("Unexpected sha");
          }),
          getParentCommitSha: jest.fn().mockImplementation((sha) => {
            if (sha === "HEAD") {
              return "failing-sha-1";
            }
            if (sha === "failing-sha-1") {
              return "passing-sha";
            }
            if (sha === "passing-sha") { 
              return "some-sha";
            }
            throw new Error("Unexpected sha");
          }),
          getCombinedStatusForCommit: jest.fn().mockImplementation(async (owner, repo, sha) => {
            if (sha === "HEAD") {
              return "passing";
            }
            if (sha.startsWith("failing-sha")) {
              return "failure";
            }
            if (sha.startsWith("passing-sha")) {
              return "success";
            }
          }),
        };
      });
  
      const { calculateTimeToRestore } = await import("./time_to_restore.lib.js");
      const result = await calculateTimeToRestore();
      const expectedOutput = 1000;
      expect(result).toEqual(expectedOutput);
    });
  });
  
});
