<?php
/**
 * MicroarrayAnalysisAdaptive
 * Inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 28 march 2019
 */
namespace MinitoolsBundle\Service;

use AppBundle\Service\MathematicsManager;

/**
 * Class MicroarrayAnalysisAdaptiveManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class MicroarrayAnalysisAdaptiveManager
{
    private $oMathematicsManager;

    /**
     * MicroarrayAnalysisAdaptiveManager constructor.
     * @param MathematicsManager $oMathematicsManager
     */
    public function __construct(MathematicsManager $oMathematicsManager)
    {
        $this->oMathematicsManager = $oMathematicsManager;
    }

    /**
     * Processes the Microarray data
     * @param       string      $file
     * @return      array
     * @throws      \Exception
     */
    public function processMicroarrayDataAdaptiveQuantificationMethod($file)
    {
        try {
            $aResults = [];

            $aData = $this->fileToArray($file);

            $iSumCh1 = 0;
            $iSumCh2 = 0;

            $aData2 = $this->createBackground($aData, $iSumCh1, $iSumCh2);
            $aData3 = $this->computeDataBackground($aData2, $iSumCh1, $iSumCh2);
            $aData4 = $this->computeRatios($aData3);
            ksort($aData4);

            if(!empty($aData4)) {
                foreach($aData4 as $key => $val) {
                    $aResults[$key]["n_data"] = count($aData4[$key][1]);
                    $aResults[$key]["median1"] = $this->oMathematicsManager->median($aData4[$key][1]);
                    $aResults[$key]["medlog1"] = round(log10($aResults[$key]["median1"]),3);
                    $aResults[$key]["median2"] = $this->oMathematicsManager->median($aData4[$key][2]);
                    $aResults[$key]["medlog2"] = round(log10($aResults[$key]["median2"]),3);
                }
            }

            return $aResults;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses the data and return array
     * @param   string      $file
     * @return  array
     */
    private function fileToArray($file)
    {
        // find data for first column and row, and remove all headings;
        $file = substr($file, strpos($file,"1\t\t1\t"));
        // remove from file returns (\r) and (\")
        $file = preg_replace("/\r|\"/","",$file);

        // split file into lines ($data_array)
        $aData = preg_split("/\n/",$file, -1, PREG_SPLIT_NO_EMPTY);

        return $aData;
    }

    /**
     * Compute data-background (save result in $aData2) and
     * sum of all data-background ($iSumCh1 and $iSumCh2)
     * Example of line to be splitted:
     * 1        2        G16        1136        159        538        118
     * where        1 and 2 define position in the plate
     *              G16 is name of gene/experiment
     *              1136 is reading of chanel 1, and 159 is the background
     *              538 is reading of chanel 2, and 159 is the background
     * @param   array   $aData
     * @param   int     $iSumCh1
     * @param   int     $iSumCh2
     * @return  array
     */
    private function createBackground($aData, &$iSumCh1, &$iSumCh2)
    {
        $aData2 = [];
        if(!empty($aData)) {
            foreach($aData as $key => $val) {
                $aLineElement = preg_split("/\t/",$val, -1, PREG_SPLIT_NO_EMPTY);
                if (sizeof ($aLineElement) < 7) {
                    continue;
                }
                $sName = $aLineElement[2]; // This is the name of the gene studied

                // For chanel 1
                // calculate data obtained in chanel 1 minus background
                $iCh1Bg = $aLineElement[3] - $aLineElement[4];
                // save data to a element in $aData2 (separate different calculations from the same gene with commas)
                $aData2[$sName][1][] = $iCh1Bg;
                $iSumCh1 += $iCh1Bg; // $sum_ch1 will record the sum of all (chanel 1 - background) values

                // For chanel 2
                // calculate data obtained in chanel 2 minus background
                $iCh2Bg = $aLineElement[5] - $aLineElement[6];
                // save data to a element in $data_array2 (separate different calculations from the same gene with commas)
                $aData2[$sName][2][] = $iCh2Bg;
                $iSumCh2 += $iCh2Bg; // $sum_ch1 will record the sum of all (chanel 2 - background) values
            }
        }
        return $aData2;
    }

    /**
     * Compute (data-background)*100/sum(data-background)),
     * where sum(data-background) is $iSumCh1 or $iSumCh2
     * and save data in $aData3
     * @param   array   $aData2
     * @param   int     $iSumCh1
     * @param   int     $iSumCh2
     * @return  array
     */
    private function computeDataBackground($aData2, $iSumCh1, $iSumCh2)
    {
        $aData3 = [];
        if(!empty($aData2)) {
            foreach($aData2 as $key => $val) {
                // split data separated by comma (chanel 1)
                foreach($aData2[$key][1] as $key2 => $value) {
                    $ratio = $value * 100 / $iSumCh1; // compute ratios
                    $aData3[$key][1][] = $ratio; // save result
                }

                // split data separated by comma (chanel 2)
                foreach($aData2[$key][2] as $key2 => $value) {
                    $ratio = $value * 100 / $iSumCh2; // compute ratios
                    $aData3[$key][2][] = $ratio; // save result
                }
            }
        }
        return $aData3;
    }

    /**
     * Compute ratios for values in chanel 1 and chanel 2
     * chanel 1/chanel 2  and  chanel 2/chanel 1
     * save results to $aData4
     * @param   array   $aData3
     * @return  array
     */
    private function computeRatios($aData3)
    {
        $aData4 = [];
        foreach($aData3 as $key => $val) {
            foreach ($aData3[$key][1] as $key2 => $value) {
                $ratio = $aData3[$key][1][$key2] / $aData3[$key][2][$key2]; //compute ch1 / ch2
                $aData4[$key][1][] = $ratio; // and save
                $ratio = $aData3[$key][2][$key2] / $aData3[$key][1][$key2]; //compute ch2 / ch1
                $aData4[$key][2][] = $ratio; // and save
            }
        }
        return $aData4;
    }
}