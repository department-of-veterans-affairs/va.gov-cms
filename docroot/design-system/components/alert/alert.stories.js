import alert from './alert.twig';
import DrupalAttribute from '../../.storybook/DrupalAttribute';
import './alert.scss';
import './index.js';

export default { title: 'Components/Alert', component: alert };

const shared = {
  parameters: {},
  // Don't show drupal attributes in storybook controls
  argTypes: {
    attributes: {
      table: {
        disable: true
      },
    },
    title_ids: {
      table: {
        disable: true
      },
    },
    status_headings: {
      table: {
        disable: true
      },
    },
  },
}

export const Status = {
  args: {
    attributes: new DrupalAttribute(),
    type: 'status',
    title_ids: ['status', 'warning', 'error'],
    status_headings: ['status', 'warning', 'error'],
    hide_bg: false,
    messages: ['this is an example drupal alert message']
  },
  ...shared
};

Status.parameters.render = async args => {
  return await alert({
    ...Status.args, ...args
  });
}

export const Warning = {
  args: {
    attributes: new DrupalAttribute(),
    type: 'warning',
    title_ids: ['status', 'warning', 'error'],
    status_headings: ['status', 'warning', 'error'],
    hide_bg: false,
    messages: ['this is an example drupal warning message']  },
  ...shared
};

Warning.parameters.render = async args => {
  return await alert({
    ...Warning.args, ...args
  });
}

export const Error = {
  args: {
    attributes: new DrupalAttribute(),
    type: 'error',
    title_ids: ['status', 'warning', 'error'],
    status_headings: ['status', 'warning', 'error'],
    hide_bg: false,
    messages: ['this is an example drupal error message']
  },
  ...shared
};

Error.parameters.render = async args => {
  return await alert({
    ...Error.args, ...args
  });
}
