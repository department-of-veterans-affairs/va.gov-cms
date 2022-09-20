const fs = require('fs');
const { Octokit } = require('@octokit/rest');

const octokit = new Octokit({
  auth: process.env.GITHUB_TOKEN,
});

const owner = process.env.TUGBOAT_GITHUB_OWNER;
const repo = process.env.TUGBOAT_GITHUB_REPO;
const issue_number = process.env.TUGBOAT_GITHUB_PR;

const getTableText = (violations) => {
  const tableText = violations
    .map(
      (value, index) =>
        `|${index}|${value.id}|${value.impact}|${value.description}|`
    )
    .join("\n");
  return `<!-- Nate Did This -->
## Cypress Accessibility Test Failures

| route | (index) | id | impact | description | nodes | Issue(s) or Resolution |
| -- | -- | -- | -- | -- | -- | -- |
${tableText}

  `;
};

const reportCypressErrors = async (violations) => {
  await octokit.rest.issues
    .listComments({
      owner,
      repo,
      issue_number,
    })
    .then((response) => response.data)
    .then((data) =>
      data.filter((comment) => comment.body.includes("<!-- Nate Did This -->"))
    )
    .then((data) =>
      Promise.all(
        data.map((comment) =>
          octokit.rest.issues.deleteComment({
            owner,
            repo,
            comment: comment.id,
          })
        )
      )
    )
    .then(() => {
      if (text.length > 0) {
        return octokit.rest.issues.createComment({
          owner,
          repo,
          issue_number,
          body: getTableText(violations),
        });
      }
    });
};

const reportAllAccessibilityViolations = (violations) => {
  try {
    const text = fs.readFileSync('error_table.md', 'utf8');
    reportCypressErrors(text);
  } catch (error) {
    console.error(error);
  }
};
