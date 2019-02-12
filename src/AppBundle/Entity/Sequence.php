<?php

namespace AppBundle\Entity;

class Sequence
{
    private $id;
    private $strands;
    private $moltype;
    private $topology;
    private $division;
    private $date;
    private $definition;
    private $seqlength;

    private $accession;
    private $secAccession;      // array
    private $version;
    private $ncbiGiId;
    private $keywords;
    private $segmentNo;
    private $segmentCount;
    private $segment;
    private $source;

    private $organism;
    private $sequence;
    private $reference;          // array
    private $features;           // array
	
    // Used by SeqAlign class
    private $start;
    private $end;
		
    // Used when DBFORMAT is "SWISSPROT"
    private $swissprot;    // array

    public function getId()
    {
        return $this->id;
    }
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getStrands()
    {
        return $this->strands;
    }
    public function setStrands($strands)
    {
        $this->strands = $strands;
    }
    
    public function getMoltype()
    {
        return $this->moltype;
    }
    public function setMoltype($moltype)
    {
        $this->moltype = $moltype;
    }
    
    public function getTopology()
    {
        return $this->topology;
    }
    public function setTopology($topology)
    {
        $this->topology = $topology;
    }
    
    public function getDivision()
    {
        return $this->division;
    }
    public function setDivision($division)
    {
        $this->division = $division;
    }
    
    public function getDate()
    {
        return $this->date;
    }
    public function setDate($date)
    {
        $this->date = $date;
    }
    
    public function getDefinition()
    {
        return $this->definition;
    }
    public function setDefinition($definition)
    {
        $this->definition = $definition;
    }
    
    public function getSeqlength()
    {
        return $this->seqlength;
    }
    public function setSeqlength($seqlength)
    {
        $this->seqlength = $seqlength;
    }

    public function getAccession()
    {
        return $this->accession;
    }
    public function setAccession($accession)
    {
        $this->accession = $accession;
    }
    
    public function getSecAccession()
    {
        return $this->secAccession;
    }
    public function setSecAccession($secAccession)
    {
        $this->secAccession = $secAccession;
    }
    
    public function getVersion()
    {
        return $this->version;
    }
    public function setVersion($version)
    {
        $this->version = $version;
    }
    
    public function getNcbiGiId()
    {
        return $this->ncbiGiId;
    }
    public function setNcbiGiId($ncbiGiId)
    {
        $this->ncbiGiId = $ncbiGiId;
    }
    
    public function getKeywords()
    {
        return $this->keywords;
    }
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }
    
    public function getSegmentNo()
    {
        return $this->segmentNo;
    }
    public function setSegmentNo($segmentNo)
    {
        $this->segmentNo = $segmentNo;
    }
    
    public function getSegmentCount()
    {
        return $this->segmentCount;
    }
    public function setSegmentCount($segmentCount)
    {
        $this->segmentCount = $segmentCount;
    }
    
    public function getSegment()
    {
        return $this->segment;
    }
    public function setSegment($segment)
    {
        $this->segment = $segment;
    }
    
    public function getSource()
    {
        return $this->source;
    }
    public function setSource($source)
    {
        $this->source = $source;
    }

    public function getOrganism()
    {
        return $this->organism;
    }
    public function setOrganism($organism)
    {
        $this->organism = $organism;
    }
    
    public function getSequence()
    {
        return $this->sequence;
    }
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }
    
    public function getReference()
    {
        return $this->reference;
    }
    public function setReference($reference)
    {
        $this->reference = $reference;
    }
    
    public function getFeatures()
    {
        return $this->features;
    }
    public function setFeatures($features)
    {
        $this->features = $features;
    }

    public function getStart()
    {
        return $this->start;
    }
    public function setStart($start)
    {
        $this->start = $start;
    }
    
    public function getEnd()
    {
        return $this->end;
    }
    public function setEnd($end)
    {
        $this->end = $end;
    }
		
    public function getSwissprot()
    {
        return $this->swissprot;
    }
    public function setSwissprot($swissprot)
    {
        $this->swissprot = $swissprot;
    }
}
