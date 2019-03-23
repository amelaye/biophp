<?php
// prf.inc.php

require_once("etc.inc.php");
require_once("seqdb.inc.php");
require_once("seq.inc.php");

class Protein_PRF
{
    var $entry_code;
    var $entry_name;
    var $source;

    var $journal;
    var $authors;
    var $title;

    var $keywords;
    var $comment;

    var $dbref;
    var $sequence;
}

function parse_protein_prf($flines)
{
    // initialize variables here.
    $auth_flag = FALSE;
    $auth_string = "";
    $aAuthors = array();

    $jour_flag = FALSE;
    $jour_string = "";

    $title_flag = FALSE;
    $title_string = "";

    $comm_flag = FALSE;
    $comm_string = "";

    while ( list($no, $linestr) = each($flines) )
    {
        $linelabel = trim(left($linestr, 12));
        $linedata = trim(substr($linestr, 12));

        /* (ENTRY) CODE data field - one entry (word) in one line, the entry code is 6-7 digits
        followed by 1-2 alpha letters.
           Example: CODE        0904306A
        */

        if ($linelabel == "CODE") $entry_code = $linedata;

        /* (ENTRY) NAME data field - for now we only support the SUBUNIT and ISOTYPE subkeys/qualifiers,
        and not the "determine" subkey/qualifier which appears in the example below.
           Example:
           NAME        interleukin 2
           determine  protein
        */

        if ($linelabel == "NAME") $entry_name = $linedata;

        /* SOURCE data field - skip for now
           Example:
           SOURCE      Homo sapiens
           cname      man
           taxon      Eucarya;Animalia;Metazoa;Chordata;Vertebrata;Gnathostomata;
                       Mammalia;Eutheria;Primates;Catarrhini;Hominidae
        */

        if ($linelabel == "SOURCE")
        {
        }

        /* JOURNAL data field
        Example: JOURNAL     Nature(London), 302(5906),305-310(1983)
           For now, let's just concatenate all the lines with space.  We don't extract
           individual data items like journal title, publication year, etc.
        */

        if ($linelabel == "JOURNAL")
        {
            $jour_string = $linedata . " ";
            $jour_flag = TRUE;
        }
        elseif ( (strlen(trim($linelabel)) == 0) and ($jour_flag) )
            $jour_string .= $linedata . " ";
        elseif ( (strlen(trim($linelabel)) > 0) and ($jour_flag) )
        {
            $journal = trim($jour_string);
            $jour_string = "";
            $jour_flag = FALSE;
        }

        /* AUTHORS data field
           Example:
           AUTHOR      Taniguchi,T., Matsui,H., Fujita,T., Takaoka,C., Kashima,N.,
                          Yoshimoto,R., Hamuro,J.
        */

        if ($linelabel == "AUTHOR")
        {
            $auth_string = $linedata . " ";
            $auth_flag = TRUE;
        }
        elseif ( (strlen(trim($linelabel)) == 0) and ($auth_flag) )
            $auth_string .= $linedata . " ";
        elseif ( (strlen(trim($linelabel)) > 0) and ($auth_flag) )
        {
            $temp = preg_split("/\.\,/", $auth_string, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($temp, "trim_element");
            $last_author = array_pop($temp);
            foreach($temp as $author)
                $aAuthors[] = "$author.";
            $aAuthors[] = $last_author;
            $auth_string = "";
            $auth_flag = FALSE;
        }

        /* TITLE data field - multiline; handle the same way as JOURNAL.
           Example:
           TITLE       Structure and expression of a cloned cDNA for human interleukin-2.
        */

        if ($linelabel == "TITLE")
        {
            $title_string = $linedata . " ";
            $title_flag = TRUE;
        }
        elseif ( (strlen(trim($linelabel)) == 0) and ($title_flag) )
            $title_string .= $linedata . " ";
        elseif ( (strlen(trim($linelabel)) > 0) and ($title_flag) )
        {
            $title = trim($title_string);
            $title_string = "";
            $title_flag = FALSE;
        }

        /* KEYWORD data field
           Example:
           KEYWORD     Interleukin 2    Human    Cloning From cDNA Library
                       Seq Determination    812bp    mRNA Hybridization Translation
                       Expression in Monkey Cell    153AAs    T Cell Growth Factor
                       Stimulation of Thymidine Uptake
        */

        if ($linelabel == "KEYWORD")
        {
        }

        /* COMMENT data field
           Example: COMMENT     CHO.x2 hetero.x3
        */

        if ($linelabel == "COMMENT")
        {
            $comm_string = $linedata . " ";
            $comm_flag = TRUE;
        }
        elseif ( (strlen(trim($linelabel)) == 0) and ($comm_flag) )
            $comm_string .= $linedata . " ";
        elseif ( (strlen(trim($linelabel)) > 0) and ($comm_flag) )
        {
            $comment = trim($comm_string);
            $comm_string = "";
            $comm_flag = FALSE;
        }

        /* CROSSREF data field
           Example:
           CROSSREF    PIR=ICHU2;PIR=ICGI2
        */

        if ($linelabel == "CROSSREF")
        {
        }

        /* SEQUENCE data field
           Example:
           SEQUENCE
                      MYRMQLLSCI ALSLALVTNS APTSSSTKKT QLQLEHLLLD LQMILNGINN YKNPKLTRML
                      TFKFYMPKKA TELKHLQCLE EELKPLEEVL NLAQSKNFHL RPRDLISNIN VIVLELKGSE
                      TTFMCEYADE TATIVEFLNR WITFCQSIIS TLT
        */

        if ($linelabel == "SEQUENCE")
        {
        }

        if ($linelabel == "///") break;
    }

    $oProtein = new Protein_PRF();
    $oProtein->entry_code = $entry_code;
    $oProtein->entry_name = $entry_name;

    $oProtein->journal = $journal;
    $oProtein->authors = $aAuthors;
    $oProtein->title = $title;

    // $oProtein->keywords = $aKeywords;
    $oProtein->comment = $comment;
    // $oProtein->dbref = $dbref;
    // $oProtein->sequence = $sequence;

    return $oProtein;
}

/*
CODE        0904306A
NAME        interleukin 2
 determine  protein
SOURCE      Homo sapiens
 cname      man
 taxon      Eucarya;Animalia;Metazoa;Chordata;Vertebrata;Gnathostomata;
            Mammalia;Eutheria;Primates;Catarrhini;Hominidae
JOURNAL     Nature(London), 302(5906),305-310(1983)
AUTHOR      Taniguchi,T., Matsui,H., Fujita,T., Takaoka,C., Kashima,N.,
            Yoshimoto,R., Hamuro,J.
TITLE       Structure and expression of a cloned cDNA for human interleukin-2.
KEYWORD     Interleukin 2    Human    Cloning From cDNA Library
            Seq Determination    812bp    mRNA Hybridization Translation
            Expression in Monkey Cell    153AAs    T Cell Growth Factor
            Stimulation of Thymidine Uptake
CROSSREF    PIR=ICHU2;PIR=ICGI2
SEQUENCE
            MYRMQLLSCI ALSLALVTNS APTSSSTKKT QLQLEHLLLD LQMILNGINN YKNPKLTRML
            TFKFYMPKKA TELKHLQCLE EELKPLEEVL NLAQSKNFHL RPRDLISNIN VIVLELKGSE
            TTFMCEYADE TATIVEFLNR WITFCQSIIS TLT
*/
?>