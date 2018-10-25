<?php declare(strict_types=1);

namespace Rector\Prefixer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class PublishCommand extends Command
{
    /**
     * @var string
     */
    private $to;

    public function __construct(string $to)
    {
        parent::__construct();
        $this->to = $to;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Publish new version to Github repository + tag it');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        dump($this->to);
        // @todo
    }
}
