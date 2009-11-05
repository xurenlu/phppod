<?php
/**
 * @Author:162cm<helloasp@hotmail.com>
 * @Version:1.0.1
 * */
define("NET_DEFAULT",1);
define("NET_TELCOM",2);
define("NET_CNC",3);
define("NET_EDU",4);


define("RECORD_ERROR",-1);
define("DOMAIN_ERROR",-2);

class HTTP {

    public $ch;
    public $url;
    public $param;
    public $option;
    private $method;

    public function __construct($url, $method = 'post', $redirect = true) {

        $this->url = $url;

        $this->ch = curl_init ( $this->url );

        if ($method == 'post') {
            curl_setopt ( $this->ch, CURLOPT_POST, true );
        } else {
            curl_setopt ( $this->ch, CURLOPT_HTTPGET, true );
        }

        $this->method = $method;

        curl_setopt ( $this->ch, CURLOPT_RETURNTRANSFER, true );

        if ($redirect) {
            curl_setopt ( $this->ch, CURLOPT_FOLLOWLOCATION, true );
        }
    }

    public function setOption($option) {

        if (! empty ( $option )) {
            $this->option = $option;
        } else {
            return false;
        }

        foreach ( $this->option as $k => $v ) {
            curl_setopt ( $this->ch, $k, $v );
        }

        return true;
    }

    public function setParam($param) {
        if (! empty ( $param )) {
            $this->param = $param;
        } else {
            return false;
        }

        foreach ( $param as $k => $v ) {
            $params [] = "$k=$v";
        }

        $this->param = implode ( '&', $params );

        if ($this->method == 'post') {
            curl_setopt ( $this->ch, CURLOPT_POSTFIELDS, $this->param );
        } else {
            curl_setopt ( $this->ch, CURLOPT_URL, $this->url . '?' . $this->param );
        }

        return true;
    }

    public function exec() {
        return curl_exec ( $this->ch );
    }

    public function __destruct() {
        curl_close ( $this->ch );
    }
}

class DnspodApi {
    private $httpHandler;
    private $email;
    private $pass;
    private $format = 'json';

    private $error = array();

    public function __construct($email, $pass) {
        if (! $email || ! $pass) {
            exit ( 'no email or pass' );
        }
        $this->email = $email;
        $this->pass = $pass;
        $this->httpHandler = new HTTP ( 'http://www.dnspod.com/API/' );
    }
    public function GetDomainId($domain){
        $ret=$this->GetDomainList();
        foreach($ret->domains->domain as $idomain){

            if($idomain->name==$domain){
                return $idomain->id;
            }
        }
        return DOMAIN_ERROR;
    }
    public function getRecordByname($domain,$record_name){
        $domainId=$this->getDomainId($domain);
        if($domainId<0){
            error_log("can't get domainId of $domain");
            return $domainId;
        }
        $ret=$this->getRecordlist($domainId);
        #print "try to done";
        #print_r($ret);
        foreach($ret->records->record as $ir){
            #print "new record:";
            #print $ir->name;
            if($ir->name==$record_name)
                return $ir->id;
        }
        return RECORD_ERROR;
    }
    public function createDomain($domain){
        $this->setAction('Domain.Create');
        $params['domain'] = $domain;
        return $this->exec($params);
    }

    public function removeDomain($domainId){
        $this->setAction('Domain.Remove');
        $params['domain_id'] = $domainId;
        return $this->exec($params);
    }

    public function setDomainStatus($domainId,$enable = true){
        $this->setAction('Domain.Status');
        $params['domain_id'] = $domainId;

        $status = $enable ? 'enable' : 'disable';
        $params['status'] = $status;

        return $this->exec($params);
    }

    public function getDomainList() {
        $this->setAction('Domain.List');
        return $this->exec();
    }
    public function RecordCreate($params,$domain){
        $domainId=$this->getDomainId($domain);
        $params["domain_id"]=$domainId;
        $this->setAction('Record.Create');
        return $this->exec($params);
    }
    public function getRecordList($domainId){
        $this->setAction('Record.List');
        $params['domain_id'] = $domainId;
        return $this->exec($params);
    }

    public function modifyRecord($params,$domain,$sub_domain){
        $domainId=$this->getDomainId($domain);
        if($domainId<0)
            return $domainId;
        $recordId=$this->getRecordByName($domain,$sub_domain);
        if($recordId<0){
            return $recordId;
        }
        $this->setAction('Record.Modify');
        $params["domain_id"]=$domainId;
        $params["record_id"]=$recordId;
        return $this->exec($params);
    }

    public function removeRecord($domainId,$recordId){
        $this->setAction('Record.Remove');
        $params['domain_id'] = $domainId;
        $params['record_id'] = $recordId;

        return $this->exec($params);
    }

    public function setRecordStatus($domainId,$recordId,$enable = true){
        $this->setAction('Record.Status');
        $params['domain_id'] = $domainId;
        $params['record_id'] = $recordId;

        $status = $enable ? 'enable' : 'disable';
        $params['status'] = $status;

        return $this->exec($params);
    }

    private function exec($params = ''){
        $params['login_email'] = $this->email;
        $params['login_password'] = $this->pass;
        $params['format'] = $this->format;

        $this->httpHandler->setParam($params);

        $result = $this->httpHandler->exec ();

        $result = json_decode ( $result );

        if ($result->status->code !== '1') {
            $this->error($result);
            return false;
        }

        return $result;
    }

    private function setAction($action){
        $this->httpHandler->setOption ( array (CURLOPT_URL => $this->httpHandler->url . $action ) );
    }

    private function error($result){
        $this->error['code'] = $result->status->code;
        $this->error['message'] = $result->status->message;
    }

    public function getError(){
        return $this->error;
    }
}
/**
 * 得到当前用户的公网IP,目前是通过ip138来实现的.
 * */
function getIP(){
        $ht=new HTTP("http://www.ip138.com/ip2city.asp","GET");
        #$ht=new HTTP("http://www.dnspod.com/About/IP","GET");
        $ret=$ht->exec();
        $patt="/\[([0-9\.]*)\]/";
        preg_match_all($patt,$ret,$out);
        return array_pop(array_pop($out));
}
/**
 * 将domain分割成顶级域和子域名.
 * 顶级域分返回@和顶级域名。
 * */
function splitDomain($domain){
    $domain=strtolower($domain);
    $patt='(.com.cn|.net.cn|.org.cn|.gov.cn|.com|.cn|.net|.org|.name|.info|.us|.me|.la)';
    $domains=preg_match_all($patt,$domain,$out,PREG_PATTERN_ORDER);
    $appendix=array_pop(array_pop(($out)));
    $sub=substr($domain,0,strlen($domain)-strlen($appendix));
    $sub_array=explode(".",$sub);
    $second=array_pop($sub_array);
    $first=join(".",$sub_array);
    if($first=="")
        $first="@";
    return  array($first,$second.$appendix);
}
?> 
