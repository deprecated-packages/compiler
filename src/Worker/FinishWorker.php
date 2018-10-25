<?php declare(strict_types=1);

namespace Rector\Prefixer\Worker;

use Rector\Prefixer\Contract\Worker\WorkerInterface;
use Symfony\Component\Process\Process;

final class FinishWorker implements WorkerInterface
{
    public function work(string $from, string $to): void
    {
        $this->makeBinExecutable($to);
        $this->clearKernelCache($to);
    }

    public function getPriority(): int
    {
        return 600;
    }

    private function makeBinExecutable(string $to): void
    {
        $process = new Process(sprintf('chmod +x %s/bin/rector', $to));
        $process->run();
    }

    private function clearKernelCache(string $to): void
    {
        # clear kernel cache to make use of this new one,

//        (find $BUILD_DESTINATION -type f | xargs sed -i 's#_rector_cache#_prefixed_rector_cache#g')
//rm -rf /tmp/_prefixed_rector_cache
    }
}
