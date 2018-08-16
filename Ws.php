<?php

namespace WSTotvs {

    const DataTypeCharacter = 'character';
    const DataTypeInteger = 'integer';
    const DataTypeDecimal = 'decimal';
    const DataTypeDate = 'date';
    const DataTypeLogical = 'logical';
    const DataTypeTemptable = 'temptable';

    const ParamTypeInput = 'input';
    const ParamTypeOutput = 'output';
    const ParamTypeInputOutput = 'input-output';

    class Ws {

        private $client = NULL;
        private $wsdl = 'http://10.0.10.8:8180/wsexecbo/WebServiceExecBO?wsdl';
        private $token = '';
        private $company_id = 1;
        private $last_error = '';

        function __construct ($wsdl='') {
            if ($wsdl !== '') {
                $this->wsdl = $wsdl;
            }
            $this->token = '';
            $this->client = $this->getSoapClient($this->wsdl);
            $this->last_error = '';
        }

        public function getWsdl() {
            return $this->wsdl;
        }
        public function getToken() {
            return $this->token;
        }

        private function getSoapClient ($wsdl) {
            $options = array(
                'soap_version' => SOAP_1_2,
                'style'   => SOAP_DOCUMENT,
                'use'     => SOAP_LITERAL,
                'encoding'=>'UTF-8',
            );
            return new \SoapClient($wsdl, $options);
        }

        public function login ($username, $password, $encoded=FALSE) {
            if ($this->client == NULL) {
                throw new \Exception("SOAP Client Not Defined");
            }
            if($this->token != '') {
                @$this->logout();
            }
            if (!$encoded) {
                $password = base64_encode(sha1($password, true));
            }
            $login = array('arg0'=>$username, 'arg1'=>$password);

            $this->last_error = '';
            try {
                $this->token = $this->client->userAndPasswordLogin($login)->return;
            } catch(\SoapFault $fault) {
                $this->last_error = $fault->faultstring;
            }
            return $this->last_error;
        }

        public function logout () {
            if ($this->client == NULL) {
                throw new \Exception("SOAP Client Not Defined");
            }
            if ($this->token == '') {
                return;
            }
            $param = array('arg0'=>$this->token);
            $status = $this->client->logoutSession($param)->return;
            if($status == 'OK') {
                $this->token = '';
            } else {
                throw new \Exception("Error Login out TOTVS WebService", 1);
            }
        }

        public function run($program, $procedure, $params) {
            if ($this->client == NULL) {
                throw new \Exception("SOAP Client Not Defined");
            }
            if($this->token == '') {
                throw new \Exception("Don't heva a token yet!");
            }
            $callProc = array(
                'arg0'=>$this->token,
                'arg1'=>$this->company_id,
                'arg2'=>$program,
                'arg3'=>$procedure,
                'arg4'=>$params->toJSON()
            );
            $ret = '';
            $this->last_error = '';
            try {
                $ret  = $this->client->callProcedureWithTokenAndCompany($callProc)->return;
                $params->update($ret);
            } catch(\SoapFault $fault) {
                $this->last_error = $fault->faultstring;
            }
            return $ret;
        }
    }

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
                    'type'=>\WSTotvs\ParamTypeInput,
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
                'type'=>\WSTotvs\ParamTypeOutput,
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
                if ($param['type'] !== \WSTotvs\ParamTypeOutput) {
                    continue;
                }
                $value = $returned_values[$param_number]->value;
                if ($param['dataType'] == DataTypeTemptable) {
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

    class WsParamBuilder {

        private $param;

        public function __construct () {
            $this->param = new \WSTotvs\WsParam();
        }

        public function build () {
            return $this->param;
        }

        public function buildJSON () {
            return $this->param->toJSON();
        }

        public function addCharacterInput ($name, $value) {
            $this->param->addInput($name, \WSTotvs\DataTypeCharacter, $value);
            return $this;
        }
        public function addDateInput ($name, $value) {
            $this->param->addInput($name, \WSTotvs\DataTypeDate, $value);
            return $this;
        }
        public function addIntegerInput ($name, $value) {
            $this->param->addInput($name, \WSTotvs\DataTypeInteger, $value);
            return $this;
        }
        public function addDecimalInput ($name, $value) {
            $this->param->addInput($name, \WSTotvs\DataTypeDecimal, $value);
            return $this;
        }
        public function addLogicalInput ($name, $value) {
            $this->param->addInput($name, \WSTotvs\DataTypeLogical, $value);
            return $this;
        }
        public function addTemptableInput ($name, $value) {
            $this->param->addInput($name, \WSTotvs\DataTypeTemptable, $value);
            return $this;
        }

        public function addCharacterOutput($name, $value='') {
            $this->param->addOutput($name, \WSTotvs\DataTypeCharacter, $value);
            return $this;
        }
        public function addDateOutput($name, $value='') {
            $this->param->addOutput($name, \WSTotvs\DataTypeDate, $value);
            return $this;
        }
        public function addIntegerOutput($name, $value='') {
            $this->param->addOutput($name, \WSTotvs\DataTypeInteger, $value);
            return $this;
        }
        public function addDecimalOutput($name, $value='') {
            $this->param->addOutput($name, \WSTotvs\DataTypeDecimal, $value);
            return $this;
        }
        public function addLogicalOutput($name, $value='') {
            $this->param->addOutput($name, \WSTotvs\DataTypeLogical, $value);
            return $this;
        }
        public function addTemptableOutput($name, $value='') {
            if (gettype($value) == 'object') {
                $value = (string)$value;
            }
            $this->param->addOutput($name, \WSTotvs\DataTypeTemptable, $value);
            return $this;
        }
    }

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
            return $this->addField($name, \WSTotvs\DataTypeCharacter);
        }
        public function addIntegerField ($name) {
            return $this->addField($name, \WSTotvs\DataTypeInteger);
        }
        public function addDecimalField ($name) {
            return $this->addField($name, \WSTotvs\DataTypeDecimal);
        }
        public function addDateField ($name) {
            return $this->addField($name, \WSTotvs\DataTypeDate);
        }
        public function addLogicalField ($name) {
            return $this->addField($name, \WSTotvs\DataTypeLogical);
        }

    }

}

?>
