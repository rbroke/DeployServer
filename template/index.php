<?php

/**
 * Sync the files of a Git repository with the Web server
 *
 * @author Ignacio de Tomás <nacho@inacho.es>
 * @copyright 2013 Ignacio de Tomás (http://inacho.es)
 */

ini_set("display_errors", true);
error_reporting(E_ALL);
session_start();
header('Content-Type: text/html; charset=UTF-8');

/* CONFIG
--------------------------------------------- */
define('LOGIN_ENABLED', true);
define('SELF_URL', $_SERVER['PHP_SELF']);
define('HOME_URL', 'http://54.64.140.240');
define('SCRIPT_PATH_DEPLOY', '/var/www/html/scripts/deploy ');
define('SCRIPT_PATH_LOG', '/var/www/html/scripts/dlog ');
define('SCRIPT_PATH_SHOW', '/var/www/html/scripts/dshow ');

/* Users Accounts
--------------------------------------------- */
$Users_file = "/home/ubuntu/accounts.json";
$Ufile_contents = file_get_contents(($Users_file),true);
$Ujson = json_decode($Ufile_contents,true);
$workspace = $_SESSION['Workspace'];
$target = $_SESSION['Workspace'].'_'.$_SESSION['location'];
$repository = exec("cd EIP && git config --get remote.origin.url");
#$repository = "temp";

function isValidUser()
{
    if (! LOGIN_ENABLED) {
        return true;
    }

    if (isset($_SESSION['validUser']) && $_SESSION['validUser'] == 1) {
        return true;
    }

    return false;
}

function setValidUser($valid)
{
    if ($valid) {
        $_SESSION['validUser'] = 1;
    } else {
        unset($_SESSION['validUser']);
    }
}

function setMsg($name, $message)
{
    $_SESSION[$name] = $message;
}

function getMsg($name)
{
    if (isset($_SESSION[$name])) {
        $res = $_SESSION[$name];
        unset($_SESSION[$name]);
        return $res;
    }

    return '';
}


$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {

    case 'logout':
        if (isValidUser()) {
            setValidUser(false);
            header('Location: ' . HOME_URL);
            exit;
        }
        break;


    case 'deploy':
        if (isValidUser()) {
            exec(SCRIPT_PATH_DEPLOY . $workspace . ' 2>&1 ', $execResult);
            if (! empty($execResult)) {
                $execResult = implode("\n", $execResult);
                setMsg('execResult', $execResult);
            }
        }
        header('Location: ' . SELF_URL);
        exit;
        break;


    case 'log':
        if (isValidUser()) {
//            exec('echo "$(git log -1 --format=medium)" 2>&1', $execResult);
            exec(SCRIPT_PATH_LOG . ' 2>&1', $execResult);
            if (! empty($execResult)) {
                $execResult = implode("\n", $execResult);
                setMsg('execResult', $execResult);
            }
        }
        header('Location: ' . SELF_URL);
        exit;
        break;

    case 'show':
        if (isValidUser()) {
            exec(SCRIPT_PATH_SHOW . ' 2>&1', $execResult);
            if (! empty($execResult)) {
                $execResult = "Latest commit deployed in this server:\n\n" . implode("\n", $execResult);
                setMsg('execResult', $execResult);
            }
        }
        header('Location: ' . SELF_URL);
        exit;
        break;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>LiVEBRiCKS DEPLOiER</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />

    <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        ul {
            margin-bottom: 1.5em;
        }
        .form-signin {
            max-width: 330px;
            padding: 15px;
            margin: 0 auto;
        }
        .form-signin .form-signin-heading,
        .form-signin .checkbox {
            margin-bottom: 10px;
        }
        .form-signin .checkbox {
            font-weight: normal;
        }
        .form-signin .form-control {
            position: relative;
            font-size: 16px;
            height: auto;
            padding: 10px;
            -webkit-box-sizing: border-box;
               -moz-box-sizing: border-box;
                    box-sizing: border-box;
        }
        .form-signin .form-control:focus {
            z-index: 2;
        }
        .form-signin input[type="text"] {
            margin-bottom: -1px;
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
        }
        .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
    </style>

    </head>
    <body>
        <?php if(isValidUser()): ?>

            <div class="container">
                <div class="page-header">
                <h1>Deploy to Production of <code>[<?= $workspace;?>]</code></h1>
                </div>
                <p class="fontend">
                Sync the files of a Git repository <code>(<?= $repository;?>)</code> to <code><?= $target;?></code>
                </p>
                <ul>
                    <li>All files in the branch being deployed will be copied to the deployment</li>
                    <li>Files that were deleted in the Git repo since the last deployment will be deleted from the deployment directory</li>
                    <li>Untracked files in the deploy directory will be left alone</li>
                </ul>
                <p>
                    <a href="<?php echo SELF_URL ?>?action=deploy" class="btn btn-success btn-lg"><span class="glyphicon glyphicon-cloud-download"></span> Run deploy</a>
                    <a href="<?php echo SELF_URL ?>?action=log" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-list"></span> Git log</a>
                    <a href="<?php echo SELF_URL ?>?action=show" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-question-sign"></span> Git show</a>
                    <a href="<?php echo SELF_URL ?>?action=logout" class="btn btn-danger btn-lg"><span class="glyphicon glyphicon-log-out"></span> Sign out</a>
                </p>
                <?php if ($result = getMsg('execResult')): ?>
                    <h3>Output of execution</h3>
                    <pre><?php echo htmlentities($result, ENT_COMPAT, 'utf-8') ?></pre>
                <?php endif ?>
            </div>
        <?php
            else:
                header('Location: '. HOME_URL);
            endif
        ?>
    </body>
</html>
