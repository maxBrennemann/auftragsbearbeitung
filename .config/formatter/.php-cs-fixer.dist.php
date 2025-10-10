<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$root = dirname(__DIR__ . "/../");

$finder = Finder::create()
    ->in([
        $root . "/src",
        $root . "/public",
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PHP84Migration' => true,
        '@PHP84Migration:risky' => true,

        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],
        'no_unused_imports' => true,

        'binary_operator_spaces' => ['default' => 'single_space'],
        'concat_space' => ['spacing' => 'one'],
        'trim_array_spaces' => true,
        'no_whitespace_in_blank_line' => true,
        'blank_line_before_statement' => [
            'statements' => ['return', 'if', 'for', 'foreach', 'while', 'switch', 'try'],
        ],

        'array_syntax' => ['syntax' => 'short'],
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments']],
        'single_quote' => false,
        'escape_implicit_backslashes' => true,

        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'single_line_after_imports' => true,
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
            ],
        ],

        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_summary' => false,
        'phpdoc_to_comment' => false,
        'no_superfluous_phpdoc_tags' => false,
        'phpdoc_scalar' => true,

        'declare_strict_types' => false,
        'native_function_invocation' => [
            'include' => ['@compiler_optimized'],
            'scope' => 'namespaced',
            'strict' => false,
        ],
    ])
    ->setFinder($finder)
    ->setCacheFile($root . '/.build/php-cs-fixer/.php-cs-fixer.cache')
    ->setUsingCache(true);
