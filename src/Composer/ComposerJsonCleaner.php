<?php declare(strict_types=1);

namespace Rector\Prefixer\Composer;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;

final class ComposerJsonCleaner
{
    /**
     * @var string[]
     */
    private $sectionsToRemove = ['require-dev', 'autoload-dev', 'scripts'];

    public function clean(string $composerJson): void
    {
        $composerJsonContent = FileSystem::read($composerJson);
        $json = $this->readJson($composerJsonContent);

        // remove unused sections
        foreach ($this->sectionsToRemove as $sectionToRemove) {
            unset($json[$sectionToRemove]);
        }

        // force platform
        $json['config']['platform']['php'] = ltrim($json['require']['php'], '^');

        FileSystem::write($composerJson, $this->printJson($json));
    }

    /**
     * @return mixed[]
     */
    private function readJson(string $content): array
    {
        return Json::decode($content, Json::FORCE_ARRAY);
    }

    /**
     * @param mixed[] $json
     */
    private function printJson(array $json): string
    {
        return Json::encode($json, Json::PRETTY);
    }
}
