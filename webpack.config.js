const path = require('path');
const VueLoaderPlugin = require('vue-loader/lib/plugin');


module.exports = (env, argv) => {
    const entry = {};
    if (argv.mode === 'production') {
        entry['shop.min'] = './src/frontend/index.js';
        //entry.polyfills = './src/polyfills.js';
    } else {
        entry.shop = './src/frontend/index.js';
        entry.manager = './src/manager/index.js';
    }

    return {
        //devtool: argv.mode === 'production' ? false : 'source-map',
        //devtool:  argv.mode === 'development' ? 'eval-cheap-module-source-map' : false,
        entry,
        output: {
            path: path.resolve(__dirname, './assets/components/effectshop'),
        },
        plugins: [
            new VueLoaderPlugin()
        ],
        module: {
            rules: [
                {
                    test: /\.vue$/,
                    loader: 'vue-loader'
                },
                {
                    test: /\.css$/i,
                    use: ['style-loader', 'css-loader'],
                },
                {
                    test: /\.m?js$/,
                    exclude: /(node_modules|manager)/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: ['@babel/preset-env']
                        }
                    }
                },
            ],
        },
    }
}