<?php
/**
 * Form ProteinToDnaType
 * Freely inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 26 february 2019
 */
namespace MinitoolsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Class ProteinToDna
 * @package MinitoolsBundle\Form
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class ProteinToDnaType extends AbstractType
{
    /**
     * Form builder
     * @param   FormBuilderInterface  $builder
     * @param   array                 $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $geneticData = [
            "Standard genetic code" => 'standard_genetic_code',
            "Vertebrate Mitochondrial" => 'vertebrate_mitochondrial',
            "Yeast Mitochondrial" => 'yeast_mitochondrial',
            "Mold, Protozoan and Coelenterate Mitochondrial. Mycoplasma, Spiroplasma" => 'mold_rotozoan_coelenterate_mitochondrial',
            "Invertebrate Mitochondrial" => 'invertebrate_mitochondrial',
            "Ciliate Nuclear; Dasycladacean Nuclear; Hexamita Nuclear" => 'ciliate_dasycladacean_hexamita_nuclear',
            "Echinoderm Mitochondrial" => 'echinoderm_mitochondrial',
            "Euplotid Nuclear" => 'euplotid_nuclear',
            "Bacterial and Plant Plastid" => 'bacterial_and_plant_plastid',
            "Alternative Yeast Nuclear" => 'alternative_yeast_nuclear',
            "Ascidian Mitochondrial" => 'ascidian_mitochondrial',
            "Flatworm Mitochondrial" => 'flatworm_mitochondrial',
            "Blepharisma Macronuclear" => 'blepharisma_macronuclear',
            "Chlorophycean Mitochondrial" => 'chlorophycean_mitochondrial',
            "Trematode Mitochondrial" => 'trematode_mitochondrial',
            "Scenedesmus obliquus mitochondrial" => 'scenedesmus_obliquus_mitochondrial',
            "Thraustochytrium mitochondrial code" => 'thraustochytrium_mitochondrial_code'
        ];

        $builder->add(
            'sequence',
            TextareaType::class,
            [
                'attr' => [
                    'cols'  => 75,
                    'rows'  => 10,
                    'class' => "form-control"
                ],
                'label' => "Protein sequence : ",
                'data' => "FLIMVSPTAYHQNKDECWRGX*"
            ]
        );

        $builder->add(
            'genetic_code',
            ChoiceType::class,
            [
                'choices' => $geneticData,
                'attr' => [
                    'class' => "custom-select d-block w-20"
                ],
                'label' => "Genetic code : ",
            ]
        );

        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label' => "Submit",
                'attr' => [
                    'class' => "btn btn-primary"
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
            'data_class' => 'MinitoolsBundle\Entity\ProteinToDna'
        ));
    }
}