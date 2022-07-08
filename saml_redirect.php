<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* SAML redirect script: users with a valid SP-lounge session
*
* @author Jephte Abijuru <jephte.abijuru@minervis.com>
* @version $Id$
*
* @package ilias-layout
*/


// jump to setup if ILIAS3 is not installed
if (!file_exists(getcwd() . "/ilias.ini.php")) {
    header("Location: ./setup/setup.php");
    exit();
}

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

global $DIC;

function isSPLounge($referer = '')
{
    global $DIC;
    $referer = strtolower(trim($referer,'/'));
    $allowed_hosts = array(
        'dev-splounge', //example: dev-splounge.cs128.force.com
        'splounge', //example: splounge.force.com
        'sp-unternehmerforum--dev' //example: sp-unternehmerforum--dev.lightning.force.com
    );
    $allowed_domains = array('force.com');

    preg_match("/[a-z0-9\-]{1,63}\.[a-z\.]{2,6}$/", parse_url($referer, PHP_URL_HOST),$domain);
    $referer_domain = $domain[0];
    if(!$referer_domain) return false;
    if(!in_array($referer_domain, $allowed_domains)){
        $DIC->logger()->root()->info($referer . " is not a valid SP lounge domain");
        return false;
    }
    $retrieved = array_filter($allowed_hosts, function($item) use ($referer){
        return strpos($referer, $item) > 0;
    });
    if(count($retrieved) < 1){
        $DIC->logger()->root()->info($referer . " is not a valid SP lounge domain");
        return false;
    }
    $DIC->logger()->root()->info($referer . " is a valid SP lounge domain");
    return true;
}


if( count(ilSamlIdp::getActiveIdpList()) > 0){
    $DIC->logger()->root()->info("Found active Idps");
    $DIC->logger()->root()->dump($_SERVER);
    if(isset($_SERVER) && $_SERVER['HTTP_REFERER'] && isSPLounge($_SERVER['HTTP_REFERER'])){
        $DIC->ctrl()->initBaseClass('ilStartUpGUI');
        $DIC->ctrl()->setCmd('doSamlAuthentication');
        $DIC->ctrl()->setTargetScript('ilias.php');
        $DIC->ctrl()->callBaseClass();
        exit;
    }else{

    }
}