<?php


namespace Amelaye\BioPHP\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class AmelayeBioPHPExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Domain/Resources/config'));
        $loader->load('services.xml');
    }

}