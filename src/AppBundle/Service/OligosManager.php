<?php
/**
 * Oligo-Nucleotids Functions
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 9 march  2019
 * Last modified 9 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace AppBundle\Service;

class OligosManager
{
    private $base_a;
    private $base_b;
    private $base_c;
    private $base_d;
    private $base_e;
    private $base_f;
    private $base_g;
    private $base_h;


    /**
     * For oligos 2 bases long
     * @param $oligos_1step
     * @param $dnaComplements
     * @return mixed
     */
    public function findOligos2BasesLong($oligos_1step, $dnaComplements)
    {
        $this->base_a = $dnaComplements;
        $this->base_b = $dnaComplements;

        foreach($this->base_a as $key_a => $val_a) {
            foreach($this->base_b as $key_b => $val_b) {
                if($oligos_1step[$val_a.$val_b]) {
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
     * @param $dnaComplements
     * @return mixed
     */
    public function findOligos3BasesLong($oligos_1step, $dnaComplements)
    {
        $this->base_a = array_values($dnaComplements);
        $this->base_b = array_values($dnaComplements);
        $this->base_c = array_values($dnaComplements);

        foreach($this->base_a as $key_a => $val_a) {
            foreach($this->base_b as $key_b => $val_b) {
                foreach($this->base_c as $key_c => $val_c) {
                    if($oligos_1step[$val_a.$val_b.$val_c]) {
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
     * @param $dnaComplements
     * @return mixed
     */
    public function findOligos4BasesLong($oligos_1step, $dnaComplements)
    {
        $this->base_a = array_values($dnaComplements);
        $this->base_b = array_values($dnaComplements);
        $this->base_c = array_values($dnaComplements);
        $this->base_d = array_values($dnaComplements);

        foreach($this->base_a as $key_a => $val_a) {
            foreach($this->base_b as $key_b => $val_b) {
                foreach($this->base_c as $key_c => $val_c) {
                    foreach($this->base_d as $key_d => $val_d) {
                        if($oligos_1step[$val_a.$val_b.$val_c.$val_d]) {
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
     * @param $dnaComplements
     * @return mixed
     */
    public function findOligos5BasesLong($oligos_1step, $dnaComplements)
    {
        $this->base_a = array_values($dnaComplements);
        $this->base_b = array_values($dnaComplements);
        $this->base_c = array_values($dnaComplements);
        $this->base_d = array_values($dnaComplements);
        $this->base_e = array_values($dnaComplements);

        foreach($this->base_a as $key_a => $val_a) {
            foreach($this->base_b as $key_b => $val_b) {
                foreach($this->base_c as $key_c => $val_c) {
                    foreach($this->base_d as $key_d => $val_d) {
                        foreach($this->base_e as $key_e => $val_e) {
                            if($oligos_1step[$val_a.$val_b.$val_c.$val_d.$val_e]) {
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
     * @param $dnaComplements
     * @return mixed
     */
    public function findOligos6BasesLong($oligos_1step, $dnaComplements)
    {
        $this->base_a = array_values($dnaComplements);
        $this->base_b = array_values($dnaComplements);
        $this->base_c = array_values($dnaComplements);
        $this->base_d = array_values($dnaComplements);
        $this->base_e = array_values($dnaComplements);
        $this->base_f = array_values($dnaComplements);

        foreach($this->base_a as $key_a => $val_a) {
            foreach($this->base_b as $key_b => $val_b) {
                foreach($this->base_c as $key_c => $val_c) {
                    foreach($this->base_d as $key_d => $val_d) {
                        foreach($this->base_e as $key_e => $val_e) {
                            foreach($this->base_f as $key_f => $val_f) {
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
        return $oligos;
    }

    /**
     * For oligos 7 bases long
     * @param $oligos_1step
     * @param $dnaComplements
     * @return mixed
     */
    public function findOligos7BasesLong($oligos_1step, $dnaComplements)
    {
        $this->base_a = array_values($dnaComplements);
        $this->base_b = array_values($dnaComplements);
        $this->base_c = array_values($dnaComplements);
        $this->base_d = array_values($dnaComplements);
        $this->base_e = array_values($dnaComplements);
        $this->base_f = array_values($dnaComplements);
        $this->base_g = array_values($dnaComplements);

        foreach($this->base_a as $key_a => $val_a) {
            foreach($this->base_b as $key_b => $val_b) {
                foreach($this->base_c as $key_c => $val_c) {
                    foreach($this->base_d as $key_d => $val_d) {
                        foreach($this->base_e as $key_e => $val_e) {
                            foreach($this->base_f as $key_f => $val_f) {
                                foreach($this->base_g as $key_g => $val_g) {
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
        return $oligos;
    }

    /**
     * For oligos 8 bases long
     * @param $oligos_1step
     * @param $dnaComplements
     * @return mixed
     */
    public function findOligos8BasesLong($oligos_1step, $dnaComplements)
    {
        $this->base_a = array_values($dnaComplements);
        $this->base_b = array_values($dnaComplements);
        $this->base_c = array_values($dnaComplements);
        $this->base_d = array_values($dnaComplements);
        $this->base_e = array_values($dnaComplements);
        $this->base_f = array_values($dnaComplements);
        $this->base_g = array_values($dnaComplements);
        $this->base_h = array_values($dnaComplements);

        foreach($this->base_a as $key_a => $val_a) {
            foreach($this->base_b as $key_b => $val_b) {
                foreach($this->base_c as $key_c => $val_c) {
                    foreach($this->base_d as $key_d => $val_d) {
                        foreach($this->base_e as $key_e => $val_e) {
                            foreach($this->base_f as $key_f => $val_f) {
                                foreach($this->base_g as $key_g => $val_g) {
                                    foreach($this->base_h as $key_h => $val_h) {
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
        return $oligos;
    }
}