<?php
/**
 * Created by PhpStorm.
 * User: amelaye
 * Date: 2019-04-16
 * Time: 14:55
 */

namespace App\DataFixtures;

use Amelaye\BioPHP\Api\DTO\TripletSpecieDTO;
use Amelaye\BioPHP\Api\TripletSpecieApi;
use App\Entity\TripletSpecie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

        $triplets_standard = [
            '(TTT |TTC )',
            '(TTA |TTG |CT. )',
            '(ATT |ATC |ATA )',
            '(ATG )',
            '(GT. )',
            '(TC. |AGT |AGC )',
            '(CC. )',
            '(AC. )',
            '(GC. )',
            '(TAT |TAC )',
            '(TAA |TAG |TGA )',
            '(CAT |CAC )',
            '(CAA |CAG )',
            '(AAT |AAC )',
            '(AAA |AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )', '(TGT |TGC )',
            '(TGG )',
            '(CG. |AGA |AGG )',
            '(GG. )',
            '(\S\S\S )'
        ];

        $triplets_vertebrate_mitochondrial = [
            '(TTT |TTC )',
            '(TTA |TTG |CT. )',
            '(ATT |ATC |ATA )',
            '(ATG )',
            '(GT. )',
            '(TC. |AGT |AGC )',
            '(CC. )',
            '(AC. )',
            '(GC. )',
            '(TAT |TAC )',
            '(TAA |TAG |AGA |AGG )',
            '(CAT |CAC )',
            '(CAA |CAG )',
            '(AAT |AAC )',
            '(AAA |AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )',
            '(TGT |TGC )',
            '(TGG |TGA )',
            '(CG. )',
            '(GG. )',
            '(\S\S\S )'
        ];

        $triplets_yeast_mitochondrial = [
            '(TTT |TTC )',
            '(TTA |TTG )',
            '(ATT |ATC )',
            '(ATG |ATA )',
            '(GT. )',
            '(TC. |AGT |AGC )',
            '(CC. )',
            '(AC. |CT. )',
            '(GC. )',
            '(TAT |TAC )',
            '(TAA |TAG )',
            '(CAT |CAC )',
            '(CAA |CAG )',
            '(AAT |AAC )',
            '(AAA |AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )',
            '(TGT |TGC )',
            '(TGG |TGA )',
            '(CG. |AGA |AGG )',
            '(GG. )',
            '(\S\S\S )'
        ];

        $triplets_mold_protozoan_coelenterate_mitochondrial = [
            '(TTT |TTC )',
            '(TTA |TTG |CT. )',
            '(ATT |ATC |ATA )',
            '(ATG )',
            '(GT. )',
            '(TC. |AGT |AGC )',
            '(CC. )',
            '(AC. )',
            '(GC. )',
            '(TAT |TAC )',
            '(TAA |TAG )',
            '(CAT |CAC )',
            '(CAA |CAG )',
            '(AAT |AAC )',
            '(AAA |AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )',
            '(TGT |TGC )',
            '(TGG |TGA )',
            '(CG. |AGA |AGG )',
            '(GG. )',
            '(\S\S\S )'
        ];

        $triplets_invertebrate_mitochondrial = [
            '(TTT |TTC )',
            '(TTA |TTG |CT. )',
            '(ATT |ATC )',
            '(ATG |ATA )',
            '(GT. )',
            '(TC. |AG. )',
            '(CC. )',
            '(AC. )',
            '(GC. )',
            '(TAT |TAC )',
            '(TAA |TAG )',
            '(CAT |CAC )',
            '(CAA |CAG )',
            '(AAT |AAC )',
            '(AAA |AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )',
            '(TGT |TGC )',
            '(TGG |TGA )',
            '(CG. )',
            '(GG. )',
            '(\S\S\S )'
        ];

        $triplets_ciliate_dasycladacean_hexamita_nuclear = [
            '(TTT |TTC )',
            '(TTA |TTG |CT. )',
            '(ATT |ATC |ATA )',
            '(ATG )',
            '(GT. )',
            '(TC. |AGT |AGC )',
            '(CC. )',
            '(AC. )',
            '(GC. )',
            '(TAT |TAC )',
            '(TGA )',
            '(CAT |CAC )',
            '(CAA |CAG |TAA |TAG )',
            '(AAT |AAC )',
            '(AAA |AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )',
            '(TGT |TGC )',
            '(TGG )',
            '(CG. |AGA |AGG )',
            '(GG. )',
            '(\S\S\S )'
        ];

        $triplets_echinoderm_mitochondrial = [
            '(TTT |TTC )',
            '(TTA |TTG |CT. )',
            '(ATT |ATC |ATA )',
            '(ATG )',
            '(GT. )',
            '(TC. |AG. )',
            '(CC. )',
            '(AC. )',
            '(GC. )',
            '(TAT |TAC )',
            '(TAA |TAG )',
            '(CAT |CAC )',
            '(CAA |CAG )',
            '(AAA |AAT |AAC )',
            '(AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )',
            '(TGT |TGC )',
            '(TGG |TGA )',
            '(CG. )',
            '(GG. )',
            '(\S\S\S )'
        ];

        $triplets_euplotid_nuclear = [
            '(TTT |TTC )',
            '(TTA |TTG |CT. )',
            '(ATT |ATC |ATA )',
            '(ATG )',
            '(GT. )',
            '(TC. |AGT |AGC )',
            '(CC. )',
            '(AC. )',
            '(GC. )',
            '(TAT |TAC )',
            '(TAA |TAG )',
            '(CAT |CAC )',
            '(CAA |CAG )',
            '(AAT |AAC )',
            '(AAA |AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )',
            '(TGT |TGC |TGA )',
            '(TGG )',
            '(CG. |AGA |AGG )',
            '(GG. )',
            '(\S\S\S )',
        ];

        $triplets_bacterial_plant_plastid = [
            '(TTT |TTC )',
            '(TTA |TTG |CT. )',
            '(ATT |ATC |ATA )',
            '(ATG )',
            '(GT. )',
            '(TC. |AGT |AGC )',
            '(CC. )',
            '(AC. )',
            '(GC. )',
            '(TAT |TAC )',
            '(TAA |TAG |TGA )',
            '(CAT |CAC )',
            '(CAA |CAG )',
            '(AAT |AAC )',
            '(AAA |AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )',
            '(TGT |TGC )',
            '(TGG )',
            '(CG. |AGA |AGG )',
            '(GG. )',
            '(\S\S\S )',
        ];

        $triplets_alternative_yeast_nuclear = [
            '(TTT |TTC )',
            '(TTA |TTG |CTA |CTT |CTC )',
            '(ATT |ATC |ATA )',
            '(ATG )',
            '(GT. )',
            '(TC. |AGT |AGC |CTG )',
            '(CC. )',
            '(AC. )',
            '(GC. )',
            '(TAT |TAC )',
            '(TAA |TAG |TGA )',
            '(CAT |CAC )',
            '(CAA |CAG )',
            '(AAT |AAC )',
            '(AAA |AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )',
            '(TGT |TGC )',
            '(TGG )',
            '(CG. |AGA |AGG )',
            '(GG. )',
            '(\S\S\S )',
        ];

        $triplets_ascidian_mitochondria = [
            '(TTT |TTC )',
            '(TTA |TTG |CT. )',
            '(ATT |ATC )',
            '(ATG |ATA )',
            '(GT. )',
            '(TC. |AGT |AGC )',
            '(CC. )',
            '(AC. )',
            '(GC. )',
            '(TAT |TAC )',
            '(TAA |TAG )',
            '(CAT |CAC )',
            '(CAA |CAG )',
            '(AAT |AAC )',
            '(AAA |AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )',
            '(TGT |TGC )',
            '(TGG |TGA )',
            '(CG. )',
            '(GG. |AGA |AGG )',
            '(\S\S\S )'
        ];

        $triplets_flatworm_mitochondrial = [
            '(TTT |TTC )',
            '(TTA |TTG |CT. )',
            '(ATT |ATC |ATA )',
            '(ATG )',
            '(GT. )',
            '(TC. |AG. )',
            '(CC. )',
            '(AC. )',
            '(GC. )',
            '(TAT |TAC |TAA )',
            '(TAG )',
            '(CAT |CAC )',
            '(CAA |CAG )',
            '(AAT |AAC |AAA )',
            '(AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )',
            '(TGT |TGC )',
            '(TGG |TGA )',
            '(CG. )',
            '(GG. )',
            '(\S\S\S )'
        ];

        $triplets_blepharisma_macronuclear = [
            '(TTT |TTC )',
            '(TTA |TTG |CT. )',
            '(ATT |ATC |ATA )',
            '(ATG )',
            '(GT. )',
            '(TC. |AGT |AGC )',
            '(CC. )',
            '(AC. )',
            '(GC. )',
            '(TAT |TAC )',
            '(TAA |TGA )',
            '(CAT |CAC )',
            '(CAA |CAG |TAG )',
            '(AAT |AAC )',
            '(AAA |AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )',
            '(TGT |TGC )',
            '(TGG )',
            '(CG. |AGA |AGG )',
            '(GG. )',
            '(\S\S\S )'
        ];

        $triplets_chlorophycean_mitochondrial = [
            '(TTT |TTC )',
            '(TTA |TTG |CT. |TAG )',
            '(ATT |ATC |ATA )',
            '(ATG )',
            '(GT. )',
            '(TC. |AGT |AGC )',
            '(CC. )',
            '(AC. )',
            '(GC. )',
            '(TAT |TAC )',
            '(TAA |TGA )',
            '(CAT |CAC )',
            '(CAA |CAG )',
            '(AAT |AAC )',
            '(AAA |AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )',
            '(TGT |TGC )',
            '(TGG )',
            '(CG. |AGA |AGG )',
            '(GG. )',
            '(\S\S\S )'
        ];

        $triplets_trematode_mitochondrial = [
            '(TTT |TTC )',
            '(TTA |TTG |CT. )',
            '(ATT |ATC )',
            '(ATG |ATA )',
            '(GT. )',
            '(TC. |AG. )',
            '(CC. )',
            '(AC. )',
            '(GC. )',
            '(TAT |TAC )',
            '(TAA |TAG )',
            '(CAT |CAC )',
            '(CAA |CAG )',
            '(AAT |AAC |AAA )',
            '(AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )',
            '(TGT |TGC )',
            '(TGG |TGA )',
            '(CG. )',
            '(GG. )',
            '(\S\S\S )'
        ];

        $triplets_scenedesmus_obliquus_mitochondrial = [
            '(TTT |TTC )',
            '(TTA |TTG |CT. |TAG )',
            '(ATT |ATC |ATA )',
            '(ATG )',
            '(GT. )',
            '(TCT |TCC |TCG |AGT |AGC )',
            '(CC. )',
            '(AC. )',
            '(GC. )',
            '(TAT |TAC )',
            '(TAA |TGA |TCA )',
            '(CAT |CAC )',
            '(CAA |CAG )',
            '(AAT |AAC )',
            '(AAA |AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )',
            '(TGT |TGC )',
            '(TGG )',
            '(CG. |AGA |AGG )',
            '(GG. )',
            '(\S\S\S )'
        ];

        $triplets_thraustochytrium_mitochondrial_code = [
            '(TTT |TTC )',
            '(TTG |CT. )',
            '(ATT |ATC |ATA )',
            '(ATG )',
            '(GT. )',
            '(TC. |AGT |AGC )',
            '(CC. )',
            '(AC. )',
            '(GC. )',
            '(TAT |TAC )',
            '(TTA |TAA |TAG |TGA )',
            '(CAT |CAC )',
            '(CAA |CAG )',
            '(AAT |AAC )',
            '(AAA |AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )',
            '(TGT |TGC )',
            '(TGG )',
            '(CG. |AGA |AGG )',
            '(GG. )',
            '(\S\S\S )'
        ];

        $aTripletSpeciesObjects = [];

        $triplet = new TripletSpecieDTO();
        $triplet->setNature("standard");
        $triplet->setTripletsGroups($triplets_standard);
        $triplet->setTriplets(["TTY","YTN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAY","TRR",
            "CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGG","MGN","GGN","NNN"]);
        $aTripletSpeciesObjects[] = $triplet;

        $triplet = new TripletSpecieDTO();
        $triplet->setNature("vertebrate mitochondrial");
        $triplet->setTripletsGroups($triplets_vertebrate_mitochondrial);
        $triplet->setTriplets(["TTY","YTN","ATY","ATR","GTN","WSN","CCN","ACN","GCN","TAY","WRR",
            "CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGR","CGN","GGN","NNN"]);
        $aTripletSpeciesObjects[] = $triplet;

        $triplet = new TripletSpecieDTO();
        $triplet->setNature("yeast mitochondrial");
        $triplet->setTripletsGroups($triplets_yeast_mitochondrial);
        $triplet->setTriplets(["TTY","TTR","ATY","ATR","GTN","WSN","CCN","MYN","GCN","TAY","TAR","CAY",
            "CAR","AAY","AAR","GAY","GAR","TGY","TGR","MGN","GGN","NNN"]);
        $aTripletSpeciesObjects[] = $triplet;

        $triplet = new TripletSpecieDTO();
        $triplet->setNature("mold protozoan coelenterate mitochondrial");
        $triplet->setTripletsGroups($triplets_mold_protozoan_coelenterate_mitochondrial);
        $triplet->setTriplets(["TTY","YTN","ATH","ATG","GTN","WSN","CCN","ACN",
            "GCN","TAY","TAR","CAY","CAR","AAY","AAR","GAY",
            "GAR","TGY","TGR","MGN","GGN","NNN"]);
        $aTripletSpeciesObjects[] = $triplet;

        $triplet = new TripletSpecieDTO();
        $triplet->setNature("invertebrate mitochondrial");
        $triplet->setTripletsGroups($triplets_invertebrate_mitochondrial);
        $triplet->setTriplets(["TTY","YTN","ATY","ATR","GTN","WSN","CCN","WSN","GCN","TAY","TAR",
            "CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGR","CGN","GGN","NNN"]);
        $aTripletSpeciesObjects[] = $triplet;

        $triplet = new TripletSpecieDTO();
        $triplet->setNature("ciliate dasycladacean hexamita nuclear");
        $triplet->setTripletsGroups($triplets_ciliate_dasycladacean_hexamita_nuclear);
        $triplet->setTriplets(["TTY","YTN","ATH","ATG","GTN","WSN","CCN","ACN","GCN",
            "TAY","TGA","CAY","YAR","AAY","AAR","GAY","GAR","TGY",
            "TGG","MGN","GGN","NNN"]);
        $aTripletSpeciesObjects[] = $triplet;

        $triplet = new TripletSpecieDTO();
        $triplet->setNature("echinoderm mitochondrial");
        $triplet->setTripletsGroups($triplets_echinoderm_mitochondrial);
        $triplet->setTriplets(["TTY","YTN","ATH","ATG","GTN","WSN","CCN","WCN","GCN","TAY","TAR",
            "CAY","CAR","AAH","AAG","GAY","GAR","TGY","TGR","CGN","GGN","NNN"]);
        $aTripletSpeciesObjects[] = $triplet;

        $triplet = new TripletSpecieDTO();
        $triplet->setNature("euplotid nuclear");
        $triplet->setTripletsGroups($triplets_euplotid_nuclear);
        $triplet->setTriplets(["TTY","YTN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAY","TAR","CAY","CAR",
            "AAY","AAR","GAY","GAR","TGH","TGG","MGN","GGN","NNN"]);
        $aTripletSpeciesObjects[] = $triplet;

        $triplet = new TripletSpecieDTO();
        $triplet->setNature("bacterial plant plastid");
        $triplet->setTripletsGroups($triplets_bacterial_plant_plastid);
        $triplet->setTriplets(["TTY","YTN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAY","TRR",
            "CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGG","MGN","GGN","NNN"]);
        $aTripletSpeciesObjects[] = $triplet;

        $triplet = new TripletSpecieDTO();
        $triplet->setNature("alternative yeast nuclear");
        $triplet->setTripletsGroups($triplets_alternative_yeast_nuclear);
        $triplet->setTriplets(["TTY","YTN","ATH","ATG","GTN","HBN","CCN","ACN","GCN","TAY","TRR",
            "CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGG","MGN","GGN","NNN"]);
        $aTripletSpeciesObjects[] = $triplet;

        $triplet = new TripletSpecieDTO();
        $triplet->setNature("ascidian mitochondria");
        $triplet->setTripletsGroups($triplets_ascidian_mitochondria);
        $triplet->setTriplets(["TTY","YTN","ATY","ATR","GTN","WSN","CCN","ACN","GCN","TAY","TAR","CAY",
            "CAR","AAY","AAR","GAY","GAR","TGY","TGR","CGN","RGN","NNN"]);
        $aTripletSpeciesObjects[] = $triplet;

        $triplet = new TripletSpecieDTO();
        $triplet->setNature("flatworm mitochondrial");
        $triplet->setTripletsGroups($triplets_flatworm_mitochondrial);
        $triplet->setTriplets(["TTY","YTN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAH","TAG","CAY",
            "CAR","ATH","AAG","GAY","GAR","TGY","TGR","CGN","GGN","NNN"]);
        $aTripletSpeciesObjects[] = $triplet;

        $triplet = new TripletSpecieDTO();
        $triplet->setNature("blepharisma macronuclear");
        $triplet->setTripletsGroups($triplets_blepharisma_macronuclear);
        $triplet->setTriplets(["TTY","YTN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAY","TRA","CAY",
            "YAR","AAY","AAR","GAY","GAR","TGY","TGG","MGN","GGN","NNN"]);
        $aTripletSpeciesObjects[] = $triplet;

        $triplet = new TripletSpecieDTO();
        $triplet->setNature("chlorophycean mitochondrial");
        $triplet->setTripletsGroups($triplets_chlorophycean_mitochondrial);
        $triplet->setTriplets(["TTY","YWN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAY","TRA",
            "CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGG","MGN","GGN","NNN"]);
        $aTripletSpeciesObjects[] = $triplet;

        $triplet = new TripletSpecieDTO();
        $triplet->setNature("trematode mitochondrial");
        $triplet->setTripletsGroups($triplets_trematode_mitochondrial);
        $triplet->setTriplets(["TTY","YTN","ATY","ATR","GTN","WSN","CCN","ACN","GCN","TAY","TAR","CAY",
            "CAR","AAH","AAG","GAY","GAR","TGY","TGR","CGN","GGN","NNN"]);
        $aTripletSpeciesObjects[] = $triplet;

        $triplet = new TripletSpecieDTO();
        $triplet->setNature("scenedesmus obliquus mitochondrial");
        $triplet->setTripletsGroups($triplets_scenedesmus_obliquus_mitochondrial);
        $triplet->setTriplets(["TTY","YWN","ATH","ATG","GTN","WSB","CCN","ACN","GCN","TAY",
            "TVR","CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGG","MGN",
            "GGN","NNN"]);
        $aTripletSpeciesObjects[] = $triplet;

        $triplet = new TripletSpecieDTO();
        $triplet->setNature("thraustochytrium mitochondrial code");
        $triplet->setTripletsGroups($triplets_thraustochytrium_mitochondrial_code);
        $triplet->setTriplets(["TTY","YTN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAY",
            "TDR","CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGG","MGN",
            "GGN","NNN"]);
        $aTripletSpeciesObjects[] = $triplet;
