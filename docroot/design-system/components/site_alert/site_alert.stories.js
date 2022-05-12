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
      message: 'The regular daily maintenance window is coming up. The update will last about 15 minutes. During that time you won’t be able to use the CMS.',
      dismissible: 'yes',
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
      message: 'The regular daily maintenance window is coming up. The update will last about 15 minutes. During that time you won’t be able to use the CMS.',
      dismissible: 'no',
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
      label: 'Upcoming system maintenance',
      message: 'The regular daily maintenance window is coming up. The update will last about 15 minutes. During that time you won’t be able to use the CMS.',
      dismissible: 'no',
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
