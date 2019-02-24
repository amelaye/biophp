<?php
/**
 * Form DnaToProteinType
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 february 2019
 * Last modified 24 february 2019
 */
namespace MinitoolsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DnaToProteinType extends AbstractType
{
    /**
     * Form builder
     * @param   FormBuilderInterface  $builder
     * @param   array                 $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*
         * Sample Datas
         */
        $optionsFrames = array("1" => "1" ,"1-3" => "3", "1-6" => "6");

        $textSequence = "GGAGTGAGGG GAGCAGTTGG GCCAAGATGG CGGCCGCCGA GGGACCGGTG GGCGACGCGG 60\r";
        $textSequence .= "GAGTGAGGGG AGCAGTTGGG CCAAGATGGC GGCCGCCGAG GGACCGGTGG GCGACGGGGG 120\r";
        $textSequence .= "AGTGAGGGGA GCAGTTGGGC CAAGATGGCG GCCGCCGAGG GACCGGTGGG CGACGGCGGA 180\r";
        $textSequence .= "GTGAGGGGAG CAGTTGGGCC AAGATGGCGG CCGCCGAGGG ACCGGTGGGC GACGGGGAGT 240\r";
        $textSequence .= "GAGGGGAGCA GTTGGGCCAA GATGGCGGCC GCCGAGGGAC CGGTGGGCGA CGCGGGAGTG 300\r";

        $aGeneticCode = [
            "Standard" => 1,
            "Vertebrate Mitochondrial" => 2,
            "Yeast Mitochondrial" => 3,
            "Mold, Protozoan and Coelenterate Mitochondrial. Mycoplasma, Spiroplasma" => 4,
            "Invertebrate Mitochondrial" => 5,
            "Ciliate Nuclear; Dasycladacean Nuclear; Hexamita Nuclear" => 6,
            "Echinoderm Mitochondrial" => 9,
            "Euplotid Nuclear" => 10,
            "Bacterial and Plant Plastid" => 11,
            "Alternative Yeast Nuclear" => 12,
            "Ascidian Mitochondrial" => 13,
            "Flatworm Mitochondrial" => 14,
            "Blepharisma Macronuclear" => 15,
            "Chlorophycean Mitochondrial" => 16,
            "Trematode Mitochondrial" => 21,
            "Scenedesmus obliquus mitochondrial" => 22,
            "Thraustochytrium mitochondrial code" => 23
        ];

        /*
         * Form construction
         */
        $builder->add(
            'sequence',
            TextareaType::class,
            [
                'data' => $textSequence,
                'attr' => [
                    'cols'  => 75,
                    'rows'  => 10,
                    'class' => "form-control"
                ],
                'label' => "Sequence : ",
            ]
        );
        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label' => "Translate to Protein",
                'attr' => [
                    'class' => "btn btn-primary"
                ]
            ]
        );
        $builder->add(
            'frames',
            ChoiceType::class,
            [
                'choices' => $optionsFrames,
                'label' => "Translate frames : ",
                'attr' => [
                    'class' => "custom-select d-block w-20"
                ]
            ]
        );
        $builder->add(
            'dgaps',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Output the amino acids with double gaps (--)"
            ]
        );
        $builder->add(
            'show_aligned',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Show Translations Aligned "
            ]
        );
        $builder->add(
            'search_orfs',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Search for ORFs "
            ]
        );
        $builder->add(
            'protsize',
            TextType::class,
            [
                'data' => 50,
                'label' => "Minimum size of protein sequence : ",
                'attr' => [
                    'class' => "form-control"
                ]
            ]
        );
        $builder->add(
            'only_coding',
            CheckboxType::class,
            [
                'label' => "... and do not show non-coding",
                'required' => false
            ]
        );
        $builder->add(
            'trimmed',
            CheckboxType::class,
            [
                'label' => "ORFs trimmed to MET-to-Stop",
                'required' => false,
            ]
        );
        $builder->add(
            'genetic_code',
            ChoiceType::class,
            [
                'choices' => $aGeneticCode,
                'label' => "Genetic code : ",
                'attr' => [
                    'class' => "custom-select d-block w-20"
                ]
            ]
        );
        $builder->add(
          'usemycode',
          CheckboxType::class,
            [
                'label' => "Use custom genetic code",
                'required' => false
            ]
        );
        $builder->add(
            'mycode',
            TextType::class,
            [
                'data' => "FFLLSSSSYY**CC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG",
                'attr' => [
                    'class' => "form-control"
                ]
            ]
        );
    }

    /**
     * Entity for builder
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MinitoolsBundle\Entity\DnaToProtein'
        ));
    }
}
