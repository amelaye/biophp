<?php
/**
 * Class RestrictionEnzymeDigestType
 * Freely inspired by BioPHP's project biophp.org
 * Created 7 april 2019
 * Last modified 7 april 2019
 */
namespace MinitoolsBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form RestrictionEnzymeDigestType
 * @package MinitoolsBundle\Form
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class RestrictionEnzymeDigestType extends AbstractType
{
    /**
     * Form builder
     * @param   FormBuilderInterface    $builder
     * @param   array                   $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

    }

    /**
     * Entity for builder
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MinitoolsBundle\Entity\RestrictionEnzymeDigest'
        ));
    }
}