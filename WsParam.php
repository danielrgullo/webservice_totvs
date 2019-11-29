<?php

namespace CCS\WSTotvs\ParamType;

const Input = 'input';
const Output = 'output';
const InputOutput = 'input-output';


namespace CCS\WSTotvs;

class WsParam implements \Iterator {

    private $params;

    public function __construct () {
        $this->params = array();
    }

    public function __toString () {
        return $this->toJSON();
    }
    public function toJSON () {
        return json_encode($this->params);
    }

    public function addInput ($name, $dataType, $value) {
        if (gettype($value) == 'object') {
            $value = (string) $value;
        }
        $this->params[] = array(
                'name'=> $name,
                'type'=>\CCS\WSTotvs\ParamType\Input,
                'dataType'=>$dataType,
                'value'=>$value
        );
    }

    public function addOutput ($name, $dataType, $value) {
        if (gettype($value) == 'object') {
            $value = (string) $value;
        }
        $this->params[] = array(
            'name'=>$name,
            'type'=>\CCS\WSTotvs\ParamType\Output,
            'dataType'=>$dataType,
            'value'=>$value
        );
    }
    //  Iterator *****************************
    public function rewind () {
        reset($this->params);
    }
    public function current () {
        return current($this->params);
    }
    public function key () {
        return key($this->params);
    }
    public function next () {
        return next($this->params);
    }
    public function valid () {
        $key = key($this->params);
        return ($key !== NULL && $key !== FALSE);
    }
    // ******************************************
    public function update ($returned_values) {
        if ( gettype($returned_values) == 'string') {
            $returned_values = json_decode($returned_values);
        }
        $param_number=0;
        foreach ($this->params as &$param) {
            if ($param['type'] !== \CCS\WSTotvs\ParamType\Output) {
                continue;
            }
            $value = $returned_values[$param_number]->value;
            if ($param['dataType'] == DataType\Temptable) {
                list($fields, $records) = $this->extractTemptable($value);
                $param['fields'] = $fields;
                $value = $records;
            }
            $param['value'] = $value;
            $param_number++;
        }
    }
    private function extractTemptable ($output_value) {
        if (gettype($output_value) == 'string') {
            $output_value = json_decode($output_value);
        }
        return array($output_value->fields, $output_value->records);
    }
}

?>