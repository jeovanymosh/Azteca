<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prove_model extends MY_Model {

	function __construct(){
		parent::__construct();
		$this->TABLE_NAME = "prove_lunes";
		$this->PRI_INDEX = "id_proveedor";
	} 

	public function getProveedores($where=[]){
		$this->db->select("id_proveedor,nombre,alias,cargo,detalles,elaborado,pagot")
		->from($this->TABLE_NAME)
		->where("estatus","1")
		->order_by($this->TABLE_NAME.".id_proveedor","ASC");
		if ($where !== NULL) {
			if (is_array($where)) {
				foreach ($where as $field=>$value) {
					$this->db->where($field, $value);
				}
			} else {
				$this->db->where($this->PRI_INDEX, $where);
			}
		}
		$result = $this->db->get()->result();
		if ($result) {
			if (is_array($where)) {
				return $result;
			} else {
				return array_shift($result);
			}
		} else {
			return false;
		}
	}

}

/* End of file Proveedores_model.php */
/* Location: ./application/models/Proveedores_model.php */
