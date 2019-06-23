<?php
/**
 * DistanceAmongSequencesManager
 * Freely inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 29 june 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

use AppBundle\Service\OligosManager;
use MinitoolsBundle\Entity\DistanceAmongSequences;

/**
 * Class DistanceAmongSequencesManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 * @todo : la classe n'est pas finie !
 */
class DistanceAmongSequencesManager
{
    /**
     * @var array
     */
    private $dnaComplements;

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
    private $min = null;

    /**
     * @var int
     */
    private $cases = null;

    private $oligosManager;

    /**
     * DistanceAmongSequencesManager constructor.
     * @param array $dnaComplements
     * @param OligosManager $oligosManager
     */
    public function __construct(array $dnaComplements, OligosManager $oligosManager)
    {
        $this->dnaComplements = $dnaComplements;
        $this->oligosManager = $oligosManager;
    }

    public function getX()
    {
        return $this->x;
    }

    public function getY()
    {
        return $this->y;
    }

    public function getMin()
    {
        return $this->min;
    }

    public function getCases()
    {
        return $this->cases;
    }

    /**
     * Get the name of each sequence (save names to array $seq_name)
     * @param $seqs
     * @return array[]|false|string[]
     */
    public function formatSequences($seqs)
    {
        $seqs = preg_split("/>/", $seqs,-1,PREG_SPLIT_NO_EMPTY);
        foreach ($seqs as $key => $val) {
            $seq_name[$key] = substr($val,0,strpos($val,"\n"));
            $temp_val = substr($val,strpos($val,"\n"));
            $temp_val = preg_replace("/\W|\d/","",$temp_val);
            $seqs[$key] = strtoupper($temp_val);
        }
        return $seqs;
    }

    /**
     * COMPUTE OLIGONUCLEOTIDE FREQUENCIES
     * @param $seqs
     * @param $len
     * @return mixed
     * @throws \Exception
     */
    public function computeOligonucleotidsFrequenciesEuclidean($seqs, $len)
    {
        foreach ($seqs as $key => $val) {
            // to compute oligonucleotide frequencies, both strands are used
            $valRevert = strrev($val);
            foreach ($this->dnaComplements as $nucleotide => $complement) {
                $valRevert = str_replace($nucleotide, strtolower($complement), $valRevert);
            }
            $seq_and_revseq = $val." ".strtoupper($valRevert);

            $oligos = $this->oligosManager->findOligos(
                $seq_and_revseq,
                $len,
                array_values($this->dnaComplements)
            );

            $oligo_array[$key] = $this->standardFrecuencies($oligos, $len);
        }
        return $oligo_array;
    }

    /**
     * COMPUTE OLIGONUCLEOTIDE FREQUENCIES
     * @param $seqs
     * @return mixed
     * @throws \Exception
     */
    public function computeOligonucleotidsFrequencies($seqs)
    {
        foreach ($seqs as $key => $theseq) {
            $aComputeZscores = $this->computeZscoresForTetranucleotides($theseq);
            $oligo_array[$key] = $this->oligosManager->findOligos(
                $aComputeZscores,
                4,
                array_values($this->dnaComplements)
            );
        }
        return $oligo_array;
    }

    /**
     * COMPUTE DISTANCES AMONG SEQUENCES
     * by computing Euclidean distance
     *  standarized oligonucleotide frequencies in $oligo_array are used, and distances are stored in $data array
     * @param $seqs
     * @param $oligo_array
     * @param $len
     * @return mixed
     * @throws \Exception
     */
    public function computeDistancesAmongFrequenciesEuclidean($seqs, $oligo_array, $len)
    {
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
    }

