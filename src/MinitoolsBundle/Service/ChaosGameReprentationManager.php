<?php
/**
 * Formulas Functions
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 3 march  2019
 * Last modified 3 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

class ChaosGameReprentationManager
{
    /**
     * @param $input_min
     * @param $input_max
     * @todo : attention données en POST de legacy
     * @throws \Exception
     */
    public function CGRCompute($input_min, $input_max)
    {
        // GET POSTED DATA
        if ($_POST["seq_name"]) {
            $seq_name = $_POST["seq_name"];
        } else {
            $seq_name = "No name";
        }
        $seq = strtoupper($_POST["seq"]);
        $seq = preg_replace("/\W|\d/", "", $seq);

        $seq_len = strlen($seq);

        // add limits to length of imput sequence
        if($seq_len > $input_max) {
            throw new \Exception("Sequence is longer than $input_max bp. At this moment we can not provide this service to such a long sequences.");
        }
        if($seq_len < $input_min) {
            throw new \Exception("Minumum sequence length: $input_min bp");
        }

        if($_POST["size"] == "auto") {
            $size = 256;
            if($seq_len > 1000000) {
                $size = 1024;
            }
            if($seq_len > 100000) {
                $size = 512;
            }
        }else{
            $size = $_POST["size"];
        }

        $this->createCGRImage($seq_name, $seq, $size);
    }


    /**
     * @param $input_min
     * @param $input_max
     * @todo : voir ce qu'il faut faire avec ce legacy. Needs complete rafacto.
     */
    public function FCGRCompute($input_min, $input_max)
    {
        print "<p align=right><a href=".$_SERVER["PHP_SELF"].">Home</a></p>\n";
        print "Computing...(time depends on sequence length and power of the server)<center><hr>";flush();

        // GET DATA
        if ($_POST["seq_name"]) {
            $seq_name = $_POST["seq_name"];
        } else {
            $seq_name = "No name";
        }
        $seq = strtoupper($_POST["seq"]);
        $seq = preg_replace ("/\W|\d/", "", $seq);
        $seq_len = strlen($seq);

        // limits for length of sequence
        if($seq_len > $input_max) {
            die("<p>Sequence is longer than $input_max bp.<p>At this moment we can not provide this service to such a long sequences.");
        }
        if($seq_len < $input_min) {
            die("<p>Minumum sequence length: $input_min bp");
        }

        $oligo_len = $_POST["len"];

        // If double strand is requested to be computed...
        if ($_POST["s"] == 2) {
            $seq .= " ".$this->revComp($seq);
        }

        // compute nucleotide frequencies
        $A = substr_count($seq,"A");
        $C = substr_count($seq,"C");
        $G = substr_count($seq,"G");
        $T = substr_count($seq,"T");

        // COMPUTE OLIGONUCLEOTIDE FREQUENCIES
        //   frequencies are saved to an array named $oligos
        $oligos = $this->findOligos($seq,$oligo_len);


        // CREATE CHAOS GAME REPRESENTATION OF FREQUENCIES IMAGE
        //      check the function for more info on parameters
        //      $data contains a string with the data to be used to create the image map
        $for_map = $this->createFCGRImage($oligos, $seq_name, $A, $C, $G, $T, $seq_len, $_POST["s"], $oligo_len);

        // PRINT THE IMAGE, WHICH WILL BE A IMAGE MAP WHEN REQUESTED
        //    to avoid submission of a huge amount of data throught the net
        if($_POST["map"] == 1) {   // image map is requested
            print "<br><MAP NAME=Kaixo>\n$for_map\n</MAP>\n<span id=txt></span>&nbsp;<br><img USEMAP=\#Kaixo src=FCGR.png?".date("U")." width=552 hight=700 border=0>\n";
        } else {
            print "<br><img  src=FCGR.png?".date("U")." width=552 hight=700 border=0>";
        }
        // PRINT TEXTAREA WITH OLIGONUCLEOTIDE FREQUENCIES  WHEN REQUESTED
        if($_POST["freq"] == 1) {   // oligonucleotide frequencies are requested
            print "<p><p>Raw data used to generate images above: <BR><textarea cols=80 rows=10>Sequence\tOccurences\n";
            foreach ($oligos as $key => $val) {
                print "\n$key\t$val";
            }
            print "</textarea>";
        }
    }


    /**
     * @param $seq_name
     * @param $seq
     * @param $size
     * @param $ip2
     */
    public function createCGRImage($seq_name, $seq, $size, $ip2)
    {
        $im = imagecreatetruecolor($size, $size+20);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, $size, $size + 20, $white);
        $x = round($size / 2);
        $y = $x;
        for($i = 0; $i < strlen($seq); $i++) {
            $w = substr($seq, $i, 1);
            if($w == "A") {
                $x -= $x / 2;
                $y += ($size - $y) / 2;
            }
            if($w == "C") {
                $x -= $x / 2;
                $y -= $y / 2;
            }
            if($w=="G"){
                $x+=($size-$x)/2;
                $y-=$y/2;
            }
            if($w=="T"){
                $x += ($size-$x) / 2;
                $y += ($size-$y) / 2;
            }
            $x2 = floor($x);
            $y2 = floor($y);

            imagesetpixel($im, $x2, $y2, $black);
        }

        $seqlen = strlen($seq);
        imagestring($im, 3, 5, $size+5, "$seq_name ($seqlen bp)", $black);
        imagepng($im, "CGR.png");
        imagedestroy($im);
    }


    /**
     * compute frequency of oligonucleotides with length $oligo_len for sequence $sequence
     * @param $sequence
     * @param $oligo_len
     * @return mixed
     */
    public function findOligos($sequence, $oligo_len)
    {
        $i = 0;
        $oligos_1step = [];
        $len = strlen($sequence) - $oligo_len + 1;
        while($i < $len) {
            $seq = substr($sequence, $i, $oligo_len);
            $oligos_1step[$seq] ++;
            $i ++;
        }
        $base_a = $base_b = $base_c = $base_d = $base_e = $base_f = $base_g = $base_h = array("A","C","G","T");

        //for oligos 2 bases long
        if($oligo_len == 2) {
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
        //for oligos 3 bases long
        if($oligo_len == 3) {
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
        //for oligos 4 bases long
        if($oligo_len == 4) {
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
        if($oligo_len == 5) {
            foreach($base_a as $key_a => $val_a) {
                foreach($base_b as $key_b => $val_b) {
                    foreach($base_c as $key_c => $val_c) {
                        foreach($base_d as $key_d => $val_d) {
                            foreach($base_e as $key_e => $val_e) {
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
        }
        //for oligos 6 bases long
        if($oligo_len == 6) {
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
        if($oligo_len == 7) {
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
        if($oligo_len == 8) {
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
    }


    /**
     * CREATE CHAOS GAME REPRESENTATION OF FREQUENCIES
     * The FCGR image will be save to a file, and an string is returned which contains data to be create a image map
     * @param   $oligos         the array containing the oligonucleotides and their frequencies
     * @param   $seq_name       name of the sequence
     * @param $A
     * @param $C
     * @param $G
     * @param $T                frequencies of nucleotides
     * @param $seq_len          length of sequence
     * @param $n                number of strands used to compute the figure
     * @param $oligo_len        length of oligonucleotides studied
     * @return string
     */
    public function createFCGRImage($oligos, $seq_name, $A, $C, $G, $T, $seq_len, $n, $oligo_len)
    {
        $max_val = max($oligos);
        $min_val = min($oligos);
        foreach($oligos as $key => $val) {
            $ratio[$key] = floor(255 - ((255 * ($val - $min_val)) / ($max_val - $min_val)));
        }

        $im = imagecreatetruecolor(552, 370) or die("Cannot initialize image");
        for($c = 0; $c < 256; $c++) {
            $thecolor[$c] = ImageColorAllocate($im, $c, $c, $c);
        }
        $background_color = imagecolorallocate($im, 255, 255, 255);
        imagefilledrectangle($im,0,0,552,700,$background_color);
        $black = imagecolorallocate($im, 0, 0, 0);
        $red = imagecolorallocate($im, 255, 0, 0);
        $blue = imagecolorallocate($im, 0, 0, 255);

        imagestring($im, 4, 10, 10, "Over or under-representation of oligonucleotides", $blue);
        imagestring($im, 3, 20, 30, "Chaos Game Representation of frequencies (FCGR)", $black);
        imageline($im, 10, 50, 350, 50, $black);
        $seq_name = substr($seq_name,0,15);
        imagestring($im, 3, 20, 55, "Sequence name: $seq_name ($seq_len bp)", $black);
        if($n == 1) {
            imagestring($im, 3, 20, 73, "Results for only one strand", $black);
        }
        if($n == 2) {
            imagestring($im, 3, 20, 73, "Results for both strands", $black);
        }


        $thecolor[255] = ImageColorAllocate($im, 255, 255, 255);
        $for_map = "";
        foreach($ratio as $seq => $val) {
            $len = strlen($seq);
            if($len == 7) {
                $len_cuadro = 1;
            }
            if($len == 6) {
                $len_cuadro = 3;
            }
            if($len == 5) {
                $len_cuadro = 7;
            }
            if($len == 4) {
                $len_cuadro = 15;
            }
            if($len == 3) {
                $len_cuadro = 31;
            }
            if($len == 2) {
                $len_cuadro = 63;
            }
            $mas_x = 10;
            $mas_y = 90;
            // para posicion
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
            $x += $mas_x;
            $x2 = $x + $len_cuadro;
            $y += $mas_y;
            $y2 = $y + $len_cuadro;

            imagefilledrectangle($im,$x,$y,$x2,$y2,$thecolor[$val]);
            $for_map.="<AREA onMouseover=\"a('$seq => ".$oligos[$seq]."');\" onMouseout=\"a('');\" COORDS=\"$x,$y,$x2,$y2\" SHAPE=RECT>\n";

        }

        imagestring($im, 3, 420, 10,  "A: $A", $black);
        imagestring($im, 3, 420, 30,  "C: $C", $black);
        imagestring($im, 3, 420, 50,  "G: $G", $black);
        imagestring($im, 3, 420, 70,  "T: $T", $black);

        // lines
        imageline ($im, 10, 90, 10, 346, $black);
        imageline ($im, 266, 90, 266, 346, $black);
        imageline ($im, 10, 90, 266, 90, $black);
        imageline ($im, 10, 346, 266, 346, $black);

        if($oligo_len == 2) {
            // lines
            imageline ($im, 10, 154, 266, 154, $black);
            imageline ($im, 10, 218, 266, 218, $black);
            imageline ($im, 10, 282, 266, 282, $black);
            imageline ($im, 74, 90, 74, 346, $black);
            imageline ($im, 138, 90, 138, 346, $black);
            imageline ($im, 202, 90, 202, 346, $black);

            // dimers in their place
            $h_pos = 24;
            $v_pos = 26;
            imagestring($im, 3, 10+$h_pos, 90+$v_pos, "CC", $black);
            imagestring($im, 3, 74+$h_pos, 90+$v_pos, "GC", $black);
            imagestring($im, 3, 138+$h_pos, 90+$v_pos, "CG", $black);
            imagestring($im, 3, 202+$h_pos, 90+$v_pos, "GG", $black);
            imagestring($im, 3, 10+$h_pos, 154+$v_pos, "AC", $black);
            imagestring($im, 3, 74+$h_pos, 154+$v_pos, "TC", $black);
            imagestring($im, 3, 138+$h_pos, 154+$v_pos, "AG", $black);
            imagestring($im, 3, 202+$h_pos, 154+$v_pos, "TG", $black);
            imagestring($im, 3, 10+$h_pos, 218+$v_pos, "CA", $black);
            imagestring($im, 3, 74+$h_pos, 218+$v_pos, "GA", $black);
            imagestring($im, 3, 138+$h_pos, 218+$v_pos, "CT", $black);
            imagestring($im, 3, 202+$h_pos, 218+$v_pos, "GT", $black);
            imagestring($im, 3, 10+$h_pos, 282+$v_pos, "AA", $black);
            imagestring($im, 3, 74+$h_pos, 282+$v_pos, "TA", $black);
            imagestring($im, 3, 138+$h_pos, 282+$v_pos, "AT", $black);
            imagestring($im, 3, 202+$h_pos, 282+$v_pos, "TT", $black);
        }
        if ($oligo_len == 3) {
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
            imagestring($im, 2, 10+$h_pos, 90+$v_pos, "CCC", $black);
            imagestring($im, 2, 42+$h_pos, 90+$v_pos, "GCC", $black);
            imagestring($im, 2, 74+$h_pos, 90+$v_pos, "CGC", $black);
            imagestring($im, 2, 106+$h_pos, 90+$v_pos, "GGC", $black);
            imagestring($im, 2, 138+$h_pos, 90+$v_pos, "CCG", $black);
            imagestring($im, 2, 170+$h_pos, 90+$v_pos, "GCG", $black);
            imagestring($im, 2, 202+$h_pos, 90+$v_pos, "CGG", $black);
            imagestring($im, 2, 234+$h_pos, 90+$v_pos, "GGG", $black);

            imagestring($im, 2, 10+$h_pos, 122+$v_pos, "ACC", $black);
            imagestring($im, 2, 42+$h_pos, 122+$v_pos, "TCC", $black);
            imagestring($im, 2, 74+$h_pos, 122+$v_pos, "AGC", $black);
            imagestring($im, 2, 106+$h_pos, 122+$v_pos, "TGC", $black);
            imagestring($im, 2, 138+$h_pos, 122+$v_pos, "ACG", $black);
            imagestring($im, 2, 170+$h_pos, 122+$v_pos, "TCG", $black);
            imagestring($im, 2, 202+$h_pos, 122+$v_pos, "AGG", $black);
            imagestring($im, 2, 234+$h_pos, 122+$v_pos, "TGG", $black);

            imagestring($im, 2, 10+$h_pos, 154+$v_pos, "CAC", $black);
            imagestring($im, 2, 42+$h_pos, 154+$v_pos, "GAC", $black);
            imagestring($im, 2, 74+$h_pos, 154+$v_pos, "ATC", $black);
            imagestring($im, 2, 106+$h_pos, 154+$v_pos, "CTC", $black);
            imagestring($im, 2, 138+$h_pos, 154+$v_pos, "CAG", $black);
            imagestring($im, 2, 170+$h_pos, 154+$v_pos, "GAG", $black);
            imagestring($im, 2, 202+$h_pos, 154+$v_pos, "CTG", $black);
            imagestring($im, 2, 234+$h_pos, 154+$v_pos, "GTG", $black);

            imagestring($im, 2, 10+$h_pos, 186+$v_pos, "AAC", $black);
            imagestring($im, 2, 42+$h_pos, 186+$v_pos, "TAC", $black);
            imagestring($im, 2, 74+$h_pos, 186+$v_pos, "GTC", $black);
            imagestring($im, 2, 106+$h_pos, 186+$v_pos, "TTC", $black);
            imagestring($im, 2, 138+$h_pos, 186+$v_pos, "AAG", $black);
            imagestring($im, 2, 170+$h_pos, 186+$v_pos, "TAG", $black);
            imagestring($im, 2, 202+$h_pos, 186+$v_pos, "ATG", $black);
            imagestring($im, 2, 234+$h_pos, 186+$v_pos, "TTG", $black);

            imagestring($im, 2, 10+$h_pos, 218+$v_pos, "CCA", $black);
            imagestring($im, 2, 42+$h_pos, 218+$v_pos, "GCA", $black);
            imagestring($im, 2, 74+$h_pos, 218+$v_pos, "CGA", $black);
            imagestring($im, 2, 106+$h_pos, 218+$v_pos, "GGA", $black);
            imagestring($im, 2, 138+$h_pos, 218+$v_pos, "CCT", $black);
            imagestring($im, 2, 170+$h_pos, 218+$v_pos, "GCT", $black);
            imagestring($im, 2, 202+$h_pos, 218+$v_pos, "CGT", $black);
            imagestring($im, 2, 234+$h_pos, 218+$v_pos, "GGT", $black);

            imagestring($im, 2, 10+$h_pos, 250+$v_pos, "ACA", $black);
            imagestring($im, 2, 42+$h_pos, 250+$v_pos, "TCA", $black);
            imagestring($im, 2, 74+$h_pos, 250+$v_pos, "AGA", $black);
            imagestring($im, 2, 106+$h_pos, 250+$v_pos, "TGA", $black);
            imagestring($im, 2, 138+$h_pos, 250+$v_pos, "ACT", $black);
            imagestring($im, 2, 170+$h_pos, 250+$v_pos, "TCT", $black);
            imagestring($im, 2, 202+$h_pos, 250+$v_pos, "AGT", $black);
            imagestring($im, 2, 234+$h_pos, 250+$v_pos, "TGT", $black);

            imagestring($im, 2, 10+$h_pos, 282+$v_pos, "CAA", $black);
            imagestring($im, 2, 42+$h_pos, 282+$v_pos, "GAA", $black);
            imagestring($im, 2, 74+$h_pos, 282+$v_pos, "CTA", $black);
            imagestring($im, 2, 106+$h_pos, 282+$v_pos, "GTA", $black);
            imagestring($im, 2, 138+$h_pos, 282+$v_pos, "CAT", $black);
            imagestring($im, 2, 170+$h_pos, 282+$v_pos, "GAT", $black);
            imagestring($im, 2, 202+$h_pos, 282+$v_pos, "CTT", $black);
            imagestring($im, 2, 234+$h_pos, 282+$v_pos, "GTT", $black);

            imagestring($im, 2, 10+$h_pos, 314+$v_pos, "AAA", $black);
            imagestring($im, 2, 42+$h_pos, 314+$v_pos, "TAA", $black);
            imagestring($im, 2, 74+$h_pos, 314+$v_pos, "ATA", $black);
            imagestring($im, 2, 106+$h_pos, 314+$v_pos, "TTA", $black);
            imagestring($im, 2, 138+$h_pos, 314+$v_pos, "AAT", $black);
            imagestring($im, 2, 170+$h_pos, 314+$v_pos, "TAT", $black);
            imagestring($im, 2, 202+$h_pos, 314+$v_pos, "ATT", $black);
            imagestring($im, 2, 234+$h_pos, 314+$v_pos, "TTT", $black);
        }
        // show length of oligonucleotides
        imagestring($im, 3, 50, 350,  "Oligonucleotide length: $oligo_len", $black);


        $cent = 286;
        imagestring($im, 2, 6+$cent, 228, "Frequency", $black);
        imagefilledrectangle($im,6+$cent,208,16+$cent,218,$thecolor[255]);
        imagefilledrectangle($im,19+$cent,208,29+$cent,218,$thecolor[240]);
        imagefilledrectangle($im,32+$cent,208,42+$cent,218,$thecolor[225]);
        imagefilledrectangle($im,45+$cent,208,55+$cent,218,$thecolor[210]);
        imagefilledrectangle($im,58+$cent,208,68+$cent,218,$thecolor[195]);
        imagefilledrectangle($im,71+$cent,208,81+$cent,218,$thecolor[180]);
        imagefilledrectangle($im,84+$cent,208,94+$cent,218,$thecolor[165]);
        imagefilledrectangle($im,97+$cent,208,107+$cent,218,$thecolor[150]);
        imagefilledrectangle($im,110+$cent,208,120+$cent,218,$thecolor[135]);
        imagefilledrectangle($im,123+$cent,208,133+$cent,218,$thecolor[135]);
        imagefilledrectangle($im,136+$cent,208,146+$cent,218,$thecolor[120]);
        imagefilledrectangle($im,149+$cent,208,159+$cent,218,$thecolor[105]);
        imagefilledrectangle($im,162+$cent,208,172+$cent,218,$thecolor[90]);
        imagefilledrectangle($im,175+$cent,208,185+$cent,218,$thecolor[75]);
        imagefilledrectangle($im,188+$cent,208,198+$cent,218,$thecolor[60]);
        imagefilledrectangle($im,201+$cent,208,211+$cent,218,$thecolor[45]);
        imagefilledrectangle($im,214+$cent,208,224+$cent,218,$thecolor[30]);
        imagefilledrectangle($im,227+$cent,208,237+$cent,218,$thecolor[15]);
        imagefilledrectangle($im,240+$cent,208,250+$cent,218,$thecolor[0]);

        imagepng($im,"FCGR.png");

        imagedestroy($im);
        return $for_map;
    }


    /**
     * REVERSE_COMPLEMENT DNA
     * @param $code
     * @return mixed|string
     */
    public function revComp($code)
    {
        $code = strrev($code);
        $code = str_replace("A", "t", $code);
        $code = str_replace("T", "a", $code);
        $code = str_replace("G", "c", $code);
        $code = str_replace("C", "g", $code);
        $code = strtoupper($code);
        return $code;
    }
}