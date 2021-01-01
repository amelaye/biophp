<?php
namespace Tests\Api;

use Amelaye\BioPHP\Api\TripletSpecieApi;
use GuzzleHttp;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TripletSpecieApiTest extends WebTestCase
{
    public function setUp()
    {
        $aTripletSpeciesObjects = [];

        require 'samples/TripletsSpecies.php';

        $this->aTriplets = $aTripletSpeciesObjects;
        $this->clientMock = new GuzzleHttp\Client(['base_uri' => 'http://api.amelayes-biophp.net']);
        $this->serializerMock = \JMS\Serializer\SerializerBuilder::create()
            ->build();
    }

    public function testGetTriplets()
    {
        $apiTriplets = new TripletSpecieApi($this->clientMock, $this->serializerMock);
        static::assertEquals($this->aTriplets, $apiTriplets->getTriplets());
    }

    public function testGetTripletsGroups()
    {
        $aExpected = [
          "standard" => [
            0 => "(TTT |TTC )",
            1 => "(TTA |TTG |CT. )",
            2 => "(ATT |ATC |ATA )",
            3 => "(ATG )",
            4 => "(GT. )",
            5 => "(TC. |AGT |AGC )",
            6 => "(CC. )",
            7 => "(AC. )",
            8 => "(GC. )",
            9 => "(TAT |TAC )",
            10 => "(TAA |TAG |TGA )",
            11 => "(CAT |CAC )",
            12 => "(CAA |CAG )",
            13 => "(AAT |AAC )",
            14 => "(AAA |AAG )",
            15 => "(GAT |GAC )",
            16 => "(GAA |GAG )",
            17 => "(TGT |TGC )",
            18 => "(TGG )",
            19 => "(CG. |AGA |AGG )",
            20 => "(GG. )",
            21 => "(\S\S\S )",
          ],
          "vertebrate_mitochondrial" => [
            0 => "(TTT |TTC )",
            1 => "(TTA |TTG |CT. )",
            2 => "(ATT |ATC |ATA )",
            3 => "(ATG )",
            4 => "(GT. )",
            5 => "(TC. |AGT |AGC )",
            6 => "(CC. )",
            7 => "(AC. )",
            8 => "(GC. )",
            9 => "(TAT |TAC )",
            10 => "(TAA |TAG |AGA |AGG )",
            11 => "(CAT |CAC )",
            12 => "(CAA |CAG )",
            13 => "(AAT |AAC )",
            14 => "(AAA |AAG )",
            15 => "(GAT |GAC )",
            16 => "(GAA |GAG )",
            17 => "(TGT |TGC )",
            18 => "(TGG |TGA )",
            19 => "(CG. )",
            20 => "(GG. )",
            21 => "(\S\S\S )",
          ],
          "yeast_mitochondrial" => [
            0 => "(TTT |TTC )",
            1 => "(TTA |TTG )",
            2 => "(ATT |ATC )",
            3 => "(ATG |ATA )",
            4 => "(GT. )",
            5 => "(TC. |AGT |AGC )",
            6 => "(CC. )",
            7 => "(AC. |CT. )",
            8 => "(GC. )",
            9 => "(TAT |TAC )",
            10 => "(TAA |TAG )",
            11 => "(CAT |CAC )",
            12 => "(CAA |CAG )",
            13 => "(AAT |AAC )",
            14 => "(AAA |AAG )",
            15 => "(GAT |GAC )",
            16 => "(GAA |GAG )",
            17 => "(TGT |TGC )",
            18 => "(TGG |TGA )",
            19 => "(CG. |AGA |AGG )",
            20 => "(GG. )",
            21 => "(\S\S\S )",
          ],
          "mold_protozoan_coelenterate_mitochondrial" => [
            0 => "(TTT |TTC )",
            1 => "(TTA |TTG |CT. )",
            2 => "(ATT |ATC |ATA )",
            3 => "(ATG )",
            4 => "(GT. )",
            5 => "(TC. |AGT |AGC )",
            6 => "(CC. )",
            7 => "(AC. )",
            8 => "(GC. )",
            9 => "(TAT |TAC )",
            10 => "(TAA |TAG )",
            11 => "(CAT |CAC )",
            12 => "(CAA |CAG )",
            13 => "(AAT |AAC )",
            14 => "(AAA |AAG )",
            15 => "(GAT |GAC )",
            16 => "(GAA |GAG )",
            17 => "(TGT |TGC )",
            18 => "(TGG |TGA )",
            19 => "(CG. |AGA |AGG )",
            20 => "(GG. )",
            21 => "(\S\S\S )",
          ],
          "invertebrate_mitochondrial" => [
            0 => "(TTT |TTC )",
            1 => "(TTA |TTG |CT. )",
            2 => "(ATT |ATC )",
            3 => "(ATG |ATA )",
            4 => "(GT. )",
            5 => "(TC. |AG. )",
            6 => "(CC. )",
            7 => "(AC. )",
            8 => "(GC. )",
            9 => "(TAT |TAC )",
            10 => "(TAA |TAG )",
            11 => "(CAT |CAC )",
            12 => "(CAA |CAG )",
            13 => "(AAT |AAC )",
            14 => "(AAA |AAG )",
            15 => "(GAT |GAC )",
            16 => "(GAA |GAG )",
            17 => "(TGT |TGC )",
            18 => "(TGG |TGA )",
            19 => "(CG. )",
            20 => "(GG. )",
            21 => "(\S\S\S )",
          ],
          "ciliate_dasycladacean_hexamita_nuclear" => [
            0 => "(TTT |TTC )",
            1 => "(TTA |TTG |CT. )",
            2 => "(ATT |ATC |ATA )",
            3 => "(ATG )",
            4 => "(GT. )",
            5 => "(TC. |AGT |AGC )",
            6 => "(CC. )",
            7 => "(AC. )",
            8 => "(GC. )",
            9 => "(TAT |TAC )",
            10 => "(TGA )",
            11 => "(CAT |CAC )",
            12 => "(CAA |CAG |TAA |TAG )",
            13 => "(AAT |AAC )",
            14 => "(AAA |AAG )",
            15 => "(GAT |GAC )",
            16 => "(GAA |GAG )",
            17 => "(TGT |TGC )",
            18 => "(TGG )",
            19 => "(CG. |AGA |AGG )",
            20 => "(GG. )",
            21 => "(\S\S\S )",
          ],
          "echinoderm_mitochondrial" => [
            0 => "(TTT |TTC )",
            1 => "(TTA |TTG |CT. )",
            2 => "(ATT |ATC |ATA )",
            3 => "(ATG )",
            4 => "(GT. )",
            5 => "(TC. |AG. )",
            6 => "(CC. )",
            7 => "(AC. )",
            8 => "(GC. )",
            9 => "(TAT |TAC )",
            10 => "(TAA |TAG )",
            11 => "(CAT |CAC )",
            12 => "(CAA |CAG )",
            13 => "(AAA |AAT |AAC )",
            14 => "(AAG )",
            15 => "(GAT |GAC )",
            16 => "(GAA |GAG )",
            17 => "(TGT |TGC )",
            18 => "(TGG |TGA )",
            19 => "(CG. )",
            20 => "(GG. )",
            21 => "(\S\S\S )",
          ],
          "euplotid_nuclear" => [
            0 => "(TTT |TTC )",
            1 => "(TTA |TTG |CT. )",
            2 => "(ATT |ATC |ATA )",
            3 => "(ATG )",
            4 => "(GT. )",
            5 => "(TC. |AGT |AGC )",
            6 => "(CC. )",
            7 => "(AC. )",
            8 => "(GC. )",
            9 => "(TAT |TAC )",
            10 => "(TAA |TAG )",
            11 => "(CAT |CAC )",
            12 => "(CAA |CAG )",
            13 => "(AAT |AAC )",
            14 => "(AAA |AAG )",
            15 => "(GAT |GAC )",
            16 => "(GAA |GAG )",
            17 => "(TGT |TGC |TGA )",
            18 => "(TGG )",
            19 => "(CG. |AGA |AGG )",
            20 => "(GG. )",
            21 => "(\S\S\S )",
          ],
          "bacterial_plant_plastid" => [
            0 => "(TTT |TTC )",
            1 => "(TTA |TTG |CT. )",
            2 => "(ATT |ATC |ATA )",
            3 => "(ATG )",
            4 => "(GT. )",
            5 => "(TC. |AGT |AGC )",
            6 => "(CC. )",
            7 => "(AC. )",
            8 => "(GC. )",
            9 => "(TAT |TAC )",
            10 => "(TAA |TAG |TGA )",
            11 => "(CAT |CAC )",
            12 => "(CAA |CAG )",
            13 => "(AAT |AAC )",
            14 => "(AAA |AAG )",
            15 => "(GAT |GAC )",
            16 => "(GAA |GAG )",
            17 => "(TGT |TGC )",
            18 => "(TGG )",
            19 => "(CG. |AGA |AGG )",
            20 => "(GG. )",
            21 => "(\S\S\S )",
          ],
          "alternative_yeast_nuclear" => [
            0 => "(TTT |TTC )",
            1 => "(TTA |TTG |CTA |CTT |CTC )",
            2 => "(ATT |ATC |ATA )",
            3 => "(ATG )",
            4 => "(GT. )",
            5 => "(TC. |AGT |AGC |CTG )",
            6 => "(CC. )",
            7 => "(AC. )",
            8 => "(GC. )",
            9 => "(TAT |TAC )",
            10 => "(TAA |TAG |TGA )",
            11 => "(CAT |CAC )",
            12 => "(CAA |CAG )",
            13 => "(AAT |AAC )",
            14 => "(AAA |AAG )",
            15 => "(GAT |GAC )",
            16 => "(GAA |GAG )",
            17 => "(TGT |TGC )",
            18 => "(TGG )",
            19 => "(CG. |AGA |AGG )",
            20 => "(GG. )",
            21 => "(\S\S\S )",
          ],
          "ascidian_mitochondria" => [
            0 => "(TTT |TTC )",
            1 => "(TTA |TTG |CT. )",
            2 => "(ATT |ATC )",
            3 => "(ATG |ATA )",
            4 => "(GT. )",
            5 => "(TC. |AGT |AGC )",
            6 => "(CC. )",
            7 => "(AC. )",
            8 => "(GC. )",
            9 => "(TAT |TAC )",
            10 => "(TAA |TAG )",
            11 => "(CAT |CAC )",
            12 => "(CAA |CAG )",
            13 => "(AAT |AAC )",
            14 => "(AAA |AAG )",
            15 => "(GAT |GAC )",
            16 => "(GAA |GAG )",
            17 => "(TGT |TGC )",
            18 => "(TGG |TGA )",
            19 => "(CG. )",
            20 => "(GG. |AGA |AGG )",
            21 => "(\S\S\S )"
          ],
          "flatworm_mitochondrial" => [
            0 => "(TTT |TTC )",
            1 => "(TTA |TTG |CT. )",
            2 => "(ATT |ATC |ATA )",
            3 => "(ATG )",
            4 => "(GT. )",
            5 => "(TC. |AG. )",
            6 => "(CC. )",
            7 => "(AC. )",
            8 => "(GC. )",
            9 => "(TAT |TAC |TAA )",
            10 => "(TAG )",
            11 => "(CAT |CAC )",
            12 => "(CAA |CAG )",
            13 => "(AAT |AAC |AAA )",
            14 => "(AAG )",
            15 => "(GAT |GAC )",
            16 => "(GAA |GAG )",
            17 => "(TGT |TGC )",
            18 => "(TGG |TGA )",
            19 => "(CG. )",
            20 => "(GG. )",
            21 => "(\S\S\S )"
          ],
          "blepharisma_macronuclear" => [
            0 => "(TTT |TTC )",
            1 => "(TTA |TTG |CT. )",
            2 => "(ATT |ATC |ATA )",
            3 => "(ATG )",
            4 => "(GT. )",
            5 => "(TC. |AGT |AGC )",
            6 => "(CC. )",
            7 => "(AC. )",
            8 => "(GC. )",
            9 => "(TAT |TAC )",
            10 => "(TAA |TGA )",
            11 => "(CAT |CAC )",
            12 => "(CAA |CAG |TAG )",
            13 => "(AAT |AAC )",
            14 => "(AAA |AAG )",
            15 => "(GAT |GAC )",
            16 => "(GAA |GAG )",
            17 => "(TGT |TGC )",
            18 => "(TGG )",
            19 => "(CG. |AGA |AGG )",
            20 => "(GG. )",
            21 => "(\S\S\S )",
          ],
          "chlorophycean_mitochondrial" => [
            0 => "(TTT |TTC )",
            1 => "(TTA |TTG |CT. |TAG )",
            2 => "(ATT |ATC |ATA )",
            3 => "(ATG )",
            4 => "(GT. )",
            5 => "(TC. |AGT |AGC )",
            6 => "(CC. )",
            7 => "(AC. )",
            8 => "(GC. )",
            9 => "(TAT |TAC )",
            10 => "(TAA |TGA )",
            11 => "(CAT |CAC )",
            12 => "(CAA |CAG )",
            13 => "(AAT |AAC )",
            14 => "(AAA |AAG )",
            15 => "(GAT |GAC )",
            16 => "(GAA |GAG )",
            17 => "(TGT |TGC )",
            18 => "(TGG )",
            19 => "(CG. |AGA |AGG )",
            20 => "(GG. )",
            21 => "(\S\S\S )"
          ],
          "trematode_mitochondrial" => [
            0 => "(TTT |TTC )",
            1 => "(TTA |TTG |CT. )",
            2 => "(ATT |ATC )",
            3 => "(ATG |ATA )",
            4 => "(GT. )",
            5 => "(TC. |AG. )",
            6 => "(CC. )",
            7 => "(AC. )",
            8 => "(GC. )",
            9 => "(TAT |TAC )",
            10 => "(TAA |TAG )",
            11 => "(CAT |CAC )",
            12 => "(CAA |CAG )",
            13 => "(AAT |AAC |AAA )",
            14 => "(AAG )",
            15 => "(GAT |GAC )",
            16 => "(GAA |GAG )",
            17 => "(TGT |TGC )",
            18 => "(TGG |TGA )",
            19 => "(CG. )",
            20 => "(GG. )",
            21 => "(\S\S\S )",
          ],
          "scenedesmus_obliquus_mitochondrial" => [
            0 => "(TTT |TTC )",
            1 => "(TTA |TTG |CT. |TAG )",
            2 => "(ATT |ATC |ATA )",
            3 => "(ATG )",
            4 => "(GT. )",
            5 => "(TCT |TCC |TCG |AGT |AGC )",
            6 => "(CC. )",
            7 => "(AC. )",
            8 => "(GC. )",
            9 => "(TAT |TAC )",
            10 => "(TAA |TGA |TCA )",
            11 => "(CAT |CAC )",
            12 => "(CAA |CAG )",
            13 => "(AAT |AAC )",
            14 => "(AAA |AAG )",
            15 => "(GAT |GAC )",
            16 => "(GAA |GAG )",
            17 => "(TGT |TGC )",
            18 => "(TGG )",
            19 => "(CG. |AGA |AGG )",
            20 => "(GG. )",
            21 => "(\S\S\S )",
          ],
          "thraustochytrium_mitochondrial_code" => [
            0 => "(TTT |TTC )",
            1 => "(TTG |CT. )",
            2 => "(ATT |ATC |ATA )",
            3 => "(ATG )",
            4 => "(GT. )",
            5 => "(TC. |AGT |AGC )",
            6 => "(CC. )",
            7 => "(AC. )",
            8 => "(GC. )",
            9 => "(TAT |TAC )",
            10 => "(TTA |TAA |TAG |TGA )",
            11 => "(CAT |CAC )",
            12 => "(CAA |CAG )",
            13 => "(AAT |AAC )",
            14 => "(AAA |AAG )",
            15 => "(GAT |GAC )",
            16 => "(GAA |GAG )",
            17 => "(TGT |TGC )",
            18 => "(TGG )",
            19 => "(CG. |AGA |AGG )",
            20 => "(GG. )",
            21 => "(\S\S\S )",
          ],
        ];

        $apiTriplets = new TripletSpecieApi($this->clientMock, $this->serializerMock);
        static::assertEquals($aExpected, $apiTriplets::GetTripletsGroups($apiTriplets->getTriplets()));
    }

    public function testGetTripletsArray()
    {
        $aExpected = [
          "standard" => [
            0 => "TTY",
            1 => "YTN",
            2 => "ATH",
            3 => "ATG",
            4 => "GTN",
            5 => "WSN",
            6 => "CCN",
            7 => "ACN",
            8 => "GCN",
            9 => "TAY",
            10 => "TRR",
            11 => "CAY",
            12 => "CAR",
            13 => "AAY",
            14 => "AAR",
            15 => "GAY",
            16 => "GAR",
            17 => "TGY",
            18 => "TGG",
            19 => "MGN",
            20 => "GGN",
            21 => "NNN",
          ],
          "vertebrate_mitochondrial" => [
            0 => "TTY",
            1 => "YTN",
            2 => "ATY",
            3 => "ATR",
            4 => "GTN",
            5 => "WSN",
            6 => "CCN",
            7 => "ACN",
            8 => "GCN",
            9 => "TAY",
            10 => "WRR",
            11 => "CAY",
            12 => "CAR",
            13 => "AAY",
            14 => "AAR",
            15 => "GAY",
            16 => "GAR",
            17 => "TGY",
            18 => "TGR",
            19 => "CGN",
            20 => "GGN",
            21 => "NNN"
          ],
          "yeast_mitochondrial" => [
            0 => "TTY",
            1 => "TTR",
            2 => "ATY",
            3 => "ATR",
            4 => "GTN",
            5 => "WSN",
            6 => "CCN",
            7 => "MYN",
            8 => "GCN",
            9 => "TAY",
            10 => "TAR",
            11 => "CAY",
            12 => "CAR",
            13 => "AAY",
            14 => "AAR",
            15 => "GAY",
            16 => "GAR",
            17 => "TGY",
            18 => "TGR",
            19 => "MGN",
            20 => "GGN",
            21 => "NNN",
          ],
          "mold_protozoan_coelenterate_mitochondrial" => [
            0 => "TTY",
            1 => "YTN",
            2 => "ATH",
            3 => "ATG",
            4 => "GTN",
            5 => "WSN",
            6 => "CCN",
            7 => "ACN",
            8 => "GCN",
            9 => "TAY",
            10 => "TAR",
            11 => "CAY",
            12 => "CAR",
            13 => "AAY",
            14 => "AAR",
            15 => "GAY",
            16 => "GAR",
            17 => "TGY",
            18 => "TGR",
            19 => "MGN",
            20 => "GGN",
            21 => "NNN",
          ],
          "invertebrate_mitochondrial" => [
            0 => "TTY",
            1 => "YTN",
            2 => "ATY",
            3 => "ATR",
            4 => "GTN",
            5 => "WSN",
            6 => "CCN",
            7 => "WSN",
            8 => "GCN",
            9 => "TAY",
            10 => "TAR",
            11 => "CAY",
            12 => "CAR",
            13 => "AAY",
            14 => "AAR",
            15 => "GAY",
            16 => "GAR",
            17 => "TGY",
            18 => "TGR",
            19 => "CGN",
            20 => "GGN",
            21 => "NNN",
          ],
          "ciliate_dasycladacean_hexamita_nuclear" => [
            0 => "TTY",
            1 => "YTN",
            2 => "ATH",
            3 => "ATG",
            4 => "GTN",
            5 => "WSN",
            6 => "CCN",
            7 => "ACN",
            8 => "GCN",
            9 => "TAY",
            10 => "TGA",
            11 => "CAY",
            12 => "YAR",
            13 => "AAY",
            14 => "AAR",
            15 => "GAY",
            16 => "GAR",
            17 => "TGY",
            18 => "TGG",
            19 => "MGN",
            20 => "GGN",
            21 => "NNN",
          ],
          "echinoderm_mitochondrial" => [
            0 => "TTY",
            1 => "YTN",
            2 => "ATH",
            3 => "ATG",
            4 => "GTN",
            5 => "WSN",
            6 => "CCN",
            7 => "WCN",
            8 => "GCN",
            9 => "TAY",
            10 => "TAR",
            11 => "CAY",
            12 => "CAR",
            13 => "AAH",
            14 => "AAG",
            15 => "GAY",
            16 => "GAR",
            17 => "TGY",
            18 => "TGR",
            19 => "CGN",
            20 => "GGN",
            21 => "NNN",
          ],
          "euplotid_nuclear" => [
            0 => "TTY",
            1 => "YTN",
            2 => "ATH",
            3 => "ATG",
            4 => "GTN",
            5 => "WSN",
            6 => "CCN",
            7 => "ACN",
            8 => "GCN",
            9 => "TAY",
            10 => "TAR",
            11 => "CAY",
            12 => "CAR",
            13 => "AAY",
            14 => "AAR",
            15 => "GAY",
            16 => "GAR",
            17 => "TGH",
            18 => "TGG",
            19 => "MGN",
            20 => "GGN",
            21 => "NNN",
          ],
          "bacterial_plant_plastid" => [
            0 => "TTY",
            1 => "YTN",
            2 => "ATH",
            3 => "ATG",
            4 => "GTN",
            5 => "WSN",
            6 => "CCN",
            7 => "ACN",
            8 => "GCN",
            9 => "TAY",
            10 => "TRR",
            11 => "CAY",
            12 => "CAR",
            13 => "AAY",
            14 => "AAR",
            15 => "GAY",
            16 => "GAR",
            17 => "TGY",
            18 => "TGG",
            19 => "MGN",
            20 => "GGN",
            21 => "NNN",
          ],
          "alternative_yeast_nuclear" => [
            0 => "TTY",
            1 => "YTN",
            2 => "ATH",
            3 => "ATG",
            4 => "GTN",
            5 => "HBN",
            6 => "CCN",
            7 => "ACN",
            8 => "GCN",
            9 => "TAY",
            10 => "TRR",
            11 => "CAY",
            12 => "CAR",
            13 => "AAY",
            14 => "AAR",
            15 => "GAY",
            16 => "GAR",
            17 => "TGY",
            18 => "TGG",
            19 => "MGN",
            20 => "GGN",
            21 => "NNN",
          ],
          "ascidian_mitochondria" => [
            0 => "TTY",
            1 => "YTN",
            2 => "ATY",
            3 => "ATR",
            4 => "GTN",
            5 => "WSN",
            6 => "CCN",
            7 => "ACN",
            8 => "GCN",
            9 => "TAY",
            10 => "TAR",
            11 => "CAY",
            12 => "CAR",
            13 => "AAY",
            14 => "AAR",
            15 => "GAY",
            16 => "GAR",
            17 => "TGY",
            18 => "TGR",
            19 => "CGN",
            20 => "RGN",
            21 => "NNN",
          ],
          "flatworm_mitochondrial" => [
            0 => "TTY",
            1 => "YTN",
            2 => "ATH",
            3 => "ATG",
            4 => "GTN",
            5 => "WSN",
            6 => "CCN",
            7 => "ACN",
            8 => "GCN",
            9 => "TAH",
            10 => "TAG",
            11 => "CAY",
            12 => "CAR",
            13 => "ATH",
            14 => "AAG",
            15 => "GAY",
            16 => "GAR",
            17 => "TGY",
            18 => "TGR",
            19 => "CGN",
            20 => "GGN",
            21 => "NNN",
          ],
          "blepharisma_macronuclear" => [
            0 => "TTY",
            1 => "YTN",
            2 => "ATH",
            3 => "ATG",
            4 => "GTN",
            5 => "WSN",
            6 => "CCN",
            7 => "ACN",
            8 => "GCN",
            9 => "TAY",
            10 => "TRA",
            11 => "CAY",
            12 => "YAR",
            13 => "AAY",
            14 => "AAR",
            15 => "GAY",
            16 => "GAR",
            17 => "TGY",
            18 => "TGG",
            19 => "MGN",
            20 => "GGN",
            21 => "NNN",
          ],
          "chlorophycean_mitochondrial" => [
            0 => "TTY",
            1 => "YWN",
            2 => "ATH",
            3 => "ATG",
            4 => "GTN",
            5 => "WSN",
            6 => "CCN",
            7 => "ACN",
            8 => "GCN",
            9 => "TAY",
            10 => "TRA",
            11 => "CAY",
            12 => "CAR",
            13 => "AAY",
            14 => "AAR",
            15 => "GAY",
            16 => "GAR",
            17 => "TGY",
            18 => "TGG",
            19 => "MGN",
            20 => "GGN",
            21 => "NNN",
          ],
          "trematode_mitochondrial" => [
            0 => "TTY",
            1 => "YTN",
            2 => "ATY",
            3 => "ATR",
            4 => "GTN",
            5 => "WSN",
            6 => "CCN",
            7 => "ACN",
            8 => "GCN",
            9 => "TAY",
            10 => "TAR",
            11 => "CAY",
            12 => "CAR",
            13 => "AAH",
            14 => "AAG",
            15 => "GAY",
            16 => "GAR",
            17 => "TGY",
            18 => "TGR",
            19 => "CGN",
            20 => "GGN",
            21 => "NNN",
          ],
          "scenedesmus_obliquus_mitochondrial" => [
            0 => "TTY",
            1 => "YWN",
            2 => "ATH",
            3 => "ATG",
            4 => "GTN",
            5 => "WSB",
            6 => "CCN",
            7 => "ACN",
            8 => "GCN",
            9 => "TAY",
            10 => "TVR",
            11 => "CAY",
            12 => "CAR",
            13 => "AAY",
            14 => "AAR",
            15 => "GAY",
            16 => "GAR",
            17 => "TGY",
            18 => "TGG",
            19 => "MGN",
            20 => "GGN",
            21 => "NNN",
          ],
          "thraustochytrium_mitochondrial_code" => [
            0 => "TTY",
            1 => "YTN",
            2 => "ATH",
            3 => "ATG",
            4 => "GTN",
            5 => "WSN",
            6 => "CCN",
            7 => "ACN",
            8 => "GCN",
            9 => "TAY",
            10 => "TDR",
            11 => "CAY",
            12 => "CAR",
            13 => "AAY",
            14 => "AAR",
            15 => "GAY",
            16 => "GAR",
            17 => "TGY",
            18 => "TGG",
            19 => "MGN",
            20 => "GGN",
            21 => "NNN"
          ]
        ];

        $apiTriplets = new TripletSpecieApi($this->clientMock, $this->serializerMock);
        static::assertEquals($aExpected, $apiTriplets::GetTripletsArray($apiTriplets->getTriplets()));
    }

    public function testGetTripletsCombinations()
    {
        $aExpected = [
          0 => "TTY",
          1 => "YTN",
          2 => "ATH",
          3 => "ATG",
          4 => "GTN",
          5 => "WSN",
          6 => "CCN",
          7 => "ACN",
          8 => "GCN",
          9 => "TAY",
          10 => "TRR",
          11 => "CAY",
          12 => "CAR",
          13 => "AAY",
          14 => "AAR",
          15 => "GAY",
          16 => "GAR",
          17 => "TGY",
          18 => "TGG",
          19 => "MGN",
          20 => "GGN",
          21 => "NNN"
        ];

        $apiTriplets = new TripletSpecieApi($this->clientMock, $this->serializerMock);
        static::assertEquals($aExpected, $apiTriplets::GetTripletsCombinations($apiTriplets->getTriplets()));
    }

    public function testGetSpeciesNames()
    {
        $aExpected = [
          "Standard" => "standard",
          "Vertebrate Mitochondrial" => "vertebrate_mitochondrial",
          "Yeast Mitochondrial" => "yeast_mitochondrial",
          "Mold Protozoan Coelenterate Mitochondrial" => "mold_protozoan_coelenterate_mitochondrial",
          "Invertebrate Mitochondrial" => "invertebrate_mitochondrial",
          "Ciliate Dasycladacean Hexamita Nuclear" => "ciliate_dasycladacean_hexamita_nuclear",
          "Echinoderm Mitochondrial" => "echinoderm_mitochondrial",
          "Euplotid Nuclear" => "euplotid_nuclear",
          "Bacterial Plant Plastid" => "bacterial_plant_plastid",
          "Alternative Yeast Nuclear" => "alternative_yeast_nuclear",
          "Ascidian Mitochondria" => "ascidian_mitochondria",
          "Flatworm Mitochondrial" => "flatworm_mitochondrial",
          "Blepharisma Macronuclear" => "blepharisma_macronuclear",
          "Chlorophycean Mitochondrial" => "chlorophycean_mitochondrial",
          "Trematode Mitochondrial" => "trematode_mitochondrial",
          "Scenedesmus Obliquus Mitochondrial" => "scenedesmus_obliquus_mitochondrial",
          "Thraustochytrium Mitochondrial Code" => "thraustochytrium_mitochondrial_code"
        ];

        $apiTriplets = new TripletSpecieApi($this->clientMock, $this->serializerMock);
        static::assertEquals($aExpected, $apiTriplets::GetSpeciesNames($apiTriplets->getTriplets()));
    }
}