<?php declare(strict_types=1);

// @see https://github.com/humbug/php-scoper

require_once __DIR__ . '/vendor/autoload.php';

use Isolated\Symfony\Component\Finder\Finder;

$from = getenv('FROM');

return [
    'prefix' => getenv('PREFIX'),
    'finders' => [
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            // ↓ this is regex!
            ->notName('#LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock|.*\\.sh#')
            // depends on PHPUnit that is not part of the prefixed package
            ->notName('#AbstractRectorTestCase\\.php#')
            ->in($from .'/bin')
            ->in($from .'/config')
            ->in($from .'/packages')
            ->in($from .'/src')
            ->in($from .'/vendor')
            ->exclude([
                'docs',
                'Tests',
                'tests',
                'Test',
                'test',
                'humbug/php-scoper',
                'tracy/tracy',
            ])
        ,
        // to make "composer dump" work
        Finder::create()->append([
            'composer.json',
            // Fixes non-standard php-cs-fixer tests in /src
            $from . '/vendor/friendsofphp/php-cs-fixer/tests/TestCase.php',
            // Files dependencies in prod vendor
            $from . '/vendor/humbug/php-scoper/src/functions.php',
            $from . '/vendor/tracy/tracy/src/shortcuts.php',
            // dependency for "composer dump"
            $from . '/vendor/composer/installed.json'
        ]),
        // 'whitelist' - be careful, this adds aliases to end of each whitelisted class

        // Fixes non-standard php-cs-fixer tests in /src:
        // "Could not scan for classes inside "../vendor/friendsofphp/php-cs-fixer/tests/Test/AbstractFixerTestCase.php" which does not appear to be a file nor a folder"
        Finder::create()
            ->files()
            ->in($from . '/vendor/friendsofphp/php-cs-fixer/tests/Test')
    ],
];
