<?php
/**
 * Common file for all modules
 * checks the media directory and display the listing or the form according to the url
 */
debug_backtrace() || die ('Direct access not permitted');
define('ADMIN', true);

define('SYSBASE', str_replace('\\', '/', realpath(dirname(__FILE__).'/../../../').'/'));

require_once(SYSBASE.'common/lib.php');
require_once(SYSBASE.'common/define.php');

if(!isset($_SESSION['user'])){
    header('Location: '.DOCBASE.PMS_ADMIN_FOLDER.'/login.php');
    exit();
}elseif($_SESSION['user']['type'] == 'registered'){
    unset($_SESSION['user']);
    $_SESSION['msg_error'][] = 'Access denied.';
    header('Location: '.DOCBASE.PMS_ADMIN_FOLDER.'/login.php');
    exit();
}

if(!isset($_SESSION['redirect'])) $_SESSION['redirect'] = false;

require_once(SYSBASE.PMS_ADMIN_FOLDER.'/includes/fn_module.php');

if(in_array('no_access', $permissions) || empty($permissions)){
    header('Location: '.DOCBASE.PMS_ADMIN_FOLDER.'/index.php');
    exit();
}

require_once(SYSBASE.PMS_ADMIN_FOLDER.'/includes/fn_actions.php');

if(NB_FILES > 0){
    $upload_allowed = true;
    $msg_notice = '';
    $media_path = SYSBASE.'medias/'.MODULE.'/';
    
    if(is_writable(SYSBASE.'medias/')){
    
        if(!is_dir($media_path)){
            mkdir($media_path, 0777);
            chmod($media_path, 0777);
        }
        if(!is_writable($media_path) && !$_SESSION['redirect'])
            $msg_notice .= str_replace('../', '', $media_path).' '.$pms_texts['NO_WRITING'].'<br>';
        
        if(!is_dir($media_path.'tmp/')){
            mkdir($media_path.'tmp/', 0777);
            chmod($media_path.'tmp/', 0777);
        }
        if(!is_writable($media_path.'tmp/') && !$_SESSION['redirect'])
            $msg_notice .= str_replace('../', '', $media_path).'tmp/ '.$pms_texts['NO_WRITING'].'<br>';
        
        if(RESIZING == 0 || RESIZING == 1){
            if(!is_dir($media_path.'big/')){
                mkdir($media_path.'big/', 0777);
                chmod($media_path.'big/', 0777);
            }
            if(!is_writable($media_path.'big/') && !$_SESSION['redirect'])
                $msg_notice .= str_replace('../', '', $media_path).'big/ '.$pms_texts['NO_WRITING'].'<br>';
        }
        if(RESIZING == 1){
            if(!is_dir($media_path.'medium/')){
                mkdir($media_path.'medium/', 0777);
                chmod($media_path.'medium/', 0777);
            }
            if(!is_writable($media_path.'medium/') && !$_SESSION['redirect'])
                $msg_notice .= str_replace('../', '', $media_path).'medium/ '.$pms_texts['NO_WRITING'].'<br>';
            
            if(!is_dir($media_path.'small/')){
                mkdir($media_path.'small/', 0777);
                chmod($media_path.'small/', 0777);
            }
            if(!is_writable($media_path.'small/') && !$_SESSION['redirect'])
                $msg_notice .= str_replace('../', '', $media_path).'small/ '.$pms_texts['NO_WRITING'].'<br>';
        }
        
        if(!is_dir($media_path.'other/')){
            mkdir($media_path.'other/', 0777);
            chmod($media_path.'other/', 0777);
        }
        if(!is_writable($media_path.'other/') && !$_SESSION['redirect'])
            $msg_notice .= str_replace('../', '', $media_path).'other/ '.$pms_texts['NO_WRITING'].'<br>';
            
    }elseif(!$_SESSION['redirect'])
        $msg_notice .= '/medias/ '.$pms_texts['NO_WRITING'].'<br>';
        
    if($msg_notice != ''){
        $upload_allowed = false;
        $_SESSION['msg_notice'][] = $msg_notice;
    }
}
if(isset($_GET['view'])){
    $view = $_GET['view'];
    if($view == 'list'){
        if(is_file('list.php')) require_once('list.php'); else require_once(SYSBASE.PMS_ADMIN_FOLDER.'/modules/default/list.php');
    }
    if($view == 'form'){
        if(is_file('form.php')) require_once('form.php'); else require_once(SYSBASE.PMS_ADMIN_FOLDER.'/modules/default/form.php');
    }
}else{
    header('Location: '.DOCBASE.'admin');
    exit();
}
