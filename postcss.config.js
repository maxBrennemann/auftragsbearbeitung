import tailwindcss from 'tailwindcss';
import autoprefixer from 'autoprefixer';
import postcssUrl from 'postcss-url';

export default {
    plugins: [
        tailwindcss(),
        autoprefixer(),
        postcssUrl({
            url: asset => {
                const fontsToRewrite = ['Raleway-Regular.ttf', 'OpenSans-VariableFont.ttf', 'OpenSans-Italic-VariableFont.ttf'];
                const filename = asset.url.split('/').pop();
                if (fontsToRewrite.includes(filename)) {
                    return `/css/fonts/${filename}`;
                    //return asset.url.replace(filename, `${filename}`);
                }
                return asset.url;
            }
        }),
    ],
};
