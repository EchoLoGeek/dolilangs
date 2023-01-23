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

// 
require 'main.inc.php';

//
$viewManager->loadClass('dolilangs');
$dolilangs = new Dolilangs($db); 

//
$projectid = GETPOST('projectid');
if(!$projectid): $list_projets = $dolilangs->getProjects();
else: $projet = $dolilangs->fetchProject($projectid);
endif;

//
$action = GETPOST('action');

$error = 0;
$error_fields = array();
$notifications = array();

/*****************************************/

switch ($action):

    //
    case 'addtrans':

        if($projectid && $_SESSION['token'] == $_POST['token']):

            // On compte les lignes
            $post_nblines = intval($_POST['nblines']);

            $success_line = 0;

            // Pour chaque ligne
            for ($i=1; $i <= $post_nblines; $i++): 
                if(!empty($_POST['transkey_'.$i])):
                    foreach($dolilangs->available_languages as $keylang => $label):
                        if(!empty($_POST['transcontent_'.$i.'_'.$keylang])):

                            $res_add = $dolilangs->addtrans($keylang,$projectid,$_POST['transkey_'.$i],addslashes($_POST['transcontent_'.$i.'_'.$keylang]));

                            // Erreur sql
                            if($res_add == 0): $error++; 
                                array_push($notifications,array('type' => 'error', 'message' => 'Erreur SQL'));

                            // Enregistrement déja existant
                            elseif($res_add == -1): //$error++;
                                array_push($notifications,array('type' => 'error', 'message' => 'Un enregistrement '.$keylang.' existe déjà pour la clé: '.$_POST['transkey_'.$i]));
                            else:
                                $success_line++;
                                //array_push($notifications,array('type' => 'success', 'title' => 'Succès !', 'message' => 'Traduction '.$keylang.' ajoutée'));
                            endif;


                        endif;
                    endforeach;
                endif;
            endfor;

            if($success_line):
                array_push($notifications,array('type' => 'success', 'message' => $success_line.' ligne(s) ajoutée(s) !'));
                $projet = $dolilangs->fetchProject($projectid);
            endif;
            
        endif;
    break;

    //
    case 'deletebytranskey':

        if($projectid && $_SESSION['token'] == $_GET['token']):

            $success_delete = 0;

            $list_todel = $dolilangs->fetchTranskey($projectid,$_GET['transkey']);
            foreach($list_todel as $id_line => $line):
                if(!$dolilangs->deletetrans($line->rowid)): $error++;
                    array_push($notifications,array('type' => 'error', 'message' => 'Erreur de suppression ID: '.$line->rowid));
                else: $success_delete++; endif;
            endforeach;

            if($success_delete):
                array_push($notifications,array('type' => 'success', 'message' => $success_delete.' enregistrement(s) supprimée(s) !'));
                $projet = $dolilangs->fetchProject($projectid);
            endif;
        endif;
    break;

    //
    case 'confirmeditbytranskey':

        if($projectid && $_SESSION['token'] == $_POST['token']):

            if(empty($_POST['transkey'])): $error++; endif;

            if(!$error):

                $secure_key = $_POST['securetranskey'];

                foreach($dolilangs->available_languages as $keylang => $label):
                    if(isset($_POST['transcontent_'.$keylang]) && !empty($_POST['transcontent_'.$keylang])):

                        $res_add = $dolilangs->updatetrans($keylang,$projectid,$secure_key,$_POST['transkey'],addslashes($_POST['transcontent_'.$keylang]));
                        
                        if($res_add == 0): $error++; 
                            array_push($notifications,array('type' => 'error','message' => 'Erreur SQL'));
                        elseif($res_add == 1): 
                            array_push($notifications,array('type' => 'success','message' => 'Traduction '.$keylang.' ajoutée'));
                        elseif($res_add == 2):
                            array_push($notifications,array('type' => 'success','message' => 'Traduction '.$keylang.' modifiée'));
                        endif;

                    endif;
                endforeach;
                $projet = $dolilangs->fetchProject($projectid);
            endif;
        endif;
    break;

    //
    case 'constructfiles':

        if($projectid && $_SESSION['token'] == $_GET['token']):

            $project = $dolilangs->fetchProject($projectid);

            // DOSSIER DU PROJET
            $path = $project->folderpath.'/langs';
            if(!is_dir($path)): mkdir($path, 0777, true); endif;

            foreach($dolilangs->available_languages as $keylang => $label):

                $lang_path = $path.'/'.$keylang;
                if(!is_dir($lang_path)): mkdir($lang_path, 0777, true); endif;

                $langfile = fopen($lang_path.'/'.$project->ref.'.lang', 'w');

                $list_translang = $dolilangs->listProjectTransByLang($projectid,$keylang);
                $file_content = "";
                foreach ($list_translang as $key => $traduction):
                    $file_content.= $traduction->transkey."=".str_replace('[%]','&#x25;',$traduction->transcontent)."\n";                    
                endforeach;
                fwrite($langfile, $file_content);
                fclose($langfile);
            endforeach;

            array_push($notifications,array('type' => 'success', 'message' => 'Fichiers de langues créés'));
        endif;
    break;

    //
    case 'transfile':

        $file_success = 0;
        $file_error = 0;

        $lines_file = file_get_contents($_FILES['fileoftrans']['tmp_name']);
        $lines_file = preg_split('/\r\n|\r|\n/', $lines_file);

        $valid_lines = array();

        foreach($lines_file as $keyline => $linefile):

            if(!empty($linefile) && strpos($linefile, '#') !== 0): 
                
                $tabline = explode('=',$linefile,2);

                $kline = trim($tabline[0]);
                $cline = (!empty($tabline[1]))?trim($tabline[1]):'';

                $res_add = $dolilangs->addtrans($_POST['filelang'],$projectid,$kline,addslashes($cline));
                if($res_add == 1): $file_success++;
                else: $file_error++; var_dump($linefile);endif;
            endif;

        endforeach;

        if($file_success > 0):
            array_push($notifications,array('type' => 'success', 'message' => $file_success.' enregistrements ajoutés'));
        endif;
        if($file_error > 0):
            array_push($notifications,array('type' => 'error', 'message' => $file_error.' enregistrements en erreur'));
        endif;
        $projet = $dolilangs->fetchProject($projectid);
    break;


