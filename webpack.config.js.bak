const glob = require('glob');
const path = require('path');

module.exports = {
    entry: glob.sync('./resources/assets/js/**/index.js').reduce((acc, path) => {
        const entry = path.replace('/index.js', '')
        acc[entry] = path
        return acc
    }, {}),
    output: {
        path: path.resolve(__dirname, 'public/dist/'),
        filename: './[name]/bundle.js',
        publicPath: ''
    },
    resolve: {
        extensions: ['.js', '.jsx']
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                loader: 'babel-loader',
                exclude: /node_modules/
            },
            {
                test: /\.css$/,
                exclude: /node_modules/,
                use: [
                    { loader: 'style-loader' },
                    { 
                        loader: 'css-loader',
                        options: {
                            modules: {
                                localIdentName: "[name]__[local]___[hash:base64:5]",
                            },														
                            sourceMap: true
                        }
                     },
                     { 
                         loader: 'postcss-loader',
                        options: {
                            postcssOptions: {
                                plugins: [
                                    [ 'autoprefixer', {}, ],
                                ],
                            },
                        }
                      }
                ]
            }
        ]
    },
    plugins: [
    ]
};