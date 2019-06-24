<?php
/**
 * Entity MeltingTemperature for Form
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 february 2019
 * Last modified 24 june 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Class MeltingTemperatureType
 * @package MinitoolsBundle\Form
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class MeltingTemperatureType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'primer',
            TextType::class,
            [
                'attr' => [
                    'cols'  => 75,
                    'rows'  => 10,
                    'class' => "form-control"
                ],
                'label' => "Primer (6 - 50 bases) : ",
            ]
        );

        $builder->add(
            'basic',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Basic Tm (Degenerated nucleotides are allowed) : ",
            ]
        );

        $builder->add(
            'nearestNeighbor',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Basic Tm (Degenerated nucleotides are NOT allowed) : ",
            ]
        );

        $builder->add(
            'cp',
            TextType::class,
            [
                'attr' => [
                    'cols'  => 75,
                    'rows'  => 10,
                    'class' => "form-control"
                ],
                'data' => 200,
                'label' => "Primer concentration (nM) : ",
            ]
        );

        $builder->add(
            'cs',
            TextType::class,
            [
                'attr' => [
                    'cols'  => 75,
                    'rows'  => 10,
                    'class' => "form-control"
                ],
                'data' => 50,
                'label' => "Salt concentration (mM) : ",
            ]
        );

        $builder->add(
            'cmg',
            TextType::class,
            [
                'attr' => [
                    'cols'  => 75,
                    'rows'  => 10,
                    'class' => "form-control"
                ],
                'data' => 0,
                'label' => "Mg2+ concentration (mM) : ",
            ]
        );


        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label' => "Calculate Tm",
                'attr' => [
                    'class' => "btn btn-primary"
                ]
            ]
        );
    }
}
