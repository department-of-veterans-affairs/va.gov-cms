import input from './sb-inputs.twig';
import './input.scss';
import './index.js';

export default { title: 'Atoms/Inputs', component: input };

export const Inputs = {
  args: {},
  parameters: {},
};

Inputs.parameters.render = async args => {
  return await input({
    ...Inputs.args, ...args
  });
}
