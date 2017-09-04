var webpack = require('webpack');
var path = require('path');
var ExtractTextPlugin = require('extract-text-webpack-plugin');

module.exports = {
    entry: ['./app/app.js', './style/main.scss'],
    devtool: 'source-map',
    module: {
        loaders: [
            {
                test: /\.js?$/,
                loader: 'babel-loader',
                exclude: /node_modules/
            },
            {
                test: /\.scss$/,
                loader: ExtractTextPlugin.extract('css-loader?sourceMap!sass-loader?sourceMap')
            }
        ]
    },
    output: {
        filename: "public/js/bundle.js"
    },
    plugins: [
        new ExtractTextPlugin({
            filename: 'public/css/styles.css',
            allChunks: true
        })
    ]
};