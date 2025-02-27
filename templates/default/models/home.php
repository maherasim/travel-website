<?php
/* ==============================================
 * CSS AND JAVASCRIPT USED IN THIS MODEL
 * ==============================================
 */
// $pms_stylesheets[] = array('file' => 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400;500;700&display=swap', 'media' => 'all');
$pms_stylesheets[] = array('file' => DOCBASE.'js/plugins/royalslider/royalslider.css', 'media' => 'all');
$pms_stylesheets[] = array('file' => DOCBASE.'js/plugins/royalslider/skins/minimal-white/rs-minimal-white.css', 'media' => 'all');
$pms_stylesheets[] = array('file' => DOCBASE.'css/custom.css', 'media' => 'all'); // New custom styles
$pms_javascripts[] = DOCBASE.'js/plugins/royalslider/jquery.royalslider.min.js';
$pms_javascripts[] = DOCBASE.'js/plugins/live-search/jquery.liveSearch.js';
$pms_javascripts[] = DOCBASE.'js/home.js';

require(pms_getFromTemplate('common/header.php', false));

$slide_id = 0;
$result_slide_file = $pms_db->prepare('SELECT * FROM pm_slide_file WHERE id_item = :slide_id AND checked = 1 AND lang = '.PMS_DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY `rank` LIMIT 1');
$result_slide_file->bindParam('slide_id', $slide_id);

$result_slide = $pms_db->query('SELECT * FROM pm_slide WHERE id_page = '.$pms_page_id.' AND checked = 1 AND lang = '.PMS_LANG_ID.' ORDER BY `rank`', PDO::FETCH_ASSOC);
if($result_slide !== false){
	$nb_slides = $pms_db->last_row_count();
	if($nb_slides > 0){ ?>
        
        <div id="search-home-wrapper" class="animated slideInDown">
            <div id="search-home" class="container search-floating">
                <div class="search-card shadow-lg">
                    <?php include(pms_getFromTemplate('common/search.php', false)); ?>
                </div>
            </div>
        </div>
	
		<section id="sliderContainer" class="hero-slider">
            <div id="mainSlider" class="royalSlider rsModernW sliderContainer fullWidth clearfix fullSized">
            <?php
                foreach($result_slide as $i => $row){
                    $slide_id = $row['id'];
                    $slide_legend = $row['legend'];
                    $url_video = $row['url'];
                    $id_page = $row['id_page'];
                    
                    $result_slide_file->execute();
                    
                    if($result_slide_file !== false && $pms_db->last_row_count() == 1){
                        $row = $result_slide_file->fetch();
                        
                        $file_id = $row['id'];
                        $filename = $row['file'];
                        $label = $row['label'];
                        
                        $realpath = SYSBASE.'medias/slide/big/'.$file_id.'/'.$filename;
                        $thumbpath = DOCBASE.'medias/slide/small/'.$file_id.'/'.$filename;
                        $zoompath = DOCBASE.'medias/slide/big/'.$file_id.'/'.$filename;
                            
                        if(is_file($realpath)){ ?>
                        
                            <div class="rsContent">
                                <img class="rsImg" src="<?php echo $zoompath; ?>" alt=""<?php if($url_video != '') echo ' data-rsVideo="'.$url_video.'"'; ?>>
                                <?php
                                if($slide_legend != ''){ ?>
                                    <div class="infoBlock" data-fade-effect="" data-move-offset="10" data-move-effect="bottom" data-speed="200">
                                        <?php echo $slide_legend; ?>
                                    </div>
                                    <?php
                                } ?>
                            </div>
                            <?php
                        }
                    }
                } ?>
            </div>
            <div class="slider-progress"></div>
		</section>
	<?php }
} ?>

<section id="content" class="pt20 pb30">
    <div class="container">
        
        <?php pms_displayWidgets('before_content', $pms_page_id); ?>
        
        <div class="row">
            <div class="col-md-12 text-center mb-5">
                <h1 class="display-4 mb-3" itemprop="name">
                    <?php echo $page['title']; ?>
                    <?php if($page['subtitle'] != ''){ ?>
                        <div class="subtitle mt-2"><?php echo $page['subtitle']; ?></div>
                    <?php } ?>
                </h1>
                <div class="lead text-muted max-w-800 mx-auto">
                    <?php echo $page['text']; ?>
                </div>
            </div>
        </div>

        <?php pms_displayWidgets('after_content', $pms_page_id); ?>
        <?php
            $result_room = $pms_db->query('SELECT * FROM pm_room WHERE lang = '.PMS_LANG_ID.' AND checked = 1 AND home = 1 ORDER BY `rank`');
            if($result_room !== false){
                $nb_rooms = $pms_db->last_row_count();
                
                $room_id = 0;
                
                $result_room_file = $pms_db->prepare('SELECT * FROM pm_room_file WHERE id_item = :room_id AND checked = 1 AND lang = '.PMS_DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY `rank` LIMIT 1');
                $result_room_file->bindParam(':room_id',$room_id);
                
                $result_rate = $pms_db->prepare('SELECT MIN(price) as min_price FROM pm_rate WHERE id_room = :room_id');
                $result_rate->bindParam(':room_id', $room_id);
                
                foreach($result_room as $i => $row){
                    $room_id = $row['id'];
                    $room_title = $row['title'];
                    $room_subtitle = $row['subtitle'];
                    
                    $room_alias = DOCBASE.$pms_pages[9]['alias'].'/'.pms_text_format($row['alias']);
                    
                    $min_price = 0;
                    if($result_rate->execute() !== false && $pms_db->last_row_count() > 0){
                        $row = $result_rate->fetch();
                        $price = $row['min_price'];
                        if($price > 0) $min_price = $price;
                    } ?>
                    
                    <article class="col-sm-4 mb20" itemscope itemtype="http://schema.org/LodgingBusiness">
                        <a itemprop="url" href="<?php echo $room_alias; ?>" class="moreLink">
                            <?php
                            if($result_room_file->execute() !== false && $pms_db->last_row_count() == 1){
                                $row = $result_room_file->fetch(PDO::FETCH_ASSOC);
                                
                                $file_id = $row['id'];
                                $filename = $row['file'];
                                $label = $row['label'];
                                
                                $realpath = SYSBASE.'medias/room/small/'.$file_id.'/'.$filename;
                                $thumbpath = DOCBASE.'medias/room/small/'.$file_id.'/'.$filename;
                                $zoompath = DOCBASE.'medias/room/big/'.$file_id.'/'.$filename;
                                
                                if(is_file($realpath)){
                                    $s = getimagesize($realpath); ?>
                                    <figure class="more-link">
                                        <div class="img-container lazyload md">
                                            <img alt="<?php echo $label; ?>" data-src="<?php echo $thumbpath; ?>" itemprop="photo" width="<?php echo $s[0]; ?>" height="<?php echo $s[1]; ?>">
                                        </div>
                                        <div class="more-content">
                                            <h3 style="border-radius: 10px;" itemprop="name"><?php echo $room_title; ?></h3>
                                            <?php
                                            if($min_price > 0){ ?>
                                                <div class="more-descr">
                                                    <div class="price">
                                                        <?php echo $pms_texts['FROM_PRICE']; ?>
                                                        <span itemprop="priceRange">
                                                            <?php echo pms_formatPrice($min_price*PMS_CURRENCY_RATE); ?>
                                                        </span>
                                                    </div>
                                                    <small><?php echo $pms_texts['PRICE'].' / '.$pms_texts['NIGHT']; ?></small>
                                                </div>
                                                <?php
                                            } ?>
                                        </div>
                                        <div class="more-action">
                                            <div class="more-icon">
                                                <i class="fa fa-link"></i>
                                            </div>
                                        </div>
                                    </figure>
                                    <?php
                                }
                            } ?>
                        </a> 
                    </article>
                    <?php
                }
            } ?>
        </div>
    </div>
    <?php
    $activity_id = 0;
    $result_activity = $pms_db->query('SELECT * FROM pm_activity WHERE lang = '.PMS_LANG_ID.' AND checked = 1 AND home = 1 ORDER BY `rank`');
    if($result_activity !== false){
        $nb_activities = $pms_db->last_row_count();
        if($nb_activities > 0){ ?>
            <div class="hotBox mb30 mt5">
                <div class="container-fluid">
                    <div class="row">
                        <h2 class="text-center mt10 mb15"><?php echo $pms_texts['FIND_ACTIVITIES_AND_TOURS']; ?></h2>
                        <?php
                        $activity_id = 0;
                        $result_activity_file = $pms_db->prepare('SELECT * FROM pm_activity_file WHERE id_item = :activity_id AND checked = 1 AND lang = '.PMS_DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY `rank` LIMIT 1');
                        $result_activity_file->bindParam(':activity_id',$activity_id);
                        foreach($result_activity as $i => $row){
                            $activity_id = $row['id'];
                            $activity_title = $row['title'];
                            $activity_alias = $row['title'];
                            $activity_subtitle = $row['subtitle'];
                            $min_price = $row['price'];
                            
                            $activity_alias = DOCBASE.$pms_sys_pages['activities']['alias'].'/'.pms_text_format($row['alias']); ?>
                            
                            <article class="col-sm-3 mb20" itemscope itemtype="http://schema.org/LodgingBusiness">
                                <a itemprop="url" href="<?php echo $activity_alias; ?>" class="moreLink">
                                    <?php
                                    if($result_activity_file->execute() !== false && $pms_db->last_row_count() > 0){
                                        $row = $result_activity_file->fetch(PDO::FETCH_ASSOC);
                                        
                                        $file_id = $row['id'];
                                        $filename = $row['file'];
                                        $label = $row['label'];
                                        
                                        $realpath = SYSBASE.'medias/activity/small/'.$file_id.'/'.$filename;
                                        $thumbpath = DOCBASE.'medias/activity/small/'.$file_id.'/'.$filename;
                                        $zoompath = DOCBASE.'medias/activity/big/'.$file_id.'/'.$filename;
                                        
                                        if(is_file($realpath)){
                                            $s = getimagesize($realpath); ?>
                                            <figure class="more-link">
                                                <div class="img-container lazyload md">
                                                    <img alt="<?php echo $label; ?>" data-src="<?php echo $thumbpath; ?>" itemprop="photo" width="<?php echo $s[0]; ?>" height="<?php echo $s[1]; ?>">
                                                </div>
                                                <div class="more-content">
                                                    <h3 itemprop="name"><?php echo $activity_title; ?></h3>
                                                </div>
                                                <div class="more-action">
                                                    <div class="more-icon">
                                                        <i class="fa fa-link"></i>
                                                    </div>
                                                </div>
                                            </figure>
                                            <?php
                                        }
                                    } ?>
                                </a> 
                            </article>
                            <?php
                        } ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    $result_article = $pms_db->query('SELECT *
                                FROM pm_article
                                WHERE (id_page = '.$pms_page_id.' OR home = 1)
                                    AND checked = 1
                                    AND (publish_date IS NULL || publish_date <= '.time().')
                                    AND (unpublish_date IS NULL || unpublish_date > '.time().')
                                    AND lang = '.PMS_LANG_ID.'
                                    AND (show_langs IS NULL || show_langs = \'\' || show_langs REGEXP \'(^|,)'.PMS_LANG_ID.'(,|$)\')
                                    AND (hide_langs IS NULL || hide_langs = \'\' || hide_langs NOT REGEXP \'(^|,)'.PMS_LANG_ID.'(,|$)\')
                                ORDER BY `rank`');
    if($result_article !== false){
        $nb_articles = $pms_db->last_row_count();
        
        if($nb_articles > 0){ ?>
            <div class="container mt10">
                <div class="row">
                    <div class="clearfix">
                        <?php
                        $pms_article_id = 0;
                        $result_article_file = $pms_db->prepare('SELECT * FROM pm_article_file WHERE id_item = :article_id AND checked = 1 AND lang = '.PMS_DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY `rank` LIMIT 1');
                        $result_article_file->bindParam(':article_id', $pms_article_id);
                        foreach($result_article as $i => $row){
                            $pms_article_id = $row['id'];
                            $article_title = $row['title'];
                            $article_alias = $row['alias'];
                            $char_limit = ($i == 0) ? 1200 : 500;
                            $article_text = pms_strtrunc(pms_rip_tags($row['text'], '<p><br>'), $char_limit, true, '');
                            $article_page = $row['id_page'];
                            
                            if(isset($pms_pages[$article_page])){
                            
                                $article_alias = (empty($article_url)) ? DOCBASE.$pms_pages[$article_page]['alias'].'/'.pms_text_format($article_alias) : $article_url;
                                $target = (strpos($article_alias, 'http') !== false) ? '_blank' : '_self';
                                if(strpos($article_alias, pms_getUrl(true)) !== false) $target = '_self'; ?>
                                                                
                                <article id="article-<?php echo $pms_article_id; ?>" class="mb20 col-sm-<?php echo ($i == 0) ? 12 : 4; ?>" itemscope itemtype="http://schema.org/Article">
                                    <div class="row">
                                        <a itemprop="url" href="<?php echo $article_alias; ?>" target="<?php echo $target; ?>" class="moreLink">
                                            <div class="col-sm-<?php echo ($i == 0) ? 8 : 12; ?> mb20">
                                                <?php
                                                if($result_article_file->execute() !== false && $pms_db->last_row_count() == 1){
                                                    $row = $result_article_file->fetch(PDO::FETCH_ASSOC);
                                                    
                                                    $file_id = $row['id'];
                                                    $filename = $row['file'];
                                                    $label = $row['label'];
                                                    
                                                    $realpath = SYSBASE.'medias/article/big/'.$file_id.'/'.$filename;
                                                    $thumbpath = DOCBASE.'medias/article/big/'.$file_id.'/'.$filename;
                                                    $zoompath = DOCBASE.'medias/article/big/'.$file_id.'/'.$filename;
                                                    
                                                    if(is_file($realpath)){
                                                        $s = getimagesize($realpath); ?>
                                                        <figure class="more-link">
                                                            <div class="img-container lazyload xl">
                                                                <img alt="<?php echo $label; ?>" data-src="<?php echo $thumbpath; ?>" itemprop="photo" width="<?php echo $s[0]; ?>" height="<?php echo $s[1]; ?>">
                                                            </div>
                                                            <div class="more-action">
                                                                <div class="more-icon">
                                                                    <i class="fa fa-link"></i>
                                                                </div>
                                                            </div>
                                                        </figure>
                                                        <?php
                                                    }
                                                } ?>
                                            </div>
                                            <div class="col-sm-<?php echo ($i == 0) ? 4 : 12; ?>">
                                                <div class="text-overflow">
                                                    <h3 itemprop="name"><?php echo $article_title; ?></h3>
                                                    <?php echo $article_text; ?>
                                                    <div class="more-btn">
                                                        <span class="btn btn-primary"><?php echo $pms_texts['READMORE']; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </article>
                                <?php
                            }
                        } ?>
                    </div>
                </div>
            </div>
            <?php
        }
    } ?>
    <?php pms_displayWidgets('full_after_content', $pms_page_id); ?>

        <!-- Rooms Section Enhanced -->
        <div class="row mb-5 room-grid">
            <?php while($row = $result_room->fetch()){ ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <article class="card room-card shadow-sm hover-effect">
                        <a href="<?php echo $room_alias; ?>" class="card-image">
                            <div class="image-wrapper">
                                <img src="<?php echo $thumbpath; ?>" alt="<?php echo $label; ?>" class="card-img-top">
                                <div class="price-badge">
                                    <?php if($min_price > 0){ ?>
                                        <span class="badge">
                                            <?php echo $pms_texts['FROM_PRICE']; ?>
                                            <strong><?php echo pms_formatPrice($min_price*PMS_CURRENCY_RATE); ?></strong>
                                        </span>
                                    <?php } ?>
                                </div>
                            </div>
                        </a>
                        <div class="card-body">
                            <h3 class="card-title"><?php echo $room_title; ?></h3>
                            <?php if($room_subtitle){ ?>
                                <p class="card-text text-muted"><?php echo $room_subtitle; ?></p>
                            <?php } ?>
                            <a href="<?php echo $room_alias; ?>" class="btn btn-primary">
                                <?php echo $pms_texts['VIEW_DETAILS']; ?>
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </article>
                </div>
            <?php } ?>
        </div>

        <!-- Activities Section -->
        <?php if($nb_activities > 0){ ?>
        <div class="activities-section py-5 bg-light">
            <div class="container">
                <h2 class="section-title text-center mb-5"><?php echo $pms_texts['FIND_ACTIVITIES_AND_TOURS']; ?></h2>
                <div class="activity-carousel owl-carousel">
                    <?php foreach($result_activity as $activity){ ?>
                    <div class="activity-item">
                        <div class="card shadow-sm">
                            <img src="<?php echo $thumbpath; ?>" class="card-img-top" alt="<?php echo $activity_title; ?>">
                            <div class="card-body">
                                <h4 class="card-title"><?php echo $activity_title; ?></h4>
                                <a href="<?php echo $activity_alias; ?>" class="btn btn-outline-primary">
                                    <?php echo $pms_texts['LEARN_MORE']; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php } ?>

        <!-- Articles Section -->
        <?php if($nb_articles > 0){ ?>
        <div class="articles-section py-5">
            <div class="container">
                <div class="row">
                    <?php foreach($result_article as $i => $article){ ?>
                    <div class="col-lg-<?php echo ($i == 0) ? '12' : '4'; ?> mb-4">
                        <div class="card article-card <?php echo ($i == 0) ? 'featured-article' : ''; ?>">
                            <div class="row no-gutters">
                                <?php if($i == 0){ ?>
                                <div class="col-md-8">
                                    <img src="<?php echo $thumbpath; ?>" class="card-img" alt="<?php echo $article_title; ?>">
                                </div>
                                <?php } ?>
                                <div class="<?php echo ($i == 0) ? 'col-md-4' : 'col-12'; ?>">
                                    <div class="card-body">
                                        <h3 class="card-title"><?php echo $article_title; ?></h3>
                                        <div class="card-text"><?php echo $article_text; ?></div>
                                        <a href="<?php echo $article_alias; ?>" class="btn btn-link read-more">
                                            <?php echo $pms_texts['READMORE']; ?>
                                            <i class="fas fa-long-arrow-alt-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php } ?>

    </div>
</section>

<style>
/* Modern CSS Enhancements */
:root {
    --primary-color: #2a4365;
    --secondary-color: #c53030;
    --accent-color: #f6ad55;
    --text-dark: #2d3748;
    --text-light: #718096;
}


.hero-slider {
    position: relative;
    /* height: 80vh; */
    overflow: hidden;
}

.search-floating {
    position: absolute;
    top: 60%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 100;
    width: 100%;
    max-width: 999px;
}

.room-card {
    transition: transform 0.3s ease;
    border-radius: 15px;
    overflow: hidden;
}

.room-card:hover {
    transform: translateY(-10px);
}

.price-badge .badge {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    font-size: 1.1rem;
    padding: 0.75rem 1.5rem;
}

.featured-article {
    border-left: 5px solid var(--primary-color);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.read-more {
    color: var(--primary-color);
    position: relative;
    padding-right: 25px;
}

.read-more i {
    transition: transform 0.3s ease;
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
}

.read-more:hover i {
    transform: translateY(-50%) translateX(5px);
}

.section-title {
    position: relative;
    padding-bottom: 1rem;
}

.section-title:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 3px;
    background: var(--primary-color);
}
</style>
