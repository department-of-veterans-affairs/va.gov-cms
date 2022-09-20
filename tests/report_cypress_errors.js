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
        `|${value.route}|${index}|\`${value.id}\`|${value.impact}|${value.description}|${value.target}|${value.nodes}|`
    )
    .join("\n");
  return `<!-- Nate Did This -->
## Cypress Accessibility Test Failures

| route | (index) | id | impact | description | target | nodes |
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
            comment_id: comment.id,
          })
        )
      )
    )
    .then(() => {
      if (violations.length > 0) {
        return octokit.rest.issues.createComment({
          owner,
          repo,
          issue_number,
          body: getTableText(violations),
        });
      }
    });
};

const reportAllAccessibilityViolations = () => {
  try {
    const json = fs.readFileSync('cypress_errors.json', 'utf8');
    reportCypressErrors(JSON.parse(json));
  } catch (error) {
    console.error(error);
  }
};

reportAllAccessibilityViolations();
