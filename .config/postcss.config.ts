import autoprefixer from 'autoprefixer';
import postcssUrl from 'postcss-url';
import tailwindcss from 'tailwindcss';
import tailwindConfig from './tailwind.config.ts';

export default {
    plugins: [
        tailwindcss(tailwindConfig),
        autoprefixer(),
        postcssUrl({
            url: asset => {
                const fontsToRewrite = ['Raleway-Regular.ttf', 'OpenSans-VariableFont.ttf', 'OpenSans-Italic-VariableFont.ttf'];
                const filename = asset.url.split('/').pop();
                if (fontsToRewrite.includes(filename)) {
                    return `/css/fonts/${filename}`;
                }
                return asset.url;
            }
        }),
    ],
};
