import block from './block.twig';
import DrupalAttribute from '../../.storybook/DrupalAttribute';
import './block.scss';
import './index.js';

export default { title: 'Components/Block', component: block };
export const Block = {
  args: {
    attributes: new DrupalAttribute(),
    title_attributes: new DrupalAttribute(),
    plugin_id: "Some plugin",
    title_prefix: "",
    title_suffix: "",
    label: "I'm a block!",
    content: "Lorem ipsum dolor sit amet.",
    configuration: {
      provider: "Some module"
    }
  },
  parameters: {},
  // Don't show drupal attributes in storybook controls
  argTypes: {
    attributes: {
      table: {
        disable: true
      },
    },
    title_attributes: {
      table: {
        disable: true
      }
    }
  },
};

Block.parameters.render = async args => {
  return await block({
    ...Block.args, ...args
  })
}
