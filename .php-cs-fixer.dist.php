<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/app')
    ->name('*.php')
    ->exclude('vendor');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR1' => true,
        '@PSR2' => true,
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => ['default' => 'align_single_space'],
        'single_quote' => true,
        'no_unused_imports' => true,
        'single_blank_line_at_eof' => true,
        'visibility_required' => ['elements' => ['const', 'method', 'property']],
        'no_trailing_whitespace' => true,
        'no_useless_else' => true,
        'phpdoc_align' => ['align' => 'vertical'],
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
    ])
    ->setFinder($finder);
