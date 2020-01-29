<?php
/**
 * Bundle initialisation
 * Created 19 january 2020
 * Last modified 25 january 2020
 */
namespace Amelaye\BioPHP;

use Amelaye\BioPHP\DependencyInjection\AmelayeBioPHPExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * Class AmelayeBioPHPBundle
 * @package Amelaye\BioPHP
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class AmelayeBioPHPBundle extends Bundle
{
    /**
     * @return ExtensionInterface
     */
    public function getContainerExtension()
    {
        if (null == $this->extension) {
           $this->extension = new AmelayeBioPHPExtension();
        }
        return $this->extension;
    }
}