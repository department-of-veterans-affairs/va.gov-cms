import colors from './colors.twig';
import spacing from './spacing.twig';
import typography from './typography.twig';

export default { title: 'Tokens' }

export const Colors = {
  args: {
    colors: [
      {
        'name': 'foo',
        'value': 'bar'
      },
      {
        'name': 'bar',
        'value': 'baz'
      },
    ]
  },
  parameters: {},
}

Colors.parameters.render = async args => {
  return await colors({...Colors.args, ...args});
}

export const Spacing = {
  args: {

  },
  parameters: {}
}

Spacing.parameters.render = async args => {
  return await spacing({...Spacing.args, ...args});
}

export const Typography = {
  args: {

  },
  parameters: {}
}

Typography.parameters.render = async args => {
  return await spacing({...Typography.args, ...args});
}
