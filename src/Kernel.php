<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir().'/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }

    protected function configureContainer(ContainerConfigurator $containerConfigurator): void
    {
        $parametersConfigurator = $containerConfigurator->parameters();
        $parametersConfigurator->set('container.dumper.inline_class_loader', $this->debug);
        $parametersConfigurator->set('container.dumper.inline_factories', true);

        $confDir = $this->getProjectDir().'/config';
        $containerConfigurator->import($confDir.'/{packages}/*'.self::CONFIG_EXTS, 'glob');
        $containerConfigurator->import($confDir.'/{packages}/'.$this->environment.'/*'.self::CONFIG_EXTS, 'glob');
        $containerConfigurator->import($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
        $containerConfigurator->import($confDir.'/{services}_'.$this->environment.self::CONFIG_EXTS, 'glob');

    }

    private function getBundlesPath(): string
    {
        return $this->getProjectDir().'/config/bundles.php';
    }
}
