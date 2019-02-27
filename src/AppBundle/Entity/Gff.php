<?php
/**
 * Gff Entity
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 26 february 2019
 */
namespace AppBundle\Entity;

class Gff
{
    private $seqid;
    private $source;
    private $type;
    private $start;
    private $end;
    private $score;
    private $strand;
    private $phase;
    private $attributes;


    public function getSeqid()
    {
        return $this->seqid;
    }
    public function setSeqid($seqid)
    {
        $this->seqid = $seqid;
    }


    public function getSource()
    {
        return $this->source;
    }
    public function setSource($source)
    {
        $this->source = $source;
    }


    public function getType()
    {
        return $this->type;
    }
    public function setType($type)
    {
        $this->type = $type;
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


    public function getScore()
    {
        return $this->score;
    }
    public function setScore($score)
    {
        $this->score = $score;
    }


    public function getStrand()
    {
        return $this->strand;
    }
    public function setStrand($strand)
    {
        $this->strand = $strand;
    }


    public function getPhase()
    {
        return $this->phase;
    }
    public function setPhase($phase)
    {
        $this->phase = $phase;
    }



    public function getAttributes()
    {
        return $this->attributes;
    }
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }
}