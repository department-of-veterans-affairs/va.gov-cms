let logText = "";

beforeEach(() => {
  const testTitle = Cypress.currentTest.title;
  const testPath = Cypress.currentTest.titlePath;
  const date = new Date().toUTCString();
  const timestamp = Math.floor(Date.now() / 1000);
  logText += `VA_GOV_DEBUG ${timestamp} ${date} BEFORE ${testPath} ${testTitle}\n`;
});

afterEach(() => {
  const testTitle = Cypress.currentTest.title;
  const testPath = Cypress.currentTest.titlePath;
  const date = new Date().toUTCString();
  const timestamp = Math.floor(Date.now() / 1000);
  logText += `VA_GOV_DEBUG ${timestamp} ${date} AFTER ${testPath} ${testTitle}\n`;
});

after(() => {
  cy.writeFile("cypress.log", logText, { flag: "a+" });
});
