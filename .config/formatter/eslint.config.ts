import js from '@eslint/js';
import globals from 'globals';
import tseslint from '@typescript-eslint/eslint-plugin';
import tsParser from '@typescript-eslint/parser';
import pluginImport from 'eslint-plugin-import';
import pluginUnusedImports from 'eslint-plugin-unused-imports';

export default [
    {
        ignores: [
            "node_modules/**",
            "vendor/**",
            "storage/**",

            "public/res/assets/**",
            ".config/**",
        ],
    },

    js.configs.recommended,

    {
        files: ["public/res/js/**/*.ts"],
        languageOptions: {
            parser: tsParser,
            ecmaVersion: "latest",
            sourceType: "module",
            globals: {
                ...globals.browser,
            },
        },
        plugins: {
            '@typescript-eslint': tseslint,
            'import': pluginImport,
            'unused-imports': pluginUnusedImports,
        },
        rules: {
            "no-unused-vars": "off",
            "no-undef": "off",
            '@typescript-eslint/no-unused-vars': ['warn', { argsIgnorePattern: '^_', varsIgnorePattern: '^_' }],
            'unused-imports/no-unused-imports': 'warn',

            'import/order': [
                'warn',
                {
                    groups: ["builtin", "external", "internal", "parent", "sibling", "index"],
                    'newlines-between': 'always',
                    alphabetize: { order: 'asc', caseInsensitive: true  },
                }
            ],
        },
    },
];
