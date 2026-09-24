<?php
/**
 * Raised when a restriction enzyme catalog lookup does not match any known name or alias
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 24 September 2026
 */
namespace Amelaye\BioPHP\Domain\Sequence\Exception;

/**
 * Class UnknownRestrictionEnzymeException
 * @package Amelaye\BioPHP\Domain\Sequence\Exception
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class UnknownRestrictionEnzymeException extends \InvalidArgumentException
{
    /**
     * Builds the exception raised when RestrictionEnzymeCatalogInterface::getByName() finds no
     * enzyme registered under the given name or alias.
     * @param   string      $sName      The name or alias that could not be resolved
     * @return  UnknownRestrictionEnzymeException
     */
    public static function forName(string $sName): self
    {
        return new self(sprintf('No restriction enzyme found for name or alias "%s".', $sName));
    }
}
