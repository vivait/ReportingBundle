<?php

namespace Vivait\ReportingBundle\Order;

use Symfony\Component\Form\AbstractType;
use Vivait\ReportingBundle\Form\Type\GenericOrderType;

class GenericDateOrder extends ReportOrder
{

    public static function getAllChoices()
    {
        return [
            self::ORDER_BY_NONE => 'Off',
            self::ORDER_BY_ASC  => 'Oldest to Newest',
            self::ORDER_BY_DESC => 'Newest to Oldest',
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