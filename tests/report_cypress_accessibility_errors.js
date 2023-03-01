/* eslint-disable no-console */
const fs = require("fs");
const { Octokit } = require("@octokit/rest");
const { exit } = require("process");

const octokit = new Octokit({
  auth: process.env.GITHUB_TOKEN,
});

// These variables can only be read from Tugboat environments.
// Fortunately, that's the only place we need them :)
const owner = process.env.TUGBOAT_GITHUB_OWNER;
const repo = process.env.TUGBOAT_GITHUB_REPO;
// Not camelcased because it corresponds to an argument in the GitHub API call.
// eslint-disable-next-line camelcase
const issue_number = process.env.TUGBOAT_GITHUB_PR;

// A hidden string we'll inject into our comments so that we can find them
// and kill them later.
const token = "<!-- Cypress Accessibility Errors -->";

// If we're not working on a PR, or not running on Tugboat, or if things don't
// work correctly, we should exit.
//
// Failing silently should be okay because we're not relying upon this script
// to prevent the commit from being approved.  The commit should pass or fail
// regardless of whether this script functions flawlessly or goes down in
// flames.
//
// The purpose of this script is merely to provide a convenient report so that
// we don't have to go to Tugboat, view the full log, scroll, scroll more, and
// then try to read the failures or copypasta them... but they would be in a
// terrible format, and if tests are still running then we might have to deal
// with the confounded autoscroll as well.
//
// eslint-disable-next-line camelcase
if (owner === undefined || repo === undefined || issue_number === undefined) {
  exit(0);
}

/**
 * Escape HTML tags that might show up in the middle of the comment text.
 *
 * If it appears unescaped, then it can break the layout of the comment and
 * make it considerably harder to read.
 *
 * @param {string} html Text possibly containing HTML tags.
 * @return {string} The text with escaped HTML tags.
 */
function escapeHTML(html) {
  const fn = function (tag) {
    const charsToReplace = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&#34;",
    };
    return charsToReplace[tag] || tag;
  };
  return html.replace(/[&<>"]/g, fn);
}

/**
 * Get comment text for a given list of violations.
 *
 * @param {object[]} violations A list of violations provided by Cypress.
 * @return {string} The comment text.
 */
const getText = (violations) => {
  const text = violations
    .map((value) => {
      const nodes = value.nodes
        .map((node) => {
          // Don't return results after the first child.
          if (
            node.target[0].match(/.*nth-child\(\d+\).*/) &&
            !node.target[0].match(/.*nth-child\(1\).*/)
          ) {
            return "";
          }
          return `
- **HTML**: \`${node.html.replace("\n", "")}\`
  **Impact**: ${node.impact}
  **Target**: \`${node.target}\`
  **Summary**: ${escapeHTML(node.failureSummary)}
`;
        })
        // Filter out the empty results that result from ignored nodes.
        .filter((description) => description.length > 0)
        .join("\n");
      return `
### \`${value.route}\`
**ID**: \`${value.id}\`
**Impact**: ${value.impact}
**Tags**: \`${value.tags.join(", ")}\`
**Description**: ${escapeHTML(value.description)}
**Help**: [${escapeHTML(value.help)}](${value.helpUrl})
**Nodes**: ${nodes}

`;
    })
    .join("\n");
  return `${token}
## Cypress Accessibility Test Failures

${text}

  `;
};

/**
 * The primary function.
 *
 * @param {object[]} violations A list of violations from Cypress.
 */
const reportCypressErrors = async (violations) => {
  await octokit.rest.issues
    // List all comments on this PR.
    .listComments({
      owner,
      repo,
      issue_number,
    })
    .then((response) => response.data)
    // For any and all comments including the token above...
    .then((data) => data.filter((comment) => comment.body.includes(token)))
    // delete them, so they no longer clutter the PR.
    // If there are no more accessibility errors, this will clean the PR!
    // Otherwise, we'll be replacing it with a new list of grievances soon.
    .then((data) =>
      Promise.all(
        data.map((comment) =>
          octokit.rest.issues.deleteComment({
            owner,
            repo,
            comment_id: comment.id,
          })
        )
      )
    )
    // With the old report comment(s) safely deleted, we create a new one.
    .then(() => {
      if (violations.length > 0) {
        return octokit.rest.issues.createComment({
          owner,
          repo,
          issue_number,
          body: getText(violations),
        });
      }
    });
};

/**
 * A wrapper for the primary script.
 *
 * Its primary purpose is to catch errors thrown by the main function call
 * and its consequences so that, in the event of failure, we do not cause
 * unanticipated issues with the other CI tests that might be running at the
 * same time.
 */
const reportAllAccessibilityViolations = () => {
  try {
    const json = fs.readFileSync(
      "cypress_accessibility_violations.json",
      "utf8"
    );
    reportCypressErrors(JSON.parse(json));
  } catch (error) {
    console.error(error);
  }
};

reportAllAccessibilityViolations();
