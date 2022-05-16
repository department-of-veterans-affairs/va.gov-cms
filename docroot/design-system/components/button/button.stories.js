import button from './sb-buttons.twig';
import './button.scss';
import './index.js';

export default { title: 'Atoms/Buttons', component: button };

export const Buttons = {
  args: {},
  parameters: {},
};

Buttons.parameters.render = async args => {
  return await button({
    ...Buttons.args, ...args
  });
}
