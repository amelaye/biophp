<?php
/**
 * Microsatellite Repeats Finder Functions
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 26 february 2019
 * @todo : beaucoup de redondances avec du code d'une autre classe (DistanceAmongSequencies), il faut faire
 * une classe à part pour les oligonucleotides et appeler le service
 */
namespace MinitoolsBundle\Service;

class OligonucleotideFrequencyManager
{
    /**
     * @param $sequence
     * @param $oligo_len
     * @return mixed
     * @throws \Exception
     * @todo : à refactoriser
     */
    public function findOligos($sequence,$oligo_len)
    {
        try {
            $oligos_1step = [];

            //for oligos 1 bases long
            if ($oligo_len == 1) {
                $oligos["A"] = substr_count($sequence,"A");
                $oligos["C"] = substr_count($sequence,"C");
                $oligos["G"] = substr_count($sequence,"G");
                $oligos["T"] = substr_count($sequence,"T");
                return $oligos;
            }

            //for oligos with at least two bases 2 bases
            $i = 0;
            $len = strlen($sequence) - $oligo_len+1;
            while ($i < $len) {
                $seq = substr($sequence,$i,$oligo_len);
                $oligos_1step[$seq]++;
                $i++;
            }

            /*
             * Supprimer à partir de là
             */
            $base_a = array("A","C","G","T");
            $base_b = $base_a;
            $base_c = $base_a;
            $base_d = $base_a;
            $base_e = $base_a;
            $base_f = $base_a;
            $base_g = $base_a;
            $base_h = $base_a;

            // for oligos 2 bases long
            if ($oligo_len == 2) {
                foreach($base_a as $key_a => $val_a) {
                    foreach($base_b as $key_b => $val_b) {
                        if($oligos_1step[$val_a.$val_b]) {
                            $oligos[$val_a.$val_b] = $oligos_1step[$val_a.$val_b];
                        } else {
                            $oligos[$val_a.$val_b] = 0;
                        }
                    }
                }
            }
            // for oligos 3 bases long
            if ($oligo_len == 3) {
                foreach($base_a as $key_a => $val_a) {
                    foreach($base_b as $key_b => $val_b) {
                        foreach($base_c as $key_c => $val_c) {
                            if($oligos_1step[$val_a.$val_b.$val_c]) {
                                $oligos[$val_a.$val_b.$val_c] = $oligos_1step[$val_a.$val_b.$val_c];
                            } else {
                                $oligos[$val_a.$val_b.$val_c] = 0;
                            }
                        }
                    }
                }
            }
            // for oligos 4 bases long
            if ($oligo_len == 4) {
                foreach($base_a as $key_a => $val_a) {
                    foreach($base_b as $key_b => $val_b) {
                        foreach($base_c as $key_c => $val_c) {
                            foreach($base_d as $key_d => $val_d) {
                                if($oligos_1step[$val_a.$val_b.$val_c.$val_d]) {
                                    $oligos[$val_a.$val_b.$val_c.$val_d] = $oligos_1step[$val_a.$val_b.$val_c.$val_d];
                                } else {
                                    $oligos[$val_a.$val_b.$val_c.$val_d] = 0;
                                }
                            }
                        }
                    }
                }
            }
            //for oligos 5 bases long
            if ($oligo_len == 5) {
                foreach($base_a as $key_a => $val_a) {
                    foreach($base_b as $key_b => $val_b) {
                        foreach($base_c as $key_c => $val_c) {
                            foreach($base_d as $key_d => $val_d) {
                                foreach($base_e as $key_e => $val_e) {
                                    if($oligos_1step[$val_a.$val_b.$val_c.$val_d.$val_e]) {
                                        $oligos[$val_a.$val_b.$val_c.$val_d.$val_e] = $oligos_1step[$val_a.$val_b.$val_c.$val_d.$val_e];
                                    }else{
                                        $oligos[$val_a.$val_b.$val_c.$val_d.$val_e] = 0;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            //for oligos 6 bases long
            if ($oligo_len == 6) {
                foreach($base_a as $key_a => $val_a) {
                    foreach($base_b as $key_b => $val_b) {
                        foreach($base_c as $key_c => $val_c) {
                            foreach($base_d as $key_d => $val_d) {
                                foreach($base_e as $key_e => $val_e) {
                                    foreach($base_f as $key_f => $val_f) {
                                        if($oligos_1step[$val_a.$val_b.$val_c.$val_d.$val_e.$val_f]) {
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
            }
            //for oligos 7 bases long
            if ($oligo_len == 7) {
                foreach($base_a as $key_a => $val_a) {
                    foreach($base_b as $key_b => $val_b) {
                        foreach($base_c as $key_c => $val_c) {
                            foreach($base_d as $key_d => $val_d) {
                                foreach($base_e as $key_e => $val_e) {
                                    foreach($base_f as $key_f => $val_f) {
                                        foreach($base_g as $key_g => $val_g) {
                                            if($oligos_1step[$val_a.$val_b.$val_c.$val_d.$val_e.$val_f.$val_g]) {
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
            }
            //for oligos 8 bases long
            if ($oligo_len == 8) {
                foreach($base_a as $key_a => $val_a) {
                    foreach($base_b as $key_b => $val_b) {
                        foreach($base_c as $key_c => $val_c) {
                            foreach($base_d as $key_d => $val_d) {
                                foreach($base_e as $key_e => $val_e) {
                                    foreach($base_f as $key_f => $val_f) {
                                        foreach($base_g as $key_g => $val_g) {
                                            foreach($base_h as $key_h => $val_h) {
                                                if($oligos_1step[$val_a.$val_b.$val_c.$val_d.$val_e.$val_f.$val_g.$val_h]) {
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
            }
            return $oligos;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * computes the reverse complement of a sequence (only ACGT nucleotides are used)
     * @param $seq
     * @return mixed|string
     * @throws \Exception
     * @todo : à supprimer
     */
    /*function revComp($seq)
    {
        try {
            $seq = strrev($seq);
            $seq = str_replace("A", "t", $seq);
            $seq = str_replace("T", "a", $seq);
            $seq = str_replace("G", "c", $seq);
            $seq = str_replace("C", "g", $seq);
            $seq = strtoupper ($seq);
            return $seq;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }*/
}