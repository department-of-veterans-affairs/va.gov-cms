const path = require('path');

module.exports = {
  stories: [
    "../components/**/*.stories.js",
    "../components/**/*.stories.mdx",
    "../stories/**/*.stories.mdx",
    "../stories/**/*.stories.@(js|jsx|ts|tsx)"
  ],
  addons: [
    "@storybook/addon-links",
    "@storybook/addon-essentials",
    "@storybook/preset-scss"
  ],
  framework: "@storybook/html",

  webpackFinal: async config => {
    // add twig support to storybook
    config.module.rules.push({
      test: /\.twig/,
      use: [
        {
          loader: 'twing-loader',
          options: {
            environmentModulePath: path.resolve(__dirname, 'twing-environment.js'),
          },
        },
      ],
      include: path.resolve(__dirname, '..', 'components'),
    });

    return config;
  },
};
