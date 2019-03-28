<?php
/**
 * Mathematics Functions
 * Inspired by BioPHP's project biophp.org
 * Created 28 march  2019
 * Last modified 28 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace AppBundle\Service;

/**
 * Class MathematicsManager
 * @package AppBundle\Service
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class MathematicsManager
{
    /**
     * Calculates the mean
     * @param       array       $data
     * @return      float|int
     * @throws      \Exception
     */
    public function mean($data)
    {
        try {
            $sum = 0;
            $numValidElements = 0;

            foreach($data as $key => $val) {
                if(isset($val)) {
                    $sum += $val;
                    $numValidElements += 1;
                }
            }
            $mean = $sum / $numValidElements;
            $mean = round ($mean,3);
            return $mean;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Calculates the median
     * @param       array       $data
     * @return      float|int
     * @throws      \Exception
     */
    public function median($data)
    {
        try {
            sort($data);
            $i = floor(sizeof($data)/2);
            if (sizeof($data) / 2 != $i) {
                return $data[$i];
            }
            return($data[$i-1] + $data[$i])/2;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Calculates the variance
     * @param       array       $data
     * @return      float|int
     * @throws      \Exception
     */
    public function variance($data)
    {
        try {
            $mean = $this->mean($data);
            $sum = 0;
            $numValidElements = 0;

            foreach($data as $key => $val) {
                if(isset($val)) {
                    $tmp = $val - $mean;
                    $sum += $tmp * $tmp;
                    $numValidElements += 1;
                }
            }

            $variance = $sum / ( $numValidElements - 1 );
            $variance = round($variance,3);
            return $variance;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}