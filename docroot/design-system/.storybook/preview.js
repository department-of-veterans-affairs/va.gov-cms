// Add token styles to make available for all pages
import '../components/tokens/_variables.css';

// Add storybook-specific styles we don't need to include in the drupal bundle
import './storybook-styles.scss';

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
      order: ['Introduction', 'Getting started', 'Changelog', 'Tokens', 'Components', '*'],
    }
  }
}
