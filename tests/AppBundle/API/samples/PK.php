<?php

use AppBundle\Api\DTO\PKDTO;

$aPKObjects = [];
$pkValue = new PKDTO();
$pkValue->setId("EMBOSS");
$pkValue->setNTerminus(8.6);
$pkValue->setK(10.8);
$pkValue->setR(12.5);
$pkValue->setH(6.5);
$pkValue->setCTerminus(3.6);
$pkValue->setD(3.9);
$pkValue->setE(4.1);
$pkValue->setC(8.5);
$pkValue->setY(10.1);
$aPKObjects[] = $pkValue;

$pkValue = new PKDTO();
$pkValue->setId("DTASelect");
$pkValue->setNTerminus(8);
$pkValue->setK(10);
$pkValue->setR(12);
$pkValue->setH(6.5);
$pkValue->setCTerminus(3.1);
$pkValue->setD(4.4);
$pkValue->setE(4.4);
$pkValue->setC(8.5);
$pkValue->setY(10);
$aPKObjects[] = $pkValue;

$pkValue = new PKDTO();
$pkValue->setId("Solomon");
$pkValue->setNTerminus(9.6);
$pkValue->setK(10.5);
$pkValue->setR(125);
$pkValue->setH(6.0);
$pkValue->setCTerminus(2.4);
$pkValue->setD(3.9);
$pkValue->setE(4.3);
$pkValue->setC(8.3);
$pkValue->setY(10.1);
$aPKObjects[] = $pkValue;