<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}

$headerComment = <<<COMMENT
This file is part of the "Image Gallery" extension for TYPO3 CMS.

For the full copyright and license information, please read the
LICENSE.txt file that was distributed with this source code.

Dev <dev@Leuchtfeuer.com>, Leuchtfeuer Digital Marketing
COMMENT;


$finder = PhpCsFixer\Finder::create()
    ->name('*.php')
    ->in(__DIR__)
    ->exclude('Configuration')
    ->exclude('Libraries')
    ->exclude('Resources')
    ->notName('ext_emconf.php')
    ->notName('ext_tables.php')
    ->notName('ext_localconf.php');

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@DoctrineAnnotation' => true,
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_opening_tag' => true,
        'braces' => ['allow_single_line_closure' => true],
        'cast_spaces' => ['space' => 'none'],
        'compact_nullable_typehint' => true,
        'concat_space' => ['spacing' => 'one'],
        'declare_equal_normalize' => ['space' => 'none'],
        'dir_constant' => true,
        'function_typehint_space' => true,
        'hash_to_slash_comment' => true,
        'header_comment' => [
            'header' => $headerComment,
            'comment_type' => 'comment',
            'separate' => 'both',
            'location' => 'after_declare_strict'
        ],
        'lowercase_cast' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'modernize_types_casting' => true,
        'native_function_casing' => true,
        'new_with_braces' => true,
        'no_alias_functions' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_extra_consecutive_blank_lines' => true,
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_null_property_initialization' => true,
        'no_short_bool_cast' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_superfluous_elseif' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_whitespace_in_blank_line' => true,
        'ordered_imports' => true,
        'php_unit_construct' => ['assertEquals', 'assertSame', 'assertNotEquals', 'assertNotSame'],
        'php_unit_mock_short_will_return' => true,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
        'phpdoc_no_access' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_scalar' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        'return_type_declaration' => ['space_before' => 'none'],
        'single_quote' => true,
        'single_trait_insert_per_statement' => true,
        'whitespace_after_comma_in_array' => true,
    ])
    ->setFinder($finder);
