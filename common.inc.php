<?php

require_once 'vendor/autoload.php';

use OpenTok\OpenTok;

session_start();

$secrets = new Battis\ConfigXML(__DIR__ . '/secrets.xml');

$opentok = $secrets->newInstanceOf(OpenTok::class, '//opentok');

$sql = $secrets->newInstanceOf(mysqli::class, '//mysql');

if (!defined('LAUNCH_LTI')) {
    $smarty = Battis\BootstrapSmarty\BootstrapSmarty::getSmarty();
    $smarty->assign(
        'rootURL',
        (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on' ?
            'http://' :
            'https://'
        ) .
        $_SERVER['SERVER_NAME'] .
        $_SERVER['CONTEXT_PREFIX'] .
        str_replace(
            $_SERVER['CONTEXT_DOCUMENT_ROOT'],
            '',
            __DIR__
        )
    );
    $smarty->addTemplateDir(__DIR__ . '/templates');

    if (!empty($_SESSION['user'])) {
        $smarty->assign('context', $_SESSION['user']->getResourceLink()->lti_context_id);
        $smarty->assign('user', $_SESSION['user']->getId());
        $smarty->assign('firstName', $_SESSION['user']->firstname);
        $smarty->assign('lastName', $_SESSION['user']->lastname);
    }
}
?>
