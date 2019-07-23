<?php
/**
 * Some extensions to format the rending code
 * Freely inspired by BioPHP's project biophp.org
 * Created 6 april 2019
 * Last modified 23 july 2019
 */
namespace AppBundle\Twig;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class MinitoolsExtension
 * @package AppBundle\Twig
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class MinitoolsExtension extends AbstractExtension
{
    /**
     * Creating new functions for my app <3
     * @return array|\Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('scale_and_bar', [$this, 'getScaleAndBar']),
        ];
    }

    /**
     * Renders a bar when translating DNA to Protein
     * Sets the bar + scale
     * @return string
     */
    public function getScaleAndBar()
    {
        $sScale = "         10        20        30        40        50        60        70        80        90         \r";
        $aBar   = "         |         |         |         |         |         |         |         |         |          ";
        return "$sScale\n$aBar";
    }
}