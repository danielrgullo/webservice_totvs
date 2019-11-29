<?php

namespace CCS\WSTotvs\DataType;

const Character = 'character';
const Integer = 'integer';
const Decimal = 'decimal';
const Date = 'date';
const Logical = 'logical';
const Temptable = 'temptable';


namespace CCS\WSTotvs;

class WsParamBuilder {

    private $param;

    public function __construct () {
        $this->param = new \CCS\WSTotvs\WsParam();
    }

    public function build () {
        return $this->param;
    }

    public function buildJSON () {
        return $this->param->toJSON();
    }

    public function addCharacterInput ($name, $value) {
        $this->param->addInput($name, \CCS\WSTotvs\DataType\Character, $value);
        return $this;
    }
    public function addDateInput ($name, $value) {
        $this->param->addInput($name, \CCS\WSTotvs\DataType\Date, $value);
        return $this;
    }
    public function addIntegerInput ($name, $value) {
        $this->param->addInput($name, \CCS\WSTotvs\DataType\Integer, $value);
        return $this;
    }
    public function addDecimalInput ($name, $value) {
        $this->param->addInput($name, \CCS\WSTotvs\DataType\Decimal, $value);
        return $this;
    }
    public function addLogicalInput ($name, $value) {
        $this->param->addInput($name, \CCS\WSTotvs\DataType\Logical, $value);
        return $this;
    }
    public function addTemptableInput ($name, $value) {
        $this->param->addInput($name, \CCS\WSTotvs\DataType\Temptable, $value);
        return $this;
    }

    public function addCharacterOutput($name, $value='') {
        $this->param->addOutput($name, \CCS\WSTotvs\DataType\Character, $value);
        return $this;
    }
    public function addDateOutput($name, $value='') {
        $this->param->addOutput($name, \CCS\WSTotvs\DataType\Date, $value);
        return $this;
    }
    public function addIntegerOutput($name, $value='') {
        $this->param->addOutput($name, \CCS\WSTotvs\DataType\Integer, $value);
        return $this;
    }
    public function addDecimalOutput($name, $value='') {
        $this->param->addOutput($name, \CCS\WSTotvs\DataType\Decimal, $value);
        return $this;
    }
    public function addLogicalOutput($name, $value='') {
        $this->param->addOutput($name, \CCS\WSTotvs\DataType\Logical, $value);
        return $this;
    }
    public function addTemptableOutput($name, $value='') {
        if (gettype($value) == 'object') {
            $value = (string)$value;
        }
        $this->param->addOutput($name, \CCS\WSTotvs\DataType\Temptable, $value);
        return $this;
    }
}

?>