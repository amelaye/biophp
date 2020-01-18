<?php
/**
 * Some useful functions
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 september 2019
 * Last modified 18 january 2020
 */
namespace App\Domain\Sequence\Traits;

/**
 * Trait FormatsTrait - Some useful functions
 * @package App\Domain\Sequence\Traits
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
     * Gets the minimum of three (usually numeric) values $x, $y, and $z.
     * For now, this can't handle situations when one or more arguments is FALSE.
     * @param   int     $x
     * @param   int     $y
     * @param   int     $z
     * @return  int
     */
    function getmin($x, $y, $z)
    {
        if ($x < $y)
            if ($x < $z) return $x;
            else return $z;
        else
            if ($y < $z) return $y;
            else return $z;
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
