<?php
/**
 * Protein Interface
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 10 january 2020
 * Last modified 18 january 2020
 */
namespace Amelaye\BioPHP\Domain\Sequence\Interfaces;

use Amelaye\BioPHP\Domain\Sequence\Entity\Protein;

/**
 * Defines protein-specific sequence operations.
 * @package Amelaye\BioPHP\Domain\Sequence\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface ProteinInterface
{
    /**
     * Sets the protein sequence to operate on.
     * @param Protein $oProtein
     */
    public function setProtein(Protein $oProtein);

    /**
     * Returns the length of a protein sequence().
     * @return  int  An integer representing the number of amino acids in the protein.
     */
    public function seqlen() : int;

    /**
     * Computes the molecular weight of a protein sequence.
     * @return  boolean|array   An array of the form: ( lower_molwt, upper_molwt )
     */
    public function molwt();
}