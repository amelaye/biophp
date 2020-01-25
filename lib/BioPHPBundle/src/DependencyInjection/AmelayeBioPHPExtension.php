<?php
/**
 * Dependency injections for the bundle
 * Freely inspired by BioPHP's project biophp.org
 * Created 19 january 2020
 * Last modified 19 january 2020
 */
namespace Amelaye\BioPHP\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * Class AmelayeBioPHPExtension
 * @package Amelaye\BioPHP\DependencyInjection
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class AmelayeBioPHPExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Loads the BioPHP Services
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Domain/Database/Resources/config'));
        $loader->load('services.xml');

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Api/Resources/config'));
        $loader->load('services.xml');

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Domain/Sequence/Resources/config'));
        $loader->load('services.xml');
    }

    /**
     * Loads the BioPHP Doctrine entities
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('doctrine', ['orm' => true]);
        $container->loadFromExtension('doctrine', [
            'orm' => [
                'auto_mapping' => true,
                'naming_strategy' => "doctrine.orm.naming_strategy.underscore_number_aware",
                'auto_generate_proxy_classes' => true,
                'mappings' => [
                    'Amelaye\BioPHP\Domain\Database\Entity' => [
                        'type'      => 'annotation',
                        'dir'       => '%kernel.project_dir%/lib/BioPHPBundle/src/Domain/Database/Entity',
                        'is_bundle' => false,
                        'prefix'    => 'Amelaye\BioPHP\Domain\Database\Entity',
                        'alias'     => 'BioPHPDb',
                    ],
                    'Amelaye\BioPHP\Domain\Sequence\Entity' => [
                        'type'      => 'annotation',
                        'dir'       => '%kernel.project_dir%/lib/BioPHPBundle/src/Domain/Sequence/Entity',
                        'is_bundle' => false,
                        'prefix'    => 'Amelaye\BioPHP\Domain\Sequence\Entity',
                        'alias'     => 'BioPHPSeq',
                    ],
                ],
            ],
        ]);
    }

}