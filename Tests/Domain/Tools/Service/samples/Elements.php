<?php
namespace Tests\AppBundle\Service;
use Amelaye\BioPHP\Api\DTO\ElementDTO;

$aElementsObjects = [];
$element = new ElementDTO();
$element->setName("carbone");
$element->setWeight(12.01);
$aElementsObjects[] = $element;

$element = new ElementDTO();
$element->setName("oxygene");
$element->setWeight(16.00);
$aElementsObjects[] = $element;

$element = new ElementDTO();
$element->setName("nitrate");
$element->setWeight(14.01);
$aElementsObjects[] = $element;

$element = new ElementDTO();
$element->setName("hydrogene");
$element->setWeight(1.01);
$aElementsObjects[] = $element;

$element = new ElementDTO();
$element->setName("phosphore");
$element->setWeight(30.97);
$aElementsObjects[] = $element;

$element = new ElementDTO();
$element->setName("water");
$element->setWeight(18.015);
$aElementsObjects[] = $element;