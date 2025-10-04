declare module 'postcss-url' {
    import { PluginCreator } from 'postcss';
    interface PostCSSUrlOptions {
        url?: (asset: { url: string }) => string;
    }
    const creator: (options?: PostCSSUrlOptions) => PluginCreator<any>;
    export default creator;
}
