<?php

namespace Vivait\ReportingBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Vivait\ReportingBundle\Report\Group\GenericGroup;

class GenericGroupType extends ReportFilterType
{

    public function buildForm(FormBuilderInterface $form, array $options)
    {
        $form->add(
            'group',
            'choice',
            array(
                'label'    => 'Grouping',
                'required' => true,
                'choices' => GenericGroup::getAllChoices(),
            )
        );
        $form->add(
            'order',
            'choice',
            array(
                'label'    => 'Ordering',
                'required' => true,
                'choices' => GenericGroup::getAllOrderChoices(),
            )
        );

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'report_group_generic';
    }
}