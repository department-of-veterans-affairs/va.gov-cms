/* eslint-disable no-console */
import {
  getCombinedStatusForCommit,
  getCommitTimestamp,
  getParentCommitSha,
  buildMetricsObject,
  getStandardizedMetricName,
  startTime,
} from "./common.js";

/**
 * @file Calculate a metric for the time to restore service.
 *
 * For our purposes, and because it's too substantial of a pain to calculate this
 * any other way, we define "time to restore service" as the delta between the
 * first failing commit and the current commit _according to their timestamps_,
 * not their actual test results.
 *
 * This metric is calculated as follows:
 *
 * 1. If the current commit has failed, we do not report anything because we are in
 *    the middle of a failure.
 * 2. If the current commit has passed, we determine whether the previous commit
 *    failed, and if so:
 *    A. We set the current commit as the "first passing commit".
 *    B. We calculate the accrued time in failure for the previous commit, that is,
 *    the time between the first failing commit and that commit.
 *    C. The time to restore service is equal to that accrued time in failure plus
 *    the time between the previous (last failing) commit and the current (first
 *    passing) commit.
 *
 * For example, if we have the following commits:
 *    A. 2021-01-01: Passed
 *    B. 2021-01-02: Passed
 *    C. 2021-01-03: Failed
 *    D. 2021-01-04: Passed
 *
 * Then the accrued time in failure for commit C is 0, because it is the first failing
 * commit. The time to restore for commit D is 86400, because it is the first
 * passing commit after commit C, and the time between commit C and commit D is 86400
 * seconds.
 *
 * For another example, if we have the following commits:
 *   A. 2021-01-01: Passed
 *   B. 2021-01-02: Failed
 *   C. 2021-01-03: Failed
 *   D. 2021-01-04: Failed
 *   E. 2021-01-05: Failed
 *   F. 2021-01-06: Passed
 *
 * Then the accrued time in failure for commit B is 0, because it is the first failing
 * commit. The accrued time in failure for commit C is 86400, because the time between
 * commit B and commit C is 86400 seconds. The accrued time in failure for commit D is
 * 172800 (D - C + C - B). The accrued time in failure for E is 259200 (E - D + D - C +
 * C - B). The time to restore for F is 345600 (F - E + E - D + D - C + C - B).
 */

const owner = "department-of-veterans-affairs";
const repo = "va.gov-cms";

/**
 * Determine whether or not to submit metrics.
 *
 * This will determine whether or not to submit metrics based on the combined statuses
 * of recent commits. If the current commit has failed, we do not submit metrics. If
 * the current commit has passed, we determine whether the previous commit failed, and
 * if so, we submit metrics.
 *
 * @param {boolean} testsFailed Whether or not the tests failed.
 * @return {boolean} Whether or not to submit metrics.
 */
export async function shouldSubmitMetrics(testsFailed) {
  if (testsFailed) {
    return false;
  }
  let previousSha = getParentCommitSha("HEAD");
  let previousStatus = await getCombinedStatusForCommit(
    owner,
    repo,
    previousSha
  );
  let limit = 25;
  while (previousStatus === "pending" && limit > 0) {
    previousSha = getParentCommitSha(previousSha);
    previousStatus = await getCombinedStatusForCommit(owner, repo, previousSha);
    limit -= 1;
  }
  return previousStatus === "failure";
}

/**
 * Build a metric series for the time_to_restore metric.
 *
 * @param {number} timeToRestore The value for this metric.
 * @return {object} The metric series for this metric.
 */
export function buildTimeToRestoreMetricSeries(timeToRestore) {
  const name = getStandardizedMetricName("time_to_restore");
  const now = startTime;
  return buildMetricsObject(name, [{
    timestamp: now,
    value: timeToRestore,
  }]);
}

/**
 * Calculate the accrued time in failure for a given commit sha.
 *
 * The assumption is the supplied commit is a failing commit.
 *
 * @param {string} sha The sha of a failing commit.
 * @return {number} The accrued time in failure for the given commit sha.
 */
export async function calculateAccruedTimeInFailure(sha) {
  const commitTimestamp = getCommitTimestamp(sha);
  const previousSha = getParentCommitSha(sha);
  const previousStatus = await getCombinedStatusForCommit(
    owner,
    repo,
    previousSha
  );
  if (previousStatus === "success") {
    // This would seem to be the first failing commit, so there is no accrued time in
    // failure.
    return 0;
  }
  const previousTimestamp = getCommitTimestamp(previousSha);
  const previousTimeInFailure = await calculateAccruedTimeInFailure(
    previousSha
  );
  const currentTimeInFailure = commitTimestamp - previousTimestamp;
  return previousTimeInFailure + currentTimeInFailure;
}

/**
 * Calculate the time to restore service.
 *
 * This is equal to the accrued time in failure for the chain of failing commits
 * leading up to the current commit, plus the time between the most recent failing
 * commit and the current commit.
 *
 * This assumes that the current commit has passed and the previous commit (at least)
 * has failed.
 *
 * @return {number} The time to restore service.
 */
export async function calculateTimeToRestore() {
  const currentCommit = "HEAD";
  const currentTimestamp = getCommitTimestamp(currentCommit);
  const previousCommit = getParentCommitSha(currentCommit);
  const previousTimestamp = getCommitTimestamp(previousCommit);
  const currentDelta = currentTimestamp - previousTimestamp;
  const accruedTimeInFailure = await calculateAccruedTimeInFailure(
    previousCommit
  );
  return accruedTimeInFailure + currentDelta;
}
