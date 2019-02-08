<?php declare(strict_types=1);

namespace Rector\Prefixer\Command;

use Rector\Prefixer\Composer\ComposerJsonCleaner;
use Rector\Prefixer\Process\ProcessRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

/**
 * @inspiration https://github.com/phpstan/phpstan-compiler/blob/master/src/Console/CompileCommand.php
 */
final class CompileCommand extends Command
{
    /**
     * @var string
     */
    private $buildDirectory;

    /**
     * @var string
     */
    private $repositoryUrl;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ProcessRunner
     */
    private $processRunner;

    /**
     * @var ComposerJsonCleaner
     */
    private $composerJsonCleaner;

    public function __construct(
        string $buildDirectory,
        SymfonyStyle $symfonyStyle,
        string $repositoryUrl,
        Filesystem $filesystem,
        ProcessRunner $processRunner,
        ComposerJsonCleaner $composerJsonCleaner
    ) {
        parent::__construct();

        $this->buildDirectory = $buildDirectory;
        $this->symfonyStyle = $symfonyStyle;
        $this->repositoryUrl = $repositoryUrl;
        $this->filesystem = $filesystem;
        $this->processRunner = $processRunner;
        $this->composerJsonCleaner = $composerJsonCleaner;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Creates prefixed rector.phar');
        $this->addArgument('version', InputArgument::OPTIONAL, 'Version (tag or commit) to compile', 'master');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
//        $this->prepareSource($input);
        $this->buildPrefixedPhar();

        $this->symfonyStyle->success('Done!');

        return ShellCode::SUCCESS;
    }

    private function prepareSource(InputInterface $input): void
    {
        $this->prepareBuildDirectory();

        /** @var string $version */
        $version = $input->getArgument('version');

        $this->symfonyStyle->section(sprintf('Building prefixed in "%s" directory', $this->buildDirectory));

        $this->symfonyStyle->note(sprintf('Cloning "%s" with version "%s"', $this->buildDirectory, $version));
        $this->processRunner->run(['git', 'clone', $this->repositoryUrl, '.'], $this->buildDirectory);
        $this->processRunner->run(['git', 'checkout', '--force', $version], $this->buildDirectory);

        // remove tests files and other rubish
        $this->processRunner->run(
            ['composer', 'require', '--no-update', 'dg/composer-cleaner:^2.0'],
            $this->buildDirectory
        );

        // runs on composer update bellow - see https://github.com/dg/composer-cleaner
        $this->symfonyStyle->note('Preparing composer.json');
        $this->composerJsonCleaner->clean($this->buildDirectory . '/composer.json');

        // needed, /vendor is missing without this
        $this->processRunner->run(
            ['composer', 'update', '--no-dev', '--classmap-authoritative'],
            $this->buildDirectory
        );

        // remove conflicting package, not sure how it got here
        $this->processRunner->run(['rm', '-rf', 'vendor/symfony/polyfill-php70'], $this->buildDirectory);
    }

    private function buildPrefixedPhar(): void
    {
        $this->processRunner->run(['cp', 'box.json', $this->buildDirectory . '/box.json']);

        $this->symfonyStyle->note('Building prefixed rector.phar');
        $boxCommand = ['php', 'box.phar', 'compile', '--working-dir', $this->buildDirectory];
        if ($this->symfonyStyle->isDebug()) {
            $boxCommand[] = '--debug';
        }

        // fighting memory leak crashes: https://github.com/humbug/box/issues/355#issuecomment-460055089
        $boxCommand[] = '--no-parallel';

        $this->processRunner->run($boxCommand);
    }

    private function prepareBuildDirectory(): void
    {
        if ($this->filesystem->exists($this->buildDirectory)) {
            $this->filesystem->remove($this->buildDirectory);
        }

        $this->filesystem->mkdir($this->buildDirectory);
    }
}
