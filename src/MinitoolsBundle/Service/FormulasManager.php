<?php
/**
 * Formulas Functions
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 3 march  2019
 * Last modified 19 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

/**
 * Class FormulasManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 * @todo :  la classe n'est pas finie, ni commentée !
 */
class FormulasManager
{
    /**
     * DNA Functions
     *
     */
    /**
     * @param $sequence
     * @return float|int
     */
    public function MW_of_dsDNA($sequence)
    {
        $no_base_pair=strlen($sequence);
        return ($no_base_pair * 660);
    }

    /**
     * @param $sequence
     * @return float|int
     */
    public function MW_of_ssDNA($sequence)
    {
        $no_base_pair = strlen($sequence);
        return ($no_base_pair * 330);
    }

    /**
     * @param $pmol_dsDNA_sequence
     * @param $pmol_dsDNA_no_of_mueg
     * @return float|int
     */
    public function pmol_of_dsDNA($pmol_dsDNA_sequence, $pmol_dsDNA_no_of_mueg)
    {
        $no_base_pair = strlen($pmol_dsDNA_sequence);
        $number_of_mueg = $pmol_dsDNA_no_of_mueg;
        if (!$no_base_pair || !$number_of_mueg) {
            return 0;
        } else {
            return ((2 * pow(10,6)) * ($number_of_mueg) / (($no_base_pair) * 660));
        }
    }

    /**
     * @param $pmol_ssDNA_sequence
     * @param $pmol_ssDNA_no_of_mueg
     * @return float|int
     */
    public function pmol_of_ssDNA($pmol_ssDNA_sequence, $pmol_ssDNA_no_of_mueg)
    {
        $no_base_pair = strlen($pmol_ssDNA_sequence);
        $number_of_mueg = $pmol_ssDNA_no_of_mueg;
        if (!$no_base_pair || !$number_of_mueg) {
            return 0;
        } else {
            return (1 * pow(10,6) * $number_of_mueg / (($no_base_pair) * 330));
        }
    }

    /**
     * @param $pmol_dsDNA_sequence
     * @param $no_of_micro_dsDNA
     * @return float|int
     */
    public function micro_to_pmol_dsDNA($pmol_dsDNA_sequence, $no_of_micro_dsDNA)
    {
        $no_base_pair = strlen($pmol_dsDNA_sequence);
        $number_of_micro = $no_of_micro_dsDNA;
        if (!$no_base_pair || !$number_of_micro) {
            return 0;
        } else {
            return (($number_of_micro * 1515) / $no_base_pair);
        }
    }

    /**
     * @param $pmol_ssDNA_sequence
     * @param $no_of_micro_ssDNA
     * @return float|int
     */
    public function micro_to_pmol_ssDNA($pmol_ssDNA_sequence, $no_of_micro_ssDNA)
    {
        $no_base_pair = strlen($pmol_ssDNA_sequence);
        $number_of_micro = $no_of_micro_ssDNA;
        if (!$no_base_pair || !$number_of_micro) {
            return 0;
        } else {
            return (($number_of_micro * 3030) / $no_base_pair);
        }

    }

    /**
     * @param $micro_dsDNA_sequence
     * @param $no_of_pmol_dsDNA
     * @return float|int
     */
    public function pmol_to_micro_dsDNA($micro_dsDNA_sequence, $no_of_pmol_dsDNA)
    {
        $no_base_pair = strlen($micro_dsDNA_sequence);
        $number_of_micro = $no_of_pmol_dsDNA;
        if (!$no_base_pair || !$number_of_micro) {
            return 0;
        } else {
            return ($number_of_micro * $no_base_pair * (6.6 * pow(10, (-4))));
        }

    }

    /**
     * @param $micro_ssDNA_sequence
     * @param $no_of_pmol_ssDNA
     * @return float|int
     */
    public function pmol_to_micro_ssDNA($micro_ssDNA_sequence, $no_of_pmol_ssDNA)
    {
        $no_base_pair = strlen($micro_ssDNA_sequence);
        $number_of_micro = $no_of_pmol_ssDNA;
        if (!$no_base_pair || !$number_of_micro) {
            return 0;
        } else {
            return ($number_of_micro*$no_base_pair * (3.3 * pow(10, (-4))));
        }
    }

    /**
     * RNA Functions
     *
     */
    /**
     * @param $sequence
     * @return float|int
     */
    public function MW_of_ssRNA($sequence)
    {
        $no_base_pair = strlen($sequence);
        return ($no_base_pair * 340);
    }

    /**
     * same for both second & third equation
     * @param $pmol_ssRNA_sequence
     * @param $pmol_ssRNA_no_of_mueg
     * @return float|int
     */
    public function pmol_of_ssRNA($pmol_ssRNA_sequence, $pmol_ssRNA_no_of_mueg)
    {
        $no_base_pair = strlen($pmol_ssRNA_sequence);
        $number_of_mueg = $pmol_ssRNA_no_of_mueg;
        if(!$no_base_pair || !$number_of_mueg) {
            return 0;
        } else {
            return (($number_of_mueg * 2941) / ($no_base_pair));
        }
    }
    function pmol_to_micro_ssRNA($micro_ssRNA_sequence,$no_of_pmol_ssRNA){
        $no_base_pair=strlen($micro_ssRNA_sequence);
        $number_of_micro=$no_of_pmol_ssRNA;
        if(!$no_base_pair || !$number_of_micro){
            return 0;
        }else{
            return ($number_of_micro*$no_base_pair*(3.4*pow(10,(-4))));
        }
    }

    function  centi_to_fahren($centigrade){

        if(!$centigrade){
            return 0;
        }else{
            return  (32+($centigrade*0.555));
        }
    }
    function  farhen_to_centi($fahren){
        if(!$fahren){
            return 0;
        }else{
            return  (0.555*($fahren-32));
        }
    }
    function mbar_to_mmHg($Hg){
        if(!$Hg){
            return 0;
        }else{
            return  (0.750000*($Hg));
        }
    }
    function mbar_to_inchHg($inchHg){
        if(!$inchHg){
            return 0;
        }else{
            return  (0.039400*($inchHg));
        }
    }
    function mbar_to_psi($psi){
        if(!$psi){
            return 0;
        }else{
            return  (0.014500*($psi));
        }
    }
    function mbar_to_atm($atm){
        if(!$atm){
            return 0;
        }else{
            return  (0.000987*($atm));
        }
    }
    function mbar_to_kPa($kPa){
        if(!$kPa){
            return 0;
        }else{
            return  (0.100000*($kPa));
        }
    }
    function mbar_to_Torr($torr){
        if(!$torr){
            return 0;
        }else{
            return  (0.750000*($torr));
        }
    }

    function revpermin($rpm,$RCF,$R){
//return 100000000;
        $rcf=1.12*$R*(pow(( $rpm/1000),2) );
//print $rcf;
        $temp=($rcf / (1.12*$R));
        print $temp;
        $res=1000*( sqrt($temp) );
        return ($res);
    }

}