<?php
/**
 * FastaUploadManager
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 18 march 2019
 * Last modified 18 march 2019
 */
namespace MinitoolsBundle\Service;

class FastaUploaderManager
{
    function isValidSequence($sequence)
    {
        $length = strlen($sequence);
        for ($i = 0; $i < $length; ++$i) {
            if(!($sequence[$i]=='a' || $sequence[$i]=='A'||
                $sequence[$i]=='t'|| $sequence[$i]=='T' ||
                $sequence[$i]=='g'|| $sequence[$i]=='G'||
                $sequence[$i]=='c'|| $sequence[$i]=='c')) {
                return false;
            }
            else return true;
        }
    }
}