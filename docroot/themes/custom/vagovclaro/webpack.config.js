const path = require("path");
const autoprefixer = require("autoprefixer");
const cssnano = require("cssnano");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const FileManagerPlugin = require("filemanager-webpack-plugin");
const WebpackShellPluginNext = require("webpack-shell-plugin-next");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");

module.exports = (env = {}, argv = {}) => {
  const isDevelopment = argv.mode === "development";
  const isProduction = argv.mode === "production";
  const isTest = argv.mode === "test";
  console.log(`Webpack mode: ${argv.mode}`);

  // Use for local development.
  if (isDevelopment) {
    return {
      devtool: "source-map",
      output: {
        // Webpack will create js files even though they are not used
        filename: "[name].bundle.js",
        sourceMapFilename: "[file].map",
        // Where the CSS is saved to
        path: path.resolve(__dirname, "./dist"),
        publicPath: "./dist",
      },

      entry: {
        // Will create css files in "dist" dir.
        styles: "./assets/scss/styles.scss",
        user_guides: "./assets/scss/user_guides.scss",
        whats_new: "./assets/scss/whats_new.scss",
        wysiwyg: "./assets/scss/wysiwyg.scss",
      },

      module: {
        rules: [
          {
            test: /\.(css|s[ac]ss)$/,
            use: [
              // Extract and save the final CSS.
              MiniCssExtractPlugin.loader,
              // Load the CSS, set url = false to prevent following urls to fonts and images.
              {
                loader: "css-loader",
                options: {
                  url: false,
                  importLoaders: 2,
                  sourceMap: true,
                },
              },
              // Add browser prefixes.
              {
                loader: "postcss-loader",
                options: {
                  postcssOptions: {
                    plugins: () => [
                      require("autoprefixer")({
                        browsers: [
                          ">1%",
                          "last 2 versions",
                          "Firefox ESR",
                          "not ie < 9",
                        ],
                      }),
                    ],
                  },
                  sourceMap: true,
                },
              },
              // Load the SCSS/SASS
              {
                loader: "sass-loader",
                options: {
                  // Prefer `dart-sass`
                  implementation: require("sass"),
                  sourceMap: true,
                  sassOptions: {
                    sourceMapIncludeSources: true,
                  },
                },
              },
            ],
          },
        ],
      },
      plugins: [
        // Define the filename pattern for CSS.
        new MiniCssExtractPlugin({
          filename: "[name].css",
          chunkFilename: "[id].css",
        }),
        // Remove the unneeded JS files added by webpack.
        new FileManagerPlugin({
          events: {
            onEnd: {
              delete: ["./dist/**.bundle.js", "./dist/**.bundle.js.map"],
            },
          },
        }),
        // Clear Drupal cache when Webpack is done.
        new WebpackShellPluginNext({
          onDoneWatch: {
            scripts: ["drush cr"],
            blocking: false,
            parallel: true,
          },
        }),
      ],
    };
  }

  // Used on Tugboat.
  if (isTest) {
    return {
      devtool: "source-map",
      output: {
        // Webpack will create js files even though they are not used
        filename: "[name].bundle.js",
        sourceMapFilename: "[file].map",
        // Where the CSS is saved to
        path: path.resolve(__dirname, "./dist"),
        publicPath: "./dist",
      },

      entry: {
        // Will create css files in "dist" dir.
        styles: "./assets/scss/styles.scss",
        user_guides: "./assets/scss/user_guides.scss",
        whats_new: "./assets/scss/whats_new.scss",
        wysiwyg: "./assets/scss/wysiwyg.scss",
      },

      module: {
        rules: [
          {
            test: /\.(css|s[ac]ss)$/,
            use: [
              // Extract and save the final CSS.
              MiniCssExtractPlugin.loader,
              // Load the CSS, set url = false to prevent following urls to fonts and images.
              {
                loader: "css-loader",
                options: {
                  url: false,
                  importLoaders: 2,
                  sourceMap: true,
                },
              },
              // Add browser prefixes.
              {
                loader: "postcss-loader",
                options: {
                  postcssOptions: {
                    plugins: () => [
                      require("autoprefixer")({
                        browsers: [
                          ">1%",
                          "last 2 versions",
                          "Firefox ESR",
                          "not ie < 9",
                        ],
                      }),
                    ],
                  },
                  sourceMap: true,
                },
              },
              // Load the SCSS/SASS
              {
                loader: "sass-loader",
                options: {
                  // Prefer `dart-sass`
                  implementation: require("sass"),
                  sourceMap: true,
                  sassOptions: {
                    sourceMapIncludeSources: true,
                  },
                },
              },
            ],
          },
        ],
      },
      plugins: [
        // Define the filename pattern for CSS.
        new MiniCssExtractPlugin({
          filename: "[name].css",
          chunkFilename: "[id].css",
        }),
        // Remove the unneeded JS files added by webpack.
        new FileManagerPlugin({
          events: {
            onEnd: {
              delete: ["./dist/**.bundle.js", "./dist/**.bundle.js.map"],
            },
          },
        }),
      ],
    };
  }

  // Used on production.
  if (isProduction) {
    return {
      output: {
        // Webpack will create js files even though they are not used
        filename: "[name].bundle.js",
        sourceMapFilename: "[file].map",
        // Where the CSS is saved to
        path: path.resolve(__dirname, "./dist"),
        publicPath: "./dist",
      },

      entry: {
        // Will create css files in "dist" dir.
        styles: "./assets/scss/styles.scss",
        user_guides: "./assets/scss/user_guides.scss",
        whats_new: "./assets/scss/whats_new.scss",
        wysiwyg: "./assets/scss/wysiwyg.scss",
      },

      module: {
        rules: [
          {
            test: /\.(css|s[ac]ss)$/,
            use: [
              // Extract and save the final CSS.
              MiniCssExtractPlugin.loader,
              // Load the CSS, set url = false to prevent following urls to fonts and images.
              {
                loader: "css-loader",
                options: {
                  url: false,
                  importLoaders: 2,
                  sourceMap: false,
                },
              },
              // Add browser prefixes.
              {
                loader: "postcss-loader",
                options: {
                  postcssOptions: {
                    plugins: () => [
                      require("autoprefixer")({
                        browsers: [
                          ">1%",
                          "last 2 versions",
                          "Firefox ESR",
                          "not ie < 9",
                        ],
                      }),
                    ],
                  },
                  sourceMap: false,
                },
              },
              // Load the SCSS/SASS
              {
                loader: "sass-loader",
                options: {
                  // Prefer `dart-sass`
                  implementation: require("sass"),
                  sourceMap: false,
                  sassOptions: {
                    sourceMapIncludeSources: false,
                  },
                },
              },
            ],
          },
        ],
      },
      optimization: {
        minimizer: [
          // For webpack@5 you can use the `...` syntax to extend existing minimizers (i.e. `terser-webpack-plugin`), uncomment the next line
          // `...`,
          new CssMinimizerPlugin(),
        ],
      },
      plugins: [
        // Define the filename pattern for CSS.
        new MiniCssExtractPlugin({
          filename: "[name].css",
          chunkFilename: "[id].css",
        }),
        // Remove the unneeded JS files added by webpack.
        new FileManagerPlugin({
          events: {
            onEnd: {
              delete: ["./dist/**.bundle.js", "./dist/**.bundle.js.map"],
            },
          },
        }),
      ],
    };
  }
};
