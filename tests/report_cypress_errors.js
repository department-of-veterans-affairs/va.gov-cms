const fs = require("fs");
const { Octokit } = require("@octokit/rest");

const octokit = new Octokit({
  auth: process.env.GITHUB_TOKEN,
});

const owner = process.env.TUGBOAT_GITHUB_OWNER;
const repo = process.env.TUGBOAT_GITHUB_REPO;
const issue_number = process.env.TUGBOAT_GITHUB_PR;

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

const getText = (violations) => {
  const text = violations
    .map((value, index) => {
      let nodes = value.nodes
        .map((node) => {
          // Don't return results after the first child.
          if (
            node.target[0].match(/.*nth-child\(\d+\).*/) &&
            !node.target[0].match(/.*nth-child\(1\).*/)
          ) {
            return "";
          }
          return `
- **HTML**: \`${node.html.replace('\n', '')}\`
  **Impact**: ${node.impact}
  **Target**: \`${node.target}\`
  **Summary**: ${escapeHTML(node.failureSummary)}
`;
        })
        .filter((value) => value.length > 0)
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