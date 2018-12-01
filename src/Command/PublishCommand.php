<?php declare(strict_types=1);

namespace Rector\Prefixer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class PublishCommand extends Command
{
    /**
     * @var string
     */
    private $to;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(string $to, SymfonyStyle $symfonyStyle)
    {
        parent::__construct();
        $this->to = $to;
        $this->symfonyStyle = $symfonyStyle;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Publish new version to Github repository + tag it');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->symfonyStyle->error('"publish" command needs to be implemented');

        return ShellCode::ERROR;
    }
}
