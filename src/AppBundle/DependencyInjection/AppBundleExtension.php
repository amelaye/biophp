<?php
/**
 * Created by PhpStorm.
 * User: amelaye
 * Date: 2019-04-11
 * Time: 00:12
 */

namespace AppBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Sensio\Bundle\FrameworkExtraBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * Class AppBundleExtension
 * @package AppBundle\DependencyInjection
 */
class AppBundleExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yml');
    }
}