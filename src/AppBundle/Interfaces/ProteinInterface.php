<?php
/**
 * Protein Interface
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 10 january 2020
 * Last modified 10 january 2020
 */
namespace AppBundle\Interfaces;

use AppBundle\Entity\Protein;

interface ProteinInterface
{
    /**
     * @param $oProtein
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