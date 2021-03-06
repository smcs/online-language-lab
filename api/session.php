<?php

/**
 * GET {language-lab instance url}/api/v1/session?context={LTI context ID}&user={LTI user ID}[&type={class|group}]
 *
 * Returns
 * {
 * 	api_key: {OpenTok API key},
 * 	session_id: {OpenTok session ID},
 * 	token: {OpenTok moderator token for `session_id`},
 * 	id: {Database ID for `session_id`}[,
 * 	group: {Database ID for group}]
 * }
 */

require_once 'common.inc.php';

use Battis\BootstrapSmarty\NotificationMessage;
use OpenTok\Role;

requiredParameters([PARAM_CONTEXT, PARAM_USER, PARAM_USER_NAME]);

/* default to TYPE_CLASS if none (or nonexistent) specified */
switch (trim(strtolower((empty($_REQUEST[PARAM_TYPE]) ? TYPE_CLASS : $_REQUEST[PARAM_TYPE])))) {
	case TYPE_GROUP:
		$type = TYPE_GROUP;
		break;
	case TYPE_CLASS:
	default:
		$type = TYPE_CLASS;
		if ($_SESSION['app']->sql->query("
			DELETE
				FROM `sessions`
				WHERE
					`context` = '" . $_SESSION['app']->sql->escape_string($_REQUEST[PARAM_CONTEXT]) . "' AND
					`type` = '" . TYPE_CLASS . "'
		") === false) {
			databaseError(__LINE__);
		}
		break;
}

$openTokSession = $_SESSION['app']->opentok->createSession();
if ($_SESSION['app']->sql->query("
	INSERT INTO `sessions`
		(
			`context`,
			`user`,
			`tokbox`,
			`type`
		) VALUES (
			'" . $_SESSION['app']->sql->escape_string($_REQUEST[PARAM_CONTEXT]) . "',
			'" . $_SESSION['app']->sql->escape_string($_REQUEST[PARAM_USER]) . "',
			'" . $_SESSION['app']->sql->escape_string($openTokSession->getSessionId()) . "',
			'$type'
		)
") === false) {
	databaseError(__LINE__);
}
$apiResponse[API_KEY] = $_SESSION['app']->config->toString('//tokbox/key');
$apiResponse[API_SESSION_ID] = $openTokSession->getSessionId();
$apiResponse[API_SESSION_TOKEN] = $_SESSION['app']->opentok->generateToken(
	$openTokSession->getSessionId(), [
		'role' => Role::MODERATOR,
		'data' => json_encode([
			'context' => $_REQUEST[PARAM_CONTEXT],
			'user' => $_REQUEST[PARAM_USER],
			'user_name' => $_REQUEST[PARAM_USER_NAME]
		])
	]
);
$apiResponse[API_DATABASE_ID] = $_SESSION['app']->sql->insert_id;

// TODO deal with residual group sessions (should probably be cleared when class session is created)
if ($type === TYPE_GROUP) {
	if ($_SESSION['app']->sql->query("
		INSERT INTO `groups`
			(
				`context`,
				`session`
			) VALUES (
				'" . $_SESSION['app']->sql->escape_string($_REQUEST[PARAM_CONTEXT]) . "',
				'" . $apiResponse[API_DATABASE_ID] . "'
			)
	") === false) {
		databaseError(__LINE__);
	}
	$apiResponse[API_GROUP_ID] = $_SESSION['app']->sql->insert_id;
}

sendResponse($apiResponse);
