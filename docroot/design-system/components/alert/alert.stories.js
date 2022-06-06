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
  },
}

export const Status = {
  args: {
    attributes: new DrupalAttribute(),
    type: 'status',
    title_ids: ['status', 'warning', 'error'],
    status_headings: ['status', 'warning', 'error'],
    messages: ['this is an example alert message']
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
    messages: ['this is an example alert message']  },
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
    messages: ['this is an example alert message']
  },
  ...shared
};

Error.parameters.render = async args => {
  return await alert({
    ...Error.args, ...args
  });
}
