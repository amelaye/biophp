<?php
/**
 * Mathematics Functions
 * Inspired by BioPHP's project biophp.org
 * Created 28 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Service;

/**
 * Class MathematicsManager
 * @package Amelaye\BioPHP\Domain\Tools\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class MathematicsFunctions
{
    /**
     * Calculates the mean
     * @param       array       $data
     * @return      float|int
     * @throws      \Exception
     */
    public static function Mean(array $data) {
        $sum = 0;
        $numValidElements = 0;

        foreach($data as $key => $val) {
            if(isset($val)) {
                $sum += $val;
                $numValidElements += 1;
            }
        }
        if ($numValidElements === 0) {
            throw new \Exception("Cannot calculate the mean of an empty data set !");
        }
        $mean = $sum / $numValidElements;
        $mean = round ($mean,3);
        return $mean;
    }


    /**
     * Calculates the median
     * @param       array       $data
     * @return      float|int
     * @throws      \Exception
     */
    public static function Median(array $data) {
        sort($data);
        $i = floor(sizeof($data)/2);
        if (sizeof($data) / 2 != $i) {
            return $data[$i];
        }
        return($data[$i-1] + $data[$i])/2;
    }


    /**
     * Calculates the variance
     * @param       array       $data
     * @return      float|int
     * @throws      \Exception
     */
    public static function Variance(array $data) {
        // Mean() rounds its result to 3 decimals for display purposes: reusing that rounded
        // figure here would bias every squared deviation below. The mean used internally is
        // computed unrounded instead; only the final variance is rounded, exactly as Mean()
        // rounds only its own final output.
        $sum = 0;
        $numValidElements = 0;
        foreach($data as $key => $val) {
            if(isset($val)) {
                $sum += $val;
                $numValidElements += 1;
            }
        }
        if ($numValidElements === 0) {
            throw new \Exception("Cannot calculate the mean of an empty data set !");
        }
        $mean = $sum / $numValidElements;

        $sum = 0;
        $numValidElements = 0;
        foreach($data as $key => $val) {
            if(isset($val)) {
                $tmp = $val - $mean;
                $sum += $tmp * $tmp;
                $numValidElements += 1;
            }
        }

        if ($numValidElements <= 1) {
            throw new \Exception("Cannot calculate the variance with fewer than 2 valid elements !");
        }
        $variance = $sum / ( $numValidElements - 1 );
        $variance = round($variance,3);
        return $variance;
    }

    /**
     * Pearson distance (1 - r) between two series of values sharing the same keys. A correlation
     * that only differs from 1 by rounding noise is taken as exactly 1, so identical series give 0.
     * @param       array       $aValsX     Values for X
     * @param       array       $aValsY     Values for Y, indexed like $aValsX
     * @return      float
     * @throws      \InvalidArgumentException  When the series do not have the same size
     * @throws      \DivisionByZeroError       When one of the series is constant
     */
    public static function PearsonDistance(array $aValsX, array $aValsY) : float {
        if (count($aValsX) !== count($aValsY)) {
            throw new \InvalidArgumentException("Both series must have the same size.");
        }

        $fSumX = $fSumX2 = $fSumY = $fSumY2 = $fSumXY = 0;
        $iN = count($aValsX);
        foreach ($aValsX as $sKey => $fValX) {
            $fValY = $aValsY[$sKey];
            $fSumX += $fValX;
            $fSumX2 += $fValX * $fValX;
            $fSumY += $fValY;
            $fSumY2 += $fValY * $fValY;
            $fSumXY += $fValX * $fValY;
        }

        $fTempA = sqrt($fSumY2 - (1 / $iN) * $fSumY * $fSumY);
        $fTempB = sqrt($fSumX2 - (1 / $iN) * $fSumX * $fSumX);
        $fTempC = $fSumXY - (1 / $iN) * $fSumX * $fSumY;
        $fRegression = $fTempC / ($fTempB * $fTempA);
        if ($fRegression > 0.999999999) {
            $fRegression = 1;
        }

        return (float) (1 - $fRegression);
    }

    /**
     * Euclidean distance between two oligonucleotide frequency tables, scaled by the table size
     * (Wang et al, Gene 2005; 346:173-185)
     * @param       array       $aValsX     Frequencies of the first sequence, by oligonucleotide
     * @param       array       $aValsY     Frequencies of the second sequence, by oligonucleotide
     * @param       int         $iOligoLen  Length of the oligonucleotides
     * @return      float
     */
    public static function EuclideanDistance(array $aValsX, array $aValsY, int $iOligoLen) : float {
        $fScale = sqrt(pow(2, $iOligoLen)) / pow(4, $iOligoLen);
        $fSum = 0;
        foreach ($aValsX as $sKey => $fVal) {
            $fSum += pow($fVal - $aValsY[$sKey], 2);
        }

        return (float) ($fScale * sqrt($fSum));
    }

    /**
     * Distance between two oligonucleotide frequency tables, based on a modified Pearson correlation
     * (Almeida et al, 2001) http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=Retrieve&db=pubmed&dopt=Abstract&list_uids=11331237
     * @param       array       $aValsX     Values for X
     * @param       array       $aValsY     Values for Y, indexed like $aValsX
     * @return      float
     * @throws      \InvalidArgumentException  When the tables do not have the same size
     */
    public static function AlmeidaDistance(array $aValsX, array $aValsY) : float {
        if (count($aValsX) !== count($aValsY)) {
            throw new \InvalidArgumentException("Both tables must have the same size.");
        }

        $fNw = $fX2y = $fXy2 = $fPreSx = $fPreSy = $fPreRw = 0;
        foreach ($aValsX as $sKey => $fValX) {
            $fValY = $aValsY[$sKey];
            $fNw += $fValX * $fValY;
            $fX2y += $fValX * $fValX * $fValY;
            $fXy2 += $fValX * $fValY * $fValY;
        }
        $fXw = $fX2y / $fNw;
        $fYw = $fXy2 / $fNw;
        foreach ($aValsX as $sKey => $fValX) {
            $fValY = $aValsY[$sKey];
            $fPreSx += pow($fValX - $fXw, 2) * $fValX * $fValY;
            $fPreSy += pow($fValY - $fYw, 2) * $fValX * $fValY;
        }
        $fSx = $fPreSx / $fNw;
        $fSy = $fPreSy / $fNw;
        foreach ($aValsX as $sKey => $fValX) {
            $fValY = $aValsY[$sKey];
            $fPreRw += ($fValX - $fXw) * ($fValY - $fYw) * $fValX * $fValY / (sqrt($fSx) * sqrt($fSy));
        }
        $fRw = $fPreRw / $fNw;

        return round(1 - $fRw, 8);
    }
}
