<?php
/**
 * FastaUploadManager
 * Inspired by BioPHP's project biophp.org
 * Created 18 march 2019
 * Last modified 24 june 2019
 */
namespace MinitoolsBundle\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Class FastaUploaderManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class FastaUploaderManager
{
    /**
     * Checks the validity of the sequence
     * @param   string      $sSequence
     * @return  bool
     */
    public function isValidSequence($sSequence)
    {
        $length = strlen($sSequence);
        for ($i = 0; $i < $length; ++$i) {
            if(!($sSequence[$i]=='a' || $sSequence[$i]=='A'||
                $sSequence[$i]=='t'|| $sSequence[$i]=='T' ||
                $sSequence[$i]=='g'|| $sSequence[$i]=='G'||
                $sSequence[$i]=='c'|| $sSequence[$i]=='C')) {
                return false;
            }
        }
        return true;
    }

    /**
     * Copy file into the server
     * @param  object   $file
     * @param  string   $sBrochuresDirectory
     * @return string
     */
    public function createFiles($file, $sBrochuresDirectory)
    {
        $fileName = md5(uniqid()).'.txt';

        try {
            $file->move(
                $sBrochuresDirectory,
                $fileName
            );
        } catch (FileException $e) {
            throw new FileException($e);
        }

        $myFile = $sBrochuresDirectory.'/'.$fileName;
        $fh = fopen($myFile, 'r');
        $var = fread($fh, 1000000);
        fclose($fh);

        return $var;
    }

    /**
     * Validates the sequence in the file
     * @param $var
     * @param $a
     * @param $g
     * @param $t
     * @param $c
     * @param $length
     * @throws \Exception
     */
    public function checkNucleotidSequence($var, &$a, &$g, &$t, &$c, $length)
    {
        if($length != '') {
            if($this->isValidSequence($var)) {
                for ($i = 0; $i < $length ; ++$i) {
                    switch($var[$i]) {
                        case 'a':
                        case 'A':
                            $a++;
                            break;
                        case 't':
                        case 'T':
                            $t++;
                            break;
                        case 'c':
                        case 'C':
                            $c++;
                            break;
                        case 'g':
                        case 'G':
                            $g++;
                    }
                }
            } else {
                throw new \Exception("Please check....This is not a Nucleotide sequence");
            }
        }
    }
}