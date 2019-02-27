<?php
/**
 * Protein To DNA Functions
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 26 february 2019
 */
namespace MinitoolsBundle\Service;

class ProteinToDnaManager
{
    /**
     * @param $seq
     * @param $genetic_code
     * @return string|string[]|null
     * @todo : reprendre tableau parameters
     */
    public function translateProteinToDNA($seq, $genetic_code)
    {
        // $aminoacids is the array of aminoacids
        $aminoacids = array(
            "(F )","(L )","(I )","(M )",
            "(V )","(S )","(P )","(T )",
            "(A )","(Y )", "(\* )","(H )",
            "(Q )","(N )","(K )","(D )",
            "(E )","(C )","(W )","(R )",
            "(G )","(X )"
        );

        // Standard genetic code
        $triplets[1] = array("TTY","YTN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAY",
            "TRR","CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGG","MGN",
            "GGN","NNN");
        // Vertebrate Mitochondrial
        $triplets[2]=array("TTY","YTN","ATY","ATR","GTN","WSN","CCN","ACN","GCN","TAY",
            "WRR","CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGR","CGN",
            "GGN","NNN");
        // Yeast Mitochondrial
        $triplets[3]=array("TTY","TTR","ATY","ATR","GTN","WSN","CCN","MYN","GCN","TAY",
            "TAR","CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGR","MGN",
            "GGN","NNN");
        // Mold, Protozoan and Coelenterate Mitochondrial. Mycoplasma, Spiroplasma
        $triplets[4]=array("TTY","YTN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAY",
            "TAR","CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGR","MGN",
            "GGN","NNN");
        // Invertebrate Mitochondrial
        $triplets[5]=array("TTY","YTN","ATY","ATR","GTN","WSN","CCN","WSN","GCN","TAY",
            "TAR","CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGR","CGN",
            "GGN","NNN");
        // Ciliate Nuclear; Dasycladacean Nuclear; Hexamita Nuclear
        $triplets[6]=array("TTY","YTN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAY",
            "TGA","CAY","YAR","AAY","AAR","GAY","GAR","TGY","TGG","MGN",
            "GGN","NNN");
        // Echinoderm Mitochondrial
        $triplets[9]=array("TTY","YTN","ATH","ATG","GTN","WSN","CCN","WCN","GCN","TAY",
            "TAR","CAY","CAR","AAH","AAG","GAY","GAR","TGY","TGR","CGN",
            "GGN","NNN");
        // Euplotid Nuclear
        $triplets[10]=array("TTY","YTN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAY",
            "TAR","CAY","CAR","AAY","AAR","GAY","GAR","TGH","TGG","MGN",
            "GGN","NNN");
        // Bacterial and Plant Plastid
        $triplets[11]=array("TTY","YTN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAY",
            "TRR","CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGG","MGN",
            "GGN","NNN");
        // Alternative Yeast Nuclear
        $triplets[12]=array("TTY","YTN","ATH","ATG","GTN","HBN","CCN","ACN","GCN","TAY",
            "TRR","CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGG","MGN",
            "GGN","NNN");
        // Ascidian Mitochondrial
        $triplets[13]=array("TTY","YTN","ATY","ATR","GTN","WSN","CCN","ACN","GCN","TAY",
            "TAR","CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGR","CGN",
            "RGN","NNN");
        // Flatworm Mitochondrial
        $triplets[14]=array("TTY","YTN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAH",
            "TAG","CAY","CAR","ATH","AAG","GAY","GAR","TGY","TGR","CGN",
            "GGN","NNN");
        // Blepharisma Macronuclear
        $triplets[15]=array("TTY","YTN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAY",
            "TRA","CAY","YAR","AAY","AAR","GAY","GAR","TGY","TGG","MGN",
            "GGN","NNN");
        // Chlorophycean Mitochondrial
        $triplets[16]=array("TTY","YWN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAY",
            "TRA","CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGG","MGN",
            "GGN","NNN");
        // Trematode Mitochondrial
        $triplets[21]=array("TTY","YTN","ATY","ATR","GTN","WSN","CCN","ACN","GCN","TAY",
            "TAR","CAY","CAR","AAH","AAG","GAY","GAR","TGY","TGR","CGN",
            "GGN","NNN");
        // Scenedesmus obliquus mitochondrial
        $triplets[22]=array("TTY","YWN","ATH","ATG","GTN","WSB","CCN","ACN","GCN","TAY",
            "TVR","CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGG","MGN",
            "GGN","NNN");
        // Thraustochytrium mitochondrial code
        $triplets[23]=array("TTY","YTN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAY",
            "TDR","CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGG","MGN",
            "GGN","NNN");

        // place a space after each aminoacid in the sequence
        $temp = chunk_split($seq,1,' ');

        // replace aminoacid by corresponding amnoacid
        $peptide = preg_replace ($aminoacids, $triplets[$genetic_code], $temp);

        // return peptide sequence
        return $peptide;
    }
}