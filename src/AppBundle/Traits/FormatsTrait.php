<?php

namespace AppBundle\Traits;

trait FormatsTrait
{
    // left() returns the first $numchars characters of a string.
    function left($str, $numchars)
    {
	return substr($str, 0, $numchars);
    }

    // right() returns the substring beginning at $numchars characters from the right end of a string.
    function right($str, $numchars)
    {
	return substr($str, strlen($str)-$numchars);
    }
}
