<?php

require_once 'common.inc.php';

use smcs\language_lab\LanguageLabLTI;

/* clear any existing session data */
unset($_SESSION['user']);
unset($_SESSION['toolProvider']);

/* set up a Tool Provider (TP) object to process the LTI request */
$toolProvider = new LanguageLabLTI(LTI_Data_Connector::getDataConnector($sql));
$toolProvider->setParameterConstraint('oauth_consumer_key', TRUE, 50);
$toolProvider->setParameterConstraint('resource_link_id', TRUE, 50, array('basic-lti-launch-request'));
$toolProvider->setParameterConstraint('user_id', TRUE, 50, array('basic-lti-launch-request'));
$toolProvider->setParameterConstraint('roles', TRUE, NULL, array('basic-lti-launch-request'));

$_SESSION['toolProvider'] = $toolProvider;

/* process the LTI request from the Tool Consumer (TC) */
$toolProvider->handle_request();
