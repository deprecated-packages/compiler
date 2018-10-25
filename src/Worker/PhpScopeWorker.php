<?php declare(strict_types=1);

namespace Rector\Prefixer\Worker;

use Rector\Prefixer\Contract\Worker\WorkerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * @see https://github.com/humbug/php-scoper
 */
final class PhpScopeWorker implements WorkerInterface
{
    /**
     * @var string
     */
    private $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function work(string $from, string $to): void
    {
        return;

        $process = new Process(
            sprintf('vendor/bin/php-scoper add-prefix --no-interaction --output-dir=%s', $to),
            null,
            [
                'FROM' => $from,
                'PREFIX' => $this->prefix,
            ],
            null,
            300.0
        );

        $process->start();

        while ($process->isRunning()) {
            echo $process->getIncrementalOutput();
        }

        if ($process->isSuccessful()) {
            echo 'OK';
        } else {
            throw new ProcessFailedException($process);
        }
    }

    public function getPriority(): int
    {
        return 100;
    }
}
