// ***********************************************************
// This example support/index.js is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

import './commands';

/**
 * Allows Cypress to collect the results of multiple operations.
 */
const chainStart = Symbol();
cy.all = (...commands) => {
  const _ = Cypress._;

  // "Start" the chain with an effectively empty action.
  const chain = cy.wrap(null, { log: false });

  // Stop command points to the empty first action.
  const stopCommand = _.find(cy.queue.commands, {
    attributes: { chainerId: chain.chainerId }
  });

  // Start command points to the first passed command.
  const startCommand = _.find(cy.queue.commands, {
    attributes: { chainerId: commands[0].chainerId }
  });

  // Construct chain with each command pointing to another.
  const result = chain.then(() => {
    return _(commands)
      .map((cmd) => {
        return cmd[chainStart]
          ? cmd[chainStart].attributes
          : _.find(cy.queue.commands, {
              attributes: { chainerId: cmd.chainerId }
            }).attributes;
      })
      .concat(stopCommand.attributes)
      .slice(1)
      .flatMap((cmd) => cmd.prev.get('subject'))
      .value();
  });

  result[chainStart] = startCommand;

  return result;
}
