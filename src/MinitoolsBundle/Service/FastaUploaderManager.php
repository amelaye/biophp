<?php
/**
 * FastaUploadManager
 * Inspired by BioPHP's project biophp.org
 * Created 18 march 2019
 * Last modified 18 march 2019
 */
namespace MinitoolsBundle\Service;

/**
 * Class FastaUploaderManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class FastaUploaderManager
{
    /**
     * Checks the validity of the sequence
     * @param   string      $sSequence
     * @return  bool
     */
    function isValidSequence($sSequence)
    {
        $length = strlen($sSequence);
        for ($i = 0; $i < $length; ++$i) {
            if(!($sSequence[$i]=='a' || $sSequence[$i]=='A'||
                $sSequence[$i]=='t'|| $sSequence[$i]=='T' ||
                $sSequence[$i]=='g'|| $sSequence[$i]=='G'||
                $sSequence[$i]=='c'|| $sSequence[$i]=='c')) {
                return false;
            }
            else return true;
        }
    }
}