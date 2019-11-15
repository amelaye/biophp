<?php
/**
 * DistanceAmongSequencesManager
 * Freely inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 2 november 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

use AppBundle\Bioapi\Bioapi;
use AppBundle\Service\Misc\OligosManager;
use AppBundle\Traits\SequenceTrait;

/**
 * Class DistanceAmongSequencesManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class DistanceAmongSequencesManager
{
    use SequenceTrait;

    /**
     * @var int
     */
    private $x = null;

    /**
     * @var int
     */
    private $y = null;

    /**
     * @var int
     */
    private $cases = null;

    /**
     * @var array
     */
    private $dnaComplements;

    /**
     * @var OligosManager
     */
    private $oligosManager;

    /**
     * DistanceAmongSequencesManager constructor.
     * @param   Bioapi  $bioapi
     */
    public function __construct(OligosManager $oligosManager, Bioapi $bioapi)
    {
        $this->dnaComplements = $bioapi->getDNAComplement();
        $this->oligosManager = $oligosManager;
    }

    /**
     * Get the name of each sequence (save names to array $seq_name)
     * Unit test created
     * @param $seqs
     * @return array[]|false|string[]
     * @throws \Exception
     */
    public function formatSequences($seqs)
    {
        try {
            $seqs = preg_split("/>/", $seqs,-1,PREG_SPLIT_NO_EMPTY);
            foreach ($seqs as $key => $val) {
                $seq_name[$key] = substr($val,0,strpos($val,"\n"));
                $temp_val = substr($val,strpos($val,"\n"));
                $temp_val = preg_replace("/\W|\d/","",$temp_val);
                $seqs[$key] = strtoupper($temp_val);
            }
            return $seqs;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * COMPUTE OLIGONUCLEOTIDE FREQUENCIES
     * Unit test created
     * @param $seqs
     * @param $len
     * @return mixed
     * @throws \Exception
     */
    public function computeOligonucleotidsFrequenciesEuclidean($seqs, $len)
    {
        try {
            $oligo_array = [];
            foreach ($seqs as $key => $val) {
                // to compute oligonucleotide frequencies, both strands are used
                $valRevert = strrev($val);
                foreach ($this->dnaComplements as $nucleotide => $complement) {
                    $valRevert = str_replace($nucleotide, strtolower($complement), $valRevert);
                }
                $seq_and_revseq = $val." ".strtoupper($valRevert);

                $oligos = $this->oligosManager->findOligos(
                    $seq_and_revseq,
                    $len
                );

                $oligo_array[$key] = $this->standardFrecuencies($oligos, $len);
            }
            return $oligo_array;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * COMPUTE OLIGONUCLEOTIDE FREQUENCIES
     * @param $seqs
     * Unit test created
     * @return array
     * @throws \Exception
     */
    public function computeOligonucleotidsFrequencies($seqs)
    {
        try {
            $oligo_array = [];
            foreach ($seqs as $key => $theseq) {
                $oligo_array[$key] = $this->computeZscoresForTetranucleotides($theseq);
            }
            return $oligo_array;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * COMPUTE DISTANCES AMONG SEQUENCES
     * by computing Euclidean distance
     * standarized oligonucleotide frequencies in $oligo_array are used, and distances are stored in $data array
     * Unit Test created
     * @param   string          $seqs
     * @param   array           $oligo_array
     * @param   int             $len
     * @return  array
     * @throws  \Exception
     */
    public function computeDistancesAmongFrequenciesEuclidean($seqs, $oligo_array, $len)
    {
        try {
            $data = [];
            foreach ($seqs as $key => $val) {
                foreach($seqs as $key2 => $val2) {
                    if ($key >= $key2) {
                        continue;
                    }
                    $data[$key][$key2] = $this->euclidDistance(
                        $oligo_array[$key],
                        $oligo_array[$key2],
                        $len
                    );
                }
            }
            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * COMPUTE DISTANCES AMONG SEQUENCES
     * by computing Pearson distance
     * standarized oligonucleotide frequencies in $oligo_array are used, and distances are stored in $data array
     * Unit Test Created
     * @param   string          $seqs
     * @param   array           $oligo_array
     * @return  mixed
     * @throws  \Exception
     */
    public function computeDistancesAmongFrequencies($seqs, $oligo_array)
    {
        try {
            $data = [];
            foreach($seqs as $key => $val){
                foreach($seqs as $key2 => $val2){
                    if ($key >= $key2) {
                        continue;
                    }
                    $data[$key][$key2]= $this->pearsonDistance(
                        $oligo_array[$key],
                        $oligo_array[$key2]
                    );
                }
            }
            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * @param $a
     * @return array|array[]|false|string[]
     * @throws \Exception
     */
    public function getArrayCases($a)
    {
        try {
            $done = "";
            foreach($a as $key => $val){
                $done .= "#$key";
                foreach($a[$key] as $key2 =>$val2){
                    $done .= "#$key2";
                }
            }
            $cases = preg_split("/#/",$done,-1,PREG_SPLIT_NO_EMPTY);
            $cases = array_unique($cases);
            sort($cases);
            return $cases;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * @param $a
     * @return mixed
     * @throws \Exception
     */
    public function newArray($a)
    {
        try {
            $cases = $this->getArrayCases($a);
            for($j = 0; $j < sizeof($cases)+1; $j++) {
                $key = $cases[$j];

                // next 3 lines are required in windows for correct comparison
                settype($key, "string");
                settype($this->x, "string");
                settype($this->y, "string");

                if($key == $this->x || $key == $this->y) {
                    continue;
                }
                if($a[$key][$this->x] != "") {
                    if($a[$key][$this->y] != "") {
                        $temp_a[$key]["($this->x,$this->y)"] = ($a[$key][$this->x]+$a[$key][$this->y])/2;
                    }
                    if($a[$this->x][$key] != "") {
                        $temp_a[$key]["($this->x,$this->y)"] = ($a[$key][$this->x]+$a[$this->x][$key])/2;
                    }
                    if($a[$this->y][$key] != "") {
                        $temp_a[$key]["($this->x,$this->y)"] = ($a[$key][$this->x]+$a[$this->y][$key])/2;
                    }
                } else {
                    if($a[$key][$this->y] != "") {
                        if ($a[$this->x][$key] != "") {
                            $temp_a[$key]["($this->x,$this->y)"] = ($a[$key][$this->y]+$a[$this->x][$key])/2;
                        }
                        if($a[$this->y][$key] != "") {
                            $temp_a[$key]["($this->x,$this->y)"] = ($a[$key][$this->y]+$a[$this->y][$key])/2;
                        }
                    } else {
                        if($a[$this->y][$key] != "") {
                            $temp_a[$key]["($this->x,$this->y)"] = ($a[$this->y][$key]+$a[$this->y][$key])/2;
                        }
                    }
                }

                for($i = $j+1; $i < sizeof($cases); $i++) {
                    $key2 = $cases[$i];
                    settype($key2, "string");
                    if ($key == $key2 || $key2 == $this->x || $key2 == $this->y) {
                        continue;
                    }
                    if ($a[$key][$key2] != "") {
                        $temp_a[$key][$key2] = $a[$key][$key2];
                    }
                    if ($a[$key2][$key] != "") {
                        $temp_a[$key][$key2] = $a[$key2][$key];
                    }
                }
            }
            return $temp_a;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * @param $a
     * @return int|mixed
     * @throws \Exception
     */
    public function minArray($a)
    {
        try {
            $str_cases  = "";
            $min        = 1000000;
            $done       = "";
            foreach ($a as $key => $val) {
                $str_cases .= "#$key";
                foreach($a[$key] as $key2 =>$val2) {
                    if ($val == "") {
                        continue;
                    }
                    $str_cases .= "#$key2";
                    if ($val2 < $min) {
                        $min = $val2;
                        $this->x = $key;
                        $this->y = $key2;
                    }
                }
            }
            $this->cases = preg_split("/#/",$done,-1,PREG_SPLIT_NO_EMPTY);
            $this->cases = array_unique($this->cases);
            sort($this->cases);
            return $min;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Creates picture for Dendogram
     * @param $str
     * @param $comp
     * @throws \Exception
     */
    public function createDendrogram($str, $comp, $dendogramFile, $method, $len)
    {
        try {
            $w      = 20;          //height for each line (case)
            $wherex = [];

            $str    = preg_replace("/\(|\)/","",$str).",";
            $a      = preg_split("/,/",$str,-1,PREG_SPLIT_NO_EMPTY);
            $rows   = sizeof($a);

            $width  = 600;     // width of scale from 0 to 2
            $im     = imagecreatetruecolor($width*1.2, $rows*$w+40);
            $white  = imagecolorallocate($im, 255, 255, 255);
            $black  = imagecolorallocate($im, 0, 0, 0);
            $red    = imagecolorallocate($im, 255, 0, 0);
            imagefilledrectangle($im,0,0,$width*1.2, $rows*$w+40,$white);

            $y = $rows*$w;    // vertical location
            $f = $width;      // multiplication factor

            // lines for scale
            $aJValues = [0.1, 0.2, 0.3, 0.5, 1.0, 1.5, 2.0];

            foreach($aJValues as $j) {
                $x = log($j + 1) * $f + 20;
                $x2 = log($j + 1) * $f - 8 + 20;
                imageline($im, $x, $y, $x, $y + 10, $black);
                imagestring($im, 1, $x2, $y + 12,  $j, $black);
            }

            // write into the image the numbers corresponding to cases
            foreach($a as $n => $val) {
                if(strlen($val) == 1) {
                    $val = " $val";
                }
                imagestring($im,3, 5, $n * $w + 5,  $val, $black);
            }

            // WRITE LINES
            foreach ($comp as $key => $val) {
                $pos1 = $pos2 = 0;
                foreach ($comp[$key] as $key2 => $val2) {

                    // get position of case in the list
                    $keya = preg_replace("/\(|\)/","",$key);
                    $pos1 = substr_count (" ,".substr($str, 0,strpos(" ,".$str,",$keya,")),",")-0.4;
                    $keyb = preg_replace("/\(|\)/","",$key2);
                    $pos2 = substr_count (" ,".substr($str, 0,strpos(" ,".$str,",$keyb,")),",")-0.4;
                    if(substr_count($keya,",")>0) {
                        $pos1b = $pos1 + substr_count($keya,",")/2;
                    } else {
                        $pos1b = $pos1;
                    }
                    if(substr_count($keyb,",") > 0) {
                        $pos2b = $pos2+substr_count($keyb,",")/2;
                    } else {
                        $pos2b = $pos2;
                    }

                    // Position related data
                    $xkey1 = isset($wherex[$key]) ? $xkey1 = $wherex[$key] : $xkey1 = 0;
                    if($xkey1 == "") {
                        $xkey1 = 0;
                    }

                    $xkey2 = isset($wherex[$key2]) ? $xkey2 = $wherex[$key2] : $xkey2 = 0;
                    if($xkey2 == "") {
                        $xkey2 = 0;
                    }
                    $max = max($xkey1,$xkey2);
                    $min = min($xkey1,$xkey2);
                    $xmax = $max+(($val2-($max))/2);
                    $val4 = log($xmax+1)*$f;
                    $val4max = log($max+1)*$f;
                    $val4min = log($min+1)*$f;

                    // write lines
                    if (isset($wherex[$key]) && $wherex[$key] == $max) {
                        imageline($im, $val4max+20, $pos1b*$w, $val4+20, $pos1b*$w, $black);
                        imageline($im, $val4+20, $pos1b*$w, $val4+20, $pos2b*$w, $black);
                        imageline($im, $val4min+20, $pos2b*$w, $val4+20, $pos2b*$w, $black);
                    }else{
                        imageline($im, $val4min+20, $pos1b*$w, $val4+20, $pos1b*$w, $black);
                        imageline($im, $val4+20, $pos1b*$w, $val4+20, $pos2b*$w, $black);
                        imageline($im, $val4max+20, $pos2b*$w, $val4+20, $pos2b*$w, $black);
                    }
                    $wherex["(".$key.",".$key2.")"] = $xmax;
                }

            }
            imageline($im, $val4+20, ($pos1b+$pos2b)*$w/2, $val4+40, ($pos1b+$pos2b)*$w/2, $black);
            imageline($im, 20, $y, $width*1.2, $y, $black);

            if ($method == "euclidean") {
                imagestring($im, 2, 5, $rows*$w+25,  "Euclidean distance for ".$len." bases long oligonucleotides.", $red);
            } else {
                imagestring($im, 2, 5, $rows*$w+25,  "Pearson distance for z-scores of tetranucleotides.", $red);
            }

            imagestring($im, 2, $width*1, $rows*$w+25,  "by insilico.ehu.es", $black);
            imagepng($im, $dendogramFile);
            imagedestroy($im);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Generates the distance value from array X and array Y
     * Only in case euclidian selected
     * Wang et al, Gene 2005; 346:173-185
     * Unit test created
     * @param   array   $a      First array for comparaison
     * @param   array   $b      Second array fo comparaison
     * @param   int     $len    Length of the combinations
     * @return  float
     * @throws \Exception
     */
    public function euclidDistance($a, $b, $len)
    {
        try {
            $c = sqrt(pow(2, $len))
                / pow(4, $len);   // content
            $sum = 0;
            foreach($a as $key => $val) {
                $sum += pow($val-$b[$key],2);
            }
            $result = $c * sqrt($sum);
            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Generates the distance value from array X and array Y
     * Only in case pearson selected
     * Unit test created
     * @param   array   $vals_x     First array for comparaison
     * @param   array   $vals_y     Second array fo comparaison
     * @return  int
     * @throws  \Exception
     */
    public function pearsonDistance($vals_x, $vals_y)
    {
        try {
            $value = 0;
            // normal correlation
            if (sizeof($vals_x) != sizeof($vals_y)) {
                return $value;
            }
            $sum_x = 0;
            $sum_x2 = 0;
            $sum_y = 0;
            $sum_y2 = 0;
            $sum_xy = 0;
            $n = sizeof($vals_x);
            foreach($vals_x as $key => $val){
                $val_x = $val;
                $val_y = $vals_y[$key];
                $sum_x += $val_x;
                $sum_x2 += $val_x * $val_x;
                $sum_y += $val_y;
                $sum_y2 += $val_y * $val_y;
                $sum_xy += $val_x * $val_y;
            }
            // calculate regression
            $tempa = sqrt($sum_y2 - (1 / $n) * $sum_y * $sum_y);
            $tempb = sqrt($sum_x2 - (1 / $n) * $sum_x * $sum_x);
            $tempc = $sum_xy - (1 / $n) * $sum_x * $sum_y;
            $regresion = $tempc / ($tempb * $tempa);
            if ($regresion > 0.999999999) {
                $regresion = 1;
            }      // round data
            $value = 1 - $regresion;
            return $value;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Feeds the oligo array
     * No Unit Test : private access
     * @param   string      $theseq
     * @param   int         $iteration
     * @return  array
     * @throws  \Exception
     */
    private function iterateOligo($theseq, $iteration)
    {
        try {
            $oligos = [];
            $i = 0;
            $len = strlen($theseq) - $iteration + 1;
            while($i < $len) {
                $seq = substr($theseq, $i,$iteration);
                if(isset($oligos[$seq])) {
                    $oligos[$seq]++;
                } else {
                    $oligos[$seq] = 1;
                }
                $i++;
            }
            return $oligos;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * As described by Teeling et al. BMC Bioinformatics 2004, 5:163.
     * Unit test created
     * @param $theseq
     * @return mixed
     * @throws \Exception
     */
    public function computeZscoresForTetranucleotides($theseq)
    {
        try {
            $theseq .= " ".$this->revCompDNA($theseq);

            $oligos2 = $this->iterateOligo($theseq, 2);
            $oligos3 = $this->iterateOligo($theseq, 3);
            $oligos4 = $this->iterateOligo($theseq, 4);

            $zscore = $this->oligosManager->findZScore($oligos2, $oligos3, $oligos4);

            return $zscore;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Generates array of frequencies
     * Unit test created
     * @param       array   $array
     * @param       int     $len
     * @return      mixed
     * @throws      \Exception
     */
    public function standardFrecuencies($array, $len)
    {
        try {
            $sum = 0;
            foreach($array as $k => $v) {
                $sum += $v;
            }
            $c = pow(4, $len) / $sum;
            foreach($array as $k => $v) {
                $array[$k] = $c * $v;
            }
            return $array;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Perform UPGMA Clustering
     * in each loop, array $data is reduced (one case per loop)
     * @param $data
     * @param $method
     * @param $len
     * @param $dendogramFile
     * @throws \Exception
     */
    public function upgmaClustering($data, $method, $len, $dendogramFile)
    {
        try {
            while (sizeof($data) > 1) {
                $min = $this->minArray($data);
                $comp[$this->x][$this->y] = $min;
                $data = $this->newArray($data);
            }

            $min = $this->minArray($data);

            $x = $this->x;
            $y = $this->y;

            /*
             * end of clustering
             * array $comp stores the important data
             */
            $comp[$x][$y] = $min;

            /*
             * $textcluster is the results of the cluster as text.
             * p.e.:  ((3,4),7),(((5,6),1),2)
             */
            $textcluster = $x.",".$y;


            // CREATE THE IMAGE WITH THE DENDROGRAM
            $this->createDendrogram($textcluster, $comp, $dendogramFile, $method, $len);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}