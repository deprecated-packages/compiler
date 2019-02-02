<?php declare(strict_types=1);

// @see https://github.com/humbug/php-scoper
// require_once __DIR__ . '/vendor/autoload.php';
// use Isolated\Symfony\Component\Finder\Finder;

return [
    'prefix' => null,
    'finders' => [],
    'patchers' => [
        // in phar __DIR__ is not current directory, but root one

        // remove Safe\function prefix, since it breaks autoload
        function (string $filePath, string $prefix, string $content): string {
            if (preg_match('#\.php$#', $filePath) === false) {
                return $content;
            }

            return str_replace('use function Safe\\', 'use function ', $content);
        },


        // correct paths inside phar, due to inner autoload.php path
        function (string $filePath, string $prefix, string $content): string {
            if (! in_array($filePath, ['bin/bootstrap.php', 'bin/container.php'])) {
                return $content;
            }

            return str_replace("__DIR__ . '/..", "'phar://rector.phar", $content);
        },

        // change vendor import "packages/NodeTypeResolver/config/config.yml" to phar path
        function (string $filePath, string $prefix, string $content): string {
            if ($filePath !== 'packages/NodeTypeResolver/config/config.yml') {
                return $content;
            }

            $before = '../../../vendor';
            $after = 'phar://rector.phar/vendor';

            return str_replace($before, $after, $content);
        },

        // Symfony scoping - @see https://github.com/symfony/symfony/blob/226e2f3949c5843b67826aca4839c2c6b95743cf/src/Symfony/Component/DependencyInjection/Dumper/PhpDumper.php#L897
        function (string $filePath, string $prefix, string $content): string {
            if ($filePath != 'src/Symfony/Component/DependencyInjection/Dumper/PhpDumper.php') {
                return $content;
            }

            return str_replace('use Symfony\\', sprintf('use %s\\Symfony\\', $prefix), $content);
        },

        // update rector cache dir path
        function (string $filePath, string $prefix, string $content): string {
            if ($filePath !== 'src/DependencyInjection/RectorKernel.php') {
                return $content;
            }

            $before = '_rector';
            $after = '_rector_phar_' . sha1(time());

            return str_replace($before, $after, $content);
        },

        // keep string for class names unprefixed, they work with app content
        // e.g. packages/Symfony/src/Bridge/NodeAnalyzer/ControllerMethodAnalyzer.php
        function (string $filePath, string $prefix, string $content): string {
            $before = sprintf('\'%s\\\\Symfony', $prefix);
            $after = '\'Symfony';

            $content = str_replace($before, $after, $content);

            $before = sprintf('\'%s\\\\App\\\\Kernel', $prefix);
            $after = '\'App\\\\Kernel';

            return str_replace($before, $after, $content);
        },

        // phpstan patchers - see https://github.com/phpstan/phpstan-compiler/blob/master/build/scoper.inc.php
        // Nette scoping - annotation is used to validate, so it needs to be prefixed
        function (string $filePath, string $prefix, string $content): string {
            if ($filePath !== 'vendor/nette/di/src/DI/Compiler.php') {
                return $content;
            }
            return str_replace('|Nette\\\\DI\\\\Statement', sprintf('|\\\\%s\\\\Nette\\\\DI\\\\Statement', $prefix), $content);
        },
        function (string $filePath, string $prefix, string $content): string {
            if ($filePath !== 'vendor/phpstan/phpstan/src/Testing/TestCase.php') {
                return $content;
            }
            return str_replace(sprintf('\\%s\\PHPUnit\\Framework\\TestCase', $prefix), '\\PHPUnit\\Framework\\TestCase', $content);
        },
        function (string $filePath, string $prefix, string $content): string {
            if ($filePath !== 'vendor/phpstan/phpstan/src/Testing/LevelsTestCase.php') {
                return $content;
            }

            return str_replace(
                [sprintf('\\%s\\PHPUnit\\Framework\\AssertionFailedError', $prefix), sprintf('\\%s\\PHPUnit\\Framework\\TestCase', $prefix)],
                ['\\PHPUnit\\Framework\\AssertionFailedError', '\\PHPUnit\\Framework\\TestCase'],
                $content
            );
        },
    ],
    'whitelist' => [
        'Rector\*',
        'PhpParser\*',
    ],
];