    /**
     * COMPUTE DISTANCES AMONG SEQUENCES
     * by computing Pearson distance
     * standarized oligonucleotide frequencies in $oligo_array are used, and distances are stored in $data array
     * @param $seqs
     * @param $oligo_array
     * @return mixed
     * @throws \Exception
     */
    public function computeDistancesAmongFrequencies($seqs, $oligo_array)
    {
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
                settype($this->getX(), "string");
                settype($this->getY(), "string");

                if($key == $this->getX() || $key == $this->getY()) {
                    continue;
                }
                if($a[$key][$this->getX()] != "") {
                    if($a[$key][$this->getY()] != "") {
                        $temp_a[$key]["($this->getX(),$this->getY())"] = ($a[$key][$this->getX()]+$a[$key][$this->getY()])/2;
                    }
                    if($a[$this->getX()][$key] != "") {
                        $temp_a[$key]["($this->getX(),$this->getY())"] = ($a[$key][$this->getX()]+$a[$this->getX()][$key])/2;
                    }
                    if($a[$this->getY()][$key] != "") {
                        $temp_a[$key]["($this->getX(),$this->getY())"] = ($a[$key][$this->getX()]+$a[$this->getY()][$key])/2;
                    }
                } else {
                    if($a[$key][$this->getY()] != "") {
                        if ($a[$this->getX()][$key] != "") {
                            $temp_a[$key]["($this->getX(),$this->getY())"] = ($a[$key][$this->getY()]+$a[$this->getX()][$key])/2;
                        }
                        if($a[$this->getY()][$key] != "") {
                            $temp_a[$key]["($this->getX(),$this->getY())"] = ($a[$key][$this->getY()]+$a[$this->getY()][$key])/2;
                        }
                    } else {
                        if($a[$this->getY()][$key] != "") {
                            $temp_a[$key]["($this->getX(),$this->getY())"] = ($a[$this->getY()][$key]+$a[$this->getY()][$key])/2;
                        }
                    }
                }

                for($i = $j+1; $i < sizeof($cases); $i++) {
                    $key2 = $cases[$i];
                    settype($key2, "string");
                    if ($key == $key2 || $key2 == $this->getX() || $key2 == $this->getY()) {
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
            $j = 0.1;
            imageline($im, log($j+1)*$f+20, $y, log($j+1)*$f+20, $y+10, $black);
            imagestring($im, 1, log($j+1)*$f-8+20, $y+12,  $j, $black);

            $j = 0.2;
            imageline($im, log($j+1)*$f+20, $y, log($j+1)*$f+20, $y+10, $black);
            imagestring($im, 1, log($j+1)*$f-8+20, $y+12,  $j, $black);

            $j = 0.3;
            imageline($im, log($j+1)*$f+20, $y, log($j+1)*$f+20, $y+10, $black);
            imagestring($im, 1, log($j+1)*$f-8+20, $y+12,  $j, $black);

            $j = 0.5;
            imageline($im, log($j+1)*$f+20, $y, log($j+1)*$f+20, $y+10, $black);
            imagestring($im, 1, log($j+1)*$f-8+20, $y+12,  $j, $black);

            $j = 1.0;
            imageline($im, log($j+1)*$f+20, $y, log($j+1)*$f+20, $y+10, $black);
            imagestring($im, 1, log($j+1)*$f-8+20, $y+12,  "1.0", $black);

            $j = 1.5;
            imageline($im, log($j+1)*$f+20, $y, log($j+1)*$f+20, $y+10, $black);
            imagestring($im, 1, log($j+1)*$f-8+20, $y+12,  $j, $black);

            $j = 2.0;
            imageline($im, log($j+1)*$f+20, $y, log($j+1)*$f+20, $y+10, $black);
            imagestring($im, 1, log($j+1)*$f-8+20, $y+12,  "2.0", $black);

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
     * @param $a
     * @param $b
     * @return float|int
     * @throws \Exception
     */
    public function euclidDistance($a,$b,$len)
    {
        try {
            // Wang et al, Gene 2005; 346:173-185
            $c = sqrt(pow(2, $len))
                / pow(4, $len);   // content
            $sum = 0;
            foreach($a as $key => $val) {
                $sum += pow($val-$b[$key],2);
            }
            return $c * sqrt($sum);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * @param $vals_x
     * @param $vals_y
     * @return int | void
     * @throws \Exception
     */
    public function pearsonDistance($vals_x, $vals_y)
    {
        try {
            // normal correlation
            if (sizeof($vals_x) != sizeof($vals_y)) {
                return;
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
                $sum_x2 += $val_x*$val_x;
                $sum_y += $val_y;
                $sum_y2 += $val_y*$val_y;
                $sum_xy += $val_x*$val_y;
            }
            // calculate regression
            $regresion = ($sum_xy-(1/$n)*$sum_x*$sum_y)/((sqrt($sum_x2-(1/$n)*$sum_x*$sum_x)*(sqrt($sum_y2-(1/$n)*$sum_y*$sum_y))));
            if ($regresion > 0.999999999) {
                $regresion = 1;
            }      // round data
            return (1 - $regresion);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * @param $theseq
     * @return mixed
     * @throws \Exception
     */
    public function computeZscoresForTetranucleotides($theseq)
    {
        try {
            $oligos2 = [];
            $oligos3 = [];
            $oligos4 = [];
            // as described by Teeling et al. BMC Bioinformatics 2004, 5:163.
            $theseq .= " ".$this->revComp($theseq);
            $i = 0;
            $len = strlen($theseq)-2+1;
            while($i<$len) {
                $seq = substr($theseq, $i,2);
                $oligos2[$seq]++;
                $i++;
            }
            $i = 0;
            $len = strlen($theseq)-3+1;
            while ($i<$len) {
                $seq = substr($theseq, $i,3);
                $oligos3[$seq]++;
                $i++;
            }
            $i = 0;
            $len = strlen($theseq)-4+1;
            while($i < $len) {
                $seq = substr($theseq, $i,4);
                $oligos4[$seq]++;
                $i++;
            }

            $zscore = $this->oligosManager->findZScore($this->dnaComplements, $oligos2, $oligos3, $oligos4);

            return $zscore;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * @param $array
     * @return mixed
     * @throws \Exception
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
        while (sizeof($data) > 1) {
            $min = $this->minArray($data);
            $comp[$this->getX()][$this->getY()] = $min;
            $data = $this->newArray($data);
        }

        $min = $this->minArray($data);

        $x = $this->getX();
        $y = $this->getY();

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
    }
}