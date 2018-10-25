<?php declare(strict_types=1);

namespace Rector\Prefixer;

use Symfony\Component\Finder\Finder;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class PrefixFixer
{
    /**
     * @var string
     */
    private $from;

    /**
     * @var StringReplacer
     */
    private $stringReplacer;

    public function __construct(string $from, StringReplacer $stringReplacer)
    {
        $this->from = $from;
        $this->stringReplacer = $stringReplacer;
    }

    /**
     * Prefix config files with "RectorPrefixed\" in all level configs
     * but not in /config, since there is only Rector\ services and "class names" that are not prefixed
     */
    public function prefixServicesInConfigs(): void
    {
        $this->stringReplacer->replaceInsideSource(
            $this->findYamlAndNeonFiles(['config']),
            '#((?:\w+\\\\{1,2})(?:\w+\\\\{0,2})+)#',
            'RectorPrefixed\\\\$1'
        );
    }

    /**
     * Un-prefix Rector files, so it's public API, in configs etc.

     * -use RectorPrefixed\Rector\...
     * +use Rector\...
     */
    public function unprefixRectorCore(): void
    {
        $this->stringReplacer->replaceInsideSource($this->findCoreFiles(), '#RectorPrefixed\\\\Rector#', 'Rector');
    }

    /**
     * prefix container dump - see https://github.com/symfony/symfony/blob/226e2f3949c5843b67826aca4839c2c6b95743cf/src/Symfony/Component/DependencyInjection/Dumper/PhpDumper.php#L897
     * @todo specific the narrow file
     */
    public function unprefixContainerDump(): void
    {
        $this->stringReplacer->replaceInsideSource(
            $this->findCoreFiles(),
            '#use Symfony#',
            'use RectorPrefixed\\Symfony'
        );
    }

    /**
     * For cases like: https://github.com/rectorphp/rector-prefixed/blob/6b690e46e54830a944618d3a2bf50a7c2bd13939/src/Bridge/Symfony/NodeAnalyzer/ControllerMethodAnalyzer.php#L16
     */
    public function unprefixStrings(): void
    {
        $this->stringReplacer->replaceInsideSource($this->findCoreFilesWithoutVendor(), '#RectorPrefixed\\\\#');
    }

    /**
     * callable|Nette\\DI\\Statement|array:1
     * ↓
     * callable|RectorPrefixed\\Nette\\DI\\Statement|array:1
     */
    public function fixNetteStringValidator(): void
    {
        $fileInfo = new SmartFileInfo($this->from . '/vendor/nette/di/src/DI/Compiler.php');

        $this->stringReplacer->replaceInsideSource($fileInfo, '#|Nette\\\\DI#', 'RectorPrefixed\\\\Nette\\\\DI');
    }

    /**
     * Symfony Bridge => keep Symfony classes
     *
     * RectorPrefixed\App\\Kernel
     * ↓
     * App\Kernel
     *
     * RectorPrefixed\Symfony\Component\HttpKernel\Kernel
     * ↓
     * Symfony\Component\HttpKernel\Kernel
     */
    public function unprefixSymfonyBridgeClasses(): void
    {
        $fileInfo = new SmartFileInfo(
            $this->from . '/packages/Symfony/src/Bridge/DefaultAnalyzedSymfonyApplicationContainer.php'
        );
        $this->stringReplacer->replaceInsideSource($fileInfo, '#RectorPrefixed\\\\App\\\\Kernel#', 'App\\Kernel');

        $finder = Finder::create()
            ->in($this->from . '/packages/Symfony/src/Bridge')
            ->files();

        $this->stringReplacer->replaceInsideSource(
            $finder,
            '#RectorPrefixed\\Symfony\\Component#',
            'Symfony\\Component'
        );
    }

    private function findCoreFiles(): Finder
    {
        return Finder::create()->name('#\.(php|yml|yaml)$#')
            ->in($this->from)
            ->files();
    }

    private function findCoreFilesWithoutVendor(): Finder
    {
        return Finder::create()->name('#\.(php|yml|yaml)$#')
            ->in($this->from)
            ->files()
            ->notPath('vendor');
    }

    /**
     * @param string[] $exclude
     */
    private function findYamlAndNeonFiles(array $exclude = []): Finder
    {
        $finder = Finder::create()->name('#\.(yml|yaml|neon)$#')
            ->in($this->from)
            ->files();

        $finder->notName('#appveyor\.(yml|yaml)$#');

        foreach ($exclude as $singleExclude) {
            $finder = $finder->notPath($singleExclude);
        }

        return $finder;
    }
}
