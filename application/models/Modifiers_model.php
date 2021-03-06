<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Modifiers_model extends MY_model {
	public function __construct(){
		parent::__construct();
	}
	public function getAllModiersByRPCode($rpCode){
		return	$this->db->select("modifiers.name,
				modifiers.value,
				modifiers.countDown,
				modifiers.id AS modifiersId,
				statsInSheet.name AS statName,
				statsInSheet.id as statId,
				characters.code")
				->from("rolePlays")
				->join("players","players.rpId=rolePlays.id")
				->join("characters","characters.playerId=players.id")
				->join("modifiers","modifiers.charId=characters.id")
				->join("statsInSheet","statsInSheet.id=modifiers.statId")
				->where("rolePlays.code",$rpCode)
				->order_by("characters.id")
				->order_by("modifiers.statId")
				->get()
				->result_array();
	}
	public function insert_batch($charId,$data,$isBase=false){
		if($isBase){
			$insertData=array();
			foreach($data as $key=>$value){
				$insertData[]=array("charId"=>$charId,"statId"=>$key,"isBase"=>1,"name"=>"Base","value"=>$value,"countDown"=>-1);
				unset($data[$key]);
			}
			$data=$insertData;
		}
		$this->db->insert_batch("modifiers",$data);
	}
	public function getStatsFromChar($charId){
		return	$this->db->select("modifiers.value, statsInSheet.name")
				->from("modifiers")
				->join("statsInSheet","statsInSheet.id=modifiers.statId")
				->where("modifiers.isBase",1)
				->where("modifiers.charId",$charId)
				->get()
				->result_array();
	}
	public function updateModifier($modId,$data){
		$this->db->where("id",$modId)->update("modifiers",$data);
	}
	public function insertModifier($data,$charId=false){
		if($charId){
			$data['charId']=$charId;
		}
		$this->db->insert("modifiers",$data);
		return	$this->db->insert_id();
	}
	public function getRPfromMod($modId){
		return	$this->db->select("rolePlays.id")
				->from("modifiers")
				->where("modifiers.id",$modId)
				->join("characters","modifiers.charId=characters.id")
				->join("players","characters.playerId=players.id")
				->join("rolePlays","rolePlays.id=players.rpId")
				->get()
				->row();
	}
	public function delete($modId,$noBase=true){
		$this->db->where("id",$modId);
		if($noBase){
			$this->db->where("isBase",0);
		}
		$this->db->delete("modifiers");
		return	$this->db->affected_rows(); 
	}

}
