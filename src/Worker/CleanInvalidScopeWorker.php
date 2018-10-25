<?php declare(strict_types=1);

namespace Rector\Prefixer\Worker;

use Rector\Prefixer\Contract\Worker\WorkerInterface;
use Rector\Prefixer\PrefixFixer;

final class CleanInvalidScopeWorker implements WorkerInterface
{
    /**
     * @var PrefixFixer
     */
    private $prefixFixer;

    public function __construct(PrefixFixer $prefixFixer)
    {
        $this->prefixFixer = $prefixFixer;
    }

    public function work(string $from, string $to): void
    {
        $this->prefixFixer->unprefixRectorCore();
        $this->prefixFixer->unprefixContainerDump();
        $this->prefixFixer->unprefixStrings();
        $this->prefixFixer->fixNetteStringValidator();
        $this->prefixFixer->unprefixSymfonyBridgeClasses();
    }

    public function getPriority(): int
    {
        return 300;
    }
}
