/* eslint-disable max-nested-callbacks */
// eslint-disable-next-line import/no-extraneous-dependencies, no-unused-vars
const { jest } = require("@jest/globals");

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
      const actualCommon = await import("./common");
      jest.unstable_mockModule("./common", () => {
        return {
          ...actualCommon,
          getParentCommitSha: jest.fn(() => "some-irrelevant-sha"),
          getCombinedStatusForCommit: jest.fn(() => "not-needed"),
        };
      });
      const { shouldSubmitMetrics } = await import("./time_to_restore.lib");
      const result = await shouldSubmitMetrics(testsFailed);
      const expectedOutput = false;
      expect(result).toEqual(expectedOutput);
    });

    it("should refuse to submit metrics when the current commit succeeded and the last commit passed tests", async () => {
      const testsFailed = false;
      const actualCommon = await import("./common");
      jest.unstable_mockModule("./common", () => {
        return {
          getParentCommitSha: () => "some-passing-sha",
          getCombinedStatusForCommit: () => "success",
          ...actualCommon,
        };
      });
      const { shouldSubmitMetrics } = await import("./time_to_restore.lib");
      const result = await shouldSubmitMetrics(testsFailed);
      const expectedOutput = false;
      expect(result).toEqual(expectedOutput);
    });

    it("should agree to submit metrics when the current commit succeeded and the last commit failed tests", async () => {
      const testsFailed = false;
      const actualCommon = await import("./common");
      jest.unstable_mockModule("./common", () => {
        return {
          getParentCommitSha: () => "some-failing-sha",
          getCombinedStatusForCommit: () => "failure",
          ...actualCommon,
        };
      });
      const { shouldSubmitMetrics } = await import("./time_to_restore.lib");
      const result = await shouldSubmitMetrics(testsFailed);
      const expectedOutput = true;
      expect(result).toEqual(expectedOutput);
    });

    it("should refuse to submit metrics when the current commit succeeded and the last commit is marked pending", async () => {
      const testsFailed = false;
      const actualCommon = await import("./common");
      jest.unstable_mockModule("./common", () => {
        return {
          getParentCommitSha: () => "some-pending-sha",
          getCombinedStatusForCommit: () => "pending",
          ...actualCommon,
        };
      });
      const { shouldSubmitMetrics } = await import("./time_to_restore.lib");
      const result = await shouldSubmitMetrics(testsFailed);
      const expectedOutput = false;
      expect(result).toEqual(expectedOutput);
    });

  });
});
