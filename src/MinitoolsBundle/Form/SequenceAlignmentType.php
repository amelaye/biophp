<?php
/**
 * Class ReduceAlphabet
 * Freely inspired by BioPHP's project biophp.org
 * Created 20 april 2019
 * Last modified 20 april 2019
 */
namespace MinitoolsBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Class SequenceAlignmentType
 * @package MinitoolsBundle\Form
 * @author Amelie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class SequenceAlignmentType extends AbstractType
{
    /**
     * Form builder
     * @param   FormBuilderInterface    $builder
     * @param   array                   $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dataSeq1 = "GGAGTGAGGG GAGCAGTTGG CTGAAGATGG TCCCCGCCGA GGGACCGGTG GGCGACGGCG 60\n";
        $dataSeq1.= "AGCTGTGGCA GACCTGGCTT CCTAACCACG TCCGTGTTCT TGCGGCTCCG GGAGGGACTG 120";

        $builder->add(
            'id1',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control"
                ],
                'data' => "Sequence 1"
            ]
        );

        $builder->add(
            'sequence',
            TextareaType::class,
            [
                'attr' => [
                    'cols'  => 75,
                    'rows'  => 4,
                    'class' => "form-control"
                ],
                'data' => $dataSeq1,
                'required' => true
            ]
        );

        $dataSeq2 = "CGCATGCGGA GTGAGGGGAG CAGTTGGGAA CAGATGGTCC CCGCCGAGGG ACCGGTGGGC 60\n";
        $dataSeq2.= "GACGGCCAGC TGTGGCAGAC CTGGCTTCCT AACCACGGAA CGTTCTTTCC GCTCCGGGAG 120";

        $builder->add(
            'id2',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control"
                ],
                'data' => "Sequence 2"
            ]
        );

        $builder->add(
            'sequence2',
            TextareaType::class,
            [
                'attr' => [
                    'cols'  => 75,
                    'rows'  => 4,
                    'class' => "form-control"
                ],
                'data' => $dataSeq2,
                'required' => true
            ]
        );

        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label' => "Align sequences",
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
            'data_class' => 'MinitoolsBundle\Entity\SequenceAlignment'
        ));
    }
}