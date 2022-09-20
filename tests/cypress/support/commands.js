/* eslint-disable no-console */
import "@testing-library/cypress/add-commands";
import "cypress-axe";
import "cypress-file-upload";
import "cypress-real-events/support";
import "cypress-xpath";
import { Octokit } from "@octokit/rest";

const octokit = new Octokit({
  auth: process.env.GITHUB_TOKEN,
});

const owner = process.env.TUGBOAT_GITHUB_OWNER;
const repo = process.env.TUGBOAT_GITHUB_REPO;
const issue_number = process.env.TUGBOAT_GITHUB_PR;

const compareSnapshotCommand = require("cypress-visual-regression/dist/command");

Cypress.Commands.add("drupalLogin", (username, password) => {
  cy.visit("/user/login");

  cy.get("#user-login-form").then(($form) => {
    // PIV login by default in claro theme, so cypress checks for the toggle to click it.
    if ($form.hasClass("piv-login")) {
      cy.get(".js-va-login-toggle").click();
    }
  });

  cy.get("#edit-name").type(username);
  cy.get("#edit-pass").type(password);
  cy.get("#edit-submit").click();
  cy.window().then((window) => {
    cy.wrap(window.drupalSettings.user.uid).as("uid");
  });
});

Cypress.Commands.add("drupalLogout", () => {
  cy.visit("/user/logout");
});

Cypress.Commands.add("drupalDrushCommand", (command) => {
  let cmd = "drush %command";
  if (Cypress.env("VAGOV_INTERACTIVE")) {
    cmd = "ddev drush %command";
  }
  if (typeof command === "string") {
    command = [command];
  }
  return cy.exec(cmd.replace("%command", command.join(" ")));
});

Cypress.Commands.add("drupalDrushEval", (php) => {
  return cy.drupalDrushCommand(["php:eval", `'${php.replace(/'/g, `'\\''`)}'`]);
});

Cypress.Commands.add("drupalDrushUserCreate", (username, password) => {
  return cy.drupalDrushCommand([
    "user:create",
    username,
    `--password=${password}`,
    `--mail=${username}@example.org`,
  ]);
});

Cypress.Commands.add("drupalDrushUserRoleAdd", (username, role) => {
  return cy.drupalDrushCommand(["user:role:add", role, username]);
});

Cypress.Commands.add("drupalAddUserWithRole", (role, username, password) => {
  cy.drupalDrushUserCreate(username, password);
  return cy.drupalDrushUserRoleAdd(username, role);
});

Cypress.Commands.add(
  "iframe",
  { prevSubject: "element" },
  ($iframe, callback = () => {}) => {
    return cy
      .wrap($iframe)
      .should((iframe) => expect(iframe.contents().find("body")).to.exist)
      .then((iframe) => cy.wrap(iframe.contents().find("body")));
  }
);

Cypress.Commands.add("type_ckeditor", (element, content) => {
  cy.wait(5000);
  cy.window().then((win) => {
    const elements = Object.keys(win.CKEDITOR.instances);
    if (elements.indexOf(element) === -1) {
      const matches = elements.filter((el) => el.includes(element));
      if (matches.length) {
        element = matches[0];
      }
    }
    win.CKEDITOR.instances[element].setData(content);
  });
});

Cypress.Commands.add("scrollToSelector", (selector) => {
  return cy.document().then((document) => {
    const htmlElement = document.querySelector("html");
    if (htmlElement) {
      htmlElement.style.scrollBehavior = "inherit";
    }
    cy.get(selector).scrollIntoView({ offset: { top: 0 } });
    return cy.get(selector);
  });
});

Cypress.Commands.add("scrollToXpath", (xpath) => {
  return cy.document().then((document) => {
    const htmlElement = document.querySelector("html");
    if (htmlElement) {
      htmlElement.style.scrollBehavior = "inherit";
    }
    cy.xpath(xpath).scrollIntoView({ offset: { top: 0 } });
    return cy.xpath(xpath);
  });
});

Cypress.Commands.add("getDataLayer", () => {
  return cy
    .window()
    .then((window) =>
      window.dataLayer.filter((object) => object.event === "pageLoad").pop()
    );
});

Cypress.Commands.add("getDrupalSettings", () => {
  return cy.window().then((window) => window.drupalSettings);
});

Cypress.Commands.add("getLastCreatedTaxonomyTerm", () => {
  return cy.get("@uid").then((uid) => {
    const command = `
        $query = \\Drupal::entityQuery('taxonomy_term');
        $result = $query
          ->condition('revision_user', ${uid})
          ->sort('revision_created' , 'DESC')
          ->execute();
        echo reset($result);
      `;
    return cy.drupalDrushEval(command);
  });
});

Cypress.Commands.add("unsetWorkbenchAccessSections", () => {
  return cy.get("@uid").then((uid) => {
    const command = `
        $user = \\Drupal\\user\\Entity\\User::load(${uid});
        $section_scheme = \\Drupal::entityTypeManager()->getStorage('access_scheme')->load('section');
        $section_storage = \\Drupal::service('workbench_access.user_section_storage');
        $current_sections = $section_storage->getUserSections($section_scheme, $user);
        if (!empty($current_sections)) {
          $section_storage->removeUser($section_scheme, $user, $current_sections);
        }
      `;
    return cy.drupalDrushEval(command);
  });
});

Cypress.Commands.add("setWorkbenchAccessSections", (value) => {
  return cy
    .unsetWorkbenchAccessSections()
    .then(() => cy.get("@uid"))
    .then((uid) => {
      const command = `
        $user = \\Drupal\\user\\Entity\\User::load(${uid});
        $section_scheme = \\Drupal::entityTypeManager()->getStorage('access_scheme')->load('section');
        $section_storage = \\Drupal::service('workbench_access.user_section_storage');
        $section_storage->addUser($section_scheme, $user, explode(',', '${value}'));
      `;
      return cy.drupalDrushEval(command);
    });
});

const getTableText = (violations) => {
  const tableText = violations
    .map(
      (value, index) =>
        `|${index}|${value.id}|${value.impact}|${value.description}|`
    )
    .join("\n");
  return `<!-- Nate Did This -->
## Cypress Accessibility Test Failures

| (index) | id | impact | description | nodes | Issue(s) or Resolution |
| -- | -- | -- | -- | -- | -- |
${tableText}

  `;
};

const reportAccessibilityViolations = async (violations) => {
  console.log(JSON.stringify(violations));
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

Cypress.Commands.add("reportAllAccessibilityViolations", (violations) => {
  try {
    if (owner && repo && issue_number) {
      reportAccessibilityViolations(violations);
    }
  } catch (error) {
    console.error(error);
  }
});


Cypress.Commands.add("accessibilityLog", (violations) => {
  const violationData = violations.map(
    ({ id, impact, description, nodes }) => ({
      id,
      impact,
      description,
      target: nodes[0].target,
      nodes: nodes.length,
    })
  );
  cy.task("table", violationData);
});

compareSnapshotCommand();
