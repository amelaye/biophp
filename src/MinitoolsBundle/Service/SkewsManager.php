<?php
/**
 * Skews Functions
 * Inspired by BioPHP's project biophp.org
 * Created 1st march 2019
 * Last modified 23 august 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

use AppBundle\Service\OligosManager;
use AppBundle\Traits\SequenceTrait;

/**
 * Class SkewsManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class SkewsManager
{
    use SequenceTrait;

    private $oligosManager;

    public function __construct(OligosManager $oligosManager)
    {
        $this->oligosManager = $oligosManager;
    }

    /**
     * Will  compare oligonucleotide frequencies in all the sequence
     * with frequencies in each window, and will return an array
     * with distances  (computed as Almeida et al, 2001).
     * @param       string      $sSequence      The sequence to analyze
     * @param       int         $iWindow        The size of the window
     * @param       int         $iOskew         Number of nucleos
     * @param       int         $iStrands       One or both strands
     * @return      array
     * @throws      \Exception
     */
    public function oligoSkewArrayCalculation($sSequence, $iWindow, $iOskew, $iStrands)
    {
        try {
            $aDistances = [];

            // search for oligos in the complet sequence
            $aOligosX = $this->oligosManager->findOligos($sSequence, $iOskew);
            $iSeqLength = strlen($sSequence);
            $iPeriod = ceil($iSeqLength / 1400);
            if($iPeriod < 10) {
                $iPeriod = 10;
            }
            if ($iStrands == 2) {
                // if both strands are used for computing oligonucleotide frequencies
                $sequence2 = $this->compDNA($sSequence);
                $i = 0;
                while ($i < $iSeqLength - $iWindow + 1) {
                    $sSequenceCut = substr($sSequence, $i, $iWindow)." ".strrev(substr($sequence2, $i, $iWindow));
                    // compute oligonucleotide frequencies in window
                    $aOligosY = $this->oligosManager->findOligos($sSequenceCut, $iOskew);
                    // compute distance between complete sequence and window
                    $aDistances[$i] = $this->distance($aOligosX, $aOligosY);
                    $i += $iPeriod;
                }
            } else {
                // if only one strand is used for computing oligonucleotide frequencies
                $i = 0;
                while($i < $iSeqLength - $iWindow + 1) {
                    $sSequenceCut = substr($sSequence, $i ,$iWindow);
                    // compute oligonucleotide frequencies in window
                    $aOligosY = $this->oligosManager->findOligos($sSequenceCut, $iOskew);
                    // compute distance between complete sequence and window
                    $aDistances[$i] = $this->distance($aOligosX, $aOligosY);
                    $i += $iPeriod;
                }
            }
            // return the array with distances
            return $aDistances;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Computes distance between two arrays of values based in Almeida et al, 2001
     * http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=Retrieve&db=pubmed&dopt=Abstract&list_uids=11331237
     * (which is a based in a modified Pearson correlation)
     * @param       array       $aValsX     Values for X
     * @param       array       $aValsY     Values for Y
     * @return      float|void
     */
    public function distance($aValsX, $aValsY)
    {
        if(sizeof($aValsX) != sizeof($aValsY)) {
            return;
        }
        $iNw = $iX2y = $iXy2 = $iPreSx = $iPreSy = $iPreRw = 0;

        foreach($aValsX as $key => $iValX) {
            $iValY = $aValsY[$key];
            $iNw += $iValX * $iValY;
            $iX2y += $iValX * $iValX * $iValY;
            $iXy2 += $iValX * $iValY * $iValY;
        }
        $iXw = $iX2y / $iNw;
        $iYw = $iXy2 / $iNw;
        foreach($aValsX as $key => $iValX) {
            $iValY = $aValsY[$key];
            $iPreSx += pow($iValX - $iXw,2) * $iValX * $iValY;
            $iPreSy += pow($iValY - $iYw,2) * $iValX * $iValY;
        }
        $sx = $iPreSx / $iNw;
        $sy = $iPreSy / $iNw;
        foreach($aValsX as $key => $iValX){
            $iValY = $aValsY[$key];
            $iPreRw += ($iValX - $iXw) * ($iValY - $iYw) * $iValX * $iValY / (sqrt($sx) * sqrt($sy));
        }
        $iRw = $iPreRw / $iNw;
        $distance = round(1 - $iRw,8);
        return $distance;
    }


    /**
     * @param $str
     * @return bool
     */
    public function strIsInt($str)
    {
        $var = intval($str);
        return("$str" == "$var");
    }

    /**
     * Creates the graph image
     * @param       string      $sSequence      Sequence to analyse
     * @param       int         $iPos           Beginning position of the sequence
     * @param       int         $iWindow        Window size
     * @param       int         $bAT            Show AT-Skew
     * @param       int         $bKETO          Show KETO-Skew
     * @param       int         $bGmC           Show G+C%
     * @param       int         $iSeqLength     Length of the sequence
     * @param       int         $iPeriod        Period
     * @param       array       $aAT            Data for AT
     * @param       array       $aGC            Data for GC
     * @param       array       $aGmC           Data for GmC
     * @param       array       $aKETO          Data for KETO
     * @return      int
     * @throws      \Exception
     */
    public function computeImage($sSequence, &$iPos, $iWindow, $bAT, $bKETO, $bGmC, $iSeqLength, $iPeriod, &$aAT, &$aGC, &$aGmC, &$aKETO)
    {
        try {
            // computes data for GC, AT, KETO and G+C skews (if requested)
            while($iPos < $iSeqLength - $iWindow) {
                $sSubSequence = substr($sSequence, $iPos, $iWindow);
                $A = substr_count($sSubSequence,"A");
                $C = substr_count($sSubSequence,"C");
                $G = substr_count($sSubSequence,"G");
                $T = substr_count($sSubSequence,"T");
                $aGC[$iPos] = ($G-$C) / ($G+$C);
                if($bAT) {
                    $aAT[$iPos] = ($A-$T) / ($A+$T);
                }
                if($bKETO) {
                    $aKETO[$iPos] = round(($G+$C-$A-$T) / ($A+$C+$G+$T),4);
                }
                if($bGmC) {
                    $aGmC[$iPos] = ($G+$C)/($A+$C+$G+$T);
                }
                $iPos += $iPeriod;
            }

            // scale related variables
            $iMax = max(max($aAT), max($aGC), max($aKETO));
            $iMin = min(min($aAT), min($aGC), min($aKETO));
            $iNmax = max($iMax, -$iMin);
            return $iNmax;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Creates the image based in data provided
     * @param       string      $sSequence      Sequence to analyse
     * @param       int         $iWindow        Window size
     * @param       int         $bGC            Show GC
     * @param       int         $bAT            Show AT-Skew
     * @param       int         $bKETO          Show KETO-Skew
     * @param       int         $bGmC           Show G+C%
     * @param       array       $aOligoSkew     Datas
     * @param       int         $iOlen          Length of the oligos
     * @param       int         $iFrom          Beginning of the sequence to analyse
     * @param       int         $iTo            End of the sequence to analyse
     * @param       string      $sName          Name of the sequence
     * @return      string
     * @throws      \Exception
     */
    public function createImage($sSequence, $iWindow, $bGC, $bAT, $bKETO, $bGmC, $aOligoSkew, $iOlen, $iFrom, $iTo, $sName)
    {
        try {
            $pos = 0;
            $len_seq = strlen($sSequence);
            $period = ceil($len_seq / 6000);
            $aAT = $aGC = $aGmC = $aKETO = [null];

            $nmax = $this->computeImage($sSequence, $pos, $iWindow, $bAT, $bKETO, $bGmC, $len_seq, $period, $aAT, $aGC, $aGmC, $aKETO);
            $rectify = round(200 / $nmax);

            // starts the image
            $im                 = imagecreate(850, 450);
            $background_color   = imagecolorallocate($im, 255, 255, 255);
            $black              = imagecolorallocate($im, 0, 0, 0);
            $qblack2            = imagecolorallocate($im, 228, 228, 228);
            $qblack             = imagecolorallocate($im, 192, 192, 192);
            $red                = imagecolorallocate($im, 255, 0, 0);
            $blue               = imagecolorallocate($im, 0, 0, 255);
            $green              = imagecolorallocate($im, 0, 255, 0);
            $rb                 = imagecolorallocate($im, 255, 0, 255);
            $gb                 = imagecolorallocate($im, 0, 150,150);

            imagestring($im, 2, 610, 432,  "by biophp.org", $black);
            imagestring($im, 3, 600, 5,  "Window: $iWindow", $black);

            // writes length of sequence
            if ($iFrom != "" || $iTo != "") {
                if($iFrom == "") {
                    $iFrom = 0;
                }
                if($iTo == "") {
                    $iTo = $len_seq;
                }
                imagestring($im, 3, 5, 432, "Length of $sName: $len_seq (from position $iFrom to $iTo)", $black);
            } else {
                imagestring($im, 3, 5, 432, "Length of $sName: $len_seq", $black);
            }

            $this->writeSkews($im, $bGC, $bAT, $bKETO, $bGmC, $aOligoSkew, $blue, $red, $green, $black, $iOlen, $gb);
            $this->printScales($im, $aOligoSkew, $bAT, $bGC, $bGmC, $bKETO, $red, $black, $gb, $nmax);

            // print oligo-skew
            // oligo-skews must be the first one to be printed out
            $xp = ($iWindow * 700) / (2 * $len_seq);
            if(sizeof($aOligoSkew) > 10) {
                foreach($aOligoSkew as $pos => $val) {
                    $x = round(($pos * 700 / $len_seq) + $xp);
                    imageline($im, $x, 20, $x, 19 + (500 * $val), $qblack2);
                    imagesetpixel($im, $x, 20 + (500 * $val), $gb);
                }
            }
            // print AT, GC and/or KETO-skews
            // each one with its color
            foreach ($aGC as $pos => $val) {
                $x = round(( $pos * 700 / $len_seq) + $xp);
                if($bAT) {
                    imagesetpixel($im, $x, 220 - $aAT[$pos] * $rectify, $red);
                }
                if($bGC) {
                    imagesetpixel($im, $x, 220 - $val * $rectify, $blue);
                }
                if($bKETO) {
                    imagesetpixel($im, $x, 220 - $aKETO[$pos] * $rectify, $green);
                }
                if($bGmC) {
                    imagesetpixel($im, $x, 470 - (500 * $aGmC[$pos]), $black);
                }
            }

            // write some aditional lines
            for($i = 20; $i < 421; $i += 50) {
                imageline($im,0,$i,700,$i,$black);
            }

            $intervals = [70, 140, 210, 280, 350, 420, 490, 560, 630];
            foreach($intervals as $interval) {
                imageline($im, $interval, 20, $interval, 420, $qblack);
            }
            imageline($im, 700, 20, 700, 420, $black);

            // output the image to a file
            imagepng($im, "public/uploads/".$sName.".png");
            imagedestroy($im);
            return $sName.".png";
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Write the kind of skews in proper color
     * @param       resource    $im             Resource of the building image
     * @param       int         $bGC            Show GC
     * @param       int         $bAT            Show AT-Skew
     * @param       int         $bKETO          Show KETO-Skew
     * @param       int         $bGmC           Show G+C%
     * @param       array       $aOligoSkew     Datas
     * @param       int         $iBlue          Shade of blue
     * @param       int         $iRed           Shade of red
     * @param       int         $iGreen         Shade of green
     * @param       int         $iBlack         Shade of black
     * @param       int         $iOlen          Length of the oligos
     * @param       int         $iGb            Shade of grey-blue
     * @return      int
     * @throws      \Exception
     */
    private function writeSkews(&$im, $bGC, $bAT, $bKETO, $bGmC, $aOligoSkew, $iBlue, $iRed, $iGreen, $iBlack, $iOlen, $iGb)
    {
        try {
            $goright = 0;
            if ($bGC) {
                imagestring($im, 3, 5 + $goright, 5, "GC-skew", $iBlue);
                $goright = 70;
            }
            if ($bAT) {
                imagestring($im, 3, 5 + $goright, 5, "AT-skew", $iRed);
                $goright += 70;
            }
            if ($bKETO) {
                imagestring($im, 3, 5 + $goright, 5, "KETO-skew", $iGreen);
                $goright += 80;
            }
            if ($bGmC) {
                imagestring($im, 3, 5 + $goright, 5, "G+C", $iBlack);
                $goright += 60;
            }
            if (sizeof($aOligoSkew) > 10) {
                imagestring($im, 3, 5 + $goright, 5, "oligo-skew ($iOlen)", $iGb);
            }
            return $goright;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Print scale for AT, GC or KETO skews + GC Skews + oligo-skew
     * @param       resource    $im             Resource of the building image
     * @param       array       $aOligoSkew     Datas
     * @param       int         $bGC            Show GC
     * @param       int         $bAT            Show AT-Skew
     * @param       int         $bKETO          Show KETO-Skew
     * @param       int         $bGmC           Show G+C%
     * @param       int         $iRed           Shade of red
     * @param       int         $iGb            Shade of grey-blue
     * @param       int         $iBlack         Shade of black
     * @param       int         $iNmax
     * @throws      \Exception
     */
    private function printScales(&$im, $aOligoSkew, $bAT, $bGC, $bGmC, $bKETO, $iRed, $iBlack, $iGb, $iNmax)
    {
        try {
            $ne = 0;
            if ($bAT || $bGC || $bKETO) {
                imagestring($im, 3, 710, 210, "0", $iRed);
                $scale = round($iNmax * 0.25,3);
                $v = $scale * 3;
                imagestring($im, 3, 710, 60, $v, $iRed);
                imagestring($im, 3, 710, 360, -$v, $iRed);
                $v = $scale * 2;
                imagestring($im, 3, 710, 110, $v, $iRed);
                imagestring($im, 3, 710, 310, -$v, $iRed);
                $v = $scale;
                imagestring($im, 3, 710, 160, $v, $iRed);
                imagestring($im, 3, 710, 260, -$v, $iRed);
                $ne = 60;
            }
            // print scale for G+C skew
            if($bGmC == 1) {
                $kkk = 360;
                for($i = 20; $i < 81; $i += 10) {
                    imagestring($im, 3, 710+$ne, $kkk, "$i%", $iBlack);
                    $kkk -= 50;
                }
                if($ne == 60) {
                    for($i = 20; $i < 421; $i += 50) {
                        imageline($im, 698 + $ne, $i, 703+$ne, $i, $iBlack);
                    }
                    imageline($im,764,20,764,420,$iBlack);
                }
                $ne += 60;
            }
            // print scale for oligo-skew
            if(sizeof($aOligoSkew) > 10) {
                $kkk = 15;
                for($i = 0; $i < 9; $i ++) {
                    imagestring($im, 3, 710+$ne, $kkk, "0.$i", $iGb);
                    $kkk += 50;
                }
                if($ne > 0) {
                    for($i = 20; $i < 421; $i += 50) {
                        imageline($im, 698 + $ne, $i, 703+$ne, $i, $iBlack);
                    }
                    imageline($im, 704+$ne, 20, 704 + $ne, 420, $iBlack);
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}