<?php declare(strict_types=1);

namespace Rector\Prefixer\Command;

use Rector\Prefixer\Contract\Worker\WorkerInterface;
use Rector\Prefixer\Exception\ConfigurationException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class PrefixCommand extends Command
{
    /**
     * @var string
     */
    private $to;

    /**
     * @var string
     */
    private $from;

    /**
     * @var array|WorkerInterface[]
     */
    private $workers = [];

    /**
     * @param WorkerInterface[] $workers
     */
    public function __construct(array $workers, string $from, string $to)
    {
        parent::__construct();

        $this->addWorkers($workers);

        $this->from = $from;
        $this->to = $to;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Creates prefixed Rector version');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        foreach ($this->workers as $worker) {
            $worker->work($this->from, $this->to);
        }
    }

    /**
     * @param WorkerInterface[] $workers
     */
    private function addWorkers(array $workers): void
    {
        foreach ($workers as $worker) {
            if (! isset($this->workers[$worker->getPriority()])) {
                $this->workers[$worker->getPriority()] = $worker;
                ksort($this->workers);
                continue;
            }

            throw new ConfigurationException(sprintf(
                'Conflicting worker priority %d already exists: %s and %s',
                $worker->getPriority(),
                get_class($worker),
                get_class($this->workers[$worker->getPriority()])
            ));
        }
    }
}
