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

class Dolilangs {

	public $db;
	public $db_prefix = 'dll_';
	public $table_langs = 'langs';
	public $table_projects = 'projects';
	public $table_trans = 'trans';

	public $available_languages = array();

	function __construct($db){

		$this->db = $db;

		// LANGS
		$sql_langs = "SELECT * FROM ".$this->db_prefix.$this->table_langs;
		$res_langs = $this->db->query($sql_langs);
		if($res_langs):
			while ($lang = $res_langs->fetch(PDO::FETCH_OBJ)):
				$this->available_languages[$lang->code] = $lang->label;
			endwhile;
		endif;

	}

	public function addProject($name,$key,$folderpath){

		$error = 0;

		$name = trim($name); if(empty($name)): $error++; endif;
		$key = trim($key); if(empty($key)): $error++; endif;
		$folderpath = trim($folderpath); if(empty($folderpath)): $error++; endif;

		if(!$error):
			$sql = "INSERT INTO ".$this->db_prefix.$this->table_projects;
			$sql.= " (label,ref,folderpath) VALUES (";
			$sql.= "'".$name."'";
			$sql.= ",'".$key."'";
			$sql.= ",'".$folderpath."'";
			$sql.= ")";
			$res = $this->db->prepare($sql);
			$res->execute();
			
			return $this->db->lastInsertId();
		endif;

	}

	// RECUPERER LES PROJETS
	public function getProjects(){

		$projects = array();
		$sql = "SELECT * FROM ".$this->db_prefix.$this->table_projects;
		$res = $this->db->query($sql);
		while($p = $res->fetch(PDO::FETCH_OBJ)): 

			foreach($this->available_languages as $keylang => $label):
				$ln = 'nb_trad_'.$keylang;
				$p->{$ln} = $this->countTrans($p->rowid,$keylang);
			endforeach;

			array_push($projects, $p); 
		endwhile;

		return $projects;
	}

	// COMPTER LES TRADUCTIONS D'UN PROJET
	public function countTrans($project_id,$lang){

		$sql = " SELECT COUNT(*) as nb FROM ".$this->db_prefix.$this->table_trans;
		$sql.= " WHERE lang = '".$lang."' AND project_id = '".$project_id."'";

		$result = $this->db->query($sql);
		$lignes = $result->fetchObject();

		return $lignes->nb;

	}

	// RECUPERER L'ENSEMBLE D'UN PROJET
	public function fetchProject($project_id){

		$project = array(
			'label' => '',
			'folderpath' => '',
			'ref' => '',
			'tradkeys' => array(),
		);

		// Infos projet
		$sql_projet = "SELECT label, folderpath, ref FROM ".$this->db_prefix.$this->table_projects;
		$sql_projet.= " WHERE rowid = ".$project_id;
		$res_projet = $this->db->query($sql_projet);
		if($res_projet):
			while ($projet = $res_projet->fetch(PDO::FETCH_OBJ)):
				$project['label'] = $projet->label;
				$project['folderpath'] = $projet->folderpath;
				$project['ref'] = $projet->ref;
			endwhile;
		endif;

		// Lignes
		$tmp_lines = array();

		$sql_trans = "SELECT * FROM ".$this->db_prefix.$this->table_trans;
		$sql_trans.= " WHERE project_id = ".$project_id;
		$sql_trans.= " ORDER BY transkey ASC";
		$res_trans = $this->db->query($sql_trans);
		if($res_trans):
			while ($trans = $res_trans->fetch(PDO::FETCH_OBJ)):
				array_push($tmp_lines, $trans);
			endwhile;
		endif;

		//
		foreach ($tmp_lines as $line):
			if(!isset($project['tradkeys'][$line->transkey])): $project['tradkeys'][$line->transkey] = array(); endif;
			$project['tradkeys'][$line->transkey][$line->lang] = $line;
		endforeach;

		return (object) $project;
	}

