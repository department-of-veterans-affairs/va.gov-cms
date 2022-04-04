import icons from "./sb-icons.twig";
import './index.js';

export default { title: 'Components/Icons' }

export const Icons = {
  args: {
    vars: [
      // List icon names to display here. Be sure to import them in icon/index.js
      'chevron-down',
      'magnifying-glass',
      'triangle-exclamation'
    ]
  },
  parameters: {}
}

Icons.parameters.render = async args => {
  return await icons({...Icons.args, ...args});
}
