<?php
/**
 * Form ProteinPropertiesType
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 26 february 2019
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

class ProteinPropertiesType extends AbstractType
{
    /**
     * Form builder
     * @param   FormBuilderInterface  $builder
     * @param   array                 $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dataSources = array(
            "EMBOSS",
            "DTASelect",
            "Solomon"
        );

        $builder->add(
            'seq',
            TextareaType::class,
            [
                'attr' => [
                    'cols'  => 75,
                    'rows'  => 10,
                    'class' => "form-control"
                ],
                'label' => "Sequence : ",
            ]
        );

        $builder->add(
            'start',
            TextType::class,
            [
                'data' => 50,
                'attr' => [
                    'class' => "form-control"
                ]
            ]
        );

        $builder->add(
            'end',
            TextType::class,
            [
                'data' => 50,
                'attr' => [
                    'class' => "form-control"
                ]
            ]
        );

        $builder->add(
            'composition',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Aminoacid composition"
            ]
        );

        $builder->add(
            'molweight',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Molecular weight"
            ]
        );

        $builder->add(
            'abscoef',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Molar absorption coefficient"
            ]
        );

        $builder->add(
            'charge',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Protein isoelectric point with pK values from"
            ]
        );

        $builder->add(
            'data_source',
            ChoiceType::class,
            [
                'choices' => $dataSources,
                'attr' => [
                    'class' => "custom-select d-block w-20"
                ]
            ]
        );

        $builder->add(
            'charge2',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Charge at pH ="
            ]
        );

        $builder->add(
            'pH',
            TextType::class,
            [
                'data' => 50,
                'attr' => [
                    'class' => "form-control"
                ]
            ]
        );

        $builder->add(
            'three_letters',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Show sequence as 3 letters aminoacid code"
            ]
        );

        $builder->add(
            'type1',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Show polar, non-polar and charged nature of aminoacids"
            ]
        );

        $builder->add(
            'type2',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Show polar, non-polar, Hydrofobic, and negatively or positively charged nature of aminoacids"
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
            'data_class' => 'MinitoolsBundle\Entity\Protein'
        ));
    }
}