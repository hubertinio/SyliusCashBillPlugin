<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\DependencyInjection;

use Sylius\Bundle\CoreBundle\DependencyInjection\PrependDoctrineMigrationsTrait;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class HubertinioSyliusCashBillExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    use PrependDoctrineMigrationsTrait;

    /** @psalm-suppress UnusedVariable */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);

        foreach (['app_id', 'app_secret', 'api_host'] as $param) {
            if (!isset($config[$param])) {
                continue;
            }

            $container->setParameter(
                'hubertinio_sylius_cash_bill.' . $param,
                $config[$param],
            );
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->prependDoctrineMigrations($container);
    }

    protected function getMigrationsNamespace(): string
    {
        return 'Hubertinio\SyliusCashBillPlugin\Migrations';
    }

    protected function getMigrationsDirectory(): string
    {
        return '@HubertinioSyliusCashBillPlugin/migrations';
    }

    protected function getNamespacesOfMigrationsExecutedBefore(): array
    {
        return [
            'Sylius\Bundle\CoreBundle\Migrations',
        ];
    }
}
