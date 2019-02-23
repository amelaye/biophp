<?php

// author    Joseba Bikandi
// license   GNU GPL v2
// source code available at  biophp.org


// the code in the top will manipulated the input sequence
// in the middle of the file is located the form
// in the botton are located the functions used in this script


//############################################################################
//#################      lets manipulated the sequence       #################
//############################################################################

if($_POST){
    $seq=$_POST["seq"];
    $action=$_POST["action"];

    // remove non coding (works by default)
    $seq=remove_non_coding($seq);

    // if subsequence is requested
    if ($_POST["start"] or $_POST["end"]){
        if($_POST["start"]!=""){$start=$_POST["start"]-1;}else{$start=0;}
        if($_POST["end"]!=""){$end=$_POST["end"];}else{$end=strlen($seq);}
        $seq=substr($seq,$start,$end-$start);
    }

    // length of sequence
    $seqlen=strlen($seq);

    if($action=="reverse"){
        // reverse the sequence
        $seq=strrev($seq);

    }

    if($action=="complement"){
        // get the complementary sequence
        $seq=Complement($seq);

    }

    if($action=="reverse_and_complement"){
        // reverse the sequence
        $seq=strrev($seq);
        // get the complementary sequence
        $seq=Complement($seq);

    }

    $result="";
    if($action=="display_both_strands"){
        // get a string with results
        $result=Display_both_strands($seq);
    }
    if($action=="toRNA"){
        // get a string with results
        $result=toRNA($seq);
    }

    if($_POST["GC"]==1){
        // calculate G+C content
        $result.=GC_content($seq);
    }
    if ($_POST["ACGT"]==1){
        // calculate nucleotide conposition
        $result.=ACGT_content($seq);
    }


    // 70 characters per line before output
    $seq = chunk_split($seq, 70);


}else{
    $seq="";
}

//############################################################################
//################# we have already manipulated the sequence #################
//############################# bellow is the form ###########################
//############################################################################
?>


<html>
<head>
    <title>DNA sequence manipulation</title>
</head>
<body bgcolor=FFFFFF>
<center>
    <H2>DNA sequence manipulation</H2>
    <form method='post' action="<? print $_SERVER["PHP_SELF"]; ?>">
        <table cellpadding=5 width=650 border=0 bgcolor=DDFFFF>
            <tr><td>
                    <B>Sequence <?php if($seq){print "($seqlen bp)";} ?>:</B>
                </td></tr>
            <tr><td>
                    <textarea name='seq' rows='8' cols='80'><?php print $seq ?></textarea>
                </td></tr>
            <tr><td>
                    <select name=action size=7>
                        <option value=remove_non_coding>Remove no coding characters
                        <option value=reverse>Reverse sequence
                        <option value=complement>Complement sequence
                        <option value=reverse_and_complement>Reverse and Complement of sequence
                        <option value=display_both_strands>Display Double-stranded Sequence
                        <option value=toRNA>Convert to RNA
                    </select>
                    <br>Select subsequence from position <input type=text name=start size=4> to <input type=text name=end size=4> (both included)
                </td></tr>
            <tr><td>
                    <input type=checkbox name=GC value=1> G+C content
                    <br><input type=checkbox name=ACGT value=1> Nucleotide composition
                </td></tr>

            <tr><td align=center>
                    <input type='submit' value='Sutmit'>
                </td></tr>
            <?php
            if($other_results!=""){
                print "<tr><td align=center>";
                print "<textarea rows=10 cols=80>$other_results</textarea>";
                print "</td></tr>";
            }
            ?>
        </table>
    </form>
    <table cellpadding=5 width=650 border=0>
        <tr><td>
                <pre><?php print $result; ?></pre>
            </td></tr>
        <tr><td>
                <b>NOTES</b>:
                <br>Non-coding characters will be removed by default, and X is replaced by N.
                <br><a href=http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=retrieve&db=pubmed&list_uids=7957164&dopt=abstract>NC-UIBMB</a>
                codes are used as a reference.

                <p>Source code is available at
                    <a href=http://www.biophp.org/minitools/sequence_manipulation_and_data>BioPHP.org</a>

            </td></tr>
    </table>

