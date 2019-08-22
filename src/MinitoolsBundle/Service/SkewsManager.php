<?php
/**
 * Skews Functions
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 1st march  2019
 * Last modified 1st march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

use AppBundle\Bioapi\Bioapi;
use AppBundle\Service\OligosManager;

class SkewsManager
{
    private $nucleotids;

    private $oligosManager;

    public function __construct(Bioapi $bioapi, OligosManager $oligosManager)
    {
        $this->nucleotids = $bioapi->getDNA();
        $this->oligosManager = $oligosManager;
    }

    /**
     * Will  compare oligonucleotide frequencies in all the sequence
     * with frequencies in each window, and will return an array
     * with distances  (computed as Almeida et al, 2001).
     * @param $sequence
     * @param $window
     * @param $oskew
     * @param $strands
     * @return mixed
     */
    public function oligoSkewArrayCalculation($sequence, $window, $oskew, $strands)
    {
        // search for oligos in the complet sequence
        $tetra_arrayA = $this->oligosManager->findOligos($sequence, $oskew);
        $seq_len = strlen($sequence);
        $period = ceil($seq_len / 1400);
        if($period < 10) {
            $period = 10;
        }
        if ($strands == 2) {
            // if both strands are used for computing oligonucleotide frequencies
            $sequence2 = $this->comp($sequence);
            $i = 0;
            while ($i < $seq_len - $window + 1) {
                $cadena = substr($sequence,$i,$window)." ".strrev(substr($sequence2,$i,$window));
                // compute oligonucleotide frequencies in window
                $tetra_arrayB = $this->oligosManager->findOligos($cadena, $oskew);
                // compute distance between complete sequence and window
                $data[$i] = $this->distance($tetra_arrayA, $tetra_arrayB);
                $i += $period;
            }
        } else {
            // if only one strand is used for computing oligonucleotide frequencies
            $i = 0;
            while($i < $seq_len - $window + 1) {
                $cadena = substr($sequence,$i,$window);
                // compute oligonucleotide frequencies in window
                $tetra_arrayB = $this->oligosManager->findOligos($cadena, $oskew);
                // compute distance between complete sequence and window
                $data[$i] = $this->distance($tetra_arrayA,$tetra_arrayB);
                $i += $period;
            }
        }
        // return the array with distances
        return $data;
    }

    /**
     * Computes distance between two arrays of values based in Almeida et al, 2001
     * http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=Retrieve&db=pubmed&dopt=Abstract&list_uids=11331237
     * (which is a based in a modified Pearson correlation)
     * @param $vals_x
     * @param $vals_y
     * @return float|void
     */
    public function distance($vals_x,$vals_y)
    {
        if(sizeof($vals_x) != sizeof($vals_y)) {
            return;
        }
        $nw = 0;
        $x2y = 0;
        $xy2 = 0;
        $pre_sx = 0;
        $pre_sy = 0;
        $pre_rw = 0;
        $n = sizeof($vals_x);
        foreach($vals_x as $key => $val_x) {
            $val_y = $vals_y[$key];
            $nw += $val_x * $val_y;
            $x2y += $val_x * $val_x * $val_y;
            $xy2 += $val_x * $val_y * $val_y;
        }
        $xw = $x2y / $nw;
        $yw = $xy2 / $nw;
        foreach($vals_x as $key => $val_x) {
            $val_y = $vals_y[$key];
            $pre_sx += pow($val_x-$xw,2) * $val_x * $val_y;
            $pre_sy += pow($val_y-$yw,2) * $val_x * $val_y;
        }
        $sx = $pre_sx / $nw;
        $sy = $pre_sy/$nw;
        foreach($vals_x as $key => $val_x){
            $val_y = $vals_y[$key];
            $pre_rw += ($val_x-$xw) * ($val_y-$yw) * $val_x * $val_y / (sqrt($sx) * sqrt($sy));
        }
        $rw = $pre_rw/$nw;
        return round(1-$rw,8);
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
     * Creates the image based in data provided
     * @param $sequence
     * @param $window
     * @param $GC
     * @param $AT
     * @param $KETO
     * @param $GmC
     * @param $oligo_skew_array
     * @param $olen
     * @param $from
     * @param $to
     * @param $name
     */
    public function createImage($sequence, $window, $GC, $AT, $KETO, $GmC, $oligo_skew_array, $olen, $from, $to, $name)
    {
        $pos = 0;
        $len_seq = strlen($sequence);
        $period = ceil($len_seq / 6000);

        // computes data for GC, AT, KETO and G+C skews (if requested)
        while($pos < $len_seq - $window) {
            $sub_seq = substr($sequence,$pos,$window);
            $A = substr_count($sub_seq,"A");
            $C = substr_count($sub_seq,"C");
            $G = substr_count($sub_seq,"G");
            $T = substr_count($sub_seq,"T");
            $dGC[$pos] = ($G-$C) / ($G+$C);
            if($AT == 1) {
                $dAT[$pos] = ($A-$T) / ($A+$T);
            }
            if($KETO == 1) {
                $dKETO[$pos] = round(($G+$C-$A-$T) / ($A+$C+$G+$T),4);
            }
            if($GmC==1) {
                $dGmC[$pos] = ($G+$C)/($A+$C+$G+$T);
            }
            $pos += $period;
        }

        // scale related variables
        $max = max(max($dAT), max($dGC), max($dKETO));
        $min = min(min($dAT), min($dGC), min($dKETO));
        $nmax = max($max, -$min);
        $rectify = round(200 / $nmax);

        // starts the image
        $im = imagecreate(850, 450);
        $background_color =imagecolorallocate($im, 255, 255, 255);
        $black=ImageColorAllocate($im, 0, 0, 0);
        $qblack2=ImageColorAllocate($im, 228, 228, 228);
        $qblack=ImageColorAllocate($im, 192, 192, 192);
        $red=ImageColorAllocate($im, 255, 0, 0);
        $blue=ImageColorAllocate($im, 0, 0, 255);
        $green=ImageColorAllocate($im, 0, 255, 0);
        $rb=ImageColorAllocate($im, 255, 0, 255);
        $gb=ImageColorAllocate($im, 0, 150,150);
        imagestring($im, 2, 610, 432,  "by biophp.org", $black);
        imagestring($im, 3, 600, 5,  "Window: $window", $black);

        // writes length of sequence
        if ($from or $to) {
            if(!$from) {
                $from = 0;
            }
            if(!$to) {
                $to = $len_seq;
            }
            imagestring($im, 3, 5, 432, "Length of $name: $len_seq (from position $from to $to)", $black);
        } else {
            imagestring($im, 3, 5, 432, "Length of $name: $len_seq", $black);
        }

        // write the kind of skews in proper color
        $goright = 0;
        if ($GC == 1) {
            imagestring($im, 3, 5+$goright, 5, "GC-skew", $blue);
            $goright = 70;
        }
        if ($AT == 1) {
            imagestring($im, 3, 5+$goright, 5, "AT-skew", $red);
            $goright += 70;
        }
        if ($KETO == 1) {
            imagestring($im, 3, 5+$goright, 5, "KETO-skew", $green);
            $goright += 80;
        }
        if ($GmC == 1) {
            imagestring($im, 3, 5+$goright, 5, "G+C", $black);
            $goright += 60;
        }
        if (sizeof($oligo_skew_array) > 10) {
            imagestring($im, 3, 5+$goright, 5, "oligo-skew ($olen)", $gb);
        }

        // print scale for AT, GC or KETO skews
        $ne = 0;
        if ($AT == 1 || $GC == 1 || $KETO == 1) {
            imagestring($im, 3, 710, 210, "0", $red);
            $scale = round($nmax * 0.25,3);
            $v = $scale * 3;
            imagestring($im, 3, 710, 60, $v, $red);
            imagestring($im, 3, 710, 360, -$v, $red);
            $v = $scale * 2;
            imagestring($im, 3, 710, 110, $v, $red);
            imagestring($im, 3, 710, 310, -$v, $red);
            $v = $scale;
            imagestring($im, 3, 710, 160, $v, $red);
            imagestring($im, 3, 710, 260, -$v, $red);
            $ne = 60;
        }
        // print scale for G+C skew
        if($GmC == 1) {
            $kkk = 360;
            for($i = 20; $i < 81; $i += 10) {
                imagestring($im, 3, 710+$ne, $kkk, "$i%", $black);
                $kkk -= 50;
            }
            if($ne == 60) {
                for($i = 20; $i < 421; $i += 50) {
                    imageline($im, 698 + $ne, $i, 703+$ne, $i, $black);
                }
                imageline($im,764,20,764,420,$black);
            }
            $ne += 60;
        }
        // print scale for oligo-skew
        if(sizeof($oligo_skew_array) > 10) {
            $kkk = 15;
            for($i = 0; $i < 9; $i ++) {
                imagestring($im, 3, 710+$ne, $kkk, "0.$i", $gb);
                $kkk += 50;
            }
            if($ne > 0) {
                for($i = 20; $i < 421; $i += 50) {
                    imageline($im, 698 + $ne, $i, 703+$ne, $i, $black);
                }
                imageline($im, 704+$ne, 20, 704 + $ne, 420, $black);
            }
        }
        // print oligo-skew
        // oligo-skews must be the first one to be printed out
        $xp = ($window * 700) / (2 * $len_seq);
        if(sizeof($oligo_skew_array) > 10) {
            foreach($oligo_skew_array as $pos => $val) {
                $x = round(($pos * 700 / $len_seq) + $xp);
                imageline($im, $x, 20, $x, 19 + (500 * $val), $qblack2);
                imagesetpixel($im, $x, 20 + (500 * $val), $gb);
            }
        }
        // print AT, GC and/or KETO-skews
        // each one with its color
        foreach($dGC as $pos => $val){
            $x = round(( $pos * 700 / $len_seq) + $xp);
            if($AT == 1) {
                imagesetpixel($im, $x, 220 - $dAT[$pos] * $rectify, $red);
            }
            if($GC == 1) {
                imagesetpixel($im, $x, 220 - $val * $rectify, $blue);
            }
            if($KETO == 1) {
                imagesetpixel($im, $x, 220 - $dKETO[$pos] * $rectify, $green);
            }
            if($GmC == 1) {
                imagesetpixel($im, $x, 470 - (500 * $dGmC[$pos]), $black);
            }
        }

        // write some aditional lines
        for($i = 20; $i < 421; $i += 50) {
            imageline($im,0,$i,700,$i,$black);
        }
        imageline($im, 70, 20, 70, 420, $qblack);
        imageline($im, 140, 20, 140, 420, $qblack);
        imageline($im, 210, 20, 210, 420, $qblack);
        imageline($im, 280, 20, 280, 420, $qblack);
        imageline($im, 350, 20, 350, 420, $qblack);
        imageline($im, 420, 20, 420, 420, $qblack);
        imageline($im, 490, 20, 490, 420, $qblack);
        imageline($im, 560, 20, 560, 420, $qblack);
        imageline($im, 630, 20, 630, 420, $qblack);
        imageline($im, 700, 20, 700, 420, $black);

        // output the image to a file
        imagepng($im, "image.png");
        imagedestroy($im);
        return;
    }


    /**
     * returns complement of sequence $code
     * @param $code
     * @return string
     */
    public function comp($code)
    {
        $code = str_replace("A", "t", $code);
        $code = str_replace("T", "a", $code);
        $code = str_replace("G", "c", $code);
        $code = str_replace("C", "g", $code);
        return strtoupper($code);
    }
}