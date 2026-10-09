<?php
namespace Tests\AppBundle\Service;

use Amelaye\BioPHP\Api\DTO\NucleotidDTO;

$aNucleoObjects = [];
$nucleotid = new NucleotidDTO();
$nucleotid->setLetter("A");
$nucleotid->setComplement("T");
$nucleotid->setNature("DNA");
$nucleotid->setWeight(313.2065);
$aNucleoObjects[] = $nucleotid;

$nucleotid = new NucleotidDTO();
$nucleotid->setLetter("T");
$nucleotid->setComplement("A");
$nucleotid->setNature("DNA");
$nucleotid->setWeight(304.1932);
$aNucleoObjects[] = $nucleotid;

$nucleotid = new NucleotidDTO();
$nucleotid->setLetter("G");
$nucleotid->setComplement("C");
$nucleotid->setNature("DNA");
$nucleotid->setWeight(329.2059);
$aNucleoObjects[] = $nucleotid;

$nucleotid = new NucleotidDTO();
$nucleotid->setLetter("C");
$nucleotid->setComplement("G");
$nucleotid->setNature("DNA");
$nucleotid->setWeight(289.1818);
$aNucleoObjects[] = $nucleotid;

$nucleotid = new NucleotidDTO();
$nucleotid->setLetter("A");
$nucleotid->setComplement("U");
$nucleotid->setNature("RNA");
$nucleotid->setWeight(329.2059);
$aNucleoObjects[] = $nucleotid;

$nucleotid = new NucleotidDTO();
$nucleotid->setLetter("U");
$nucleotid->setComplement("A");
$nucleotid->setNature("RNA");
$nucleotid->setWeight(306.166);
$aNucleoObjects[] = $nucleotid;

$nucleotid = new NucleotidDTO();
$nucleotid->setLetter("G");
$nucleotid->setComplement("C");
$nucleotid->setNature("RNA");
$nucleotid->setWeight(345.2053);
$aNucleoObjects[] = $nucleotid;

$nucleotid = new NucleotidDTO();
$nucleotid->setLetter("C");
$nucleotid->setComplement("G");
$nucleotid->setNature("RNA");
$nucleotid->setWeight(305.1812);
$aNucleoObjects[] = $nucleotid;