<?php
/**
 * Some useful functions
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 september 2019
 * Last modified 11 september 2019
 */
namespace AppBundle\Traits;

/**
 * Trait FormatsTrait - Some useful functions
 * @package AppBundle\Traits
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
trait FormatsTrait
{
    /**
     * Returns the first $numchars characters of a string.
     * @param   string  $str
     * @param   int     $numchars
     * @return  bool|string
     */
    public function left($str, $numchars)
    {
        return substr($str, 0, $numchars);
    }

    /**
     * Returns the substring beginning at $numchars characters from the right end of a string.
     * @param   string      $str
     * @param   int         $numchars
     * @return  bool|string
     */
    public function right($str, $numchars)
    {
        return substr($str, strlen($str)-$numchars);
    }

    /**
     * Removes "internal spaces" (as opposed to leading and trailing spaces) from a string.
     * @param   string      $string
     * @return  mixed
     */
    function intrim($string)
    {
        return str_replace(' ', '', $string);
    }

    /**
     * Removes $charcount characters from the right (end) of a string.
     * @param   string  $str
     * @param   int     $charcount
     * @return  bool|string
     */
    public function rem_right($str, $charcount = 1)
    {
        return substr($str, 0, strlen($str)-$charcount);
    }

    /**
     * trim_element() removes leading and trailing spaces from a string.  In conjunction
     * with the array_walk() function, it removes spaces from each element of an array.
     * @param $value
     * @param $key
     */
    public function trim_element(&$value, $key)
    {
        $value = trim($value);
    }
}
