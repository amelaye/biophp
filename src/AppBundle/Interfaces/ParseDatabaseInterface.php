<?php

namespace AppBundle\Interfaces;

interface ParseDatabaseInterface
{

    /**
     * ParseGenbankManager constructor.
     */
    public function __construct();


    /**
     * Parses a GenBank data file and returns a Seq object containing parsed data.
     * @param   type        $aFlines
     * @return  Sequence    $oSequence
     */
    public function parseDataFile($aFlines);
}