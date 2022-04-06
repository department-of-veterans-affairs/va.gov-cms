import icons from "./sb-icons.twig";
import './index.js';

export default { title: 'Design System Tokens' }

export const Icons = {
  args: {
    vars: [
      // List icon names to display here. Be sure to import them in icon/index.js and add them to the library!
      'ban',
      'bullhorn',
      'check',
      'chevron-down',
      'circle-info',
      'clone',
      'ellipsis-vertical',
      'magnifying-glass',
      'pencil',
      'plus',
      'trash',
      'warning',
      // 'triangle-exclamation'
    ]
  },
  parameters: {}
}

Icons.parameters.render = async args => {
  return await icons({...Icons.args, ...args});
}
