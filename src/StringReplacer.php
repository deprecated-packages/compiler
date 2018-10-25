<?php declare(strict_types=1);

namespace Rector\Prefixer;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class StringReplacer
{
    /**
     * @param Finder|SplFileInfo|SplFileInfo[] $source
     */
    public function replaceInsideSource($source, string $pattern, string $replacement = ''): void
    {
        if ($source instanceof Finder) {
            $fileInfos = iterator_to_array($source->getIterator());
        } elseif ($source instanceof SplFileInfo) {
            $fileInfos = [$source];
        } else {
            $fileInfos = $source;
        }

        foreach ($fileInfos as $fileInfo) {
            $content = $fileInfo->getContents();
            $replacedContent = Strings::replace($content, $pattern, $replacement);
            FileSystem::write($fileInfo->getRealPath(), $replacedContent);
        }
    }
}
