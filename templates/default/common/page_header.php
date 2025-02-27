<?php debug_backtrace() || die ("Direct access not permitted"); ?>
<header class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <?php
                $page_name = $pms_article_id == 0 ? $page['name'] : $page_title;
                $page_title_display = $pms_article_id == 0 ? $page['title'] : $page_title;
                $page_subtitle_display = !empty($page_subtitle) ? $page_subtitle : $page['title'];
                ?>
                
                <h1 class="page-title" itemprop="name"><?php echo $page_title_display; ?></h1>
                <p class="lead page-subtitle"><?php echo $page_subtitle_display; ?></p>
            </div>
            <div class="col-md-<?php echo (PMS_RTL_DIR) ? 12 : 4; ?> d-none d-md-block">
                <nav class="breadcrumb-container" itemprop="breadcrumb">
                    <a href="<?php echo DOCBASE.trim(PMS_LANG_ALIAS, "/"); ?>" title="<?php echo $pms_homepage['title']; ?>">
                        <i class="fas fa-home"></i> <?php echo $pms_homepage['name']; ?>
                    </a>
                    <?php foreach($breadcrumbs as $id_parent): 
                        if(isset($pms_pages[$id_parent])): ?>
                            <a href="<?php echo DOCBASE.$pms_pages[$id_parent]['alias']; ?>" title="<?php echo $pms_pages[$id_parent]['title']; ?>">
                                <i class="fas fa-angle-right"></i> <?php echo $pms_pages[$id_parent]['name']; ?>
                            </a>
                    <?php endif; endforeach; ?>
                    <?php if($pms_article_id > 0): ?>
                        <a href="<?php echo DOCBASE.$page['alias']; ?>" title="<?php echo $page['title']; ?>">
                            <i class="fas fa-file-alt"></i> <?php echo $page['name']; ?>
                        </a>
                    <?php endif; ?>
                    <span class="current-page"><?php echo $page_name; ?></span>
                </nav>
            </div>
        </div>
    </div>
</header>

<style>
.page-header {
    /* background: linear-gradient(135deg, rgba(0, 0, 0, 0.7), rgba(45, 45, 45, 0.9)), url('header-bg.jpg'); */
    /* background-size: cover; */
    /* background-position: center; */
    /* color: white; */
    /* padding: 60px 0; */
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
    /* border-radius: 10px; */
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px);
}
.page-title {
    font-size: 2.5rem;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    animation: fadeInDown 0.8s ease-out;
}
.page-subtitle {
    font-size: 1.2rem;
    opacity: 0.85;
    font-weight: 300;
}
.breadcrumb-container {
    display: flex;
    gap: 18px;
    align-items: center;
    /* font-size: 0.9rem; */
    padding: 18px 15px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
}
.breadcrumb-container a {
    color: #f8f9fa;
    text-decoration: none;
    transition: 0.3s;
}
.breadcrumb-container a:hover {
    color: #00d9ff;
    transform: scale(1.05);
}
.current-page {
    font-weight: bold;
    color: #00d9ff;
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-15px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
