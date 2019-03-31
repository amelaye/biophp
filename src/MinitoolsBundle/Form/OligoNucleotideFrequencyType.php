<?php
/**
 * Form to calculate Oligonucleotide Frequency
 * Freely inspired by BioPHP's project biophp.org
 * Created 31 march 2019
 * Last modified 31 march 2019
 */
namespace MinitoolsBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;

/**
 * Class OligoNucleotideFrequencyType
 * @package MinitoolsBundle\Form
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class OligoNucleotideFrequencyType extends AbstractType
{
    /**
     * Form builder
     * @param   FormBuilderInterface $builder
     * @param   array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sequenceSample = "AACAATGCCATGATGATGATTATTACGACACAACAACACCGCGCTTGACGGCGGCGGATGGATGCCG";
        $sequenceSample .= "CGATCAGACGTTCAACGCCCACGTAACGTAACGCAACGTAACCTAACGACACTGTTAACGGTACGAT";

        $builder->add(
            'sequence',
            TextareaType::class,
            [
                'attr' => [
                    'cols'  => 75,
                    'rows'  => 10,
                    'class' => "form-control"
                ],
                'label' => "Sequence : ",
                'data' => $sequenceSample
            ]
        );

        $builder->add(
            'len',
            ChoiceType::class,
            [
                'choices' => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8],
                'label' => "Select length of oligonucleotides :",
                'attr' => [
                    'class' => "form-control"
                ],
            ]
        );

        $builder->add(
            'strands',
            ChoiceType::class,
            [
                'choices' => ["one strand" => 1, "both strands" => 2],
                'label' => "Compute frequencies in :",
                'attr' => [
                    'class' => "form-control"
                ],
            ]
        );


        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label' => "Find oligonucleotide frequencies",
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
            'data_class' => 'MinitoolsBundle\Entity\OligoNucleotideFrequency'
        ));
    }
}