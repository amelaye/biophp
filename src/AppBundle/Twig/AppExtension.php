<?php
/**
 * Some extensions to format the rending code
 * Freely inspired by BioPHP's project biophp.org
 * Created 6 april 2019
 * Last modified 7 april 2019
 */
namespace AppBundle\Twig;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

/**
 * Class AppExtension
 * @package AppBundle\Twig
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 * @TODO : revoir pour le tagging as a service
 */
class AppExtension extends AbstractExtension
{
    /**
     * @var array
     */
    private $protein_colors;

    /**
     * @var array
     */
    private $generic_colors;

    /**
     * AppExtension constructor.
     * @param $protein_colors
     * @param $generic_colors
     */
    public function __construct(array $protein_colors = [], array $generic_colors = [])
    {
        $this->protein_colors = $protein_colors;
        $this->generic_colors = $generic_colors;
    }

    /**
     * Creating new filters for my app <3
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('chunk_split', [$this, '_chunk_split'], ['is_safe' => ['html']]),
            new TwigFilter('atgc_sublimer', [$this, 'sublimerATGC'], ['is_safe' => ['html']]),
            new TwigFilter('color_amino_custom', [$this, 'colorAminoCustom'], ['is_safe' => ['html']]),
            new TwigFilter('color_amino', [$this, 'colorAmino'], ['is_safe' => ['html']]),
            new TwigFilter('digest_code', [$this, 'disposeDigestedCode'], ['is_safe' => ['html']]),
            new TwigFilter('dispose_sequences', [$this, 'disposeAlignmentSequences'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Split a string into smaller chunks
     * @param   string  $subject
     * @param   int     $chucklen
     * @param   string  $end
     * @return  string
     */
    public function _chunk_split($subject, $chucklen, $end = "\r\n")
    {
        return chunk_split($subject, $chucklen, $end);
    }

    /**
     * Shows ATGC in another color
     * @param   string  $subject
     * @param   string  $color
     * @return  string
     */
    public function sublimerATGC($subject, $color = "red")
    {
        $subject = preg_replace("/A/",'<span style="color:'.$color.'">A</span>',$subject);
        $subject = preg_replace("/C/",'<span style="color:'.$color.'">C</span>',$subject);
        $subject = preg_replace("/G/",'<span style="color:'.$color.'">G</span>',$subject);
        $subject = preg_replace("/T/",'<span style="color:'.$color.'">T</span>',$subject);

        // two lines to reduce the code to be transmitted
        $subject = preg_replace('/<\/span><span style="color:#'.$color.'">/',"",$subject);
        $subject = preg_replace('/<\/span> <span style="color:#'.$color.'">>/'," ",$subject);

        return $subject;
    }

    /**
     * Get colored html code for $seq by using the $seq2 (the reduced sequence)
     * as a reference, and according to the personalized alphabet included in the form
     * returns an html code
     * @param       string          $subject
     * @param       string          $sequence
     * @param       string          $customAlphabet
     * @return      string
     * @throws      \Exception
     */
    public function colorAminoCustom($subject, $sequence, $customAlphabet)
    {
        try {
            // get array with letters
            $a = preg_split("//", $customAlphabet, -1, PREG_SPLIT_NO_EMPTY);
            $a = array_unique($a);

            foreach ($a as $key => $val) {
                $letters[$val] = $this->generic_colors[$key];
            }

            $coloredSeq = "";
            for ($i = 0; $i < strlen($subject); $i++) {
                $letterSeq = substr($subject, $i, 1);
                $letterSeq2 = substr($sequence, $i, 1);
                if (isset($letters[$letterSeq2]) && $letters[$letterSeq2] != ""){
                    $coloredSeq .= '<span style="color: #'.strtolower($letters[$letterSeq2]).'">'.$letterSeq.'</span>';
                } else {
                    $coloredSeq .= $letterSeq;
                }
            }

            return $coloredSeq;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get colored html code for $seq by using the $seq2 (the reduced sequence)
     * as a reference, and according to the $type of reduction selected
     * returns an html code
     * @param       string          $subject
     * @param       string          $sequence
     * @param       string          $sType
     * @return      string
     * @throws      \Exception
     */
    public function colorAmino($subject, $sequence, $sType)
    {
        try {
            $letters_array = $this->protein_colors;
            $coloredSeq = "";

            for ($i = 0; $i < strlen($subject); $i ++) {
                $sLetterSeq = substr($subject,$i,1);
                $sLetterSeq2 = substr($sequence,$i,1);

                if (isset($letters_array[$sType][$sLetterSeq2]) && $letters_array[$sType][$sLetterSeq2] != "") {
                    $coloredSeq .= '<span style="color: #'.strtolower($letters_array[$sType][$sLetterSeq2]).'">'.$sLetterSeq.'</span>';
                } else {
                    $coloredSeq .= $sLetterSeq;
                }
            }

            return $coloredSeq;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Renders beautifully the analyzed code
     * @param   string      $subject
     */
    public function disposeDigestedCode($subject)
    {
        $s = 0;
        $rtop = chunk_split($subject,10,' ');
        while ($s <= strlen($rtop)) {
            $rline = substr($rtop, $s, 110);
            print "$rline ";
            $s = $s + 110;
            if (strlen($rline) == 110){
                print $s / 1.1;
            }
            print "<br />";
        }
    }

    /**
     * Renders beautifully two sequences for comparaison
     * @param $compare
     * @param $align_seqa
     * @param $align_seqb
     */
    public function disposeAlignmentSequences($compare, $align_seqa, $align_seqb)
    {
        print "<pre>";
        $i=0;
        while($i<strlen($align_seqa)){
            $ii=$i+100;
            if ($ii>strlen($align_seqa)){$ii=strlen($align_seqa);}
            print substr($align_seqa,$i,100)."  $ii\n";
            print substr($compare,$i,100)."\n";
            print substr($align_seqb,$i,100)."  $ii\n\n";
            $i+=100;
        }
        print "</pre>";
    }
}