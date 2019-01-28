<?php declare(strict_types=1);

namespace Rector\Prefixer\Process;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

final class ProcessRunner
{
    /**
     * @var float
     */
    private const TIMEOUT_IN_SECONDS = 10 * 60.0;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    /**
     * @param string[] $command
     */
    public function run(array $command, ?string $cwd = null): void
    {
        $process = new Process($command, $cwd, null, null, self::TIMEOUT_IN_SECONDS);
        $process->mustRun(function (string $type, string $buffer): void {
            $this->symfonyStyle->write($buffer);
        });
    }
}
