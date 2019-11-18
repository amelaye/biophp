<?php


namespace Tests\MinitoolsBundle\Service;

use MinitoolsBundle\Service\ReduceProteinAlphabetManager;
use PHPUnit\Framework\TestCase;

class ReduceProteinAlphabetManagerTest extends TestCase
{
    protected $proteinColors;

    public function setUp()
    {
        $this->proteinColors = [
          20 => [
            "A" => "CCFFFF",
            "R" => "E60606",
            "N" => "FF9900",
            "D" => "FFCC99",
            "C" => "00FFFF",
            "E" => "FFCC00",
            "Q" => "FF6600",
            "G" => "00FF00",
            "H" => "FFFF99",
            "I" => "000088",
            "L" => "3366FF",
            "K" => "C64200",
            "M" => "99CCFF",
            "F" => "00CCFF",
            "P" => "FFFF00",
            "S" => "CCFF99",
            "T" => "00FF99",
            "W" => "CC99FF",
            "Y" => "CCFFCC",
            "V" => "0000FF",
          ],
          2 => [
            "P" => "0000FF",
            "H" => "FF0000",
          ],
          5 => [
            "A" => "FF0000",
            "R" => "00FF00",
            "C" => "0000FF",
            "T" => "FFFF00",
            "D" => "00FFFF",
          ],
          6 => [
            "A" => "FF0000",
            "R" => "00FF00",
            "P" => "0000FF",
            "N" => "8888FF",
            "T" => "FFFF00",
            "D" => "00FFFF",
          ],
          "3IMG" => [
            "P" => "E60606",
            "N" => "FFFF00",
            "H" => "3366FF",
          ],
          "5IMG" => [
            "G" => "E60606",
            "C" => "E60606",
            "E" => "E60606",
            "M" => "E60606",
            "F" => "E60606",
          ],
          "11IMG" => [
            "A" => "1B04AE",
            "F" => "00CCFF",
            "C" => "CCECFF",
            "G" => "00FF00",
            "S" => "89F88B",
            "W" => "CC99FF",
            "Y" => "CCFFCC",
            "P" => "FFFF00",
            "D" => "FFCC00",
            "N" => "F4A504",
            "H" => "EC1504",
          ],
          "Murphy15" => [
            "L" => "FF0000",
            "C" => "00FF00",
            "A" => "0000FF",
            "G" => "FFFF00",
            "S" => "00FFFF",
            "T" => "FF00FF",
            "P" => "880000",
            "F" => "008800",
            "W" => "000088",
            "E" => "888800",
            "D" => "008888",
            "N" => "880088",
            "Q" => "FF8888",
            "K" => "88FF88",
            "H" => "8888FF",
          ],
          "Murphy10" => [
            "L" => "FF0000",
            "C" => "00FF00",
            "A" => "0000FF",
            "G" => "FFFF00",
            "S" => "00FFFF",
            "P" => "880000",
            "F" => "008800",
            "E" => "888800",
            "K" => "88FF88",
            "H" => "8888FF",
          ],
          "Murphy8" => [
            "L" => "FF0000",
            "A" => "0000FF",
            "S" => "00FFFF",
            "P" => "880000",
            "F" => "0000FF",
            "E" => "008800",
            "K" => "88FF88",
            "H" => "0000FF",
          ],
          "Murphy4" => [
            "L" => "00FF00",
            "A" => "00FFFF",
            "F" => "FF0000",
            "E" => "0000FF",
          ],
          "Murphy2" => [
            "P" => "FF0000",
            "E" => "0000FF",
          ],
          "Wang5" => [
            "I" => "FF0000",
            "A" => "00FF00",
            "G" => "FFFF00",
            "E" => "0000FF",
            "K" => "00FFFF",
          ],
          "Wang5v" => [
            "I" => "FF0000",
            "L" => "FFFF00",
            "A" => "00FF00",
            "E" => "0000FF",
            "K" => "00FFFF",
          ],
          "Wang3" => [
            "I" => "FF0000",
            "A" => "00FF00",
            "E" => "0000FF",
          ],
          "Wang2" => [
            "I" => "FF0000",
            "A" => "0000FF",
          ],
          "Li10" => [
            "C" => "FF0000",
            "Y" => "FFFF00",
            "L" => "FF00FF",
            "V" => "FF8888",
            "G" => "00FFFF",
            "P" => "88FF88",
            "S" => "00FF00",
            "N" => "8888FF",
            "E" => "0000FF",
            "K" => "88FFFF",
          ],
          "Li5" => [
            "Y" => "FFFF00",
            "I" => "FF0000",
            "G" => "00FFFF",
            "S" => "00FF00",
            "E" => "0000FF",
          ],
          "Li4" => [
            "Y" => "FFFF00",
            "I" => "FF0000",
            "S" => "00FF00",
            "E" => "0000FF",
          ],
          "Li3" => [
            "I" => "FF0000",
            "S" => "00FF00",
            "E" => "0000FF",
          ]
        ];

        $aReductions = [
          20 => [
            "pattern" => [
              0 => "/-/"
            ],
            "reduction" => [
              0 => "-"
            ]
          ],
          2 => [
            "pattern" => [
              0 => "/A|G|T|S|N|Q|D|E|H|R|K|P/",
              1 => "/C|M|F|I|L|V|W|Y/"
            ],
            "reduction" => [
              0 => "p",
              1 => "h"
            ]
          ],
          5 => [
            "pattern" => [
              0 => "/I|V|L/",
              1 => "/F|Y|W|H/",
              2 => "/K|R|D|E/",
              3 => "/G|A|C|S/",
              4 => "/T|M|Q|N|P/"
            ],
            "reduction" => [
              0 => "a",
              1 => "r",
              2 => "c",
              3 => "t",
              4 => "d",
            ],
          ],
          6 => [
            "pattern" => [
              0 => "/I|V|L/",
              1 => "/F|Y|W|H/",
              2 => "/K|R/",
              3 => "/D|E/",
              4 => "/G|A|C|S/",
              5 => "/T|M|Q|N|P/",
            ],
            "reduction" => [
              0 => "a",
              1 => "r",
              2 => "p",
              3 => "n",
              4 => "t",
              5 => "d",
            ],
          ],
          "3IMG" => [
            "pattern" => [
              0 => "/D|N|E|Q|K|R/",
              1 => "/G|T|S|Y|P|M/",
              2 => "/I|V|L|F|C|M|A|W/",
            ],
            "reduction" => [
              0 => "p",
              1 => "n",
              2 => "h",
            ],
          ],
          "5IMG" => [
            "pattern" => [
              0 => "/G|A|S/",
              1 => "/C|D|P|N|T/",
              2 => "/E|V|Q|H/",
              3 => "/M|I|L|K|R/",
              4 => "/F|Y|W/",
            ],
            "reduction" => [
              0 => "g",
              1 => "c",
              2 => "e",
              3 => "m",
              4 => "f",
            ],
          ],
          "11IMG" => [
            "pattern" => [
              0 => "/A|V|I|L/",
              1 => "/F/",
              2 => "/C|M/",
              3 => "/G/",
              4 => "/S|T/",
              5 => "/W/",
              6 => "/Y/",
              7 => "/P/",
              8 => "/D|E/",
              9 => "/N|Q/",
              10 => "/H|K|R/",
            ],
            "reduction" => [
              0 => "a",
              1 => "f",
              2 => "c",
              3 => "g",
              4 => "s",
              5 => "w",
              6 => "y",
              7 => "p",
              8 => "d",
              9 => "n",
              10 => "h",
            ],
          ],
          "Murphy15" => [
            "pattern" => [
              0 => "/L|V|I|M/",
              1 => "/C/",
              2 => "/A/",
              3 => "/G/",
              4 => "/S/",
              5 => "/T/",
              6 => "/P/",
              7 => "/F|Y/",
              8 => "/W/",
              9 => "/E/",
              10 => "/D/",
              11 => "/N/",
              12 => "/Q/",
              13 => "/K|R/",
              14 => "/H/",
            ],
            "reduction" => [
              0 => "l",
              1 => "c",
              2 => "a",
              3 => "g",
              4 => "s",
              5 => "t",
              6 => "p",
              7 => "f",
              8 => "w",
              9 => "e",
              10 => "d",
              11 => "n",
              12 => "q",
              13 => "k",
              14 => "h",
            ],
          ],
          "Murphy10" => [
            "pattern" => [
              0 => "/L|V|I|M/",
              1 => "/C/",
              2 => "/A/",
              3 => "/G/",
              4 => "/S|T/",
              5 => "/P/",
              6 => "/F|Y|W/",
              7 => "/E|D|N|Q/",
              8 => "/K|R/",
              9 => "/H/",
            ],
            "reduction" => [
              0 => "l",
              1 => "c",
              2 => "a",
              3 => "g",
              4 => "s",
              5 => "p",
              6 => "f",
              7 => "e",
              8 => "k",
              9 => "h",
            ],
          ],
          "Murphy4" => [
            "pattern" => [
              0 => "/L|V|I|M|C/",
              1 => "/A|G|S|T|P/",
              2 => "/F|Y|W/",
              3 => "/E|D|N|Q|K|R|H/"
            ],
            "reduction" => [
              0 => "l",
              1 => "a",
              2 => "f",
              3 => "e",
            ],
          ],
          "Murphy2" => [
            "pattern" => [
              0 => "/L|V|I|M|C|A|G|S|T|P|F|Y|W/",
              1 => "/E|D|N|Q|K|R|H/",
            ],
            "reduction" => [
              0 => "p",
              1 => "e",
            ],
          ],
          "Wang5" => [
            "pattern" => [
              0 => "/C|M|F|I|L|V|W|Y/",
              1 => "/A|T|H/",
              2 => "/G|P/",
              3 => "/D|E/",
              4 => "/S|N|Q|R|K/"
            ],
            "reduction" => [
              0 => "i",
              1 => "a",
              2 => "g",
              3 => "e",
              4 => "k",
            ],
          ],
          "Wang5v" => [
            "pattern" => [
              0 => "/C|M|F|I/",
              1 => "/L|V|W|Y/",
              2 => "/A|T|G|S/",
              3 => "/N|Q|D|E/",
              4 => "/H|P|R|K/",
            ],
            "reduction" => [
              0 => "i",
              1 => "l",
              2 => "a",
              3 => "e",
              4 => "k",
            ],
          ],
          "Wang3" => [
            "pattern" => [
              0 => "/C|M|F|I|L|V|W|Y/",
              1 => "/A|T|H|G|P|R/",
              2 => "/D|E|S|N|Q|K/",
            ],
            "reduction" => [
              0 => "i",
              1 => "a",
              2 => "e",
            ],
          ],
          "Wang2" => [
            "pattern" => [
              0 => "/C|M|F|I|L|V|W|Y/",
              1 => "/A|T|H|G|P|R|D|E|S|N|Q|K/",
            ],
            "reduction" => [
              0 => "i",
              1 => "a",
            ],
          ],
          "Li10" => [
            "pattern" => [
              0 => "/C/",
              1 => "/F|Y|W/",
              2 => "/M|L/",
              3 => "/I|V/",
              4 => "/G/",
              5 => "/P/",
              6 => "/A|T|S/",
              7 => "/N|H/",
              8 => "/Q|E|D/",
              9 => "/R|K/",
            ],
            "reduction" => [
              0 => "c",
              1 => "y",
              2 => "l",
              3 => "v",
              4 => "g",
              5 => "p",
              6 => "s",
              7 => "n",
              8 => "e",
              9 => "k",
            ],
          ],
          "Li5" => [
            "pattern" => [
              0 => "/C|F|Y|W/",
              1 => "/M|L|I|V/",
              2 => "/G/",
              3 => "/P|A|T|S/",
              4 => "/N|H|Q|E|D|R|K/",
            ],
            "reduction" => [
              0 => "y",
              1 => "i",
              2 => "g",
              3 => "s",
              4 => "e",
            ],
          ],
          "Li4" => [
            "pattern" => [
              0 => "/C|F|Y|W/",
              1 => "/M|L|I|V/",
              2 => "/G|P|A|T|S/",
              3 => "/N|H|Q|E|D|R|K/",
            ],
            "reduction" => [
              0 => "y",
              1 => "i",
              2 => "s",
              3 => "e",
            ],
          ],
          "Li3" => [
            "pattern" => [
              0 => "/C|F|Y|W|M|L|I|V/",
              1 => "/G|P|A|T|S/",
              2 => "/N|H|Q|E|D|R|K/",
            ],
            "reduction" => [
              0 => "i",
              1 => "s",
              2 => "e",
            ],
          ]
        ];

        $a11Imgt = [
            "Description" => "11 IMGT amino acid chemical characteristics alphabet",
            "Elements" => [
                "AVIL" => "A: Aliphatic",
                "F" => "F: Phenylalanine",
                "CM" => "G: Sulfur",
                "G" => "G: Glycine",
                "ST" => "S: Hydroxyl",
                "W" => "W: Tryptophan",
                "Y" => "Y: Tyrosine",
                "P" => "P: Proline",
                "DE" => "A: Acidic",
                "NQ" => "N: Amide",
                "HKR" => "H: Basic",
            ]
        ];

        /**
         * Mock API
         */
        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->apiMock = $this->getMockBuilder('AppBundle\Api\Bioapi')
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(["getReductions","createReduceCode"])
            ->getMock();
        $this->apiMock->method("getReductions")->will($this->returnValue($aReductions));
        $this->apiMock->method("createReduceCode")->will($this->returnValue($a11Imgt));
    }

    public function testReduceAlphabet()
    {
        $sSequence = "ARNDCEQGHILKMFPSTWYVX*";
        $sType = "11IMG";
        $sExpected = "AHNDCDNGHAAHCFPSSWYAX*";
        $service = new ReduceProteinAlphabetManager($this->proteinColors, $this->apiMock);
        $testFunction = $service->reduceAlphabet($sSequence, $sType);

        $this->assertEquals($sExpected, $testFunction);
    }

    public function testReduceAlphabetCustom()
    {
        $sSequence = "ARNDCEQGHILKMFPSTWYVX*";
        $sCustomAlphabet = "TCDCTCDTRAACDRDTDRRA";
        $sExpected = "TCDCTCDTRAACDRDTDRRAX*";

        $service = new ReduceProteinAlphabetManager($this->proteinColors, $this->apiMock);
        $testFunction = $service->reduceAlphabetCustom($sSequence, $sCustomAlphabet);

        $this->assertEquals($sExpected, $testFunction);
    }
}