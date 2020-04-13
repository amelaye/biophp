<?php
namespace Tests\AppBundle\Service;

use Amelaye\BioPHP\Api\DTO\NucleotidDTO;

$aNucleoObjects = [];
$nucleotid = new NucleotidDTO();
$nucleotid->setLetter("A");
$nucleotid->setComplement("T");
$nucleotid->setNature("DNA");
$nucleotid->setWeight(313.245);
$aNucleoObjects[] = $nucleotid;

$nucleotid = new NucleotidDTO();
$nucleotid->setLetter("T");
$nucleotid->setComplement("A");
$nucleotid->setNature("DNA");
$nucleotid->setWeight(304.225);
$aNucleoObjects[] = $nucleotid;

$nucleotid = new NucleotidDTO();
$nucleotid->setLetter("G");
$nucleotid->setComplement("C");
$nucleotid->setNature("DNA");
$nucleotid->setWeight(329.245);
$aNucleoObjects[] = $nucleotid;

$nucleotid = new NucleotidDTO();
$nucleotid->setLetter("C");
$nucleotid->setComplement("G");
$nucleotid->setNature("DNA");
$nucleotid->setWeight(289.215);
$aNucleoObjects[] = $nucleotid;

$nucleotid = new NucleotidDTO();
$nucleotid->setLetter("A");
$nucleotid->setComplement("U");
$nucleotid->setNature("RNA");
$nucleotid->setWeight(329.245);
$aNucleoObjects[] = $nucleotid;

$nucleotid = new NucleotidDTO();
$nucleotid->setLetter("U");
$nucleotid->setComplement("A");
$nucleotid->setNature("RNA");
$nucleotid->setWeight(306.195);
$aNucleoObjects[] = $nucleotid;

$nucleotid = new NucleotidDTO();
$nucleotid->setLetter("G");
$nucleotid->setComplement("C");
$nucleotid->setNature("RNA");
$nucleotid->setWeight(345.245);
$aNucleoObjects[] = $nucleotid;

$nucleotid = new NucleotidDTO();
$nucleotid->setLetter("C");
$nucleotid->setComplement("G");
$nucleotid->setNature("RNA");
$nucleotid->setWeight(305.215);
$aNucleoObjects[] = $nucleotid;