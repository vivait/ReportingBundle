<?php

namespace Vivait\ReportingBundle\Group;

use Symfony\Component\Form\AbstractType;
use Vivait\ReportingBundle\Form\Type\GenericGroupType;
use Vivait\ReportingBundle\Interfaces\ReportGroupOrderableInterface;

class GenericGroup extends ReportGroup implements ReportGroupOrderableInterface
{

    CONST GROUP_BY_OFF = 0;
    CONST GROUP_BY_ON = 1;

    /**
     * @param $field string     This is the DQL field to group by
     * @param $label string     This is the label to show in output
     */
    function __construct($field, $label)
    {
        $this->label = $label;
        $this->field = $field;
        $this->group = self::GROUP_BY_OFF;
    }

    public static function getAllChoices()
    {
        return [
            self::GROUP_BY_OFF => 'Off',
            self::GROUP_BY_ON => 'On',
        ];
    }

    public function getSelect()
    {
        if ($this->group == self::GROUP_BY_OFF) {
            return null;
        } elseif ($this->group == self::GROUP_BY_ON) {
            return sprintf("%s as %s", $this->field, $this->getAlias());
        }
        throw new \Exception('Unknown group type');
    }

    /**
     * @return AbstractType
     */
    public function getFormType()
    {
        return new GenericGroupType();
    }

    /**
     * Returns the column mappings used by the group by
     * @return array
     */
    public function getColumnMapping()
    {
        if ($this->group) {
            return [
                $this->getAlias() => [
                    'label'   => $this->label,
                    'grouped' => true
                ],
            ];
        }

        return [];
    }

    /**
     * @return array|null
     */
    public static function getAllOrderChoices()
    {
        return [
            self::ORDER_BY_NONE   => 'None',
            self::ORDER_BY_ASC   => 'A - Z',
            self::ORDER_BY_DESC   => 'Z - A',
        ];
    }
}