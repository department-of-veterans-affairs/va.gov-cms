import path from 'path';
import CopyPlugin from "copy-webpack-plugin";
import { Buffer } from 'buffer';

module.exports = {
  context: path.resolve(__dirname, 'src'),
  entry: './index.js',
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: 'bundle.min.js',
  },
  resolve: {
    extensions: ['.jsx', '.js', '.json', '.mjs'],
    modules: [
      path.resolve(__dirname, 'node_modules'),
      'node_modules',
    ],
  },
  module: {
    rules: [
      {
        test: /\.m?js$/,
        exclude: /node_modules/,
        use: ['babel-loader'],
      },
    ],
  },
  plugins: [
    new CopyPlugin({
      patterns: [
        { from: path.resolve(__dirname, 'src/container.css') },
        {
          from: path.resolve(__dirname, 'node_modules/graphiql/graphiql.css'),
          transform: {
            transformer(content, absoluteFrom) {
              // Add no style checking to the top
              return Buffer.concat([
                Buffer.from('/* stylelint-disable */ \n'),
                content
              ]);
            }
          }
        },
      ]
    })
  ],
  externals: {
    jquery: 'jQuery',
    drupal: 'Drupal',
  },
};
