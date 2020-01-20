<?php
/**
 * Oligo-Nucleotids Functions
 * Inspired by BioPHP's project biophp.org
 * Created 9 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 * Last modified 18 january 2020
 */
namespace App\Domain\Tools\Service;

use Amelaye\BioPHP\Api\Interfaces\NucleotidApiAdapter;
use App\Domain\Tools\Interfaces\OligosInterface;

/**
 * Class OligosManager
 * @package App\Domain\Tools\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class OligosManager implements OligosInterface
{
    private $dnaComplements;

    /**
     * OligosManager constructor.
     * @param NucleotidApiAdapter $nucleotidApi
     */
    public function __construct(NucleotidApiAdapter $nucleotidApi)
    {
        $dnaComplements = $nucleotidApi::GetDNAComplement($nucleotidApi->getNucleotids());
        asort($dnaComplements);
        $this->dnaComplements = $dnaComplements;
    }

    /**
     * For oligos 2 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public function findOligos2BasesLong($oligos_1step)
    {
        $base_a = $base_b = $this->dnaComplements;
        $oligos = [];

        foreach($base_a as $key_a => $val_a) {
            foreach($base_b as $key_b => $val_b) {
                if(isset($oligos_1step[$val_a.$val_b])) {
                    $oligos[$val_a.$val_b] = $oligos_1step[$val_a.$val_b];
                } else {
                    $oligos[$val_a.$val_b] = 0;
                }
            }
        }
        return $oligos;
    }

    /**
     * For oligos 3 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public function findOligos3BasesLong($oligos_1step)
    {
        $base_a = $base_b = $base_c = $this->dnaComplements;
        $oligos = [];

        foreach($base_a as $key_a => $val_a) {
            foreach($base_b as $key_b => $val_b) {
                foreach($base_c as $key_c => $val_c) {
                    if(isset($oligos_1step[$val_a.$val_b.$val_c])) {
                        $oligos[$val_a.$val_b.$val_c] = $oligos_1step[$val_a.$val_b.$val_c];
                    } else {
                        $oligos[$val_a.$val_b.$val_c] = 0;
                    }
                }
            }
        }
        return $oligos;
    }

    /**
     * For oligos 4 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public function findOligos4BasesLong($oligos_1step)
    {
        $base_a = $base_b = $base_c = $base_d = $this->dnaComplements;
        $oligos = [];

        foreach($base_a as $key_a => $val_a) {
            foreach($base_b as $key_b => $val_b) {
                foreach($base_c as $key_c => $val_c) {
                    foreach($base_d as $key_d => $val_d) {
                        if(isset($oligos_1step[$val_a.$val_b.$val_c.$val_d])) {
                            $oligos[$val_a.$val_b.$val_c.$val_d] = $oligos_1step[$val_a.$val_b.$val_c.$val_d];
                        } else {
                            $oligos[$val_a.$val_b.$val_c.$val_d] = 0;
                        }
                    }
                }
            }
        }
        return $oligos;
    }

    /**
     * For oligos 5 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public function findOligos5BasesLong($oligos_1step)
    {
        $base_a = $base_b = $base_c = $base_d = $base_e = $this->dnaComplements;
        $oligos = [];

        foreach($base_a as $key_a => $val_a) {
            foreach($base_b as $key_b => $val_b) {
                foreach($base_c as $key_c => $val_c) {
                    foreach($base_d as $key_d => $val_d) {
                        foreach($base_e as $key_e => $val_e) {
                            if(isset($oligos_1step[$val_a.$val_b.$val_c.$val_d.$val_e])) {
                                $oligos[$val_a.$val_b.$val_c.$val_d.$val_e] = $oligos_1step[$val_a.$val_b.$val_c.$val_d.$val_e];
                            } else {
                                $oligos[$val_a.$val_b.$val_c.$val_d.$val_e] = 0;
                            }
                        }
                    }
                }
            }
        }
        return $oligos;
    }

    /**
     * For oligos 6 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public function findOligos6BasesLong($oligos_1step)
    {
        $base_a = $base_b = $base_c = $base_d = $base_e = $base_f = $this->dnaComplements;
        $oligos = [];

        foreach($base_a as $key_a => $val_a) {
            foreach($base_b as $key_b => $val_b) {
                foreach($base_c as $key_c => $val_c) {
                    foreach($base_d as $key_d => $val_d) {
                        foreach($base_e as $key_e => $val_e) {
                            foreach($base_f as $key_f => $val_f) {
                                if(isset($oligos_1step[$val_a.$val_b.$val_c.$val_d.$val_e.$val_f])) {
                                    $oligos[$val_a.$val_b.$val_c.$val_d.$val_e.$val_f] = $oligos_1step[$val_a.$val_b.$val_c.$val_d.$val_e.$val_f];
                                } else {
                                    $oligos[$val_a.$val_b.$val_c.$val_d.$val_e.$val_f] = 0;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $oligos;
    }

    /**
     * For oligos 7 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public function findOligos7BasesLong($oligos_1step)
    {
        $base_a = $base_b = $base_c = $base_d = $base_e = $base_f = $base_g = $this->dnaComplements;
        $oligos = [];

        foreach($base_a as $key_a => $val_a) {
            foreach($base_b as $key_b => $val_b) {
                foreach($base_c as $key_c => $val_c) {
                    foreach($base_d as $key_d => $val_d) {
                        foreach($base_e as $key_e => $val_e) {
                            foreach($base_f as $key_f => $val_f) {
                                foreach($base_g as $key_g => $val_g) {
                                    if(isset($oligos_1step[$val_a.$val_b.$val_c.$val_d.$val_e.$val_f.$val_g])) {
                                        $oligos[$val_a.$val_b.$val_c.$val_d.$val_e.$val_f.$val_g] = $oligos_1step[$val_a.$val_b.$val_c.$val_d.$val_e.$val_f.$val_g];
                                    } else {
                                        $oligos[$val_a.$val_b.$val_c.$val_d.$val_e.$val_f.$val_g] = 0;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $oligos;
    }

    /**
     * For oligos 8 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public function findOligos8BasesLong($oligos_1step)
    {
        $base_a = $base_b = $base_c = $base_d = $base_e = $base_f = $base_g = $base_h = $this->dnaComplements;
        $oligos = [];

        foreach($base_a as $key_a => $val_a) {
            foreach($base_b as $key_b => $val_b) {
                foreach($base_c as $key_c => $val_c) {
                    foreach($base_d as $key_d => $val_d) {
                        foreach($base_e as $key_e => $val_e) {
                            foreach($base_f as $key_f => $val_f) {
                                foreach($base_g as $key_g => $val_g) {
                                    foreach($base_h as $key_h => $val_h) {
                                        if(isset($oligos_1step[$val_a.$val_b.$val_c.$val_d.$val_e.$val_f.$val_g.$val_h])) {
                                            $oligos[$val_a.$val_b.$val_c.$val_d.$val_e.$val_f.$val_g.$val_h] = $oligos_1step[$val_a.$val_b.$val_c.$val_d.$val_e.$val_f.$val_g.$val_h];
                                        } else {
                                            $oligos[$val_a.$val_b.$val_c.$val_d.$val_e.$val_f.$val_g.$val_h] = 0;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $oligos;
    }

    /**
     * Compute frequency of oligonucleotides with length $iOligoLen for sequence $sSequence
     * @param       string      $sSequence
     * @param       int         $iOligoLen
     * @return      array
     * @throws      \Exception
     */
    public function findOligos($sSequence, $iOligoLen)
    {
        try {
            $i              = 0;
            $aOligos1Step   = [];
            $aOligos        = [];

            $iLength = strlen($sSequence) - $iOligoLen + 1;
            while ($i < $iLength) {
                $sMySequence = substr($sSequence, $i, $iOligoLen);

                if (!isset($aOligos1Step[$sMySequence])) {
                    $aOligos1Step[$sMySequence] = 1;
                } else {
                    $aOligos1Step[$sMySequence] ++;
                }
                $i ++;
            }

            switch ($iOligoLen) {
                case 1:
                    foreach($this->dnaComplements as $key => $oligo) {
                        $aOligos[$oligo] = substr_count($sSequence, $oligo);
                    }
                    break;
                case 2:
                    $aOligos = $this->findOligos2BasesLong($aOligos1Step);
                    break;
                case 3:
                    $aOligos = $this->findOligos3BasesLong($aOligos1Step);
                    break;
                case 4:
                    $aOligos = $this->findOligos4BasesLong($aOligos1Step);
                    break;
                case 5:
                    $aOligos = $this->findOligos5BasesLong($aOligos1Step);
                    break;
                case 6:
                    $aOligos = $this->findOligos6BasesLong($aOligos1Step);
                    break;
                case 7:
                    $aOligos = $this->findOligos7BasesLong($aOligos1Step);
                    break;
                case 8:
                    $aOligos = $this->findOligos8BasesLong($aOligos1Step);
                    break;
                default:
                    throwException(new \Exception("Invalid base format ! "));
            }

            return $aOligos;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * COMPUTE Z-SCORES FOR TETRANUCLEOTIDES
     * @param $oligos2
     * @param $oligos3
     * @param $oligos4
     * @return array
     */
    public function findZScore($oligos2, $oligos3, $oligos4)
    {
        $base_a = $base_b = $base_c = $base_d = $base_e = $base_f = $this->dnaComplements;
        $i = 0;
        $zscore = [];
        $exp = [];
        $var = [];
        foreach($base_a as $key_a => $val_a) {
            foreach($base_b as $key_b => $val_b) {
                foreach($base_c as $key_c => $val_c) {
                    foreach($base_d as $key_d => $val_d) {
                        if(!isset($oligos3[$val_b.$val_c.$val_d])) {
                            $oligos3[$val_b.$val_c.$val_d] = null;
                        }
                        $atemp = $oligos3[$val_a.$val_b.$val_c] * $oligos3[$val_b.$val_c.$val_d];

                        if(!isset($oligos2[$val_b.$val_c])) {
                            $oligos2[$val_b.$val_c] = null;
                            $exp[$val_a.$val_b.$val_c.$val_d] = 0;
                        } else {
                            $exp[$val_a.$val_b.$val_c.$val_d] = $atemp / $oligos2[$val_b.$val_c];
                        }


                        $btemp = $oligos2[$val_b.$val_c] - $oligos3[$val_b.$val_c.$val_d];
                        $ctemp = $oligos2[$val_b.$val_c] - $oligos3[$val_a.$val_b.$val_c];

                        if(pow($oligos2[$val_b.$val_c],2) != 0) {
                            $dtemp = ($ctemp * $btemp) / pow($oligos2[$val_b.$val_c],2);
                        } else {
                            $dtemp = 0;
                        }

                        $var[$val_a.$val_b.$val_c.$val_d] = $exp[$val_a.$val_b.$val_c.$val_d] * $dtemp;

                        if(!isset($oligos4[$val_a.$val_b.$val_c.$val_d])) {
                            $oligos4[$val_a.$val_b.$val_c.$val_d] = null;
                        }
                        $etemp = $oligos4[$val_a.$val_b.$val_c.$val_d] - $exp[$val_a.$val_b.$val_c.$val_d];

                        if(isset($var[$val_a.$val_b.$val_c.$val_d]) && sqrt($var[$val_a.$val_b.$val_c.$val_d] != 0)) {
                            $zscore[$i] = $etemp / sqrt($var[$val_a.$val_b.$val_c.$val_d]);
                        }
                        $i ++;
                    }
                }
            }
        }

        return $zscore;
    }
}