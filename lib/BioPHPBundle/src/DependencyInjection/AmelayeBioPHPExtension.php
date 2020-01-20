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

/**
 * Class AmelayeBioPHPExtension
 * @package Amelaye\BioPHP\DependencyInjection
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class AmelayeBioPHPExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Domain/Database/Resources/config'));
        $loader->load('services.xml');

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Api/Resources/config'));
        $loader->load('services.xml');

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Domain/Sequence/Resources/config'));
        $loader->load('services.xml');
    }

}