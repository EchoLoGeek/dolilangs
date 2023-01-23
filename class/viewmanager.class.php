<?php
/* 
 * Copyright (C) 2023 Anthony Damhet <anthony.damhet@outlook.fr>
 *
 * This program and files/directory inner it is free software: you can 
 * redistribute it and/or modify it under the terms of the 
 * GNU Affero General Public License (AGPL) as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AGPL for more details.
 *
 * You should have received a copy of the GNU AGPL
 * along with this program.  If not, see <https://www.gnu.org/licenses/agpl-3.0.html>.

 */

class ViewManager {

    public $baseurl = 'http://127.0.0.1/dolilangs';

    public $menu = array();

    public $more_css = array();
    public $more_js = array();

    public $db;

    /**/
    public function __construct($db){
        $this->db = $db;

        // 
        $this->loadClass('dolilangs');
        $dolilangs = new Dolilangs($db);
        $list_projets = $dolilangs->getProjects();

        $sublevels_projets = array();
        if(!empty($list_projets)): 
            foreach($list_projets as $projet):
                array_push($sublevels_projets, array('label' => $projet->label, 'link' => $this->baseurl.'/?projectid='.$projet->rowid));
            endforeach; 
        endif;

        $this->menu['addModule'] = array('icon' => 'fa-solid fa-plus', 'label' =>'Ajouter un module', 'link' => $this->baseurl.'/addmodule.php','sublevels' => array());
        $this->menu['listModule'] = array('icon' => 'fa-solid fa-bars-staggered', 'label' =>'Liste des modules', 'link' => $this->baseurl,'sublevels' => $sublevels_projets );


    }

    /**
     *  Afficher le header HTML
     *
     *  @param  string  $title  Titre de la page
     *  @return string  Head Html
     */
    public function htmlHeader($title = ''){ ?>

        <!DOCTYPE html>
        <html dir="ltr">
        <head>

            <!-- META DATA -->
            <meta charset="UTF-8">
            <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
            <meta http-equiv="X-UA-Compatible" content="IE=edge">

            <!-- TITLE -->
            <title><?php echo $title; ?></title>

            <!-- BOOTSTRAP CSS -->
            <link id="style" href="<?php echo $this->baseurl.'/assets/plugins/bootstrap_5.0.2/css/bootstrap.min.css'; ?>" rel="stylesheet" />

            <!-- FONTAWESOME -->            
            <link id="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" rel="stylesheet" />

            <!-- STYLE CSS -->
            <link href="<?php echo $this->baseurl.'/assets/css/reset.css'; ?>" rel="stylesheet" />
            <link href="<?php echo $this->baseurl.'/assets/css/dolilangs.css'; ?>" rel="stylesheet" />

            <?php if($this->more_css): ?>
                <?php foreach($this->more_css as $css_file): ?>
                <link rel="stylesheet" type="text/css" href="<?php echo $this->baseurl.'/assets/css/'.$css_file; ?>">
                <?php endforeach; ?>
            <?php endif; ?>

        </head>
        <body>
        <?php
    }

    /**
     *  Afficher le menu en Sidebar
     *
     *  @return string  Sidebar Menu
     */
    public function showSidebar(){ ?>

        <div class="sidebar">
        
            <div class="sidebar-logo"><i class="fa-solid fa-earth-europe"></i> DOLI<span>LANGS</span></div>
            <nav class="sidebar-menu">
                <ul>
                    <?php if(!empty($this->menu)): ?>
                        <?php foreach($this->menu as $key_menu => $menu): ?>
                            <li class="sidetitle"><a href="<?php echo $menu['link']; ?>"><i class="<?php echo $menu['icon']; ?>"></i> <?php echo $menu['label']; ?></a></li>
                            <?php if(!empty($menu['sublevels'])): ?>
                                <?php foreach($menu['sublevels'] as $sublevel): ?>
                                    <li class="sidelink">
                                        <?php //$class_link = (isset($_GET['projectid']) && $_GET['projectid'] == $projet->rowid)?'active':'';?>
                                        <a class="<?php //echo $class_link; ?>" href="<?php echo $sublevel['link']; ?>"><?php echo $sublevel['label']; ?></a>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <!-- <li class="sidetitle"><a href="#"><i class="fa-solid fa-bars-staggered"></i> Liste des modules</a></li>
                    <?php if(!empty($list_projets)): ?>
                        <?php foreach($list_projets as $projet): ?>
                            <li class="sidelink">
                                <?php $class_link = (isset($_GET['projectid']) && $_GET['projectid'] == $projet->rowid)?'active':'';?>
                                <a class="<?php echo $class_link; ?>" href="<?php echo $_SERVER['PHP_SELF'].'?projectid='.$projet->rowid; ?>"><?php echo $projet->label; ?></a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?> -->
                </ul>
            </nav>

        </div><?php 

    }

    /**
     *  Intégrer fichiers JS
     *
     *  @param  string  $classname  Titre de la page
     *  @return   void
     */
    public function footerJS(){ ?>
        
        <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
        <script src="<?php echo $this->baseurl.'/assets/js/dolilangs.js'; ?>"></script><?php 

        if(!empty($this->more_js)):
            foreach($this->more_js as $script_url):
                ?><script src="<?php echo $this->baseurl.'/assets/js/'.$script_url; ?>"></script><?php 
            endforeach;
        endif;

    }

    /**
     *  Charger une class
     *
     *  @param  string  $classname  Titre de la page
     *  @return   void
     */
    public function loadClass($classname){
        require_once($classname.'.class.php');
    }

    




}
?>