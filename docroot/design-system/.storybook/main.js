const path = require('path');
const NodePolyfillPlugin = require('node-polyfill-webpack-plugin');
const TerserPlugin = require("terser-webpack-plugin");

/** @type { import('@storybook/html').StorybookConfig } */
const config = {
  features: {
    storyStoreV7: true
  },
  stories: [
    '../components/**/*.stories.js',
    '../stories/**/*.mdx'
  ],
  addons: [
    "@storybook/addon-links",
    "@storybook/addon-essentials",
    "@storybook/preset-scss"
  ],
  framework: {
    name: '@storybook/html-webpack5',
    options: {}
  },
  webpackFinal: async (config, {configType}) => {
    config.experiments = {
      ...(config.experiments ? config.experiments : {}),
      topLevelAwait: true
    };

    // make twing-loader compatible with webpack5
    config.plugins.push(
      new NodePolyfillPlugin({
        includeAliases: ['Buffer', 'crypto']
      })
    );

    // add twig support to storybook
    config.module.rules.push({
      test: /\.twig/,
      use: [{
        loader: 'twing-loader',
        options: {
          environmentModulePath: path.resolve(__dirname, 'twing-environment.js')
        }
      }],
      include: path.resolve(__dirname, '..', 'components')
    });
    config.optimization = {
      ...config.optimization,
      chunkIds: 'named',
      usedExports: false,
    }

    config.resolve.fallback = {
      ...config.resolve.fallback,
      crypto: require.resolve('crypto-browserify'),
      stream: require.resolve('stream-browserify')
    };

    return config
  },
  docs: {
    source: {
      format: 'dedent',
    },
    autodocs: true
  }
};
export default config;
