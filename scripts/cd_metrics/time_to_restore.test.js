/* eslint-disable max-nested-callbacks */
// eslint-disable-next-line import/no-extraneous-dependencies, no-unused-vars
import { jest } from "@jest/globals";
const actualCommon = await import("./common");

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
      jest.unstable_mockModule("./common", () => {
        return {
          ...actualCommon,
          getParentCommitSha: jest.fn(() => "some-irrelevant-sha"),
          getCombinedStatusForCommit: jest.fn(async () => "not-needed"),
        };
      });
      const { shouldSubmitMetrics } = await import("./time_to_restore.lib");
      const result = await shouldSubmitMetrics(testsFailed);
      const expectedOutput = false;
      expect(result).toEqual(expectedOutput);
    });

    it("should agree to submit metrics when the current commit succeeded and the last commit failed tests", async () => {
      const testsFailed = false;
      jest.unstable_mockModule("./common", () => {
        return {
          ...actualCommon,
          getParentCommitSha: jest.fn(() => "some-failing-sha"),
          getCombinedStatusForCommit: jest.fn(async () => "failure"),
        };
      });
      const { shouldSubmitMetrics } = await import("./time_to_restore.lib");
      const result = await shouldSubmitMetrics(testsFailed);
      const expectedOutput = true;
      expect(result).toEqual(expectedOutput);
    });

    it("should refuse to submit metrics when the current commit succeeded and the last commit passed tests", async () => {
      const testsFailed = false;
      jest.unstable_mockModule("./common", async () => {
        return {
          ...actualCommon,
          getParentCommitSha: jest.fn(() => "some-passing-sha"),
          getCombinedStatusForCommit: jest.fn(() => "success"),
        };
      });
      const { shouldSubmitMetrics } = await import("./time_to_restore.lib");
      const result = await shouldSubmitMetrics(testsFailed);
      const expectedOutput = false;
      expect(result).toEqual(expectedOutput);
    });

    it("should agree to submit metrics when the current commit succeeded and the last commit failed tests", async () => {
      const testsFailed = false;
      jest.unstable_mockModule("./common", () => {
        return {
          ...actualCommon,
          getParentCommitSha: () => "some-failing-sha",
          getCombinedStatusForCommit: () => "failure",
        };
      });
      const { shouldSubmitMetrics } = await import("./time_to_restore.lib");
      const result = await shouldSubmitMetrics(testsFailed);
      const expectedOutput = true;
      expect(result).toEqual(expectedOutput);
    });

    it("should refuse to submit metrics when the current commit succeeded and the last commit is marked pending", async () => {
      const testsFailed = false;
      jest.unstable_mockModule("./common", () => {
        return {
          ...actualCommon,
          getParentCommitSha: () => "some-pending-sha",
          getCombinedStatusForCommit: () => "pending",
        };
      });
      const { shouldSubmitMetrics } = await import("./time_to_restore.lib");
      const result = await shouldSubmitMetrics(testsFailed);
      const expectedOutput = false;
      expect(result).toEqual(expectedOutput);
    });

    it("should agree to submit metrics when the current commit succeeded and the last commit failed tests", async () => {
      const testsFailed = false;
      jest.unstable_mockModule("./common", () => {
        return {
          ...actualCommon,
          getParentCommitSha: () => "some-failing-sha",
          getCombinedStatusForCommit: () => "failure",
        };
      });
      const { shouldSubmitMetrics } = await import("./time_to_restore.lib");
      const result = await shouldSubmitMetrics(testsFailed);
      const expectedOutput = true;
      expect(result).toEqual(expectedOutput);
    });

    it("should agree to submit metrics when the current commit succeeded and the last commit failed tests", async () => {
      const testsFailed = false;
      jest.unstable_mockModule("./common", () => {
        return {
          ...actualCommon,
          getParentCommitSha: () => "some-failing-sha",
          getCombinedStatusForCommit: () => "failure",
        };
      });
      const { shouldSubmitMetrics } = await import("./time_to_restore.lib");
      const result = await shouldSubmitMetrics(testsFailed);
      const expectedOutput = true;
      expect(result).toEqual(expectedOutput);
    });

  });
});
