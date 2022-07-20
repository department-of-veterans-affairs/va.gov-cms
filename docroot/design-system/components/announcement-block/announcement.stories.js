import block from './announcement-block.twig';
import DrupalAttribute from '../../.storybook/DrupalAttribute';
import './announcement.scss';
import './index.js';

export default { title: 'Components/Announcement Block', component: block };
export const Example = {
  args: {
    attributes: new DrupalAttribute(),
    title_attributes: new DrupalAttribute(),
    plugin_id: "Some plugin",
    title_prefix: "",
    title_suffix: "",
    title: "New Look!",
    body: "We gave the CMS a new, modernized look to better support your work. You can continue to use workflows and features as before.",
    type: "information",
    configuration: {
      provider: "Some module"
    }
  },
  parameters: {},
  // Don't show drupal attributes in storybook controls
  argTypes: {
    type: {
      options: ['information', 'warning', 'emergency', 'new'],
      control: { type: 'select' }
    },
    attributes: {
      table: {
        disable: true
      },
    },
    title_attributes: {
      table: {
        disable: true
      }
    },
    configuration: {
      table: {
        disable: true
      }
    },
  },
};

Example.parameters.render = async args => {
  return await block({
    ...Example.args, ...args
  })
}