</center>
</body>
</html>

<?php
//############################################################################
//################# Functions used in this script ############################
//############################################################################

function Complement($seq){
    // change the sequence to upper case
    $seq = strtoupper ($seq);
    // the system used to get the complementary sequence is simple but fas
    $seq=str_replace("A", "t", $seq);
    $seq=str_replace("T", "a", $seq);
    $seq=str_replace("G", "c", $seq);
    $seq=str_replace("C", "g", $seq);
    $seq=str_replace("Y", "r", $seq);
    $seq=str_replace("R", "y", $seq);
    $seq=str_replace("W", "w", $seq);
    $seq=str_replace("S", "s", $seq);
    $seq=str_replace("K", "m", $seq);
    $seq=str_replace("M", "k", $seq);
    $seq=str_replace("D", "h", $seq);
    $seq=str_replace("V", "b", $seq);
    $seq=str_replace("H", "d", $seq);
    $seq=str_replace("B", "v", $seq);
    // change the sequence to upper case again for output
    $seq = strtoupper ($seq);
    return $seq;
}

function remove_non_coding($seq) {
    // change the sequence to upper case
    $seq=strtoupper($seq);
    // remove non-words (\W), con coding ([^ATGCYRWSKMDVHBN]) and digits (\d) from sequence
    $seq=preg_replace("/\W|[^ATGCYRWSKMDVHBN]|\d/","",$seq);
    // replace all X by N (to normalized sequences)
    $seq=preg_replace("/X/","N",$seq);
    return $seq;
}
function Display_both_strands($seq) {
    // get the complementary sequence
    $revcomp=Complement($seq);
    $result="";
    $i=0;
    while ($i<strlen($seq)){
        if(strlen($seq)<($i+70)){$j=strlen($seq);}else{$j=$i;}
        $result.=substr($seq,$i,70)."\t$j\n";
        $result.=substr($revcomp,$i,70)."\t$j\n";
        $result.="\n"; //line break
        $i+=70;
    }
    return $result;
}
function GC_content($seq) {
    $number_of_G=substr_count($seq,"G");
    $number_of_C=substr_count($seq,"C");
    $gc_porcentaje=round(100*($number_of_G+$number_of_C)/strlen($seq),2);
    return "G+C %: $gc_porcentaje\n\n";
}
function toRNA($seq) {
    // replace T by U
    $seq=preg_replace("/T/","U",$seq);
    $seq=chunk_split($seq, 70);
    return $seq;
}
function ACGT_content($seq) {
    $result="Nucleotide composition";
    $result.="\nA: ".substr_count($seq,"A");
    $result.="\nC: ".substr_count($seq,"C");
    $result.="\nG: ".substr_count($seq,"G");
    $result.="\nT: ".substr_count($seq,"T");
    if (substr_count($seq,"Y")>0){$result.="\nY: ".substr_count($seq,"Y");}
    if (substr_count($seq,"R")>0){$result.="\nR: ".substr_count($seq,"R");}
    if (substr_count($seq,"W")>0){$result.="\nW: ".substr_count($seq,"W");}
    if (substr_count($seq,"S")>0){$result.="\nS: ".substr_count($seq,"S");}
    if (substr_count($seq,"K")>0){$result.="\nK: ".substr_count($seq,"K");}
    if (substr_count($seq,"M")>0){$result.="\nM: ".substr_count($seq,"M");}
    if (substr_count($seq,"D")>0){$result.="\nD: ".substr_count($seq,"D");}
    if (substr_count($seq,"V")>0){$result.="\nV: ".substr_count($seq,"V");}
    if (substr_count($seq,"H")>0){$result.="\nH: ".substr_count($seq,"H");}
    if (substr_count($seq,"B")>0){$result.="\nB: ".substr_count($seq,"B");}
    if (substr_count($seq,"N")>0){$result.="\nN: ".substr_count($seq,"N");}
    $result.="\n\n";
    return $result;
}
//############################################################################
//############################### End of fuctions ############################
//############################################################################

?>
