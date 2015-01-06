<?php

namespace Vivait\ReportingBundle\Order;

use Symfony\Component\Form\AbstractType;
use Vivait\ReportingBundle\Form\Type\GenericOrderType;

class GenericStringOrder extends ReportOrder
{

    public static function getAllChoices()
    {
        return [
            self::ORDER_BY_NONE => 'Off',
            self::ORDER_BY_ASC  => 'A to Z',
            self::ORDER_BY_DESC => 'Z to A',
        ];
    }

    /**
     * @return AbstractType
     */
    public function getFormType()
    {
        return new GenericOrderType(self::getAllChoices());
    }

}