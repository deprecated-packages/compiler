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
    private $buildDirectory;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(string $buildDirectory, SymfonyStyle $symfonyStyle)
    {
        parent::__construct();
        $this->buildDirectory = $buildDirectory;
        $this->symfonyStyle = $symfonyStyle;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Publish new version to Github repository + tag it');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->symfonyStyle->error(sprintf(
            '"publish" of "%s" directory to "%s" repository command needs to be implemented',
            $this->buildDirectory,
            'https://github.com/rectorphp/rector-prefixed'
        ));

        // bash
//        cd build
//
        # init non-existing .git or fetch existing one
        //if [ ! -d .git ]; then
//    git init
//    # travis needs token to push
//    if [ $TRAVIS == true ]; then
//        git remote add -f origin https://$GITHUB_TOKEN@github.com/rectorphp/rector-prefixed.git
//    else
//        git remote add -f origin git@github.com:rectorphp/rector-prefixed.git
//    fi
//
        //else
//    git fetch origin
        //fi
//
        //git add .
//    git commit -m "rebuild prefixed Rector"
        //# needs to be force pushed to delete old files
        //git push origin master -f

        return ShellCode::ERROR;
    }
}
