const path = require('path');
const glob = require('glob');
const CompressionPlugin = require("compression-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");

function getEntries(pattern, outputPrefix = '') {
    const files = glob.sync(pattern);
    const entries = {};
    for (const file of files) {
        const name = path.basename(file, '.js');
        entries[outputPrefix + name] = path.resolve(__dirname, file);
    }
    return entries;
}

const entries =  {
    ...getEntries('files/res/js/*.js'),
    global: [
        ...glob.sync('files/res/js/classes/*.js'),
    ],
    auftrag: [
        ...glob.sync('files/res/js/auftrag/*.js'),
    ],
    sticker: [
        ...glob.sync('files/res/js/sticker/*.js'),
    ],
}

module.exports = {
    mode: 'production',
    entry: entries,
    output: {
        filename: '[name].[contenthash].js',
        path: path.resolve(__dirname, 'files/res/js/min'),
        clean: true,
    },
    plugins: [
        new CompressionPlugin(),
    ],
    optimization: {
        minimize: true,
        concatenateModules: true,
        minimizer: [
            new TerserPlugin()
        ],
        /*splitChunks: {
            chunks: 'all',
            minSize: 0,
        },*/
        splitChunks: false,
        runtimeChunk: 'single',
    },
    resolve: {
        extensions: ['.js'],
        preferRelative: true
    },
};
