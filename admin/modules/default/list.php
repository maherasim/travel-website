<?php
/**
 * Template of the module listing
 */
debug_backtrace() || die ('Direct access not permitted');
 
// Action to perform
$action = (isset($_GET['action'])) ? htmlentities($_GET['action'], ENT_QUOTES, 'UTF-8') : '';

if($action != '' && defined('PMS_DEMO') && PMS_DEMO == 1){
    $action = '';
    $_SESSION['msg_error'][] = 'This action is disabled in the demo mode';
}

// Item ID
$id = (isset($_GET['id']) && is_numeric($_GET['id'])) ? $_GET['id'] : 0;

// current page
if(isset($_GET['offset']) && is_numeric($_GET['offset'])) $offset = $_GET['offset'];
elseif(isset($_SESSION['offset']) && isset($_SESSION['module_referer']) && $_SESSION['module_referer'] == MODULE) $offset = $_SESSION['offset'];
else $offset = 0;

// Items per page
if(isset($_GET['limit']) && is_numeric($_GET['limit'])){
    $limit = $_GET['limit'];
    $offset = 0;
}
elseif(isset($_SESSION['limit']) && isset($_SESSION['module_referer']) && $_SESSION['module_referer'] == MODULE) $limit = $_SESSION['limit'];
else $limit = 20;

$_SESSION['limit'] = $limit;

$_SESSION['offset'] = $offset;

