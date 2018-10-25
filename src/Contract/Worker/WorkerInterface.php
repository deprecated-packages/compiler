<?php declare(strict_types=1);

namespace Rector\Prefixer\Contract\Worker;

interface WorkerInterface
{
    public function work(string $from, string $to): void;

    public function getPriority(): int;
}
