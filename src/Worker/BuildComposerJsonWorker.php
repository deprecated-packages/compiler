<?php declare(strict_types=1);

namespace Rector\Prefixer\Worker;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Rector\Prefixer\Contract\Worker\WorkerInterface;
use Symfony\Component\Process\Process;

final class BuildComposerJsonWorker implements WorkerInterface
{
    /**
     * @var string[]
     */
    private $sectionsToRemove = ['authors', 'require-dev', 'autoload-dev', 'scripts', 'config'];

    public function work(string $from, string $to): void
    {
        $json = Json::decode(FileSystem::read($from . '/composer.json'), Json::FORCE_ARRAY);
        $json = $this->processComposerJson($json);
        FileSystem::write($to . '/composer.json', Json::encode($json, Json::PRETTY));

        # rebuild composer dump so the new prefixed namespaces are autoloaded
        # the new "RectorPrefixed\" is taken into account thanks to /vendor/composer/installed.json file,
        $process = new Process('composer dump-autoload --no-dev', $to);
        $process->run();
    }

    public function getPriority(): int
    {
        return 400;
    }

    /**
     * @param mixed[] $json
     * @return mixed[]
     */
    private function processComposerJson(array $json): array
    {
        // remove unused sections
        foreach ($this->sectionsToRemove as $sectionToRemove) {
            unset($json[$sectionToRemove]);
        }

        // change name
        $json['name'] = 'rector/rector-prefixed';

        // keep only requirements on PHP 7.1+
        $json['require'] = ['php' => '^7.1'];

        return $json;
    }
}
