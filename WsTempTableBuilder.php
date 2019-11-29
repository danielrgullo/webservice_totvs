<?php

namespace CCS\WSTotvs;

class WsTempTableBuilder {

    private $tt_fields;

    public function __construct () {
        $this->tt_fields = array();
    }

    public function __toString () {
        return $this->toJSON();
    }

    public function toJSON () {
        $tt = array('fields'=>$this->tt_fields);
        return json_encode($tt);
    }

    public function build () {
        return $this->toJSON();
    }

    public function addField ($name, $type) {
        $this->tt_fields[] = array(
                'name'=> $name,
                'label'=> ucfirst(str_replace('-', ' ', $name)),
                "type"=> $type
        );
        return $this;
    }

    public function addCharacterField ($name) {
        return $this->addField($name, \CCS\WSTotvs\DataType\Character);
    }
    public function addIntegerField ($name) {
        return $this->addField($name, \CCS\WSTotvs\DataType\Integer);
    }
    public function addDecimalField ($name) {
        return $this->addField($name, \CCS\WSTotvs\DataType\Decimal);
    }
    public function addDateField ($name) {
        return $this->addField($name, \CCS\WSTotvs\DataType\Date);
    }
    public function addLogicalField ($name) {
        return $this->addField($name, \CCS\WSTotvs\DataType\Logical);
    }

}
?>