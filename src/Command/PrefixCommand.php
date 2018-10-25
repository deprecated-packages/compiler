<?php declare(strict_types=1);

namespace Rector\Prefixer\Command;

use Rector\Prefixer\Contract\Worker\WorkerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class PrefixCommand extends Command
{
    /**
     * @var array|WorkerInterface[]
     */
    private $workers;

    /**
     * @param WorkerInterface[] $workers
     */
    public function __construct(array $workers)
    {
        // @todo from to
        parent::__construct();
        $this->workers = $workers;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Creates prefixed Rector version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        dump($this->workers);
        dump('OK');
        die;
    }
}
