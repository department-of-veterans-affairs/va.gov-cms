// Add storybook-specific styles we don't need to include in the drupal bundle
import './storybook-styles.scss';

// Add token styles to make available for all pages. Import after to take precedence in the cascade.
import '../components/tokens/_variables.scss';

// Enable design system components in storybook (particularly for icons)
import { enableAllComponents } from '../components/design-system';

const sbRoot = document.getElementById('root');
enableAllComponents(sbRoot, {});

// Adds support for twing and storybook... issue with promises in Storybook HTML
// see: https://github.com/NightlyCommit/twing-loader/issues/33#issuecomment-889409418
export const loaders = [
  async ({
           args,
           parameters,
         }) => {
    const renderedStory = await parameters.render(args);
    return { renderedStory };
  },
];

export const render = (_, { loaded: { renderedStory } }) => renderedStory;

export const parameters = {
  actions: { argTypesRegex: "^on[A-Z].*" },
  controls: {
    matchers: {
      color: /(background|color)$/i,
      date: /Date$/,
    },
  },
  options: {
    storySort: {
      method: 'alphabetical',
      order: [
        'Introduction',
        'Getting started',
        'Changelog',
        'Design System Tokens',
        ['Colors', 'Spacing', 'Typography', 'Icons'], // nested under DST
        'Components',
        'Atoms',
        '*',
        'Experimental',
        'Deprecated'
      ],
    }
  }
}