// Inclusions
require_once(SYSBASE.PMS_ADMIN_FOLDER.'/includes/fn_list.php');
if($pms_db !== false){
    // Initializations
    $cols = getCols();
    $filters = getFilters($pms_db);
    if(is_null($cols)) $cols = array();
    if(is_null($filters)) $filters = array();
    $total = 0;
    $total_page = 0;
    $q_search = '';
    $result_lang = false;
    $total_lang = 1;
    $result = false;
    $referer = DIR.'index.php?view=list';

    // Sort order
    if(isset($_GET['order'])) $order = htmlentities($_GET['order'], ENT_QUOTES, 'UTF-8');
    elseif(isset($_SESSION['order']) && $_SESSION['order'] != '' && isset($_SESSION['module_referer']) && $_SESSION['module_referer'] == MODULE) $order = $_SESSION['order'];
    else $order = getOrder();

    if(isset($_GET['sort'])) $sort = htmlentities($_GET['sort'], ENT_QUOTES, 'UTF-8');
    elseif(isset($_SESSION['sort']) && $_SESSION['sort'] != '' && isset($_SESSION['module_referer']) && $_SESSION['module_referer'] == MODULE) $sort = $_SESSION['sort'];
    else $sort = 'asc';

    $sort = (strtolower(substr($order, -5)) == ' desc') ? 'desc' : 'asc';
    $order = trim(str_ireplace($sort, '', $order));
    
    $sort_class = ($sort == 'asc') ? 'up' : 'down';

    $_SESSION['order'] = $order;
    $_SESSION['sort'] = $sort;

    $rsort = ($sort == 'asc') ? 'desc' : 'asc';

    // Getting languages
    if(MULTILINGUAL){
        $result_lang = $pms_db->query('SELECT id, title FROM pm_lang WHERE id != '.PMS_DEFAULT_LANG.' AND checked = 1');
        if($result_lang !== false)
            $total_lang = $pms_db->last_row_count();
    }
    // Getting filters values
    if(isset($_SESSION['module_referer']) && $_SESSION['module_referer'] !== MODULE){
        unset($_SESSION['filters']);
        unset($_SESSION['q_search']);
    }
    if(isset($_POST['search'])){
        foreach($filters as $filter){
            $fieldName = $filter->getName();
            $value = (isset($_POST[$fieldName])) ? htmlentities($_POST[$fieldName], ENT_QUOTES, 'UTF-8') : '';
            $filter->setValue($value);
        }
        $q_search = htmlentities($_POST['q_search'], ENT_QUOTES, 'UTF-8');
        $_SESSION['filters'] = serialize($filters);
        $_SESSION['q_search'] = $q_search;
        $offset = 0;
        $_SESSION['offset'] = $offset;
    }else{
        if(isset($_SESSION['filters'])) $filters = unserialize($_SESSION['filters']);
        if(isset($_SESSION['q_search'])) $q_search = $_SESSION['q_search'];
    }

    // Getting items in the database
    $condition = '';

    if(MULTILINGUAL) $condition .= ' lang = '.PMS_DEFAULT_LANG;

    foreach($filters as $filter){
        $fieldName = $filter->getName();
        $fieldValue = $filter->getValue();
        if($fieldValue != ''){
            if($condition != '') $condition .= ' AND';
            $condition .= ' '.$fieldName.' = '.$pms_db->quote($fieldValue);
        }
    }
    
    if(!in_array($_SESSION['user']['type'], array('administrator', 'manager', 'editor')) && pms_db_column_exists($pms_db, 'pm_'.MODULE, 'users')){
        if($condition != '') $condition .= ' AND';
        $condition .= ' users REGEXP \'(^|,)'.$_SESSION['user']['id'].'(,|$)\'';
    }
    
    $tmp_order = $order;
    if(!empty($tmp_order)) $tmp_order = '`'.str_replace(' , ', '`, `', $tmp_order).'`';
    $tmp_order .= ' '.$sort;
    $query_search = pms_db_getRequestSelect($pms_db, 'pm_'.MODULE, getSearchFieldsList($cols), $q_search, $condition, $tmp_order);
    $result_total = $pms_db->query($query_search);
    if($result_total !== false)
        $total = $pms_db->last_row_count();
        
    if($limit > 0) $query_search .= ' LIMIT '.$limit.' OFFSET '.$offset;
    $result = $pms_db->query($query_search);
    if($result !== false)
        $total_page = $pms_db->last_row_count();
        
    if(empty($_SESSION['msg_error'])){
        if(in_array('edit', $permissions) || in_array('all', $permissions)){
            
            // Setting main item
            if($action == 'define_main' && $id > 0 && pms_check_token($referer, 'list', 'get'))
                define_main($pms_db, 'pm_'.MODULE, $id, 1);

            if($action == 'remove_main' && $id > 0 && pms_check_token($referer, 'list', 'get'))
                define_main($pms_db, 'pm_'.MODULE, $id, 0);
                
            // Items displayed in homepage
            if($action == 'display_home' && $id > 0 && pms_check_token($referer, 'list', 'get'))
                display_home($pms_db, 'pm_'.MODULE, $id, 1);

            if($action == 'remove_home' && $id > 0 && pms_check_token($referer, 'list', 'get'))
                display_home($pms_db, 'pm_'.MODULE, $id, 0);
                
            if($action == 'display_home_multi' && isset($_POST['multiple_item']) && pms_check_token($referer, 'list', 'get'))
                display_home_multi($pms_db, 'pm_'.MODULE, 1, $_POST['multiple_item']);
                
            if($action == 'remove_home_multi' && isset($_POST['multiple_item']) && pms_check_token($referer, 'list', 'get'))
                display_home_multi($pms_db, 'pm_'.MODULE, 0, $_POST['multiple_item']);
                
            // Item activation/deactivation
            if($action == 'check' && $id > 0 && pms_check_token($referer, 'list', 'get'))
                check($pms_db, 'pm_'.MODULE, $id, 1);

            if($action == 'uncheck' && $id > 0 && pms_check_token($referer, 'list', 'get'))
                check($pms_db, 'pm_'.MODULE, $id, 2);
                
            if($action == 'archive' && $id > 0 && pms_check_token($referer, 'list', 'get'))
                check($pms_db, 'pm_'.MODULE, $id, 3);
                
            if($action == 'check_multi' && isset($_POST['multiple_item']) && pms_check_token($referer, 'list', 'get'))
                check_multi($pms_db, 'pm_'.MODULE, 1, $_POST['multiple_item']);
                
            if($action == 'uncheck_multi' && isset($_POST['multiple_item']) && pms_check_token($referer, 'list', 'get'))
                check_multi($pms_db, 'pm_'.MODULE, 2, $_POST['multiple_item']);
            
            if($action == 'archive_multi' && isset($_POST['multiple_item']) && pms_check_token($referer, 'list', 'get'))
                check_multi($pms_db, 'pm_'.MODULE, 3, $_POST['multiple_item']);
        }
        
        if(in_array('delete', $permissions) || in_array('all', $permissions)){

            // Item deletion
            if($action == 'delete' && $id > 0 && pms_check_token($referer, 'list', 'get'))
                delete_item($pms_db, $id);

            if($action == 'delete_multi' && isset($_POST['multiple_item']) && pms_check_token($referer, 'list', 'get'))
                delete_multi($pms_db, $_POST['multiple_item']);
        }
        
        if(in_array('all', $permissions)){
            
            // Languages completion
            if(MULTILINGUAL && isset($_POST['complete_lang']) && isset($_POST['languages']) && pms_check_token($referer, 'list', 'post')){
                foreach($_POST['languages'] as $id_lang){
                    complete_lang_module($pms_db, 'pm_'.MODULE, $id_lang);
                    if(NB_FILES > 0) complete_lang_module($pms_db, 'pm_'.MODULE.'_file', $id_lang, true);
                }
            }
        }
    }
}

