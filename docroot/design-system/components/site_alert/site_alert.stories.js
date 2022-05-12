import alert from './site_alert.twig';
import DrupalAttribute from '../../.storybook/DrupalAttribute';
import './site_alert.scss';
import './index.js';

export default { title: 'Components/Site Alert', component: alert };

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

export const Low = {
  args: {
    attributes: new DrupalAttribute(),
    alert: {
      id: 1,
      label: 'Upcoming system maintenance',
      message: 'The regular daily maintenance window is coming up. The update will last about 15 minutes. During that time you won’t be able to use the CMS.' + '<br>' +
        '<strong>Start:</strong> Today at about 3:30 p.m. ET\n' +
        '<br>' +
        '<strong>End:</strong> Around 15 minutes later',
      dismissible: true,
      severity: 'low'
    }
  },
  ...shared
};

Low.parameters.render = async args => {
  return await alert({
    ...Low.args, ...args
  });
}

export const Medium = {
  args: {
    attributes: new DrupalAttribute(),
    alert: {
      id: 1,
      label: 'Upcoming system maintenance',
      message: 'The regular daily maintenance window is coming up. The update will last about 15 minutes. During that time you won’t be able to use the CMS.' + '<br>' +
        '<strong>Start:</strong> Today at about 3:30 p.m. ET\n' +
        '<br>' +
        '<strong>End:</strong> Around 15 minutes later',
      dismissible: false,
      severity: 'medium'
    }
  },
  ...shared
};

Medium.parameters.render = async args => {
  return await alert({
    ...Medium.args, ...args
  });
}

export const High = {
  args: {
    attributes: new DrupalAttribute(),
    alert: {
      id: 1,
      label: 'System maintenance in progress',
      message: 'The maintenance will last about 15 minutes. Stay on this page to avoid losing changes. This message will disappear when it’s safe to continue working.',
      dismissible: false,
      severity: 'high'
    }
  },
  ...shared
};

High.parameters.render = async args => {
  return await alert({
    ...High.args, ...args
  });
}
