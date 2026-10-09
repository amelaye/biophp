<?php


namespace Tests\Domain\Sequence\Service;

use Amelaye\BioPHP\Api\DTO\VendorLinkDTO;

$vendorLinksObjects = [];

$link = new VendorLinkDTO();
$link->setId("B");
$link->setName("Thermo Fisher Scientific");
$link->setLink("https://www.thermofisher.com");
$vendorLinksObjects[] = $link;

$link = new VendorLinkDTO();
$link->setId("E");
$link->setName("Agilent Technologies");
$link->setLink("https://www.agilent.com");
$vendorLinksObjects[] = $link;

$link = new VendorLinkDTO();
$link->setId("I");
$link->setName("SibEnzyme Ltd.");
$link->setLink("http://www.sibenzyme.com");
$vendorLinksObjects[] = $link;

$link = new VendorLinkDTO();
$link->setId("J");
$link->setName("Nippon Gene Co., Ltd.");
$link->setLink("http://www.nippongene.jp");
$vendorLinksObjects[] = $link;

$link = new VendorLinkDTO();
$link->setId("K");
$link->setName("Takara Bio Inc.");
$link->setLink("https://www.takarabio.com");
$vendorLinksObjects[] = $link;

$link = new VendorLinkDTO();
$link->setId("M");
$link->setName("Roche Custom Biotech");
$link->setLink("http://www.roche.com");
$vendorLinksObjects[] = $link;

$link = new VendorLinkDTO();
$link->setId("N");
$link->setName("New England Biolabs");
$link->setLink("http://www.neb.com");
$vendorLinksObjects[] = $link;

$link = new VendorLinkDTO();
$link->setId("O");
$link->setName("Toyobo Biochemicals");
$link->setLink("http://www.toyobo.co.jp/e/");
$vendorLinksObjects[] = $link;

$link = new VendorLinkDTO();
$link->setId("Q");
$link->setName("CHIMERx");
$link->setLink("http://www.CHIMERx.com");
$vendorLinksObjects[] = $link;

$link = new VendorLinkDTO();
$link->setId("R");
$link->setName("Promega Corporation");
$link->setLink("http://www.promega.com");
$vendorLinksObjects[] = $link;

$link = new VendorLinkDTO();
$link->setId("S");
$link->setName("Sigma Chemical Corporation");
$link->setLink("http://www.sigmaaldrich.com");
$vendorLinksObjects[] = $link;

$link = new VendorLinkDTO();
$link->setId("V");
$link->setName("Vivantis Technologies");
$link->setLink("https://vivantechnologies.com");
$vendorLinksObjects[] = $link;

$link = new VendorLinkDTO();
$link->setId("X");
$link->setName("EURx Ltd.");
$link->setLink("http://www.eurx.com.pl/index.php?op=catalog&cat=8");
$vendorLinksObjects[] = $link;
