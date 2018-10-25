<?php declare(strict_types=1);

namespace Rector\Prefixer;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Symfony\Component\Finder\SplFileInfo;

final class StringReplacer
{
    /**
     * @param SplFileInfo|SplFileInfo[] $fileInfos
     */
    public function replaceInsideFileInfos($fileInfos, string $pattern, string $replacement = ''): void
    {
        $fileInfos = is_iterable($fileInfos) ? $fileInfos : [$fileInfos];

        foreach ($fileInfos as $fileInfo) {
            $content = $fileInfo->getContents();
            $replacedContent = Strings::replace($content, $pattern, $replacement);
            FileSystem::write($fileInfo->getRealPath(), $replacedContent);
        }
    }
}
