const fs = require("fs");
const { Octokit } = require("@octokit/rest");

const octokit = new Octokit({
  auth: process.env.GITHUB_TOKEN,
});

const owner = process.env.TUGBOAT_GITHUB_OWNER;
const repo = process.env.TUGBOAT_GITHUB_REPO;
const issue_number = process.env.TUGBOAT_GITHUB_PR;

const getText = (violations) => {
  const text = violations
    .map((value, index) => {
      return `**Route**: \`${value.route}\`
**Issue #**: ${index}
**Impact**: ${value.impact}
**ID**: \`${value.id}\`
**Target**: \`${value.target}\`
**Nodes**: \`${value.nodes}\`
**Description**: ${value.description}
`;
    })
    .join("\n");
  return `<!-- Nate Did This -->
## Cypress Accessibility Test Failures

${text}

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
          body: getText(violations),
        });
      }
    });
};

const reportAllAccessibilityViolations = () => {
  try {
    const json = fs.readFileSync("cypress_errors.json", "utf8");
    reportCypressErrors(JSON.parse(json));
  } catch (error) {
    console.error(error);
  }
};

reportAllAccessibilityViolations();
