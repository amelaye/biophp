<!--
Title: Microsatellites Script
Author: Joseba Bikandi
-->

<html><head>
    <title>Find tandem repeats</title>
</head>
<body bgcolor=FFFFFF>
<center>
    <h1>Microsatellite repeats finder</h1>


    <?php
    error_reporting(0); // just in case

    // IN CASE COMPUTING IS REQUESTED, COMPUTE AND DIE
    if($_POST){
        // Get the sequence
        $sequence=strtoupper($_POST["sequence"]);
        // Remove non word and digits from sequence
        $sequence=preg_replace("/\\W|\\d/","",$sequence);

        // Get paramethers
        $min_length=$_POST["min"];
        $max_length=$_POST["max"];
        $min_repeats=$_POST["min_repeats"];
        $min_length_of_MR=$_POST["length_of_MR"];
        $mismatches_allowed=$_POST["mismatch"];

        //Find microsatellite repeats (uses a function)
        $results=find_microsatellite_repeats ($sequence,$min_length,$max_length,$min_repeats,$min_length_of_MR,$mismatches_allowed);

        // Print results
        print "<table cellpadding=4>";
        print "<tr><td bgcolor=AAAAAFF>Posici&oacute;n</td><td bgcolor=AAAAAFF>Cicle</td><td bgcolor=AAAAAFF>Repeats</td><td bgcolor=AAAAAFF>Sequence</td></tr>\n";
        foreach ($results as $key => $val){
            print "<tr>";
            print "<td>".$results[$key]["start_position"]."</td>";
            print "<td>".$results[$key]["length"]."</td>";
            print "<td>".$results[$key]["repeats"]."</td>";
            print "<td>".$results[$key]["sequence"]."</td>\n";
            print "</tr>";
        }
        print "</table><p> <a href=\"javascript:history.go(-1);\">Back</a></center></body></html>";
        die();
    }
    ?>

    <form method="POST" action=""<? print $_SERVER["PHP_SELF"]; ?>"">
    <table><tr><td>
                <div align=right><a href=?info>info</a></div>
                <b>Sequence</b>:<br>
                <TEXTAREA name=sequence cols="75" rows="10">
