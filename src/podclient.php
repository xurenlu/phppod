#!/usr/bin/env php
<?php
/**
 * @author:162cm<xurenlu@gmail.com>
 * @version:1.0.0
 * @url:http://www.162cm.com/
 *
 * */
#set_include_path("./:/home/z/share/pear/");
define("VERSION","1.0.1");
define("PROG","phppod");
include "/usr/share/podclient/phpcolor.php";
include "/usr/share/podclient/phppod.php";

function my_error_log($msg,$INFOLEVEL="ERROR"){
    if($INFOLEVEL=="ERROR")
        error_log(phpcl_str($msg,PHPCL_HIGHLIGHT,PHPCL_RED,PHPCL_BLACK));
    elseif($INFOLEVEL=="INFO")
        error_log(phpcl_str($msg,PHPCL_HIGHLIGHT,PHPCL_YELLOW,PHPCL_BLACK));
    elseif($INFOLEVEL=="DEBUG")
        error_log(phpcl_str($msg,PHPCL_HIGHLIGHT,PHPCL_BLUE,PHPCL_BLACK));
    else
        error_log(phpcl_str($msg,PHPCL_HIGHLIGHT,PHPCL_GREEN,PHPCL_BLACK));


}
// signal handler function
function sig_handler($signo)
{

     switch ($signo) {
         case SIGTERM:
             // handle shutdown tasks
            my_error_log("got SIGTERM signal.exit...");
            exit;
            break;
         case SIGHUP:
             // handle restart tasks
            my_error_log("got SIGHUP signal.exit...");
             break;
         case SIGUSR1:
             echo "Caught SIGUSR1...\n";
             break;
         case SIGINT:
             my_error_log("got SIGINT signal.exit...");
             exit();
            break;
         default:
             // handle all other signals
     }

}
function usage(){
    print '
    podclient version 1.0.0
    Author:162cm<xurenlu@gmail.com>

Usage:'.$ARGV[0].' -c [configure] [-d]

We have provided a file in /etc/podclientd.conf please modify it,and execut:
/usr/bin/podclient -c /etc/podclientd.conf  > YOURLOG
';
}
function version(){
    echo PROG." ".VERSION."\n\n";
}

if(function_exists("pcntl_signal")){
    declare(ticks = 1);
    pcntl_signal(SIGTERM, "sig_handler");
    pcntl_signal(SIGHUP,  "sig_handler");
    pcntl_signal(SIGUSR1, "sig_handler");
    pcntl_signal(SIGINT, "sig_handler");
}
global $ARGC,$ARGV;
$ARGC=$_SERVER["argc"];
$ARGV=$_SERVER["argv"];
$shortopts=join("",array("c:","d","h","v","p:"));
$options=getopt($shortopts);
if(!array_key_exists("p",$options)){
    $options["pid"]="/tmp/pid.podclient";
}
else{
    $options["pid"]=$options["p"];
}
if(array_key_exists("v",$options)){
    version();
    exit();
}
if(array_key_exists("h",$options)){
    usage();
    exit();
}
if(!array_key_exists("c",$options)){
    usage();
    exit();
};

if(!array_key_exists("d",$options)){
    $options["daemon"]=false;
}else{
    $options["daemon"]=true;
}

/**
 * 如果老的进程存在,先杀掉.
 * */
if(file_exists($options["pid"])){
    $old_pid=intval(trim(file_get_contents($options["pid"])));
    if($old_pid>0){
        if(function_exists("posix_kill")){
            if(!posix_kill($old_pid,SIGTERM)){
                my_error_log("kill pid error:".$old_pid);
            }
        }
    }
    else
    {
        my_error_log("old_pid reading error","INFO");
    }
}
else{
   my_error_log("pid file [".$options["pid"]."] not found.","INFO"); 
}
if(function_exists("posix_getpid")){
    $pid=posix_getpid();
    if(!file_put_contents($options["pid"],$pid)){
        my_error_log("pid file [".$options["pid"]."] write failed.","INFO");
    }
}

    $ip=getIP();
    $configs=parse_ini_file($options["c"],true);
    $must_fields=array("username","password");
    foreach($must_fields as $k){
        if(!array_key_exists($k,$configs)){
            my_error_log("conf file parse error.$k field must be specificed.");
            exit();
        }
    }
    $domains=array();
    foreach($configs as $k=>$v){
        if(is_array($v)){
            $domainArray=splitDomain($k);
            $v["old_sub_domain"]=$domainArray[0];
            $v["domain"]=$domainArray[1];
            $domains[]=$v;
        }
    }
    $dnspod = new dnspodapi($configs["username"], $configs["password"]);
    foreach($domains as $domain){
        $domain["value"]=$ip;
        if(!array_key_exists("sub_domain",$domain))
            $domain["sub_domain"]=$domain["old_sub_domain"];
        if(!array_key_exists("record_type",$domain))
            $domain["record_type"]=1;
        if(!array_key_exists("record_line",$domain))
            $domain["record_line"]=1;
        if(!array_key_exists("mx",$domain))
            $domain["mx"]=10;
        if(!array_key_exists("ttl",$domain))
            $domain["ttl"]=1;
        
        $return=$dnspod->ModifyRecord(
            $domain,
            $domain["domain"],
            $domain["old_sub_domain"]
        );
        if($return===false){
            my_error_log("domain:".$domain["domain"].",subdomain:".$domain["old_sub_domain"]." modify failed");
            print_r($dnspod->getError());
        }
        if($return==DOMAIN_ERROR){
            my_error_log("domain:".$domain["domain"]." not exists.try to create one for you","INFO");
            $dnspod->createDomain($domain["domain"]);
            $return=$dnspod->ModifyRecord(
                $domain,
                $domain["domain"],
                $domain["old_sub_domain"]
            );
        }
        if($return==RECORD_ERROR){
            $return=$dnspod->RecordCreate($domain,$domain["domain"]);
            print "try to create domain record,wait a moment pelase...\n";
        }
        elseif($return<0){
            my_error_log("got some error when create domain:[".$domain["domain"]."],error code:$return\n");
        }
        elseif($return==false){
            print "got some unkown error\n";
        }
        else{
            print "update information successful\n";
        }
    }

?>
