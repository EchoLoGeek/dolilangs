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
	$app_version = '0.1';

	//
	$db = new PDO('mysql:dbname=dolilangs;host=127.0.0.1', 'root', '');

	//
	require __DIR__.'/class/viewmanager.class.php';
	$viewManager = new ViewManager($db);

	//
	session_start();
	$_SESSION['token'] = isset($_SESSION['newtoken'])?$_SESSION['newtoken']:'';
	$_SESSION['newtoken'] = md5(uniqid(microtime(), true));

	/**
     *  Recupère les variables GET ou POST
     *
     *  @param  string  $varname  Nom de la variable
     *  @return string  value
     */
	function GETPOST($varname){

		$varvalue = isset($_GET[$varname])?trim($_GET[$varname]):(isset($_POST[$varname])?trim($_POST[$varname]):'');
		return $varvalue;
	}


?>