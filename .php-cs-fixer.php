<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = Finder::create()
    ->exclude([
        'var',
        'vendor',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
    ->in(__DIR__)
;

return (new Config())
    ->setUnsupportedPhpVersionAllowed(true)
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setCacheFile(__DIR__ . '/var/.php-cs-fixer.cache')
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer'                            => true,
        '@PHPUnit100Migration:risky'             => true,
        '@Symfony:risky'                         => true,
        'binary_operator_spaces'                 => ['operators' => ['=>' => 'align_single_space_minimal']],
        'blank_line_after_namespace'             => true,
        'blank_line_after_opening_tag'           => true,
        'blank_line_before_statement'            => ['statements' => ['exit', 'return']],
        'blank_lines_before_namespace'           => true,
        'cast_spaces'                            => ['space' => 'none'],
        'class_attributes_separation'            => [
            'elements' => ['method' => 'one', 'property' => 'one'],
        ],
        'comment_to_phpdoc'                      => [
            'ignored_tags' => [
                'codeCoverageIgnoreEnd',
                'codeCoverageIgnoreStart',
                'phan-file-suppress',
                'phan-suppress-current-line',
                'phan-suppress-next-line',
                'phpstan-ignore-line',
                'phpstan-ignore-next-line',
                'todo',
            ],
        ],
        'concat_space'                           => ['spacing' => 'one'],
        'declare_equal_normalize'                => ['space' => 'none'],
        'doctrine_annotation_indentation'        => true,
        'doctrine_annotation_spaces'             => true,
        'elseif'                                 => true,
        'encoding'                               => true,
        'full_opening_tag'                       => true,
        'function_declaration'                   => true,
        'global_namespace_import'                => ['import_classes' => true, 'import_functions' => true],
        'increment_style'                        => ['style' => 'post'],
        'lowercase_cast'                         => true,
        'lowercase_keywords'                     => true,
        'lowercase_static_reference'             => true,
        'mb_str_functions'                       => false,
        'native_constant_invocation'             => false,
        'native_function_invocation'             => false,
        'no_blank_lines_after_class_opening'     => true,
        'no_closing_tag'                         => true,
        'no_leading_import_slash'                => true,
        'no_spaces_after_function_name'          => true,
        'no_trailing_whitespace'                 => true,
        'no_trailing_whitespace_in_comment'      => true,
        'no_unset_cast'                          => false,
        'no_unused_imports'                      => true,
        'no_useless_return'                      => true,
        'no_whitespace_in_blank_line'            => true,
        'ordered_imports'                        => [
            'sort_algorithm' => 'alpha',
            'imports_order'  => ['const', 'class', 'function'],
        ],
        'ordered_types'                          => ['null_adjustment' => 'always_first', 'sort_algorithm' => 'none'],
        'php_unit_internal_class'                => ['types' => []],
        'php_unit_test_class_requires_covers'    => false,
        'phpdoc_summary'                         => false,
        'phpdoc_to_comment'                      => ['ignored_tags' => ['var', 'see']],
        'phpdoc_types'                           => true,
        'phpdoc_types_order'                     => ['null_adjustment' => 'always_first', 'sort_algorithm' => 'none'],
        'self_static_accessor'                   => true,
        'short_scalar_cast'                      => true,
        'simplified_null_return'                 => false,
        'single_blank_line_at_eof'               => true,
        'single_class_element_per_statement'     => true,
        'single_import_per_statement'            => true,
        'single_line_after_imports'              => true,
        'single_line_empty_body'                 => false,
        'single_quote'                           => true,
        'spaces_inside_parentheses'              => ['space' => 'none'],
        'static_lambda'                          => true,
        'switch_case_semicolon_to_colon'         => true,
        'switch_case_space'                      => true,
        'ternary_operator_spaces'                => true,
        'trailing_comma_in_multiline'            => ['elements' => ['arrays', 'arguments', 'parameters']],
        'use_arrow_functions'                    => true,
        'void_return'                            => true,
        'phpdoc_array_type'                      => true,
        'attribute_empty_parentheses'            => true,
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'new_line_for_chained_calls',
        ],
        'ordered_attributes'                     => true,
        'ordered_interfaces'                     => true,
    ])
    ->setFinder($finder)
;
