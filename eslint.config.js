import js from '@eslint/js';
import pluginImport from 'eslint-plugin-import';
import pluginUnusedImports from 'eslint-plugin-unused-imports';

export default [
    js.configs.recommended,
    {
        plugins: {
            'unused-imports': pluginUnusedImports,
            'import': pluginImport,
        },
        rules: {
            'no-unused-vars': 'off',
            'unused-imports/no-unused-imports': 'error',
            'import/order': ['warn', {
                groups: ['builtin', 'external', 'internal', 'parent', 'sibling', 'index'],
                'newlines-between': 'always',
                alphabetize: { order: 'asc', caseInsensitive: true }
            }]
        }
    }
];
