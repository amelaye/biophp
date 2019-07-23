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

/**
 * Class PhpExtension : add some functions existing in PHP
 * @package AppBundle\Twig
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class PhpExtension extends AbstractExtension
{
    /**
     * Creating new filters for my app <3
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('chunk_split', [$this, '_chunk_split'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Split a string into smaller chunks
     * @param   string  $subject
     * @param   int     $chucklen
     * @param   string  $end
     * @return  string
     */
    public function _chunk_split($subject, $chucklen, $end = "\r\n")
    {
        return chunk_split($subject, $chucklen, $end);
    }
}