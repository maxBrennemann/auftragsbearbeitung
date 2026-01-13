import postcssUrl from 'postcss-url';

export default {
    plugins: [
        postcssUrl({
            url: (asset: { url: string }) => {
                const fontsToRewrite = [
                    'Raleway-Regular.ttf',
                    'OpenSans-VariableFont.ttf',
                    'OpenSans-Italic-VariableFont.ttf'
                ];
                const filename = asset.url.split('/').pop();
                if (!filename) return asset.url;
                
                if (fontsToRewrite.includes(filename)) {
                    return `/fonts/${filename}`;
                }
                return asset.url;
            }
        }),
    ],
};