endswitch;

/*****************************************/

$viewManager->htmlHeader('Accueil dolilangs'); 

?>

<body class="dolilangs">

    
    <?php echo $viewManager->showSidebar(); ?>

    <div class="main-content">

        <?php // AFFICHAGE LISTE MODULES ?>
        <?php if(!$projectid): ?>

            <h1>Liste des modules</h1>

            <div class="row list-modules">
                <?php if(!empty($list_projets)): ?>
                    <?php foreach($list_projets as $projet): //var_dump($projet);?>

                        <?php 
                        if(intval($projet->nb_trad_fr_FR) != 0):
                            $percentage = (intval($projet->nb_trad_en_US) / intval($projet->nb_trad_fr_FR)) * 100;
                        else: $percentage = 0;
                        endif;
                        if($percentage == 100): $color_percent = 'bg-success';
                        elseif($percentage < 100 && $percentage >= 60): $color_percent = 'bg-primary';
                        elseif($percentage < 60 && $percentage >= 20): $color_percent = 'bg-warning';
                        elseif($percentage < 20 ): $color_percent = 'bg-danger'; endif;
                        ?>

                        <div class="col-3">                
                            <div class="card">

                                <div class="card-header p-4">
                                    <h3 class="card-title"><a href="<?php echo $viewManager->baseurl.'?projectid='.$projet->rowid; ?>"><?php echo $projet->label; ?></a></h3>
                                    <div class="card-options">
                                        <?php echo floor($percentage); ?>%
                                    </div>
                                </div>

                                <div class="card-body p-4">
                                    <div class="progress mb-2">
                                        <div class="progress-bar <?php echo $color_percent; ?>" style="width: <?php echo floor($percentage); ?>%;" role="progressbar"></div>
                                    </div>
                                    <div class="row mb-0">
                                        <?php foreach($dolilangs->available_languages as $keylang => $label): $nb_trad_label = 'nb_trad_'.$keylang; ?>
                                            <div class="col-auto">
                                                <div><img src="<?php echo $viewManager->baseurl.'/assets/img/flags/'.$keylang.'.jpg'; ?>" width="16" style="margin-right: 3px;"> <?php echo $projet->{$nb_trad_label}; ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                        
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php // DETAILS MODULE ?>
        <?php else: ?>

            <h1><?php echo $projet->label; ?></h1>

            <?php if(!empty($notifications)): ?>
            <div class="notifs mb-4">
                <?php foreach($notifications as $n): ?>
                    <div class="notif <?php echo $n['type']; ?> p-3 mb-2">
                        <div class="notif-msg"><?php echo $n['message']; ?></div>
                        <!-- <div class="notif-close"><i class="fa-solid fa-xmark"></i></div> -->
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if($action == 'prepareaddtrans'): $nblines = (isset($_POST['nblines']))?intval($_POST['nblines']):1; ?>

                <div class="card">
                    <div class="card-header p-4">
                        <h3 class="card-title">Ajouter des traductions</h3>
                        <div class="card-options">
                            <a class="gray-link" href="javascript:void(0)" id="btn-addline" data-addurl="ajax/trans_newline.php" data-langs="<?php echo implode(',',array_keys($dolilangs->available_languages)); ?>"><i class="fa-solid fa-plus"></i></a>
                        </div>
                    </div>
                    <div class="card-body">

                        <form action="<?php echo $_SERVER['PHP_SELF'].'?projectid='.$projectid; ?>" method="POST">

                            <input type="hidden" name="action" value="addtrans">
                            <input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>">
                            <input type="hidden" name="nblines" value="<?php echo $nblines; ?>" id="addtrans-nblines">

                            <div class="table-responsive">
                            <table class="table border table-bordered mb-4" id="addtrans-table">
                                <thead>
                                    <tr>
                                        <th>Clé de traduction</th>
                                        <?php foreach($dolilangs->available_languages as $keylang => $label): ?>
                                        <th><img src="assets/img/flags/<?php echo $keylang; ?>.jpg" width="16" class="me-2"> <?php echo $label; ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php for ($i=1; $i <= $nblines; $i++): ?> 
                                        <tr id="addline-<?php echo $i; ?>">
                                        <td><input type="text" name="transkey_<?php echo $i; ?>" value="" class="form-control"></td>
                                        <?php foreach($dolilangs->available_languages as $keylang => $label): ?>
                                            <td><input type="text" name="transcontent_<?php echo $i.'_'.$keylang; ?>" class="form-control"></td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>                      

                            <div class="text-end ms-6">
                                <a href="<?php echo $_SERVER['PHP_SELF'].'?projectid='.$projectid; ?>" class="btn btn-danger btn-sm text-white">Annuler</a>
                                <input type="submit" name="" class="btn btn-primary btn-sm">
                            </div>
                            
                        </form>
                    </div>
                </div>
            <?php elseif($action == 'editbytranskey'): $edit_key = $_GET['transkey']; $edit_obj = $dolilangs->fetchTranskey($projectid,$edit_key); ?>

                <div class="card">
                    <div class="card-header p-4"><h3 class="card-title">Modifier une traduction</h3></div>
                    
                    <div class="card-body">
                        <form action="<?php echo $_SERVER['PHP_SELF'].'?projectid='.$projectid; ?>" method="POST">
                            
                            <input type="hidden" name="action" value="confirmeditbytranskey">
                            <input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>">
                            <input type="hidden" name="securetranskey" value="<?php echo $edit_key; ?>">

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td class="fw-semibold col-3">Clé de traduction</td>
                                            <td><input class="form-control" type="text" name="transkey" value="<?php echo $edit_key; ?>"></td>
                                        </tr>
                                        <?php foreach($dolilangs->available_languages as $keylang => $label): ?>
                                        <tr class="input-row margin">
                                            <td class="fw-semibold col-3"><img src="assets/img/flags/<?php echo $keylang; ?>.jpg" width="16" class="me-2"> <?php echo $label; ?></td>
                                            <td><textarea class="form-control" name="transcontent_<?php echo $keylang; ?>"><?php echo (isset($edit_obj[$keylang]))?$edit_obj[$keylang]->transcontent:''; ?></textarea></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-end mt-4">
                                <a href="<?php echo $_SERVER['PHP_SELF'].'?projectid='.$projectid; ?>" class="btn btn-danger btn-sm text-white">Annuler</button></a>
                                <input type="submit" name="" value="Modifier" class="btn btn-primary btn-sm">
                            </div>

                        </form>
                    </div>
                </div>
            <?php elseif($action == 'importtrans'): ?>

                <div class="card">
                    <div class="card-header p-4"><h3 class="card-title">Importer un fichier</h3></div>
                    <div class="card-body">
                        <form action="<?php echo $_SERVER['PHP_SELF'].'?projectid='.$projectid; ?>" method="POST" enctype="multipart/form-data" >
                                        
                            <input type="hidden" name="action" value="transfile">
                            <input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>">

                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td class="fw-semibold col-3">Langue</td>
                                        <td>
                                            <select name="filelang">
                                                <?php foreach($dolilangs->available_languages as $keylang => $label): ?>
                                                    <option value="<?php echo $keylang; ?>"><?php echo $keylang; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold col-3">Fichier</td>
                                        <td><input type="file" name="fileoftrans" ></td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="text-end mt-4">
                                <a href="<?php echo $_SERVER['PHP_SELF'].'?projectid='.$projectid; ?>" class="btn btn-danger btn-sm text-white">Annuler</button></a>
                                <input type="submit" name="" value="Ajouter" class="btn btn-primary btn-sm">
                            </div>

                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card">

                <div class="card-header p-4">
                    <h3 class="card-title">Liste des traductions</h3>
                    <div class="card-options">
                        <a class="gray-link me-1" href="<?php echo $viewManager->baseurl.'/?projectid='.$projectid.'&action=prepareaddtrans&token='.$_SESSION['newtoken']; ?>"><i class="fa-regular fa-square-plus"></i></a>
                        <a class="gray-link me-1" href="<?php echo $viewManager->baseurl.'/?projectid='.$projectid.'&action=importtrans&token='.$_SESSION['newtoken']; ?>"><i class="fa-solid fa-upload"></i></a>
                        <a class="gray-link me-0" href="<?php echo $viewManager->baseurl.'/?projectid='.$projectid.'&action=constructfiles&token='.$_SESSION['newtoken']; ?>"><i class="fa-regular fa-floppy-disk"></i></a>
                    </div>
                </div>

                <div class="card-body">

                    <?php //var_dump($project); ?>

                    <div class="table-responsive">
                        <table class="table border table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Key</th>
                                    <th>Traduction FR</th>
                                    <th class="text-nowrap">Languages</th>
                                    <th class="text-nowrap"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($projet->tradkeys as $tradkey => $tradlang): ksort($tradlang);?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo $tradkey; ?></td>
                                        <td class="text-wrap"><?php if(isset($tradlang['fr_FR'])): echo $tradlang['fr_FR']->transcontent; endif; ?></td>
                                        <td class="text-nowrap">
                                        <?php foreach ($tradlang as $keylang => $trad): ?>
                                            <?php if(!empty($trad->transcontent)): ?>
                                                <img src="assets/img/flags/<?php echo $keylang; ?>.jpg" width="20" class="me-2">
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        </td>
                                        <td class="text-nowrap">
                                            <a class="gray-link me-1" href="<?php echo $_SERVER['PHP_SELF'].'?projectid='.$projectid.'&action=editbytranskey&transkey='.$tradkey.'&token='.$_SESSION['newtoken']; ?>"><i class="fa-regular fa-pen-to-square"></i></a>
                                            <a class="gray-link" href="<?php echo $_SERVER['PHP_SELF'].'?projectid='.$projectid.'&action=deletebytranskey&transkey='.$tradkey.'&token='.$_SESSION['newtoken']; ?>" onclick="return confirm('Certain ?')"><i class="fa-regular fa-trash-can"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>

    <?php echo $viewManager->footerJS(); ?>

</body>

