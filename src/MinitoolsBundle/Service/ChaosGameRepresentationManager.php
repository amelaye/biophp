<?php
/**
 * Chaos Game Representation Functions
 * Inspired by BioPHP's project biophp.org
 * Created 3 march  2019
 * Last modified 23 june 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

use AppBundle\Bioapi\Bioapi;

/**
 * Class ChaosGameRepresentationManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class ChaosGameRepresentationManager
{
    /**
     * @var array
     */
    private $nucleotidsGraphs;

    /**
     * ChaosGameRepresentationManager constructor.
     * @param   array           $nucleotidsGraphs
     * @param   Bioapi          $bioapi
     */
    public function __construct(array $nucleotidsGraphs, Bioapi $bioapi)
    {
        $this->nucleotidsGraphs = $nucleotidsGraphs;
        $this->dnaComplements = $bioapi->getDNAComplement();
    }

    /**
     * Compute nucleotide frequencies
     * @param   array   $aSeqData   Data of the sequence
     * @return  array
     * @throws  \Exception
     */
    public function numberNucleos($aSeqData)
    {
        try {
            $aNucleotides = [];

            foreach($this->dnaComplements as $sNucleotide) {
                $aNucleotides[$sNucleotide] = substr_count($aSeqData["sequence"], $sNucleotide);
            }

            return $aNucleotides;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Analyses Data before sending the image
     * @param   string  $sSeqName
     * @param   string  $sSequence
     * @param   int     $iSize
     * @throws  \Exception
     */
    public function CGRCompute($sSeqName, $sSequence, $iSize)
    {
        try {
            $iSeqLen = strlen($sSequence);

            if($iSize == "auto") {
                $iSize = 256;
                if($iSeqLen > 1000000) {
                    $iSize = 1024;
                }
                if($iSeqLen > 100000) {
                    $iSize = 512;
                }
            }

            $this->createCGRImage($sSeqName, $sSequence, $iSize);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Gets data sequences
     * @param   string      $sSequence
     * @param   int         $iOligoLen
     * @param   int         $iStrand
     * @return  array
     * @throws  \Exception
     */
    public function FCGRCompute($sSequence, $iOligoLen, $iStrand)
    {
        try {
            // If double strand is requested to be computed...
            if ($iStrand == 2) {
                $seqRevert = strrev($sSequence);
                foreach ($this->dnaComplements as $nucleotide => $complement) {
                    $seqRevert = str_replace($nucleotide, strtolower($complement), $seqRevert);
                }
                $sSequence .= " ".strtoupper($seqRevert);
            }

            $aDataSeq = array(
                "sequence" => $sSequence,
                "length"   => $iOligoLen,
            );

            return $aDataSeq;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * CREATE CHAOS GAME REPRESENTATION OF FREQUENCIE
     * @param   string      $sSeqName
     * @param   string      $sSequence
     * @param   int         $iSize
     */
    public function createCGRImage($sSeqName, $sSequence, $iSize)
    {
        $im = imagecreatetruecolor($iSize, $iSize + 20);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, $iSize, $iSize + 20, $white);
        $x = round($iSize / 2);
        $y = $x;
        for($i = 0; $i < strlen($sSequence); $i++) {
            $w = substr($sSequence, $i, 1);
            if($w == "A") {
                $x -= $x / 2;
                $y += ($iSize - $y) / 2;
            }
            if($w == "C") {
                $x -= $x / 2;
                $y -= $y / 2;
            }
            if($w == "G") {
                $x += ($iSize-$x)/2;
                $y -= $y/2;
            }
            if($w == "T"){
                $x += ($iSize-$x) / 2;
                $y += ($iSize-$y) / 2;
            }
            $x2 = floor($x);
            $y2 = floor($y);

            imagesetpixel($im, $x2, $y2, $black);
        }

        $iSeqlen = strlen($sSequence);
        imagestring($im, 3, 5, $iSize+5, "$sSeqName ($iSeqlen bp)", $black);
        imagepng($im, $this->nucleotidsGraphs["cgr_file"]);
        imagedestroy($im);
    }

    /**
     * CREATE CHAOS GAME REPRESENTATION OF FREQUENCIES
     * The FCGR image will be save to a file, and an string is returned which contains data to be create a image map
     * @param   array       $oligos
     * @param   string      $seq_name
     * @param   array       $aNucleotids
     * @param   int         $seq_len
     * @param   string      $n
     * @param   int         $oligo_len
     * @return  array
     * @throws  \Exception
     */
    public function createFCGRImage($oligos, $seq_name, $aNucleotids, $seq_len, $n, $oligo_len)
    {
        try {
            $iFontWeight = 3;

            $max_val = max($oligos);
            $min_val = min($oligos);

            foreach($oligos as $key => $val) {
                $ratio[$key] = floor(255 - ((255 * ($val - $min_val)) / ($max_val - $min_val)));
            }

            $im = imagecreatetruecolor(552, 370);


            for($c = 0; $c < 256; $c++) {
                $thecolor[$c] = imagecolorallocate($im, $c, $c, $c);
            }
            $background_color = imagecolorallocate($im, 255, 255, 255);
            imagefilledrectangle($im,0,0,552,700,$background_color);

            $black  = imagecolorallocate($im, 0, 0, 0);
            $red    = imagecolorallocate($im, 255, 0, 0);
            $blue   = imagecolorallocate($im, 0, 0, 255);

            imagestring($im, 4, 10, 10, "Over or under-representation of oligonucleotides", $blue);
            imagestring($im, 3, 20, 30, "Chaos Game Representation of frequencies (FCGR)", $black);
            imageline($im, 10, 50, 350, 50, $black);
            $seq_name = substr($seq_name,0,15);
            imagestring($im, 3, 20, 55, "Sequence name: $seq_name ($seq_len bp)", $black);

            if($n == 1) {
                imagestring($im, 3, 20, 73, "Results for only one strand", $black);
            }
            else if($n == 2) {
                imagestring($im, 3, 20, 73, "Results for both strands", $black);
            }

            $thecolor[255] = imagecolorallocate($im, 255, 255, 255);

            // maps area data
            $for_map = $this->mapAreaData($ratio, $thecolor, $im);

            $imageNucleotids = array(
                "A" => array("font" => $iFontWeight, "x" => 420,  "y" => 10, "occurences" => $aNucleotids["A"]),
                "C" => array("font" => $iFontWeight, "x" => 420,  "y" => 30, "occurences" => $aNucleotids["C"]),
                "G" => array("font" => $iFontWeight, "x" => 420,  "y" => 50, "occurences" => $aNucleotids["G"]),
                "T" => array("font" => $iFontWeight, "x" => 420,  "y" => 70, "occurences" => $aNucleotids["T"]),
            );

            foreach($imageNucleotids as $key => $l) {
                imagestring($im, $l["font"], $l["x"], $l["y"],  $key.': '.$l["occurences"].'', $black);
            }

            // lines
            imageline ($im, 10,  90,  10,  346, $black);
            imageline ($im, 266, 90,  266, 346, $black);
            imageline ($im, 10,  90,  266, 90,  $black);
            imageline ($im, 10,  346, 266, 346, $black);

            if($oligo_len == 2) {
                $this->createGraphFor2Nucleo($im, $black, $iFontWeight);
            }
            if ($oligo_len == 3) {
                $this->createGraphForTrinucleo($im, $black, $iFontWeight);
            }

            // show length of oligonucleotides
            imagestring($im, $iFontWeight, 50, 350,  "Oligonucleotide length: $oligo_len", $black);


            $cent = 286;
            imagestring($im, 2, 6   + $cent, 228, "Frequency", $black);
            imagefilledrectangle($im,6   + $cent,208,16  + $cent,218,$thecolor[255]);
            imagefilledrectangle($im,19  + $cent,208,29  + $cent,218,$thecolor[240]);
            imagefilledrectangle($im,32  + $cent,208,42  + $cent,218,$thecolor[225]);
            imagefilledrectangle($im,45  + $cent,208,55  + $cent,218,$thecolor[210]);
            imagefilledrectangle($im,58  + $cent,208,68  + $cent,218,$thecolor[195]);
            imagefilledrectangle($im,71  + $cent,208,81  + $cent,218,$thecolor[180]);
            imagefilledrectangle($im,84  + $cent,208,94  + $cent,218,$thecolor[165]);
            imagefilledrectangle($im,97  + $cent,208,107 + $cent,218,$thecolor[150]);
            imagefilledrectangle($im,110 + $cent,208,120 + $cent,218,$thecolor[135]);
            imagefilledrectangle($im,123 + $cent,208,133 + $cent,218,$thecolor[135]);
            imagefilledrectangle($im,136 + $cent,208,146 + $cent,218,$thecolor[120]);
            imagefilledrectangle($im,149 + $cent,208,159 + $cent,218,$thecolor[105]);
            imagefilledrectangle($im,162 + $cent,208,172 + $cent,218,$thecolor[90]);
            imagefilledrectangle($im,175 + $cent,208,185 + $cent,218,$thecolor[75]);
            imagefilledrectangle($im,188 + $cent,208,198 + $cent,218,$thecolor[60]);
            imagefilledrectangle($im,201 + $cent,208,211 + $cent,218,$thecolor[45]);
            imagefilledrectangle($im,214 + $cent,208,224 + $cent,218,$thecolor[30]);
            imagefilledrectangle($im,227 + $cent,208,237 + $cent,218,$thecolor[15]);
            imagefilledrectangle($im,240 + $cent,208,250 + $cent,218,$thecolor[0]);

            imagepng($im,$this->nucleotidsGraphs["fcgr_file"]);

            imagedestroy($im);
            return $for_map;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Creates graph for two nucleotids
     * @param   resource $im
     * @param   string $black
     * @param   int $iFontWeight
     * @throws  \Exception
     */
    private function createGraphFor2Nucleo(&$im, $black, $iFontWeight)
    {
        try {
            // lines
            $startx     = $this->nucleotidsGraphs["startx_2"];
            $starty     = $this->nucleotidsGraphs["starty_2"];
            $interval   = $this->nucleotidsGraphs["intervals_2"];

            imageline($im, $startx, ($starty + $interval), ($startx + $interval * 4), ($starty + $interval), $black);
            imageline($im, $startx, ($starty + $interval * 2), ($startx + $interval * 4), ($starty + $interval * 2), $black);
            imageline($im, $startx, ($starty + $interval * 3), ($startx + $interval * 4), ($starty + $interval * 3), $black);

            imageline($im, ($startx + $interval), $starty, ($startx + $interval), ($starty + $interval * 4), $black);
            imageline($im, ($startx + $interval * 2), $starty, ($startx + $interval * 2), ($starty + $interval * 4), $black);
            imageline($im, ($startx + $interval * 3), $starty, ($startx + $interval * 3), ($starty + $interval * 4), $black);

            // dimers in their place
            $h_pos = $this->nucleotidsGraphs["positions_2"]["h_pos"];
            $v_pos = $this->nucleotidsGraphs["positions_2"]["v_pos"];

            $imageNucleotids = array(
                "CC" => array(
                    "x" => $startx + $h_pos,
                    "y" => $starty + $v_pos
                ),
                "GC" => array(
                    "x" => $startx + $interval + $h_pos,
                    "y" => $starty + $v_pos
                ),
                "CG" => array(
                    "x" => $startx + ($interval * 2) + $h_pos,
                    "y" => $starty + $v_pos
                ),
                "GG" => array(
                    "x" => $startx + ($interval * 3) + $h_pos,
                    "y" => $starty + $v_pos
                ),

                "AC" => array(
                    "x" => $startx  + $h_pos,
                    "y" => $starty + $interval + $v_pos
                ),
                "TC" => array(
                    "x" => $startx + $interval + $h_pos,
                    "y" => $starty + $interval + $v_pos
                ),
                "AG" => array(
                    "x" => $startx + ($interval * 2) + $h_pos,
                    "y" => $starty + $interval + $v_pos
                ),
                "TG" => array(
                    "x" => $startx + ($interval * 3) + $h_pos,
                    "y" => $starty + $interval + $v_pos
                ),

                "CA" => array(
                    "x" => $startx + $h_pos,
                    "y" => $starty + ($interval * 2) + $v_pos
                ),
                "GA" => array(
                    "x" => $startx + $interval + $h_pos,
                    "y" => $starty + ($interval * 2) + $v_pos
                ),
                "CT" => array(
                    "x" => $startx + ($interval * 2) + $h_pos,
                    "y" => $starty + ($interval * 2) + $v_pos
                ),
                "GT" => array(
                    "x" => $startx + ($interval * 3) + $h_pos,
                    "y" => $starty + ($interval * 2) + $v_pos
                ),

                "AA" => array(
                    "x" => $startx  + $h_pos,
                    "y" => $starty + ($interval * 3) + $v_pos
                ),
                "TA" => array(
                    "x" => $startx + $interval + $h_pos,
                    "y" => $starty + ($interval * 3) + $v_pos
                ),
                "AT" => array(
                    "x" => $startx + ($interval * 2) + $h_pos,
                    "y" => $starty + ($interval * 3) + $v_pos
                ),
                "TT" => array(
                    "x" => $startx + ($interval * 3) + $h_pos,
                    "y" => $starty + ($interval * 3) + $v_pos
                ),
            );

            foreach($imageNucleotids as $key => $l) {
                imagestring($im, $iFontWeight, $l["x"], $l["y"], $key, $black);
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Creates graph for three nucleotids
     * @param   resource $im
     * @param   string $black
     * @param   int $iFontWeight
     * @throws  \Exception
     */
    private function createGraphForTrinucleo(&$im, $black, $iFontWeight)
    {
        try {
            // lines
            imageline ($im, 10, 122, 266, 122, $black);
            imageline ($im, 10, 154, 266, 154, $black);
            imageline ($im, 10, 186, 266, 186, $black);
            imageline ($im, 10, 218, 266, 218, $black);
            imageline ($im, 10, 250, 266, 250, $black);
            imageline ($im, 10, 282, 266, 282, $black);
            imageline ($im, 10, 314, 266, 314, $black);
            imageline ($im, 42, 90, 42, 346, $black);
            imageline ($im, 74, 90, 74, 346, $black);
            imageline ($im, 106, 90, 106, 346, $black);
            imageline ($im, 138, 90, 138, 346, $black);
            imageline ($im, 170, 90, 170, 346, $black);
            imageline ($im, 202, 90, 202, 346, $black);
            imageline ($im, 234, 90, 234, 346, $black);

            // trinucleotides in their place
            $h_pos = 8;
            $v_pos = 10;

            $imageNucleotids = array(
                "CCC" => array("x" => 10   + $h_pos,  "y" => 90 + $v_pos), // x + 32
                "GCC" => array("x" => 42   + $h_pos,  "y" => 90 + $v_pos),
                "CGC" => array("x" => 74   + $h_pos,  "y" => 90 + $v_pos),
                "GGC" => array("x" => 106  + $h_pos,  "y" => 90 + $v_pos),
                "CCG" => array("x" => 138  + $h_pos,  "y" => 90 + $v_pos),
                "GCG" => array("x" => 170  + $h_pos,  "y" => 90 + $v_pos),
                "CGG" => array("x" => 202  + $h_pos,  "y" => 90 + $v_pos),
                "GGG" => array("x" => 234  + $h_pos,  "y" => 90 + $v_pos),

                "ACC" => array("x" => 10   + $h_pos,  "y" => 122 + $v_pos), // y + 32
                "TCC" => array("x" => 42   + $h_pos,  "y" => 122 + $v_pos),
                "AGC" => array("x" => 74   + $h_pos,  "y" => 122 + $v_pos),
                "TGC" => array("x" => 106  + $h_pos,  "y" => 122 + $v_pos),
                "ACG" => array("x" => 138  + $h_pos,  "y" => 122 + $v_pos),
                "TCG" => array("x" => 170  + $h_pos,  "y" => 122 + $v_pos),
                "AGG" => array("x" => 202  + $h_pos,  "y" => 122 + $v_pos),
                "TGG" => array("x" => 234  + $h_pos,  "y" => 122 + $v_pos),

                "CAC" => array("x" => 10   + $h_pos,  "y" => 154 + $v_pos),
                "GAC" => array("x" => 42   + $h_pos,  "y" => 154 + $v_pos),
                "ATC" => array("x" => 74   + $h_pos,  "y" => 154 + $v_pos),
                "CTC" => array("x" => 106  + $h_pos,  "y" => 154 + $v_pos),
                "CAG" => array("x" => 138  + $h_pos,  "y" => 154 + $v_pos),
                "GAG" => array("x" => 170  + $h_pos,  "y" => 154 + $v_pos),
                "CTG" => array("x" => 202  + $h_pos,  "y" => 154 + $v_pos),
                "GTG" => array("x" => 234  + $h_pos,  "y" => 154 + $v_pos),

                "AAC" => array("x" => 10   + $h_pos,  "y" => 186 + $v_pos),
                "TAC" => array("x" => 42   + $h_pos,  "y" => 186 + $v_pos),
                "GTC" => array("x" => 74   + $h_pos,  "y" => 186 + $v_pos),
                "TTC" => array("x" => 106  + $h_pos,  "y" => 186 + $v_pos),
                "AAG" => array("x" => 138  + $h_pos,  "y" => 186 + $v_pos),
                "TAG" => array("x" => 170  + $h_pos,  "y" => 186 + $v_pos),
                "ATG" => array("x" => 202  + $h_pos,  "y" => 186 + $v_pos),
                "TTG" => array("x" => 234  + $h_pos,  "y" => 186 + $v_pos),

                "CCA" => array("x" => 10   + $h_pos,  "y" => 218 + $v_pos),
                "GCA" => array("x" => 42   + $h_pos,  "y" => 218 + $v_pos),
                "CGA" => array("x" => 74   + $h_pos,  "y" => 218 + $v_pos),
                "GGA" => array("x" => 106  + $h_pos,  "y" => 218 + $v_pos),
                "CCT" => array("x" => 138  + $h_pos,  "y" => 218 + $v_pos),
                "GCT" => array("x" => 170  + $h_pos,  "y" => 218 + $v_pos),
                "CGT" => array("x" => 202  + $h_pos,  "y" => 218 + $v_pos),
                "GGT" => array("x" => 234  + $h_pos,  "y" => 218 + $v_pos),

                "ACA" => array("x" => 10   + $h_pos,  "y" => 250 + $v_pos),
                "TCA" => array("x" => 42   + $h_pos,  "y" => 250 + $v_pos),
                "AGA" => array("x" => 74   + $h_pos,  "y" => 250 + $v_pos),
                "TGA" => array("x" => 106  + $h_pos,  "y" => 250 + $v_pos),
                "ACT" => array("x" => 138  + $h_pos,  "y" => 250 + $v_pos),
                "TCT" => array("x" => 170  + $h_pos,  "y" => 250 + $v_pos),
                "AGT" => array("x" => 202  + $h_pos,  "y" => 250 + $v_pos),
                "TGT" => array("x" => 234  + $h_pos,  "y" => 250 + $v_pos),

                "CAA" => array("x" => 10   + $h_pos,  "y" => 282 + $v_pos),
                "GAA" => array("x" => 42   + $h_pos,  "y" => 282 + $v_pos),
                "CTA" => array("x" => 74   + $h_pos,  "y" => 282 + $v_pos),
                "GTA" => array("x" => 106  + $h_pos,  "y" => 282 + $v_pos),
                "CAT" => array("x" => 138  + $h_pos,  "y" => 282 + $v_pos),
                "GAT" => array("x" => 170  + $h_pos,  "y" => 282 + $v_pos),
                "CTT" => array("x" => 202  + $h_pos,  "y" => 282 + $v_pos),
                "GTT" => array("x" => 234  + $h_pos,  "y" => 282 + $v_pos),

                "AAA" => array("x" => 10   + $h_pos,  "y" => 314 + $v_pos),
                "TAA" => array("x" => 10   + $h_pos,  "y" => 314 + $v_pos),
                "ATA" => array("x" => 10   + $h_pos,  "y" => 314 + $v_pos),
                "TTA" => array("x" => 10   + $h_pos,  "y" => 314 + $v_pos),
                "AAT" => array("x" => 10   + $h_pos,  "y" => 314 + $v_pos),
                "TAT" => array("x" => 10   + $h_pos,  "y" => 314 + $v_pos),
                "ATT" => array("x" => 10   + $h_pos,  "y" => 314 + $v_pos),
                "TTT" => array("x" => 10   + $h_pos,  "y" => 314 + $v_pos),
            );

            foreach($imageNucleotids as $key => $l) {
                imagestring($im, $iFontWeight, $l["x"], $l["y"], $key, $black);
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Creates the different positions of areas
     * @param   array   $aRatio
     * @param   array   $aThecolor
     * @param   $im
     * @return  array
     * @throws  \Exception
     */
    private function mapAreaData($aRatio, $aThecolor, $im)
    {
        try {
            $aAreas = [];
            $frameLength = null;

            foreach($aRatio as $seq => $val) {
                $len = strlen($seq);
                switch($len) {
                    case 7:
                        $frameLength = 1;
                        break;
                    case 6:
                        $frameLength = 3;
                        break;
                    case 5:
                        $frameLength = 7;
                        break;
                    case 4:
                        $frameLength = 15;
                        break;
                    case 3:
                        $frameLength = 31;
                        break;
                    case 2:
                        $frameLength = 63;
                        break;
                }

                $h_pos = $this->nucleotidsGraphs["startx_2"];
                $v_pos = $this->nucleotidsGraphs["starty_2"];

                // each position
                $x = 0;
                $y = 0;
                $tt = 0;
                $len2 = $len;
                while($len2 > 0) {
                    $len2 --;
                    $ttt = pow(2, $tt);
                    $tt ++;
                    $subseq1 = substr($seq, $len2, 1);
                    if($subseq1 == "A" || $subseq1 == "T") {
                        $y += 128 / $ttt;
                    }
                    if($subseq1 == "G" || $subseq1 == "T") {
                        $x += 128 / $ttt;
                    }
                }
                $x += $h_pos;
                $x2 = $x + $frameLength;
                $y += $v_pos;
                $y2 = $y + $frameLength;

                imagefilledrectangle($im,$x,$y,$x2,$y2,$aThecolor[$val]);

                $aAreas[$seq] = array($x,$y,$x2,$y2);
            }
            return $aAreas;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}