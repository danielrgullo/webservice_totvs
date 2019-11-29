<?php

namespace CCS\WSTotvs;

class Ws {

    private $client = NULL;
    private $wsdl = 'http://10.0.10.7:8080/wsexecbo/WebServiceExecBO?wsdl';
    // private $wsdl = 'http://10.0.10.8:8180/wsexecbo/WebServiceExecBO?wsdl';
    private $token = '';
    private $company_id = 1;
    private $last_error = '';

    function __construct ($wsdl='') {
        if ($wsdl !== '') {
            $this->wsdl = $wsdl;
        }
        $this->token = '';
        $this->last_error = '';
        $this->client = $this->getSoapClient($this->wsdl);
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
            'style' => SOAP_DOCUMENT,
            'use' => SOAP_LITERAL,
            'encoding' => 'UTF-8',
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
            $this->last_error = str_replace('com.totvs.framework.ws.execbo.service.ExecBOServiceException: ', '',
                                            $fault->faultstring);
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
            throw new \Exception("Error quiting TOTVS WebService", 1);
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

    public function getLastError () {
        return $this->last_error;
    }
}

?>
