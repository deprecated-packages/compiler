<?php declare(strict_types=1);

namespace Rector\Prefixer\Worker;

use Nette\Utils\FileSystem;
use Rector\Prefixer\Contract\Worker\WorkerInterface;

final class CopyTemplatesWorker implements WorkerInterface
{
    /**
     * @var string
     */
    private $templates;

    public function __construct(string $templates)
    {
        $this->templates = $templates;
    }

    public function work(string $from, string $to): void
    {
        FileSystem::copy($this->templates, $to);
    }

    public function getPriority(): int
    {
        return 500;
    }
}
