<?php

/**
 * Entity used by form RandomSequencesType
 * Freely inspired by BioPHP's project biophp.org
 * Created 6 april 2019
 * Last modified 6 april 2019
 */
namespace MinitoolsBundle\Entity;

/**
 * Class RandomSequences
 * @package MinitoolsBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class RandomSequences
{
    /**
     * @var string
     */
    private $procedure;

    /**
     * @var string
     */
    private $seq;

    /**
     * @var int
     */
    private $length1;

    /**
     * @var int
     */
    private $length2;

    /**
     * @var float
     */
    private $dnaA;

    /**
     * @var float
     */
    private $dnaC;

    /**
     * @var float
     */
    private $dnaG;

    /**
     * @var float
     */
    private $dnaT;

    /**
     * @var int
     */
    private $length3;

    /**
     * @var float
     */
    private $a;

    /**
     * @var float
     */
    private $c;

    /**
     * @var float
     */
    private $d;

    /**
     * @var float
     */
    private $e;

    /**
     * @var float
     */
    private $f;

    /**
     * @var float
     */
    private $g;

    /**
     * @var float
     */
    private $h;

    /**
     * @var float
     */
    private $i;

    /**
     * @var float
     */
    private $k;

    /**
     * @var float
     */
    private $l;

    /**
     * @var float
     */
    private $m;

    /**
     * @var float
     */
    private $n;

    /**
     * @var float
     */
    private $p;

    /**
     * @var float
     */
    private $q;

    /**
     * @var float
     */
    private $r;

    /**
     * @var float
     */
    private $s;

    /**
     * @var float
     */
    private $t;

    /**
     * @var float
     */
    private $v;

    /**
     * @var float
     */
    private $w;

    /**
     * @var float
     */
    private $y;

    /**
     * @return string
     */
    public function getProcedure()
    {
        return $this->procedure;
    }

    /**
     * @param string $procedure
     */
    public function setProcedure($procedure)
    {
        $this->procedure = $procedure;
    }

    /**
     * @return string
     */
    public function getSeq()
    {
        return $this->seq;
    }

    /**
     * @param string $seq
     */
    public function setSeq($seq)
    {
        $seq = strtoupper($seq);
        $seq = preg_replace("/\W|[^ABCDEFGHIKLMNPQRSTVWXY]|\d/","",$seq);
        $this->seq = $seq;
    }

    /**
     * @return int
     */
    public function getLength1()
    {
        return $this->length1;
    }

    /**
     * @param int $length1
     */
    public function setLength1($length1)
    {
        $this->length1 = $length1;
    }

    /**
     * @return int
     */
    public function getLength2()
    {
        return $this->length2;
    }

    /**
     * @param int $length2
     */
    public function setLength2($length2)
    {
        $this->length2 = $length2;
    }

    /**
     * @return float
     */
    public function getDnaA()
    {
        return $this->dnaA;
    }

    /**
     * @param float $dnaA
     */
    public function setDnaA($dnaA)
    {
        $this->dnaA = $dnaA;
    }

    /**
     * @return float
     */
    public function getDnaC()
    {
        return $this->dnaC;
    }

    /**
     * @param float $dnaC
     */
    public function setDnaC($dnaC)
    {
        $this->dnaC = $dnaC;
    }

    /**
     * @return float
     */
    public function getDnaG()
    {
        return $this->dnaG;
    }

    /**
     * @param float $dnaG
     */
    public function setDnaG($dnaG)
    {
        $this->dnaG = $dnaG;
    }

    /**
     * @return float
     */
    public function getDnaT()
    {
        return $this->dnaT;
    }

    /**
     * @param float $dnaT
     */
    public function setDnaT($dnaT)
    {
        $this->dnaT = $dnaT;
    }

    /**
     * @return int
     */
    public function getLength3()
    {
        return $this->length3;
    }

    /**
     * @param int $length3
     */
    public function setLength3($length3)
    {
        $this->length3 = $length3;
    }

    /**
     * @return float
     */
    public function getA()
    {
        return $this->a;
    }

    /**
     * @param float $a
     */
    public function setA($a)
    {
        $this->a = $a;
    }

    /**
     * @return float
     */
    public function getC()
    {
        return $this->c;
    }

    /**
     * @param float $c
     */
    public function setC($c)
    {
        $this->c = $c;
    }

    /**
     * @return float
     */
    public function getD()
    {
        return $this->d;
    }

    /**
     * @param float $d
     */
    public function setD($d)
    {
        $this->d = $d;
    }

    /**
     * @return float
     */
    public function getE()
    {
        return $this->e;
    }

    /**
     * @param float $e
     */
    public function setE($e)
    {
        $this->e = $e;
    }

    /**
     * @return float
     */
    public function getF()
    {
        return $this->f;
    }

    /**
     * @param float $f
     */
    public function setF($f)
    {
        $this->f = $f;
    }

    /**
     * @return float
     */
    public function getG()
    {
        return $this->g;
    }

    /**
     * @param float $g
     */
    public function setG($g)
    {
        $this->g = $g;
    }

    /**
     * @return float
     */
    public function getH()
    {
        return $this->h;
    }

    /**
     * @param float $h
     */
    public function setH($h)
    {
        $this->h = $h;
    }

    /**
     * @return float
     */
    public function getI()
    {
        return $this->i;
    }

    /**
     * @param float $i
     */
    public function setI($i)
    {
        $this->i = $i;
    }

    /**
     * @return float
     */
    public function getK()
    {
        return $this->k;
    }

    /**
     * @param float $k
     */
    public function setK($k)
    {
        $this->k = $k;
    }

    /**
     * @return float
     */
    public function getL()
    {
        return $this->l;
    }

    /**
     * @param float $l
     */
    public function setL($l)
    {
        $this->l = $l;
    }

    /**
     * @return float
     */
    public function getM()
    {
        return $this->m;
    }

    /**
     * @param float $m
     */
    public function setM($m)
    {
        $this->m = $m;
    }

    /**
     * @return float
     */
    public function getN()
    {
        return $this->n;
    }

    /**
     * @param float $n
     */
    public function setN($n)
    {
        $this->n = $n;
    }

    /**
     * @return float
     */
    public function getP()
    {
        return $this->p;
    }

    /**
     * @param float $p
     */
    public function setP($p)
    {
        $this->p = $p;
    }

    /**
     * @return float
     */
    public function getQ()
    {
        return $this->q;
    }

    /**
     * @param float $q
     */
    public function setQ($q)
    {
        $this->q = $q;
    }

    /**
     * @return float
     */
    public function getR()
    {
        return $this->r;
    }

    /**
     * @param float $r
     */
    public function setR($r)
    {
        $this->r = $r;
    }

    /**
     * @return float
     */
    public function getS()
    {
        return $this->s;
    }

    /**
     * @param float $s
     */
    public function setS($s)
    {
        $this->s = $s;
    }

    /**
     * @return float
     */
    public function getT()
    {
        return $this->t;
    }

    /**
     * @param float $t
     */
    public function setT($t)
    {
        $this->t = $t;
    }

    /**
     * @return float
     */
    public function getV()
    {
        return $this->v;
    }

    /**
     * @param float $v
     */
    public function setV($v)
    {
        $this->v = $v;
    }

    /**
     * @return float
     */
    public function getW()
    {
        return $this->w;
    }

    /**
     * @param float $w
     */
    public function setW($w)
    {
        $this->w = $w;
    }

    /**
     * @return float
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @param float $y
     */
    public function setY($y)
    {
        $this->y = $y;
    }
}