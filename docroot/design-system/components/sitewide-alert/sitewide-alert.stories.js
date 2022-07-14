import alert from './sitewide-alert.twig';
import DrupalAttribute from '../../.storybook/DrupalAttribute';
import './sitewide-alert.scss';
import './index.js';

export default { title: 'Components/Sitewide Alert', component: alert };

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

export const Informational = {
  args: {
    attributes: new DrupalAttribute(),
    uuid: 1,
    title: 'Upcoming system maintenance',
    message: 'The regular daily maintenance window is coming up. The update will last about 15 minutes. During that time you won’t be able to use the CMS.' + '<br>' +
      '<strong>Start:</strong> Today at about 3:30 p.m. ET\n' +
      '<br>' +
      '<strong>End:</strong> Around 15 minutes later',
    is_dismissible: true,
    style: 'info'
  },
  ...shared
};

Informational.parameters.render = async args => {
  return await alert({
    ...Informational.args, ...args
  });
}

export const Warning = {
  args: {
    attributes: new DrupalAttribute(),
    uuid: 1,
    title: 'Upcoming system maintenance',
    message: 'The regular daily maintenance window is coming up. The update will last about 15 minutes. During that time you won’t be able to use the CMS.' + '<br>' +
      '<strong>Start:</strong> Today at about 3:30 p.m. ET\n' +
      '<br>' +
      '<strong>End:</strong> Around 15 minutes later',
    is_dismissible: false,
    style: 'warning'
  },
  ...shared
};

Warning.parameters.render = async args => {
  return await alert({
    ...Warning.args, ...args
  });
}

export const Shield = {
  args: {
    attributes: new DrupalAttribute(),
    uuid: 1,
    title: 'System maintenance in progress',
    message: 'The maintenance will last about 15 minutes. Stay on this page to avoid losing changes. This message will disappear when it’s safe to continue working.',
    is_dismissible: false,
    style: 'shield'
  },
  ...shared
};

Shield.parameters.render = async args => {
  return await alert({
    ...Shield.args, ...args
  });
}
