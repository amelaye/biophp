<?php
/**
 * Restrictions Digest  Functions
 * Inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Modified 27 february 2019 - RIP Pasha =^._.^= ∫
 * last modified 19 april 2019
 */
namespace MinitoolsBundle\Service;

/**
 * Class RestrictionDigestManager
 * @package MinitoolsBundle\Service
 * @author Amelie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class RestrictionDigestManager
{
    /**
     * Remove from the list of endonucleases the ones not matching the criteria in the form:
     * $minimum, $retype and $defined_sq
     * @param       array       $aEnzymes
     * @param       int         $iMinimun
     * @param       int         $iRetype
     * @param       bool        $bDefinedSq
     * @param       string      $sWre
     * @return      mixed
     * @throws      \Exception
     */
    public function reduceEnzymesArray($aEnzymes, $iMinimun, $iRetype, $bDefinedSq, $sWre)
    {
        try {
            $aNewEnzymes = [];
            // if $wre not null => all endonucleases but the selected one must be removed
            if($sWre != null) {
                foreach($aEnzymes as $key => $val) {
                    if (strpos(" ,".$aEnzymes[$key][0].",",$sWre) > 0) {
                        $aNewEnzymes[$sWre] = $aEnzymes[$key];
                        return $aNewEnzymes;
                    }
                }
            }
            // remove endonucleases which do not match requeriments
            foreach($aEnzymes as $enzyme => $val) {
                if ($iRetype == 1 && $aEnzymes[$enzyme][5] != 0) {
                    continue; // if retype==1 -> only Blund ends (continue for rest)
                }
                if ($iRetype == 2 && $aEnzymes[$enzyme][5] == 0) {
                    continue; // if retype==2 -> only Overhang end (continue for rest)
                }
                if ($iMinimun > $aEnzymes[$enzyme][6]) {
                    continue; // Only endonucleases with which recognized in template a minimum of bases (continue for rest)
                }
                if ($bDefinedSq == 1) {
                    if (strpos($aEnzymes[$enzyme][2],".") > 0 || strpos($aEnzymes[$enzyme][2],"|") > 0) {
                        continue; // if defined sequence selected, no N (".") or "|" in pattern
                    }
                }
                $aNewEnzymes[$enzyme] = $aEnzymes[$enzyme];
            }
            return $aNewEnzymes;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Calculate digestion results - will return an array like this
     * $digestion[$enzyme]["cuts"] - with number of cuts within the sequence
     * @param       array       $aEnzymes   List of available enzymes
     * @param       string      $sSequence  Sequence to analyze
     * @return      array
     * @throws      \Exception
     */
    public function restrictionDigest($aEnzymes, $sSequence)
    {
        try {
            $aDigestion = [];
            foreach ($aEnzymes as $sEnzyme => $aVal) {
                // this is to put together results for IIb endonucleases, which are computed as "enzyme_name" and "enzyme_name@"
                $aNewEnzyme = str_replace("@","", $sEnzyme);

                // split sequence based on pattern from restriction enzyme
                $aFragments = preg_split("/".$aEnzymes[$sEnzyme][2]."/", $sSequence,-1,PREG_SPLIT_DELIM_CAPTURE);
                reset($aFragments);
                $iMaxFragments = sizeof($aFragments);

                // when sequence is cleaved ($iMaxFragments > 1) start further calculations
                if($iMaxFragments > 1) {
                    $iRecognitionPosition = strlen($aFragments[0]);
                    // for each frament generated, calculate cleavage position,
                    // add it to a list, and add 1 to counter
                    for($i = 2; $i < $iMaxFragments; $i += 2) {
                        $iCleavagePosition = $iRecognitionPosition + $aEnzymes[$sEnzyme][4];
                        $aDigestion[$aNewEnzyme]["cuts"][$iCleavagePosition] = "";

                        // As overlapping may occur for many endonucleases,
                        // a subsequence starting in position 2 of fragment is calculate
                        if(isset($aFragments[$i+1])) {
                            $sSubSequence = substr($aFragments[$i-1],1)
                                .$aFragments[$i]
                                .substr($aFragments[$i+1],0,40);
                        } else {
                            $sSubSequence = substr($aFragments[$i-1],1) . $aFragments[$i];
                        }

                        $sSubSequence = substr($sSubSequence,0,2 * $aEnzymes[$sEnzyme][3] - 2);
                        // Previous process is repeated
                        // split subsequence based on pattern from restriction enzyme
                        $aFragmentsSubsequence = preg_split($aEnzymes[$sEnzyme][2],$sSubSequence);
                        // when subsequence is cleaved start further calculations
                        if(sizeof($aFragmentsSubsequence) > 1) {
                            // for each fragment of subsequence, calculate overlapping cleavage position,
                            //    add it to a list, and add 1 to counter
                            $iOverlappedCleavage = $iRecognitionPosition + 1 + strlen($aFragmentsSubsequence[0]) + $aEnzymes[$sEnzyme][4];
                            $aDigestion[$aNewEnzyme]["cuts"][$iOverlappedCleavage]="";
                        }
                        // this is a counter for position
                        $iRecognitionPosition += strlen($aFragments[$i-1]) + strlen($aFragments[$i]);
                    }
                }
            }
            return $aDigestion;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Extract sequences, which will be stored in an array
     * @param   string      $sSequence
     * @return  array
     * @throws  \Exception
     */
    public function extractSequences($sSequence)
    {
        try {
            $aSequence = [];
            if (substr_count($sSequence,">") == 0) {
                $aSequence[0]["seq"] = preg_replace("/\W|\d/", "", strtoupper($sSequence));
            } else {
                $aExtractSequences = preg_split("/>/", $sSequence,-1,PREG_SPLIT_NO_EMPTY);
                $iCounter = 0;
                foreach($aExtractSequences as $key => $val) {
                    $sSeq = substr($val,strpos($val,"\n"));
                    $sSeq = preg_replace ("/\W|\d/", "", strtoupper($sSeq));
                    if (strlen($sSeq)>0){
                        $aSequence[$iCounter]["seq"] = $sSeq;
                        $aSequence[$iCounter]["name"] = substr($val,0,strpos($val,"\n"));
                        $iCounter++;
                    }
                }
            }
            return $aSequence;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * @param $company
     * @param $enzyme
     * @return string
     * @todo better integrate in twig
     */
    public function showVendors($company, $enzyme)
    {
        $company = " ".$company;
        $sMessage = '<b>'.$enzyme.'</b><a href="http://rebase.neb.com/rebase/enz/'.$enzyme.'.html">REBASE</a>\n<pre>';
        if(strpos($company,"C") > 0) {
            $sMessage .= ' <a href="http://www.minotech.gr">Minotech Biotechnology</a>\n';
        }
        if(strpos($company,"E") > 0) {
            $sMessage .= ' <a href="http://www.stratagene.com">Stratagene</a>\n';
        }
        if(strpos($company,"F") > 0) {
            $sMessage .= ' <a href="http://www.fermentas.com/catalog/re/'.$re.'.htm">Fermentas AB</a>\n';
        }
        if(strpos($company,"H") > 0) {
            $sMessage .= ' <a href="http://www.aablabs.com/">American Allied Biochemical, Inc.</a>\n';
        }
        if(strpos($company,"I") > 0) {
            $sMessage .= ' <a href="http://www.sibenzyme.com">SibEnzyme Ltd.</a>\n';
        }
        if(strpos($company,"J") > 0) {
            $sMessage .= ' <a href="http://www.nippongene.jp/">Nippon Gene Co., Ltd.</a>\n';
        }
        if(strpos($company,"K") > 0) {
            $sMessage .= ' <a href="http://www.takarashuzo.co.jp/english/index.htm">Takara Shuzo Co. Ltd.</a>\n';
        }
        if(strpos($company,"M") > 0) {
            $sMessage .= ' <a href="http://www.roche.com">Roche Applied Science</a>\n';
        }
        if(strpos($company,"N") > 0) {
            $sMessage .= ' <a href="http://www.neb.com">New England Biolabs</a>\n';
        }
        if(strpos($company,"O") > 0) {
            $sMessage .= ' <a href="http://www.toyobo.co.jp/e/">Toyobo Biochemicals</a>\n';
        }
        if(strpos($company,"P") > 0) {
            $sMessage .= ' <a href="http://www.cvienzymes.com/">Megabase Research Products</a>\n';
        }
        if(strpos($company,"Q") > 0) {
            $sMessage .= ' <a href="http://www.CHIMERx.com">CHIMERx</a>\n';
        }
        if(strpos($company,"R") > 0) {
            $sMessage .= ' <a href="http://www.promega.com">Promega Corporation</a>\n';
        }
        if(strpos($company,"S") > 0) {
            $sMessage .= ' <a href="http://www.sigmaaldrich.com/">Sigma Chemical Corporation</a>n\n';
        }
        if(strpos($company,"U") > 0) {
            $sMessage .= ' <a href="http://www.bangaloregenei.com/">Bangalore Genei</a>\n';
        }
        if(strpos($company,"V") > 0) {
            $sMessage .= ' <a href="http://www.mrc-holland.com">MRC-Holland</a>\n';
        }
        if(strpos($company,"X") > 0) {
            $sMessage .= ' <a href="http://www.eurx.com.pl/index.php?op=catalog&cat=8">EURx Ltd.</a>\n';
        }
        $sMessage .= "</pre>";
        return $sMessage;
    }
}