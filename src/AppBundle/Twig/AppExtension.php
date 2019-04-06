<?php
/**
 * Some extensions to format the rending code
 * Freely inspired by BioPHP's project biophp.org
 * Created 6 april 2019
 * Last modified 6 april 2019
 */
namespace AppBundle\Twig;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

/**
 * Class AppExtension
 * @package AppBundle\Twig
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class AppExtension extends AbstractExtension
{
    /**
     * Creating new filters for my app <3
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('chunk_split', [$this, '_chunk_split'], ['is_safe' => ['html']]),
            new TwigFilter('atgc_sublimer', [$this, 'sublimerATGC'], ['is_safe' => ['html']])
        ];
    }

    /**
     * Split a string into smaller chunks
     * @param   string  $subject
     * @param   int     $chucklen
     * @param   string  $end
     * @return  string
     */
    public function _chunk_split($subject, $chucklen, $end)
    {
        return chunk_split($subject, $chucklen, $end);
    }

    /**
     * Shows ATGC in another color
     * @param   string  $subject
     * @param   string  $color
     * @return  string
     */
    public function sublimerATGC($subject, $color = "red")
    {
        $subject = preg_replace("/A/",'<span style="color:'.$color.'">A</span>',$subject);
        $subject = preg_replace("/C/",'<span style="color:'.$color.'">C</span>',$subject);
        $subject = preg_replace("/G/",'<span style="color:'.$color.'">G</span>',$subject);
        $subject = preg_replace("/T/",'<span style="color:'.$color.'">T</span>',$subject);

        // two lines to reduce the code to be transmitted
        $subject = preg_replace('/<\/span><span style="color:'.$color.'">/',"",$subject);
        $subject = preg_replace('/<\/span> <span style="color:'.$color.'">>/'," ",$subject);

        return $subject;
    }
}