<?php declare(strict_types=1);

use Rector\Prefixer\DependencyInjection\PrefixerKernel;

$possibleConfigPath = __DIR__ . '/../prefixer.yaml';

if (! file_exists($possibleConfigPath)) {
    throw new LogicException(sprintf('Unable to find "%s" config, add it.', $possibleConfigPath));
}

$kernel = new PrefixerKernel();
$kernel->bootWithConfig($possibleConfigPath);

return $kernel->getContainer();
