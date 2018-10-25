<?php declare(strict_types=1);

namespace Rector\Prefixer;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
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
        $this->stringReplacer->replaceInsideFileInfos(
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
        $this->stringReplacer->replaceInsideFileInfos($this->findCoreFiles(), '#RectorPrefixed\\\\Rector#', 'Rector');
    }

    /**
     * prefix container dump - see https://github.com/symfony/symfony/blob/226e2f3949c5843b67826aca4839c2c6b95743cf/src/Symfony/Component/DependencyInjection/Dumper/PhpDumper.php#L897
     * @todo specific the narrow file
     */
    public function unprefixContainerDump(): void
    {
        $this->stringReplacer->replaceInsideFileInfos(
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
        $this->stringReplacer->replaceInsideFileInfos($this->findCoreFilesWithoutVendor(), '#RectorPrefixed\\\\#');
    }

    /**
     * callable|Nette\\DI\\Statement|array:1
     * ↓
     * callable|RectorPrefixed\\Nette\\DI\\Statement|array:1
     */
    public function fixNetteStringValidator(): void
    {
        $fileInfo = new SmartFileInfo($this->from . '/vendor/nette/di/src/DI/Compiler.php');

        $this->stringReplacer->replaceInsideFileInfos($fileInfo, '#|Nette\\\\DI#', 'RectorPrefixed\\\\Nette\\\\DI');
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
        $this->stringReplacer->replaceInsideFileInfos($fileInfo, '#RectorPrefixed\\\\App\\\\Kernel#', 'App\\Kernel');

        $finder = Finder::create()
            ->in($this->from . '/packages/Symfony/src/Bridge')
            ->files();

        $fileInfos = iterator_to_array($finder->getIterator());

        $this->stringReplacer->replaceInsideFileInfos(
            $fileInfos,
            '#RectorPrefixed\\Symfony\\Component#',
            'Symfony\\Component'
        );
    }

    /**
     * @return SplFileInfo[]
     */
    private function findCoreFiles(): array
    {
        $finder = Finder::create()->name('#\.(php|yml|yaml)$#')
            ->in($this->from)
            ->files();

        return iterator_to_array($finder->getIterator());
    }

    /**
     * @return SplFileInfo[]
     */
    private function findCoreFilesWithoutVendor(): array
    {
        $finder = Finder::create()->name('#\.(php|yml|yaml)$#')
            ->in($this->from)
            ->files()
            ->notPath('vendor');

        return iterator_to_array($finder->getIterator());
    }

    /**
     * @param string[] $exclude
     * @return SplFileInfo[]
     */
    private function findYamlAndNeonFiles(array $exclude = []): array
    {
        $finder = Finder::create()->name('#\.(yml|yaml|neon)$#')
            ->in($this->from)
            ->files();

        $finder->notName('#appveyor\.(yml|yaml)$#');

        foreach ($exclude as $singleExclude) {
            $finder = $finder->notPath($singleExclude);
        }

        return iterator_to_array($finder->getIterator());
    }
}
