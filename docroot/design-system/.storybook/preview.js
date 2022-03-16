// bring default claro variables into storybook so we can override properly
import '../../core/themes/claro/css/base/elements.css';

// // bring token styles to make available for all pages
import '../components/tokens/_variables.scss';

// Adds support for twing and storybook... issue with promises in Storybook HTML
// see: https://github.com/NightlyCommit/twing-loader/issues/33#issuecomment-889409418
export const loaders = [
  async ({ args, originalStoryFn }) => {
    if (originalStoryFn.render) {
      const component = await originalStoryFn.render(args);
      return { component };
    }
  }
];

export const parameters = {
  actions: { argTypesRegex: "^on[A-Z].*" },
  controls: {
    matchers: {
      color: /(background|color)$/i,
      date: /Date$/,
    },
  },
}
