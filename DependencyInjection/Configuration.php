<?php
/**
 * Dependency injections for the bundle
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 january 2020
 * Last modified 12 August 2026
 */
namespace Amelaye\BioPHP\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Amelaye\BioPHP\DependencyInjection
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Just in case. But useless for the moment.
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('amelaye_biophp');
        $rootNode = $treeBuilder->getRootNode();

        return $treeBuilder;
    }
}