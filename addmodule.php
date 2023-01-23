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

//
$action = GETPOST('action');

$error = 0;
$error_fields = array();
$notifications = array();

/*****************************************/

switch ($action):

    case 'addproject':

        if($_SESSION['token'] == $_POST['token']):

            $module_name = GETPOST('module_name');
            $module_key = GETPOST('module_key');
            $module_folderpath = GETPOST('module_path');

            if(empty($module_name)): $error++; array_push($notifications,array('type' => 'error', 'message' => 'Nom obligatoire')); endif;
            if(empty($module_key)): $error++; array_push($notifications,array('type' => 'error', 'message' => 'Clé module obligatoire')); endif;
            if(empty($module_folderpath)): $error++; array_push($notifications,array('type' => 'error', 'message' => 'Chemin absolu obligatoire')); endif;

            if(!$error):               

                if($new_id = $dolilangs->addProject($module_name,$module_key,$module_folderpath)):
                    header('Location:'.$viewManager->baseurl.'/?projectid='.$new_id);
                else:
                    $error++;
                    array_push($notifications,array('type' => 'error', 'message' => 'Une erreur est survenue'));
                endif;

            endif;

        endif;

    break;

endswitch;

/*****************************************/

$viewManager->htmlHeader('Accueil dolilangs'); 

?>

<body class="dolilangs">

    
    <?php echo $viewManager->showSidebar(); ?>

    <div class="main-content">

        <h1>Ajouter un module</h1>

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

        <div class="card">
            <div class="card-header p-4">
                <h3>Nouveau module</h3>
            </div>
            <div class="card-body">
                
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                            
                    <input type="hidden" name="action" value="addproject">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>">

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold col-3">Nom du module <span class="clr-danger">*</span></td>
                                    <td><input class="form-control" type="text" name="module_name" value="<?php echo GETPOST('module_name'); ?>" required></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold col-3">Clé du module <span class="clr-danger">*</span></td>
                                    <td><input class="form-control" type="text" name="module_key" value="<?php echo GETPOST('module_key'); ?>" required></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold col-3">Chemin absolu vers le module <span class="clr-danger">*</span></td>
                                    <td><input class="form-control" type="text" name="module_path" value="<?php echo GETPOST('module_path'); ?>" required></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-4">
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-danger btn-sm text-white">Annuler</button></a>
                        <input type="submit" name="" value="Ajouter" class="btn btn-primary btn-sm">
                    </div>

                </form>


            </div>
        </div>

    </div>

    <?php echo $viewManager->footerJS(); ?>

</body>

