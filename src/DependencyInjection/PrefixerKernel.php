<?php declare(strict_types=1);

namespace Rector\Prefixer\DependencyInjection;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\ConfigurableCollectorCompilerPass;
use Symplify\PackageBuilder\HttpKernel\SimpleKernelTrait;

final class PrefixerKernel extends Kernel
{
    use SimpleKernelTrait;

    /**
     * @var string|null
     */
    private $configFile;

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../../config/config.yaml');

        if ($this->configFile) {
            $loader->load($this->configFile);
        }
    }

    public function bootWithConfig(string $config): void
    {
        $this->configFile = $config;
        $this->boot();
    }

    protected function build(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addCompilerPass(new ConfigurableCollectorCompilerPass());
        $containerBuilder->addCompilerPass(new AutowireArrayParameterCompilerPass());
    }
}
