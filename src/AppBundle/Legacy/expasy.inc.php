<?php

class Enzyme_Expasy
{
    var $id;
    var $desc;
    var $alt_names;
    var $cofactors;
    var $comments;
    var $diseases;
    var $prosite_refs;
    var $swp_refs;
}

function parse_enzyme_expasy($flines)
{
    // initialize variables
    $desc_flag = FALSE;
    $desc_string = "";

    $an_flag = FALSE;
    $aAltNames = array();

    $ca_flag = FALSE;
    $ca_string = "";

    $cf_flag = FALSE;
    $cf_string = "";

    $cc_string = "";

    $aDiseases = array();
    $aPrositeRefs = array();

    $dr_flag = FALSE;
    $dr_string = "";
    $aDRs = array();

    while ( list($no, $linestr) = each($flines) )
    {
        $linelabel = substr($linestr,0,2);
        $linedata = trim(substr($linestr,5));

        /* ID - IDENTIFIER data field
            Example: ID   1.1.1.2
        */
        if ($linelabel == "ID") $id = $linedata;

        /* DE - DESCRIPTION data field
            Example:
            DE   Alcohol dehydrogenase (NADP+).
        */
        if ($linelabel == "DE")
        {
            $desc_string .= $linedata . " ";
            $desc_flag = TRUE;
        }
        elseif ($desc_flag)
        {
            $desc = trim($desc_string);
            $desc_string = "";
            $desc_flag = FALSE;
        }

        /* AN - ALTERNATE NAME(S) data field
            Example: AN   Aldehyde reductase (NADPH).
        */
        if ($linelabel == "AN")
        { // we remove the last character from each AN line, which is always a period (.).
            $aAltNames[] = substr($linedata, 0, strlen($linedata)-1);
            $an_flag = TRUE;
        }
        elseif ($an_flag) $an_flag = FALSE;

        /* CA - CATALYTIC ACTIVITY data field - (>=0 per entry)
            Example:
            CA   Propane-1,2-diol 1-phosphate + NAD(+) = hydroxyacetone phosphate +
            CA   NADH.
        */
        if ($linelabel == "CA")
        {
            $ca_string .= $linedata . " ";
            $ca_flag = TRUE;
        }
        elseif ($ca_flag)
        {
            $ca_string = trim($ca_string);
            // we move the last character which is always a period (.)
            $cat_activity = substr($ca_string, 0, strlen($ca_string)-1);
            $ca_string = "";
            $ca_flag = FALSE;
        }

        /* CF - COFACTORS data field - >= 0 per entry.
            Example: CF   Potassium or Ammonia; Manganese or Cobalt.
        */
        if ($linelabel == "CF")
        {
            $cf_string .= $linedata . " ";
            $cf_flag = TRUE;
        }
        elseif ($cf_flag)
        {
            $aCofactors = preg_split("/;/", trim($cf_string), -1, PREG_SPLIT_NO_EMPTY);
            array_walk($aCofactors, "trim_element");
            $lastindex = count($aCofactors)-1;
            $last_cf = $aCofactors[$lastindex];
            $aCofactors[$lastindex] = substr( $last_cf, 0, strlen($last_cf)-1 );
            $cf_string = "";
            $cf_flag = FALSE;
        }

        /* CC - COMMENTS data field - for now, let's ignore -!- (COMMENT BLOCKS) and treat
           the whole thing as one long text.  Also, we will retain the given line division.
            Example:
            CC   -!- Some members of this group oxidize only primary alcohols; others act
            CC       also on secondary alcohols.
            CC   -!- May be identical with EC 1.1.1.19, EC 1.1.1.33 and EC 1.1.1.55.
            CC   -!- A-specific with respect to NADPH.
        */
        if ($linelabel == "CC") $cc_string .= substr($linestr,5) . "\n";

        /* DI - DISEASES (associated with enzyme) data field - assume one compound entry per line,
           multiline.
            Example: DI   6-phosphogluconate dehydrogenase deficiency; MIM: 172200.
        */
        if ($linelabel == "DI")
        {
            $di_tokens = preg_split("/;/", $linedata, -1, PREG_SPLIT_NO_EMPTY);
            $disease = trim($di_tokens[0]);
            $lit_str = trim($di_tokens[1]);
            $lit_tokens = preg_split("/\:/", $lit_str, -1, PREG_SPLIT_NO_EMPTY);
            $lit_ref = trim($lit_tokens[1]);
            // remove the last character which is always a period (.)
            $lit_ref = substr( $lit_ref, 0, strlen($lit_ref)-1 );
            $aDiseases[] = array($lit_ref, $disease);
        }

        /* PR - PROSITE CROSS-REFERENCES data field
            Example:
            PR   PROSITE; PDOC00058;
            PR   PROSITE; PDOC00059;
            PR   PROSITE; PDOC00060;
        */
        if ($linelabel == "PR")
        {
            $pr_tokens = preg_split("/;/", $linedata, -1, PREG_SPLIT_NO_EMPTY);
            $pr_id = trim($pr_tokens[1]);
            $aPrositeRefs[] = $pr_id;
        }

        /* DR - Swissprot Database References data field
            Example:
            DR   P35630, ADH1_ENTHI;  Q24857, ADH3_ENTHI;  O57380, ADH4_RANPE;
            DR   P25984, ADH_CLOBE ;  P75214, ADH_MYCPN ;  P31975, ADH_MYCTU ;
            DR   P27800, ALDX_SPOSA;
        */
        if ($linelabel == "DR")
        {
            $dr_string .= $linedata;
            $dr_flag = TRUE;
        }
        elseif ($dr_flag)
        {
            $dr_tokens = preg_split("/;/", trim($dr_string), -1, PREG_SPLIT_NO_EMPTY);
            foreach($dr_tokens as $dr_entry)
            {
                $dr_entry_tokens = preg_split("/\,/", $dr_entry, -1, PREG_SPLIT_NO_EMPTY);
                $swp_pacc = trim($dr_entry_tokens[0]);
                $swp_name = trim($dr_entry_tokens[1]);
                $aDRs[$swp_pacc] = $swp_name;
            }
            // reset the values of string accumulator and flag for the DR data field
            $dr_string = "";
            $dr_flag = FALSE;
        }

        if ($linelabel == "//") break;
    }

    $oEnzyme = new Enzyme_Expasy();
    $oEnzyme->id = $id;
    $oEnzyme->desc = $desc;
    $oEnzyme->alt_names = $aAltNames;
    $oEnzyme->cat_activity = $cat_activity;
    $oEnzyme->cofactors = $aCofactors;
    $oEnzyme->comments = $cc_string;
    $oEnzyme->diseases = $aDiseases;
    $oEnzyme->prosite_refs = $aPrositeRefs;
    $oEnzyme->swp_refs = $aDRs;

    return $oEnzyme;
}

/*
   AN  Alternate name(s)                      (>=0 per entry)
   CA  Catalytic activity                     (>=0 per entry)
   CF  Cofactor(s)                            (>=0 per entry)
   CC  Comments                               (>=0 per entry)
   DI  Disease(s) associated with the enzyme  (>=0 per entry)
   PR  Cross-references to PROSITE            (>=0 per entry)
   DR  Cross-references to SWISS-PROT         (>=0 per entry)

ID   1.1.1.2
DE   Alcohol dehydrogenase (NADP+).
AN   Aldehyde reductase (NADPH).
CA   An alcohol + NADP(+) = an aldehyde + NADPH.
CF   Zinc.
CC   -!- Some members of this group oxidize only primary alcohols; others act
CC       also on secondary alcohols.
CC   -!- May be identical with EC 1.1.1.19, EC 1.1.1.33 and EC 1.1.1.55.
CC   -!- A-specific with respect to NADPH.
PR   PROSITE; PDOC00061;
DR   P35630, ADH1_ENTHI;  Q24857, ADH3_ENTHI;  O57380, ADH4_RANPE;
*/
?>