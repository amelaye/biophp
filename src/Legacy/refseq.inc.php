<?php
// refseq.inc.php

// SeqAlign() is the constructor method for the SeqAlign class.  It initializes class properties.
function SeqAlign($filename = "", $format = "FASTA")
{ // OPENS function SeqAlign
    if (strlen($filename) == 0)
    {
        $this->seq_count = 0;
        $this->length = 0;
        $this->seqptr = 0;
        $this->gap_count = 0;
        $this->is_flush = TRUE;
        $this->seqset = array();
        return;
    }

    if ($format == "FASTA")
    {
        $flines = file($filename);
        $seqctr = 0;
        $maxlen = 0;
        $maxctr = 0;
        $gapctr = 0;
        $this->seqset = array();
        $samelength = TRUE;

        while ( list($no, $linestr) = each($flines) )
        { // OPENS while ( list($no, $linestr) = each($flines) )
            if (substr($linestr, 0, 1) == ">")
            { // start of a new sequence
                $seqctr++;
                $seqlen = strlen($seqstr);

                $seq_obj = new seq();
                $seq_obj->id = $prev_id;
                $seq_obj->length = $seqlen;
                $seq_obj->sequence = $seqstr;
                $seq_obj->start = $prev_start;
                $seq_obj->end = $prev_end;
                $localgaps = $seq_obj->symfreq("-");
                $gapctr += $seq_obj->symfreq("-");

                if ($seqctr > 1)
                {
                    if ($seqlen > $maxlen) $maxlen = $seqlen;
                    if (($seqctr >= 3) and ($seqlen != $prev_len))
                        $samelength = FALSE;
                    array_push($this->seqset, $seq_obj);
                }
                $seqstr = "";

                $words = preg_split("/[\>\/]/", substr($linestr, 1));
                $prev_id = $words[0];

                $indexes = preg_split("/-/", $words[1]);
                $prev_start = $indexes[0];
                $prev_end = $indexes[1];
                $prev_len = $seqlen;
                continue;
            }
            else
            {
                $seqstr = $seqstr . trim($linestr);
            }
        } // CLOSES while ( list($no, $linestr) = each($flines) )

        $seqlen = strlen($seqstr);
        $seq_obj = new seq();
        $seq_obj->id = $prev_id;
        $seq_obj->start = $prev_start;
        $seq_obj->end = $prev_end;
        $seq_obj->length = $seqlen;
        $seq_obj->sequence = $seqstr;
        $localgaps = $seq_obj->symfreq("-");
        $gapctr += $seq_obj->symfreq("-");

        if ($seqctr > 1)
        {
            if ($seqlen > $maxlen) $maxlen = $seqlen;
            if (($seqctr >= 3) and ($seqlen != $prev_len))
                $samelength = FALSE;
            array_push($this->seqset, $seq_obj);
        }

        $this->seq_count = $seqctr;
        $this->length = $maxlen;
        $this->seqptr = 0;
        $this->gap_count = $gapctr;
        $this->is_flush = $samelength;
    } // CLOSES if ($format == "FASTA")
    elseif ($format == "CLUSTAL")
    { // OPENS elseif ($format == "CLUSTAL")
        $flines = file($filename);
        $namelist = array();
        $conserve_line = "";
        $linectr = 0;
        while( list($no, $linestr) = each($flines) )
        { // OPENS while( list($no, $linestr) = each($flines) )
            $linectr++;
            if ($linectr == 1) continue; // skip the first line.
            if (strlen(trim($linestr)) == 0) continue; // ignore blank lines.

            $seqname = trim(substr($linestr, 0, 16));
            $seqline = substr($linestr, 16, 60);

            if (strlen(trim($seqname)) == 0)
            {
                $conserve_line .= substr($seqline, 0, $lastlen);
                continue;
            }
            if (in_array($seqname, $namelist) == FALSE)
            {
                $namelist[] = $seqname;
                $seq[$seqname] = $seqline;
                $lastlen = strlen(trim($seqline));
            }
            else
            {
                $seq[$seqname] .= trim($seqline);
                $lastlen = strlen(trim($seqline));
            }
        } // CLOSES while( list($no, $linestr) = each($flines) )

        $this->seqset = array();
        $gapctr = 0;
        foreach($seq as $key => $value)
        {
            $seq_obj = new seq();
            $seq_obj->id = $key;
            $seq_obj->length = strlen($value);
            $seq_obj->sequence = $value;
            $seq_obj->start = 0;
            $seq_obj->end = $seq_obj->length - 1;
            $gapctr += $seq_obj->symfreq("-");
            array_push($this->seqset, $seq_obj);
        }
        $this->seq_count = count($namelist);
        $this->length = strlen($conserve_line);
        $this->seqptr = 0;
        $this->gap_count = $gapctr;
        $this->is_flush = TRUE;
    } // CLOSES elseif ($format == "CLUSTAL")
} // CLOSES function SeqAlign
?>