<?php
/**
 * Protein Managing
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 14 february 2019
 */
namespace AppBundle\Entity;

class Sequence
{
    private $sId;
    private $sStrands;
    private $sMoltype;
    private $sTopology;
    private $sDivision;
    private $sDate;
    private $sDefinition;
    private $iSeqlength;
    private $sAccession;
    private $aSecAccession;
    private $sVersion;
    private $ncbiGiId;
    private $aKeywords;
    private $iSegmentNo;
    private $iSegmentCount;
    private $segment;
    private $sSource;
    private $sOrganism;
    private $aSequence;
    private $aReference;
    private $aFeatures;

    // Used by SeqAlign class
    private $iStart;
    private $iEnd;

    /**
     * Used when DBFORMAT is "SWISSPROT"
     * @var array
     */
    private $aSwissprot;
    
    
    /*****************************
     *****  GETTERS / SETTERS ****
     *****************************/

    public function getId()
    {
        return $this->sId;
    }
    public function setId($sId)
    {
        $this->sId = $sId;
    }

    public function getStrands()
    {
        return $this->sStrands;
    }
    public function setStrands($sStrands)
    {
        $this->sStrands = $sStrands;
    }

    public function getMoltype()
    {
        return $this->sMoltype;
    }
    public function setMoltype($sMoltype)
    {
        $this->sMoltype = $sMoltype;
    }

    public function getTopology()
    {
        return $this->sTopology;
    }
    public function setTopology($sTopology)
    {
        $this->sTopology = $sTopology;
    }

    public function getDivision()
    {
        return $this->sDivision;
    }
    public function setDivision($sDivision)
    {
        $this->sDivision = $sDivision;
    }

    public function getDate()
    {
        return $this->sDate;
    }
    public function setDate($sDate)
    {
        $this->sDate = $sDate;
    }

    public function getDefinition()
    {
        return $this->sDefinition;
    }
    public function setDefinition($sDefinition)
    {
        $this->sDefinition = $sDefinition;
    }

    public function getSeqlength()
    {
        return $this->iSeqlength;
    }
    public function setSeqlength($iSeqlength)
    {
        $this->iSeqlength = $iSeqlength;
    }

    public function getAccession()
    {
        return $this->sAccession;
    }
    public function setAccession($sAccession)
    {
        $this->sAccession = $sAccession;
    }

    public function getSecAccession()
    {
        return $this->aSecAccession;
    }
    public function setSecAccession($aSecAccession)
    {
        $this->aSecAccession = $aSecAccession;
    }

    public function getVersion()
    {
        return $this->sVersion;
    }
    public function setVersion($sVersion)
    {
        $this->sVersion = $sVersion;
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
        return $this->aKeywords;
    }
    public function setKeywords($aKeywords)
    {
        $this->aKeywords = $aKeywords;
    }

    public function getSegmentNo()
    {
        return $this->iSegmentNo;
    }
    public function setSegmentNo($iSegmentNo)
    {
        $this->iSegmentNo = $iSegmentNo;
    }

    public function getSegmentCount()
    {
        return $this->iSegmentCount;
    }
    public function setSegmentCount($iSegmentCount)
    {
        $this->iSegmentCount = $iSegmentCount;
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
        return $this->sSource;
    }
    public function setSource($sSource)
    {
        $this->sSource = $sSource;
    }

    public function getOrganism()
    {
        return $this->sOrganism;
    }
    public function setOrganism($sOrganism)
    {
        $this->sOrganism = $sOrganism;
    }

    public function getSequence()
    {
        return $this->aSequence;
    }
    public function setSequence($aSequence)
    {
        $this->aSequence = $aSequence;
    }

    public function getReference()
    {
        return $this->aReference;
    }
    public function setReference($aReference)
    {
        $this->aReference = $aReference;
    }

    public function getFeatures()
    {
        return $this->aFeatures;
    }
    public function setFeatures($aFeatures)
    {
        $this->aFeatures = $aFeatures;
    }

    public function getStart()
    {
        return $this->iStart;
    }
    public function setStart($iStart)
    {
        $this->iStart = $iStart;
    }

    public function getEnd()
    {
        return $this->iEnd;
    }
    public function setEnd($iEnd)
    {
        $this->iEnd = $iEnd;
    }

    public function getSwissprot()
    {
        return $this->aSwissprot;
    }
    public function setSwissprot($aSwissprot)
    {
        $this->aSwissprot = $aSwissprot;
    }
}