$_SESSION['module_referer'] = MODULE;
$csrf_token = pms_get_token('list'); ?>
<!DOCTYPE html>
<head>
    <?php include(SYSBASE.PMS_ADMIN_FOLDER.'/includes/inc_header_list.php'); ?>
</head>
<body>
    <div id="wrapper">
        <?php include(SYSBASE.PMS_ADMIN_FOLDER.'/includes/inc_top.php'); ?>
        <div id="page-wrapper">
            <form id="form" action="index.php?view=list" method="post" class="ajax-form">
				<div class="page-header">
					<div class="container-fluid">
						<div class="row">
							<div class="col-md-12 clearfix">
								<h1 class="pull-left"><i class="fas fa-fw fa-<?php echo ICON; ?>"></i> <?php echo TITLE_ELEMENT; ?></h1>
								<div class="pull-left text-right">
									&nbsp;&nbsp;
									<?php
									if(in_array('add', $permissions) || in_array('all', $permissions)){ ?>
										<a href="index.php?view=form&id=0" class="btn btn-primary mt15 mb15">
											<i class="fas fa-fw fa-plus-circle"></i> <?php echo $pms_texts['NEW']; ?>
										</a>
										<?php
									}
									if(is_file('custom_nav.php')) include('custom_nav.php'); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="container-fluid">
					<div class="alert-container">
						<div class="alert alert-success alert-dismissable"></div>
						<div class="alert alert-warning alert-dismissable"></div>
						<div class="alert alert-danger alert-dismissable"></div>
					</div>
					<?php
					if($pms_db !== false){
						if(!in_array('no_access', $permissions)){ ?>
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"/>
                            <div class="panel panel-default">
                                <div class="panel-heading form-inline clearfix">
                                    <div class="row">
                                        <div class="col-md-6 text-left">
                                            <div class="form-inline">
                                                <input type="text" name="q_search" value="<?php echo $q_search; ?>" class="form-control input-sm" placeholder="<?php echo $pms_texts['SEARCH']; ?>..."/>
                                                <?php displayFilters($filters); ?>
                                                <button class="btn btn-default btn-sm" type="submit" id="search" name="search"><i class="fas fa-fw fa-search"></i> <?php echo $pms_texts['SEARCH']; ?></button>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="fas fa-fw fa-th-list"></i> <?php echo $pms_texts['DISPLAY']; ?></div>
                                                <select class="select-url form-control input-sm">
                                                    <?php
                                                    echo ($limit != 20) ? '<option value="index.php?view=list&limit=20">20</option>' : '<option selected="selected">20</option>';
                                                    echo ($limit != 50) ? '<option value="index.php?view=list&limit=50">50</option>' : '<option selected="selected">50</option>';
                                                    echo ($limit != 100) ? '<option value="index.php?view=list&limit=100">100</option>' : '<option selected="selected">100</option>'; ?>
                                                </select>
                                            </div>
                                            <?php
                                            if($limit > 0){
                                                $nb_pages = ceil($total/$limit);
                                                if($nb_pages > 1){ ?>
                                                    <div class="input-group">
                                                        <div class="input-group-addon"><?php echo $pms_texts['PAGE']; ?></div>
                                                        <select class="select-url form-control input-sm">
                                                            <?php

                                                            for($i = 1; $i <= $nb_pages; $i++){
                                                                $offset2 = ($i-1)*$limit;
                                                                
                                                                if($offset2 == $offset)
                                                                    echo '<option value="" selected="selected">'.$i.'</option>';
                                                                else
                                                                    echo '<option value="index.php?view=list&offset='.$offset2.'">'.$i.'</option>';
                                                            } ?>
                                                        </select>
                                                    </div>
                                                    <?php
                                                }
                                            } ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped" id="listing_base">
                                            <thead>
                                                <tr class="nodrop nodrag">
                                                    <th width="80">
                                                        <?php
                                                        if(RANKING){ ?>
                                                            <a href="index.php?view=list&order=rank&sort=<?php echo ($order == 'rank') ? $rsort : 'asc'; ?>">
                                                                # <i class="fas fa-fw fa-sort<?php if($order == 'rank') echo '-'.$sort_class; ?>"></i>
                                                            </a>
                                                            <?php
                                                        } ?>
                                                    </th>
                                                    <th width="70">
                                                        <a href="index.php?view=list&order=id&sort=<?php echo ($order == 'id') ? $rsort : 'asc'; ?>">
                                                            ID <i class="fas fa-fw fa-sort<?php if($order == 'id') echo '-'.$sort_class; ?>"></i>
                                                        </a>
                                                    </th>
                                                    <?php
                                                    if(NB_FILES > 0) echo '<th width="160">'.$pms_texts['IMAGE'].'</th>';
                                                    foreach($cols as $col){ ?>
                                                        <th>
                                                            <a href="index.php?view=list&order=<?php echo $col->getName(); ?>&sort=<?php echo ($order == $col->getName()) ? $rsort : 'asc'; ?>">
                                                                <?php echo $col->getLabel(); ?>
                                                                <i class="fas fa-fw fa-sort<?php if($order == $col->getName()) echo '-'.$sort_class; ?>"></i>
                                                            </a>
                                                        </th>
                                                        <?php
                                                    }
                                                    if(count($cols) == 0){
                                                        $type_module = 'file';
                                                        if(NB_FILES > 0){ ?>
                                                            <th><?php echo $pms_texts['FILE']; ?></th>
                                                            <th><?php echo $pms_texts['LABEL']; ?></th>
                                                            <?php
                                                        }
                                                    }
                                                    if(DATES){ ?>
                                                        <th width="160">
                                                            <a href="index.php?view=list&order=add_date&sort=<?php echo ($order == 'add_date') ? $rsort : 'asc'; ?>">
                                                                <?php echo $pms_texts['ADDED_ON']; ?> <i class="fas fa-fw fa-sort<?php if($order == 'add_date') echo '-'.$sort_class; ?>"></i>
                                                            </a>
                                                        </th>
                                                        <th width="160">
                                                            <a href="index.php?view=list&order=edit_date&sort=<?php echo ($order == 'edit_date') ? $rsort : 'asc'; ?>">
                                                                <?php echo $pms_texts['UPDATED_ON']; ?> <i class="fas fa-fw fa-sort<?php if($order == 'edit_date') echo '-'.$sort_class; ?>"></i>
                                                            </a>
                                                        </th>
                                                        <?php
                                                    }
                                                    if(MAIN){ ?>
                                                        <th width="100">
                                                            <a href="index.php?view=list&order=main&sort=<?php echo ($order == 'main') ? $rsort : 'asc'; ?>">
                                                                <?php echo $pms_texts['MAIN']; ?> <i class="fas fa-fw fa-sort<?php if($order == 'main') echo '-'.$sort_class; ?>"></i>
                                                            </a>
                                                        </th>
                                                        <?php
                                                    }
                                                    if(HOME){ ?>
                                                        <th width="100">
                                                            <a href="index.php?view=list&order=home&sort=<?php echo ($order == 'home') ? $rsort : 'asc'; ?>">
                                                                <?php echo $pms_texts['HOME']; ?> <i class="fas fa-fw fa-sort<?php if($order == 'home') echo '-'.$sort_class; ?>"></i>
                                                            </a>
                                                        </th>
                                                        <?php
                                                    }
                                                    if(VALIDATION){ ?>
                                                        <th width="100">
                                                            <a href="index.php?view=list&order=checked&sort=<?php echo ($order == 'checked') ? $rsort : 'asc'; ?>">
                                                                <?php echo $pms_texts['STATUS']; ?> <i class="fas fa-fw fa-sort<?php if($order == 'checked') echo '-'.$sort_class; ?>"></i>
                                                            </a>
                                                        </th>
                                                        <?php
                                                    } ?>
                                                    <th width="140"><?php echo $pms_texts['ACTIONS']; ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if($result !== false){
													if(NB_FILES > 0){
														$query_img = 'SELECT * FROM pm_'.MODULE.'_file WHERE type = \'image\' AND id_item = :id AND file != \'\'';
														if(MULTILINGUAL) $query_img .= ' AND lang = '.PMS_DEFAULT_LANG;
														$query_img .= ' ORDER BY `rank` LIMIT 1';
														$result_img = $pms_db->prepare($query_img);
														$result_img->bindParam(':id', $id);
														
														$query_pdf = 'SELECT * FROM pm_'.MODULE.'_file WHERE type = \'other\' AND id_item = :id AND file LIKE \'%.pdf\'';
														if(MULTILINGUAL) $query_pdf .= ' AND lang = '.PMS_DEFAULT_LANG;
														$query_pdf .= ' ORDER BY `rank` LIMIT 1';
														$result_pdf = $pms_db->prepare($query_pdf);
														$result_pdf->bindParam(':id', $id);
													}   
													
                                                    foreach($result as $i => $row){
                                                        
                                                        $id = $row['id'];
                                                        $cols = getColsValues($pms_db, $row, $i, $cols);
                                                        
                                                        if(isset($preview_path)) unset($preview_path); ?>
                                                        
                                                        <tr id="item_<?php echo $id ?>">
                                                        
                                                            <td class="text-left">
                                                                <input type="checkbox" class="checkitem" name="multiple_item[]" value="<?php echo $id; ?>"/>
                                                                <?php if(RANKING) echo $row['rank']; ?>
                                                            </td>
                                                            
                                                            <td class="text-center"><?php echo $id; ?></td>
                                                            
                                                            <?php
                                                            if(NB_FILES > 0){
                                                                if($result_img->execute() !== false && $pms_db->last_row_count() > 0){
                                                                    $row_img = $result_img->fetch();
                                                                    $filename_img = $row_img['file'];
                                                                    $id_img_file = $row_img['id'];
                                                                    $label = $row_img['label'];
                                                                    
                                                                    $big_path = 'medias/'.MODULE.'/big/'.$id_img_file.'/'.$filename_img;
                                                                    $medium_path = 'medias/'.MODULE.'/medium/'.$id_img_file.'/'.$filename_img;
                                                                    $small_path = 'medias/'.MODULE.'/small/'.$id_img_file.'/'.$filename_img;
                                                                    
                                                                    if(RESIZING == 0 && is_file(SYSBASE.$big_path)) $preview_path = $big_path;
                                                                    elseif(RESIZING == 1 && is_file(SYSBASE.$medium_path)) $preview_path = $medium_path;
                                                                    elseif(is_file(SYSBASE.$small_path)) $preview_path = $small_path;
                                                                    elseif(is_file(SYSBASE.$medium_path)) $preview_path = $medium_path;
                                                                    elseif(is_file(SYSBASE.$big_path)) $preview_path = $big_path;
                                                                    else $preview_path = '';
                                                                    
                                                                    if(is_file(SYSBASE.$big_path)) $zoom_path = $big_path;
                                                                    elseif(is_file(SYSBASE.$medium_path)) $zoom_path = $medium_path;
                                                                    elseif(is_file(SYSBASE.$small_path)) $zoom_path = $small_path;
                                                                    else $zoom_path = '';
                                                                } ?>
                                                            
                                                                <td class="text-center wrap-img">
                                                                    <?php
                                                                    if(isset($preview_path) && is_file(SYSBASE.$preview_path)){
                                                                            
                                                                        $max_w = 160;
                                                                        $max_h = 36;
                                                                        $dim = getimagesize(SYSBASE.$preview_path);
                                                                        $w = $dim[0];
                                                                        $h = $dim[1]; ?>
                                                                        
                                                                        <a href="<?php echo DOCBASE.$zoom_path; ?>" class="image-link" rel="<?php echo DOCBASE.$zoom_path; ?>">
                                                                            <?php
                                                                            if($w < $max_w && $h < $max_h){
                                                                                $new_dim = pms_getNewSize($w, $h, $max_w, $max_h);
                                                                        
                                                                                $new_w = $new_dim[0];
                                                                                $new_h = $new_dim[1];
                                                                                
                                                                                $margin_w = round(($max_w-$new_w)/2);
                                                                                $margin_h = round(($max_h-$new_h)/2);
                                                                                
                                                                                echo '<img src="'.DOCBASE.$preview_path.'" width="'.$new_w.'" height="'.$new_h.'" style="margin:'.$margin_h.'px '.$margin_w.'px;">';
                                                                            
                                                                            }elseif(($w/$max_w) > ($h/$max_h))
                                                                                echo '<img src="'.DOCBASE.$preview_path.'" height="'.$max_h.'" style="margin: 0px -'.ceil(((($w*$max_h)/$h)/2)-($max_w/2)).'px;">';
                                                                            else
                                                                                echo '<img src="'.DOCBASE.$preview_path.'" width="'.$max_w.'" style="margin: -'.ceil(((($h*$max_w)/$w)/2)-($max_h/2)).'px 0px;">'; ?>
                                                                        </a>
                                                                        <?php
                                                                    } ?>
                                                                </td>
                                                                <?php
                                                            }
                                                            if(isset($type_module) && $type_module == 'file'){
                                                            
                                                                $query_file = 'SELECT * FROM pm_'.MODULE.'_file WHERE id_item = '.$id;
                                                                if(MULTILINGUAL) $query_file .= ' AND lang = '.PMS_DEFAULT_LANG;
                                                                $query_file .= ' ORDER BY `rank` LIMIT 1';
                                                                $result_file = $pms_db->query($query_file);
                                                                
                                                                if($result_file !== false && $pms_db->last_row_count() > 0){
                                                                    $row_file = $result_file->fetch();
                                                                    
                                                                    $label = $row_file['label'];
                                                                    $filename = $row_file['file'];
                                                                }else{
                                                                    $label = '';
                                                                    $filename = '';
                                                                }
                                                                echo '<td>'.$filename.'</td>';
                                                                echo '<td>'.$label.'</td>';
                                                            }
                                                            foreach($cols as $col){
                                                                echo '<td';
                                                                $type = $col->getType();
                                                                if($type == 'date' || $type == 'date') echo ' class="text-center"';
                                                                if($type == 'price') echo ' class="text-right"';
                                                                echo '>'.$col->getValue($i).'</td>';
                                                            }
                                                            if(DATES){
                                                                $add_date = (is_null($row['add_date'])) ? '-' : strftime(PMS_DATE_FORMAT.' '.PMS_TIME_FORMAT, $row['add_date']);
                                                                $edit_date = (is_null($row['edit_date'])) ? '-' : strftime(PMS_DATE_FORMAT.' '.PMS_TIME_FORMAT, $row['edit_date']); ?>
                                                                <td class="text-center">
                                                                    <?php echo $add_date; ?>
                                                                </td>
                                                                <td class="text-center">
                                                                    <?php echo $edit_date; ?>
                                                                </td>
                                                                <?php
                                                            }
                                                            if(MAIN){
                                                                $main = $row['main']; ?>
                                                                <td class="text-center">
                                                                    <?php
                                                                    if($main == 0){
                                                                        if((in_array('publish', $permissions) || in_array('all', $permissions))){ ?>
                                                                            <a class="tips" href="index.php?view=list&id=<?php echo $id; ?>&csrf_token=<?php echo $csrf_token; ?>&action=define_main" title="<?php echo $pms_texts['DEFINE_MAIN']; ?>"><i class="fas fa-fw fa-star text-muted"></i></a>
                                                                            <?php
                                                                        }else{ ?>
                                                                            <i class="fas fa-fw fa-star text-muted"></i>
                                                                            <?php
                                                                        }
                                                                    }elseif($main == 1){ ?>
                                                                        <i class="fas fa-fw fa-star text-primary"></i>
                                                                        <?php
                                                                    } ?>
                                                                </td>
                                                                <?php
                                                            }
                                                            if(HOME){
                                                                $home = $row['home']; ?>
                                                                <td class="text-center">
                                                                    <?php
                                                                    if($home == 0){
                                                                        if((in_array('publish', $permissions) || in_array('all', $permissions))){ ?>
                                                                            <a class="tips" href="index.php?view=list&id=<?php echo $id; ?>&csrf_token=<?php echo $csrf_token; ?>&action=display_home" title="<?php echo $pms_texts['SHOW_HOMEPAGE']; ?>"><i class="fas fa-fw fa-home text-danger"></i></a>
                                                                            <?php
                                                                        }else{ ?>
                                                                            <i class="fas fa-fw fa-home text-danger"></i>
                                                                            <?php
                                                                        }
                                                                    }elseif($home == 1){
                                                                        if((in_array('publish', $permissions) || in_array('all', $permissions))){ ?>
                                                                            <a class="tips" href="index.php?view=list&id=<?php echo $id; ?>&csrf_token=<?php echo $csrf_token; ?>&action=remove_home" title="<?php echo $pms_texts['REMOVE_HOMEPAGE']; ?>"><i class="fas fa-fw fa-home text-success"></i></a>
                                                                            <?php
                                                                        }else{ ?>
                                                                            <i class="fas fa-fw fa-home text-success"></i>
                                                                            <?php
                                                                        }
                                                                    } ?>
                                                                </td>
                                                                <?php
                                                            }
                                                            if(VALIDATION){
                                                                $checked = $row['checked']; ?>
                                                                <td class="text-center">
                                                                    <?php
                                                                    if($checked == 0) echo '<span class="label label-warning">'.$pms_texts['AWAITING'].'</span>';
                                                                    elseif($checked == 1) echo '<span class="label label-success">'.$pms_texts['PUBLISHED'].'</span>';
                                                                    elseif($checked == 2) echo '<span class="label label-danger">'.$pms_texts['NOT_PUBLISHED'].'</span>';
                                                                    elseif($checked == 3) echo '<span class="label label-default">'.$pms_texts['ARCHIVED'].'</span>'; ?>
                                                                </td>
                                                                <?php
                                                            } ?>
                                                            <td class="text-center">
                                                                <?php
																if(NB_FILES > 0){
																	if($result_pdf->execute() !== false && $pms_db->last_row_count() > 0){
																		$row_file = $result_pdf->fetch();
																	
																		$filename = $row_file['file'];
																		$id_file = $row_file['id'];
																		$label = $row_file['label'];
																		
																		$file_path = DOCBASE.'medias/'.MODULE.'/other/'.$id_file.'/'.$filename; ?>
																		
																		<a class="tips" href="<?php echo $file_path; ?>" title="<?php echo $filename; ?>" target="_blank"><i class="far fa-fw fa-file-pdf text-danger"></i></a> 
																		<?php
																	}
																}
                                                                if(VALIDATION && (in_array('publish', $permissions) || in_array('all', $permissions))){
                                                                    if($checked == 0){ ?>
                                                                        <a class="tips" href="index.php?view=list&id=<?php echo $id; ?>&csrf_token=<?php echo $csrf_token; ?>&action=check" title="<?php echo $pms_texts['PUBLISH']; ?>"><i class="fas fa-fw fa-check text-success"></i></a>
                                                                        <a class="tips" href="index.php?view=list&id=<?php echo $id; ?>&csrf_token=<?php echo $csrf_token; ?>&action=uncheck" title="<?php echo $pms_texts['UNPUBLISH']; ?>"><i class="fas fa-fw fa-ban text-danger"></i></a>
                                                                        <a class="tips" href="index.php?view=list&id=<?php echo $id; ?>&csrf_token=<?php echo $csrf_token; ?>&action=archive" title="<?php echo $pms_texts['ARCHIVE']; ?>"><i class="fas fa-fw fa-archive text-warning"></i></a>
                                                                        <?php
                                                                    }elseif($checked == 1){ ?>
                                                                        <i class="fas fa-fw fa-check text-muted"></i>
                                                                        <a class="tips" href="index.php?view=list&id=<?php echo $id; ?>&csrf_token=<?php echo $csrf_token; ?>&action=uncheck" title="<?php echo $pms_texts['UNPUBLISH']; ?>"><i class="fas fa-fw fa-ban text-danger"></i></a>
                                                                        <a class="tips" href="index.php?view=list&id=<?php echo $id; ?>&csrf_token=<?php echo $csrf_token; ?>&action=archive" title="<?php echo $pms_texts['ARCHIVE']; ?>"><i class="fas fa-fw fa-archive text-warning"></i></a>
                                                                        <?php
                                                                    }elseif($checked == 2){ ?>
                                                                        <a class="tips" href="index.php?view=list&id=<?php echo $id; ?>&csrf_token=<?php echo $csrf_token; ?>&action=check" title="<?php echo $pms_texts['PUBLISH']; ?>"><i class="fas fa-fw fa-check text-success"></i></a>
                                                                        <i class="fas fa-fw fa-ban text-muted"></i>
                                                                        <a class="tips" href="index.php?view=list&id=<?php echo $id; ?>&csrf_token=<?php echo $csrf_token; ?>&action=archive" title="<?php echo $pms_texts['ARCHIVE']; ?>"><i class="fas fa-fw fa-archive text-warning"></i></a>
                                                                        <?php
                                                                    }elseif($checked == 3){ ?>
                                                                        <a class="tips" href="index.php?view=list&id=<?php echo $id; ?>&csrf_token=<?php echo $csrf_token; ?>&action=check" title="<?php echo $pms_texts['PUBLISH']; ?>"><i class="fas fa-fw fa-check text-success"></i></a>
                                                                        <a class="tips" href="index.php?view=list&id=<?php echo $id; ?>&csrf_token=<?php echo $csrf_token; ?>&action=uncheck" title="<?php echo $pms_texts['UNPUBLISH']; ?>"><i class="fas fa-fw fa-ban text-danger"></i></a>
                                                                        <i class="fas fa-fw fa-archive text-muted"></i>
                                                                        <?php
                                                                    }
                                                                }
                                                                if(in_array('edit', $permissions) || in_array('all', $permissions) || in_array('view', $permissions)){ ?>
                                                                    <a class="tips" href="index.php?view=form&id=<?php echo $id; ?>" title="<?php echo $pms_texts['EDIT']; ?>"><i class="fas fa-fw fa-edit"></i></a>
                                                                    <?php
                                                                }
                                                                if(in_array('delete', $permissions) || in_array('all', $permissions)){ ?>
                                                                    <a class="tips" href="javascript:if(confirm('<?php echo $pms_texts['DELETE_CONFIRM2']; ?>')) window.location = 'index.php?view=list&id=<?php echo $id; ?>&csrf_token=<?php echo $csrf_token; ?>&action=delete';" title="<?php echo $pms_texts['DELETE']; ?>"><i class="fas fa-fw fa-trash-alt text-danger"></i></a>
                                                                    <?php
                                                                } ?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    }
                                                } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php
                                    if($total == 0){ ?>
                                        <div class="text-center mt20 mb20">- <?php echo $pms_texts['NO_ELEMENT']; ?> -</div>
                                        <?php
                                    } ?>
                                </div>
                                <div class="panel-footer form-inline clearfix">
                                    <div class="row">
                                        <div class="col-md-6 text-left">
                                            <?php
                                            if($total > 0){ ?>
                                                &nbsp;<input type="checkbox" class="selectall"/>
                                                <?php echo $pms_texts['SELECT_ALL']; ?>&nbsp;
                                                <select name="multiple_actions" class="form-control input-sm">
                                                    <option value="">- <?php echo $pms_texts['ACTIONS']; ?> -</option>
                                                    <?php
                                                    if(in_array('publish', $permissions) || in_array('all', $permissions)){
                                                        if(VALIDATION){ ?>
                                                            <option value="check_multi"><?php echo $pms_texts['PUBLISH']; ?></option>
                                                            <option value="uncheck_multi"><?php echo $pms_texts['UNPUBLISH']; ?></option>
                                                            <?php
                                                        }
                                                        if(HOME){ ?>
                                                            <option value="display_home_multi"><?php echo $pms_texts['SHOW_HOMEPAGE']; ?></option>
                                                            <option value="remove_home_multi"><?php echo $pms_texts['REMOVE_HOMEPAGE']; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    if(in_array('delete', $permissions) || in_array('all', $permissions)){ ?>
                                                        <option value="delete_multi"><?php echo $pms_texts['DELETE']; ?></option>
                                                        <?php
                                                    } ?>
                                                </select>
                                                <?php
                                            } ?>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="fas fa-fw fa-th-list"></i> <?php echo $pms_texts['DISPLAY']; ?></div>
                                                <select class="select-url form-control input-sm">
                                                    <?php
                                                    echo ($limit != 20) ? '<option value="index.php?view=list&limit=20">20</option>' : '<option selected="selected">20</option>';
                                                    echo ($limit != 50) ? '<option value="index.php?view=list&limit=50">50</option>' : '<option selected="selected">50</option>';
                                                    echo ($limit != 100) ? '<option value="index.php?view=list&limit=100">100</option>' : '<option selected="selected">100</option>'; ?>
                                                </select>
                                            </div>
                                            
                                            <?php
                                            if($limit > 0){
                                                $nb_pages = ceil($total/$limit);
                                                if($nb_pages > 1){ ?>
                                                    <div class="input-group">
                                                        <div class="input-group-addon"><?php echo $pms_texts['PAGE']; ?></div>
                                                        <select class="select-url form-control input-sm">
                                                            <?php

                                                            for($i = 1; $i <= $nb_pages; $i++){
                                                                $offset2 = ($i-1)*$limit;
                                                                
                                                                if($offset2 == $offset)
                                                                    echo '<option value="" selected="selected">'.$i.'</option>';
                                                                else
                                                                    echo '<option value="index.php?view=list&offset='.$offset2.'">'.$i.'</option>';
                                                            } ?>
                                                        </select>
                                                    </div>
                                                    <?php
                                                }
                                            } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            if(in_array('all', $permissions)){
                                if($pms_db != false && MULTILINGUAL && $total_lang > 0){ ?>
                                    <div class="well">
                                        <div id="translation">
                                            <p><?php echo $pms_texts['COMPLETE_LANGUAGE']; ?></p>
                                            <?php
                                            foreach($result_lang as $row_lang){
                                                $id_lang = $row_lang['id'];
                                                $title_lang = $row_lang['title']; ?>
                                                
                                                <input type="checkbox" name="languages[]" value="<?php echo $id_lang; ?>">
                                                <?php
                                                $result_img_lang = $pms_db->query('SELECT * FROM pm_lang_file WHERE id_item = '.$id_lang.' AND type = \'image\' AND file != \'\' ORDER BY `rank` LIMIT 1');
                                                if($result_img_lang !== false && $pms_db->last_row_count() > 0){
                                                    $row_img_lang = $result_img_lang->fetch();
                                                    
                                                    $id_img_lang = $row_img_lang['id'];
                                                    $file_img_lang = $row_img_lang['file'];
                                                
                                                    if(is_file(SYSBASE.'medias/lang/big/'.$id_img_lang.'/'.$file_img_lang))
                                                        echo '<img src="'.DOCBASE.'medias/lang/big/'.$id_img_lang.'/'.$file_img_lang.'" alt="" border="0" class="flag"> ';
                                                }
                                                echo $title_lang.'<br>';
                                            } ?>
                                            <button type="submit" name="complete_lang" class="btn btn-default mt10" data-toggle="tooltip" data-placement="right" title="<?php echo $pms_texts['COMPLETE_LANG_NOTICE']; ?>"><i class="fas fa-fw fa-magic"></i> <?php echo $pms_texts['APPLY_LANGUAGE']; ?></button>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            if(is_file('custom_list.php')) include('custom_list.php'); ?>
							<?php
						}else echo '<p>'.$pms_texts['ACCESS_DENIED'].'</p>';
					} ?>
				</div>
			</form>
        </div>
    </div>
</body>
</html>
<?php
$_SESSION['redirect'] = false;
$_SESSION['msg_error'] = array();
$_SESSION['msg_success'] = array();
$_SESSION['msg_notice'] = array(); ?>
