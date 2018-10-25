<?php declare(strict_types=1);

namespace Rector\Prefixer\Worker;

use Rector\Prefixer\Contract\Worker\WorkerInterface;
use Rector\Prefixer\StringReplacer;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class PrefixConfigsWorker implements WorkerInterface
{
    /**
     * @var StringReplacer
     */
    private $stringReplacer;

    public function __construct(StringReplacer $stringReplacer)
    {
        $this->stringReplacer = $stringReplacer;
    }

    /**
     * Prefix config files with "RectorPrefixed\" in all level configs
     * but not in /config, since there is only Rector\ services and "class names" that are not prefixed
     */
    public function work(string $from, string $to): void
    {
        $configFiles = $this->findYamlAndNeonFiles($from, ['config']);

        $this->stringReplacer->replaceInsideFileInfos(
            $configFiles,
            '#((?:\w+\\\\{1,2})(?:\w+\\\\{0,2})+)#',
            'RectorPrefixed\\\\$1'
        );
    }

    public function getPriority(): int
    {
        return 200;
    }

    /**
     * @param string[] $exclude
     * @return SplFileInfo[]
     */
    private function findYamlAndNeonFiles(string $from, array $exclude = []): array
    {
        $finder = Finder::create()->name('#\.(yml|yaml|neon)$#')
            ->in($from)
            ->files();

        $finder->notName('#appveyor\.(yml|yaml)$#');

        foreach ($exclude as $singleExclude) {
            $finder = $finder->notPath($singleExclude);
        }

        return iterator_to_array($finder->getIterator());
    }
}
