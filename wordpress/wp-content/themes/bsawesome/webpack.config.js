const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const webpack = require('webpack');
const WebpackBar = require('webpackbar');

module.exports = (env, argv) => {
  const isDevelopment = argv.mode === 'development';

  return {
    entry: {
      bootstrap: path.resolve(__dirname, './assets/bootstrap.js'),
      global: path.resolve(__dirname, './assets/global.js'),
      home: path.resolve(__dirname, './assets/home.js'),
      category: path.resolve(__dirname, './assets/category.js'),
      product: path.resolve(__dirname, './assets/product.js'),
      configurator: path.resolve(__dirname, './assets/configurator.js'),
      cart: path.resolve(__dirname, './assets/cart.js'),
      checkout: path.resolve(__dirname, './assets/checkout.js'),
      account: path.resolve(__dirname, './assets/account.js'),
    },
    output: {
      filename: 'js/[name].js',
      path: path.resolve(__dirname, 'dist'),
      clean: true,
    },
    mode: isDevelopment ? 'development' : 'production',
    devtool: isDevelopment ? 'source-map' : false,
    watch: isDevelopment,
    watchOptions: {
      poll: 1000,
      ignored: /node_modules/,
    },
    devServer: {
      host: 'badspiegel.de',
      open: true,
      liveReload: true,
    },
    module: {
      rules: [
        {
          test: /\.(woff(2)?|eot|ttf|otf|svg)$/,
          type: 'asset/resource',
          generator: {
            filename: 'fonts/[name][ext][query]'
          }
        },
        {
          test: /\.js$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',
            options: {
              presets: [
                [
                  '@babel/preset-env',
                  {
                    targets: {
                      browsers: ['last 2 versions', 'ie >= 11'],
                    },
                    useBuiltIns: 'usage',
                    corejs: 3,
                  },
                ],
              ],
            },
          },
        },
        {
          test: /\.(sa|sc|c)ss$/,
          exclude: /node_modules/,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: 'css-loader',
              options: {
              },
            },
            {
              loader: 'postcss-loader',
              options: {
                postcssOptions: {
                  plugins: [
                    [
                      'autoprefixer',
                      {
                        overrideBrowserslist: ['last 2 versions', 'ie >= 11'],
                      },
                    ],
                  ],
                },
              },
            },
            {
              loader: 'sass-loader',
              options: {
                sourceMap: isDevelopment,
                sassOptions: {
                  includePaths: [path.resolve(__dirname, 'node_modules')],
                },
              },
            }
          ],
        },
      ],
    },
    optimization: {
      minimize: !isDevelopment,
      minimizer: [
        new TerserPlugin({
          parallel: true,
        }),
        new CssMinimizerPlugin(),
      ],
    },
    plugins: [
      new MiniCssExtractPlugin({
        filename: 'css/[name].css',
      }),
      new webpack.ProvidePlugin({
        $: 'jquery',
        jQuery: 'jquery',
      }),
      new WebpackBar({
        name: isDevelopment ? 'Development' : 'Production',
        color: 'green',
        basic: false,
      }),
    ],
    externals: {
      jquery: 'jQuery',
    },
    stats: isDevelopment
      ? { all: false, errors: true, assets: true, assetsSort: 'name', colors: true, excludeAssets: /\.(ttf|woff2?)$/i, }
      : { all: false, errors: true, assets: true, assetsSort: 'name', colors: true }
  };
};