AACAATGCCATGATGATGATTATTACGACACAACAACACCGCGCTTGACGGCGGCGGATGGATGCCG
CGATCAGACGTTCAACGCCCACGTAACGTAACGCAACGTAACCTAACGACACTGTTAACGGTACGAT
</TEXTAREA>
                <br><b>Length of repeated sequence</b>:
                <br> &nbsp; &nbsp; Minimum:<select name=min><option selected>2<option>3<option>4<option>5<option>6</select>
                &nbsp; Maximum:<select name=max><option>3<option>4<option>5<option selected>6<option>7<option>8<option>9<option>10</select>
                <br><b>Minimum number of repeats</b>: <select name=min_repeats><option>2<option selected>3<option>4<option>5<option>6</select>
                <br><b>Minimum length of tanden repeat</b>: <select name=length_of_MR><option>5<option selected>6<option>7<option>8<option>9<option>10<option>11<option>12<option>13<option>14<option>15<option>16<option>17<option>18<option>19<option>20</select></select></select>
                <br><b>Allowed percentaje of mismatches</b>: <select name=mismatch><option selected>0<option>10<option>20<option>30</select> <a href="javascript:alert('NOTE: For sort repeated sequences (p.e. AA or AAA), no mismatches are available.');">info</a>
                <br><input type=submit value="Find Microsatellite repeats">
                </form>
                <hr>This tool was used to generate the microtatellites database at <a href=http://insilico.ehu.es/microsatellites/ target=micros>insilico.ehu.es</a>
                <br>
                Source code is available at <a href=http://www.biophp.org/minitools/microsatellite_repeats_finder/>biophp.org</a>
            </td></tr></table>
    <p>


        <?php

        if($_SERVER["QUERY_STRING"]=="info"){info();}

        // Description for Function find_microsatellite_repeats
        //      This function will search for microsatellite repeats within a sequence. A microsatellite repeat is defined as a sequence
        //      which shows a repeated pattern, as for example in sequence 'ACGTACGTACGTACGT', where 'ACGT' is repeated
        //      4 times. The function allows searching for this kind of subsequences within a sequence.
        //
        // Parameters
        //      $sequence is the sequence
        //      $min_length and $max_length are the range of oligo lengths to be searched; p.e. oligos with length 2 to 6
        //      $min_repeats is the minimal number of time a sequence must be repeated to be considered as a microsatellite repeat
        //      $min_length_of_MR  minimum length of tanden repeat; to avoid considering AAAA as a microsatellite repeat, set it to >4
        //      $mismatches_allowed is the porcentaje of errors allowed when searching in the repetitive sequence
        //         so that sequence AACCGGTT-AAGCGGTT-AACCGGAT-AACCGGTT may be considered as a microsatellite repeat
        //
        // Return
        //      The function will return an array with the following structure:
        //      $results=Array(
        //             0=>Array(
        //                    start_position => 10,
        //                    length => 4,
        //                    repeats => 4,
        //                    sequence => ACGTACGTACGTACGT,
        //                     ),
        //             0=>Array(
        //                    start_position => 50,
        //                    length => 3,
        //                    repeats => 3,
        //                    sequence => ATCATCATC,
        //                     ),
        //      );
        //
        // Requeriments:
        //      Functions IncludeN_1, IncludeN_2 and IncludeN_3
        function find_microsatellite_repeats($sequence,$min_length,$max_length,$min_repeats,$min_length_of_MR,$mismatches_allowed){
            $len_seq=strlen($sequence);
            $counter=0;
            for ($i=0;$i<$len_seq-3;$i++){
                for ($j=$min_length;$j<$max_length+1;$j++){
                    if (($i+$j)>$len_seq){break;}
                    $sub_seq=substr($sequence,$i,$j);
                    $len_sub_seq=strlen ($sub_seq);
                    $mismatches=floor($len_sub_seq*$mismatches_allowed/100);
                    if ($mismatches==1){$sub_seq_pattern=includeN_1($sub_seq,0);}
                    elseif ($mismatches==2){$sub_seq_pattern=includeN_2($sub_seq,0);}
                    elseif ($mismatches==3){$sub_seq_pattern=includeN_3($sub_seq,0);}
                    else {$sub_seq_pattern=$sub_seq;}

                    $matches=1;
                    while (preg_match_all("/($sub_seq_pattern)/",substr($sequence,($i+$j*$matches),$j),$out)==1){$matches++;}

                    if ($matches>=$min_repeats and ($j*$matches)>=$min_length_of_MR){
                        $results[$counter]["start_position"]=$i;
                        $results[$counter]["length"]=$j;
                        $results[$counter]["repeats"]=$matches;
                        $results[$counter]["sequence"]=substr($sequence,$i,$j*$matches);
                        $counter++;
                        $i+=$j*$matches;
                    }
                }
            }
            return ($results);
        }

        // Description for Function IncludeN_1
        //      When a DNA sequence ("$primer") is provided to this function, as for example "acgt", this function will return
        //      a pattern like ".cgt|a.gt|ac.t|acg.". This pattern may be useful to find within a DNA sequence
        //      subsequences matching $primer, but allowing one missmach. The parameter $minus
        //      is a numeric value which indicates number of bases always maching  the DNA sequence in 3' end.
        //      For example, when $minus is 1, the pattern for "acgt" will be  ".cgt|a.gt|ac.t".
        //      Check also IncludeN_2 and IncludeN_3.
        //
        // Parameters
        //      $primer is a DNA sequence (oligonucleotide, primer)
        //      $minus indicates number of bases in 3' which will always much the DNA sequence.
        //
        // Return
        //      Returns a pattern (as described in "Description").

        function includeN_1($primer,$minus) {
            $code=".".substr($primer,1);
            $wpos=1;
            while ($wpos<strlen($primer)-$minus){
                $code.="|".substr($primer,0,$wpos).".".substr($primer,$wpos+1);
                $wpos++;
            }
            return ($code);
        }

        // Description for Function IncludeN_2
        //      Similar to function IncludeN_1. When a DNA sequence ("$primer") is provided to this function, as for example "acgt",
        //      this function will return a pattern like "..gt|.c.t|.cg.|a..t|a.g.|ac..". This pattern may be useful to find within
        //      a DNA sequence subsequences matching $primer, but allowing two missmaches. The parameter $minus
        //      is a numeric value which indicates number of bases always maching  the DNA sequence in 3' end.
        //      For example, when $minus is 1, the pattern for "acgt" will be  "..gt|.c.t|a..t".
        //      Check also IncludeN_1 and IncludeN_3.
        //
        // Parameters
        //      $primer is a DNA sequence (oligonucleotide, primer)
        //      $minus indicates number of bases in 3' which will always much the DNA sequence.
        //
        // Return
        //      Returns a pattern (as described in "Description").
        function includeN_2($primer,$minus) {
            $max=strlen($primer)-$minus;
            $code="";
            for($i=0;$i<$max;$i++){
                for($j=0;$j<$max-$i-1;$j++){
                    $code.="|".substr($primer,0,$i).".";
                    $resto=substr($primer,$i+1);
                    $code.=substr($resto,0,$j).".".substr($resto,$j+1);
                }
            }

            $code=substr($code,1);
            return ($code);
        }

        // Description for Function IncludeN_3
        //      Similar to function IncludeN_1 and IncludeN_2, but allows two missmaches. The parameter $minus
        //      is a numeric value which indicates number of bases always maching  the DNA sequence in 3' end.
        //
        // Parameters

        //      $primer is a DNA sequence (oligonucleotide, primer)
        //      $minus indicates number of bases in 3' which will always much the DNA sequence.
        //
        // Return
        //      Returns a pattern (as described in "Description").
        function includeN_3($primer,$minus) {
            $max=strlen($primer)-$minus;
            $code="";
            for($i=0;$i<$max;$i++){
                for($j=0;$j<$max-$i-1;$j++){
                    $code.="|".substr($primer,0,$i).".";
                    $resto=substr($primer,$i+1);
                    $code.=substr($resto,0,$j).".".substr($resto,$j+1);
                }
            }
            $code=substr($code,1);
            return ($code);
        }
        // will be use to show the info when requested
        function info () {
        ?>
        <table width=600><tr><td>
                    <b><u>Definitions</u></b>
    <p><b>Microsatellite Repeat</b>:
        <br>A variety of simple di- (DINUCLEOTIDE REPEATS), tri- (TRINUCLEOTIDE REPEATS), tetra-, and pentanucleotide tandem repeats (usually less than 100 bases long).
    <p><b>Tandem repeats</b>:
        <br>Copies of DNA sequences which lie adjacent to each other
    <p><b>Related terms</b>:
        Simple Tandem Repeats (STR), Exact Tandem Repeats (ETRs), Short� Sequence Repeats (SSRs),
        Repetitive DNA, Simple Sequence Repeats (SSR)
        </td></tr></table>
        <?php
        }
        ?>

</center>
</body>
</html>