	// AJOUTER TRADUCTION
	public function addtrans($lang,$projectid,$transkey,$transcontent){

		// CHECK
		$sql_check = "SELECT rowid FROM ".$this->db_prefix.$this->table_trans;
		$sql_check.= " WHERE project_id = ".$projectid;
		$sql_check.= " AND lang = '".$lang."'";
		$sql_check.= " AND transkey = '".$transkey."'";

		$res_check = $this->db->prepare($sql_check);
		$res_check->execute();

		if($res_check->rowCount() > 0): return -1;
		else:

			$sql = "INSERT INTO ".$this->db_prefix.$this->table_trans;
			$sql.= " (lang,project_id,transkey,transcontent)";
			$sql.= " VALUES ('".$lang."',".$projectid.",'".$transkey."','".$transcontent."')";

			$res = $this->db->query($sql);

			if(!$res): return 0; else: return 1; endif;

		endif;
	}

	public function updatetrans($lang,$projectid,$secure_key,$transkey,$transcontent){
		
		// CHECK
		$sql_check = "SELECT rowid FROM ".$this->db_prefix.$this->table_trans;
		$sql_check.= " WHERE project_id = ".$projectid;
		$sql_check.= " AND lang = '".$lang."'";
		$sql_check.= " AND transkey = '".$secure_key."'";

		$res_check = $this->db->prepare($sql_check);
		$res_check->execute();

		if($res_check->rowCount() > 0):

			$sql = "UPDATE ".$this->db_prefix.$this->table_trans;
			$sql .= " SET transkey = '".$transkey."'";
			$sql .= ", transcontent = '".$transcontent."'";
			$sql .= " WHERE transkey = '".$secure_key."'";
			$sql .= " and lang = '".$lang."'";

			$res = $this->db->query($sql);

			if(!$res): return 0; else: return 2; endif;

		else:

			$sql = "INSERT INTO ".$this->db_prefix.$this->table_trans;
			$sql.= " (lang,project_id,transkey,transcontent)";
			$sql.= " VALUES ('".$lang."',".$projectid.",'".$transkey."','".$transcontent."')";

			$res = $this->db->query($sql);

			if(!$res): return 0; else: return 1; endif;

		endif;

	}

	// RECUPERER LES TRADUCTIONS D'UNE CLE
	public function fetchTranskey($projectid,$transkey){

		$sql = "SELECT * FROM ".$this->db_prefix.$this->table_trans;
		$sql.= " WHERE transkey = '".$transkey."'";
		$sql.= " AND project_id = '".$projectid."'";
		$res = $this->db->query($sql);

		$lines = array();

		if($res):
			while ($t = $res->fetch(PDO::FETCH_OBJ)):
				$lines[$t->lang] = $t;
			endwhile;
		endif;

		return $lines;
	}

	// SUPPRIMER UNE TRADUCTION
	public function deletetrans($rowid){

		$sql = "DELETE FROM ".$this->db_prefix.$this->table_trans;
		$sql .= " WHERE rowid = ".$rowid;

		$res = $this->db->query($sql);
		if(!$res): return false; else: return true; endif;

	}

	//
	public function onlyLacking($project){
		//var_dump($project['tradkeys']);

		$nb_langs = count($this->available_languages);

		$lacking_lines = array();

		foreach($project['tradkeys'] as $tradkey => $lines):
			if(count($lines) == $nb_langs): unset($project['tradkeys'][$tradkey]);
			endif;
		endforeach;

		return $project;
	}

	// 
	public function listProjectTransByLang($projectid,$lang){

		$sql = "SELECT * FROM ".$this->db_prefix.$this->table_trans;
		$sql.= " WHERE lang = '".$lang."'";
		$sql.= " AND project_id = '".$projectid."'";
		$res = $this->db->query($sql);

		$lines = array();

		if($res): while ($t = $res->fetch(PDO::FETCH_OBJ)):
			$lines[$t->rowid] = $t;			
		endwhile; endif;

		return $lines;

	}

}

?>