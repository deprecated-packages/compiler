<?php declare(strict_types=1);

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

require __DIR__ . '/../../vendor/autoload.php';

$buildDestination = getenv('BUILD_DESTINATION') ?: __DIR__ . '/../../../rector-prefixed-build';

$prefixFixer = new PrefixFixer($buildDestination);
$prefixFixer->prefixServicesInConfigs();
$prefixFixer->unprefixRectorCore();
$prefixFixer->unprefixContainerDump();
$prefixFixer->unprefixStrings();
$prefixFixer->fixNetteStringValidator();
$prefixFixer->unprefixSymfonyBridgeClasses();


final class PrefixFixer
{
    /**
     * @var string
     */
    private $buildDirectory;

    public function __construct(string $buildDirectory)
    {
        $this->buildDirectory = $buildDirectory;
    }

    /**
     * Prefix config files with "RectorPrefixed\" in all level configs
     * but not in /config, since there is only Rector\ services and "class names" that are not prefixed
     */
    public function prefixServicesInConfigs(): void
    {
        $this->replaceInsideFileInfos(
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
        $this->replaceInsideFileInfos($this->findCoreFiles(), '#RectorPrefixed\\\\Rector#', 'Rector');
    }

    /**
     * prefix container dump - see https://github.com/symfony/symfony/blob/226e2f3949c5843b67826aca4839c2c6b95743cf/src/Symfony/Component/DependencyInjection/Dumper/PhpDumper.php#L897
     * @todo specific the narrow file
     */
    public function unprefixContainerDump(): void
    {
        $this->replaceInsideFileInfos($this->findCoreFiles(), '#use Symfony#', 'use RectorPrefixed\\Symfony');
    }

    /**
     * For cases like: https://github.com/rectorphp/rector-prefixed/blob/6b690e46e54830a944618d3a2bf50a7c2bd13939/src/Bridge/Symfony/NodeAnalyzer/ControllerMethodAnalyzer.php#L16
     */
    public function unprefixStrings(): void
    {
        $this->replaceInsideFileInfos($this->findCoreFilesWithoutVendor(), '#RectorPrefixed\\\\#');
    }

    /**
     * callable|Nette\\DI\\Statement|array:1
     * ↓
     * callable|RectorPrefixed\\Nette\\DI\\Statement|array:1
     */
    public function fixNetteStringValidator(): void
    {
        $fileInfo = new SmartFileInfo($this->buildDirectory . '/vendor/nette/di/src/DI/Compiler.php');

        $this->replaceInsideFileInfos($fileInfo, '#|Nette\\\\DI#', 'RectorPrefixed\\\\Nette\\\\DI');
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
            $this->buildDirectory . '/packages/Symfony/src/Bridge/DefaultAnalyzedSymfonyApplicationContainer.php'
        );
        $this->replaceInsideFileInfos($fileInfo, '#RectorPrefixed\\\\App\\\\Kernel#', 'App\\Kernel');

        $fileInfos = Finder::create()
            ->in($this->buildDirectory . '/packages/Symfony/src/Bridge')
            ->files()
            ->getIterator();

        $this->replaceInsideFileInfos($fileInfos, '#RectorPrefixed\\Symfony\\Component#', 'Symfony\\Component');
    }

    /**
     * @return SplFileInfo[]
     */
    private function findCoreFiles(): Iterator
    {
        return Finder::create()->name('#\.(php|yml|yaml)$#')
            ->in($this->buildDirectory)
            ->files()
            ->getIterator();
    }

    /**
     * @return SplFileInfo[]
     */
    private function findCoreFilesWithoutVendor(): Iterator
    {
        return Finder::create()->name('#\.(php|yml|yaml)$#')
            ->in($this->buildDirectory)
            ->files()
            ->notPath('vendor')
            ->getIterator();
    }

    /**
     * @param string[] $exclude
     * @return SplFileInfo[]
     */
    private function findYamlAndNeonFiles(array $exclude = []): Iterator
    {
        $finder = Finder::create()->name('#\.(yml|yaml|neon)$#')
            ->in($this->buildDirectory)
            ->files();

        $finder->notName('#appveyor\.(yml|yaml)$#');

        foreach ($exclude as $singleExclude) {
            $finder = $finder->notPath($singleExclude);
        }

        return $finder->getIterator();
    }

    /**
     * @param SplFileInfo|SplFileInfo[] $fileInfos
     */
    private function replaceInsideFileInfos($fileInfos, string $pattern, string $replacement = ''): void
    {
        $fileInfos = is_iterable($fileInfos) ?: [$fileInfos];

        foreach ($fileInfos as $fileInfo) {
            $content = $fileInfo->getContents();
            $replacedContent = Strings::replace($content, $pattern, $replacement);
            FileSystem::write($fileInfo->getRealPath(), $replacedContent);
        }
    }
}
