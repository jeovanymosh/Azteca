<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prolunes_model extends MY_Model {

	function __construct(){
		parent::__construct();
		$this->TABLE_NAME = "pro_lunes";
		$this->PRI_INDEX = "codigo";
	} 

	public function getCount($where=[]){
		$this->db->select("count(*) as noprod")
		->from($this->TABLE_NAME." p1")
		->where("p1.estatus","1");
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
				return $result;
			}
		} else {
			return false;
		}
	}

	public function getProductos($where=[]){
		$this->db->select("p1.codigo,p1.descripcion,p2.alias,p2.nombre,p1.unidad,p1.precio,p1.sistema")
		->from($this->TABLE_NAME." p1")
		->join("prove_lunes p2","p1.id_proveedor = p2.id_proveedor","LEFT")
		->where("p1.estatus","1")
		->order_by("p1.descripcion","ASC");
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


	public function buscaProdis($where=[],$values,$tiendas){
		$value = json_decode($values);
		$this->db->select("* FROM pro_lunes p LEFT JOIN ex_lunes e ON p.codigo = e.id_producto AND WEEKOFYEAR(e.fecha_registro) = WEEKOFYEAR(CURDATE()) 
						WHERE (p.codigo LIKE '%".$value->busca."%' OR p.descripcion LIKE '%".$value->busca."%')")
		->order_by("p.codigo","ASC");
		if ($where !== NULL) {
			if (is_array($where)) {
				foreach ($where as $field=>$value) {
					$this->db->where($field, $value);
				}
			} else {
				$this->db->where($this->PRI_INDEX, $where);
			}
		}
		$comparativa = $this->db->get()->result();
		$comparativaIndexada = [];
		$flag = 0;
		for ($i=0; $i<sizeof($comparativa); $i++) {
			if (isset($comparativaIndexada[$comparativa[$i]->codigo])) {
				if (isset($comparativaIndexada[$comparativa[$i]->codigo]["existencias"][$comparativa[$i]->id_tienda])) {
					$comparativaIndexada[$comparativa[$i]->codigo]["existencias"][$comparativa[$i]->id_tienda]["pzs"]	=	$comparativa[$i]->piezas;
					$comparativaIndexada[$comparativa[$i]->codigo]["existencias"][$comparativa[$i]->id_tienda]["cja"]	=	$comparativa[$i]->cajas;
					$comparativaIndexada[$comparativa[$i]->codigo]["existencias"][$comparativa[$i]->id_tienda]["ped"]	=	$comparativa[$i]->pedido;
				}
			}else{
				$flag++;
				$comparativaIndexada[$comparativa[$i]->codigo]					=	[];
				$comparativaIndexada[$comparativa[$i]->codigo]["codigo"]		=	$comparativa[$i]->codigo;
				$comparativaIndexada[$comparativa[$i]->codigo]["descripcion"]	=	$comparativa[$i]->descripcion;
				$comparativaIndexada[$comparativa[$i]->codigo]["existencias"]	=	[];
				for($key = 1; $key <= $tiendas; $key++) {
					$comparativaIndexada[$comparativa[$i]->codigo]["existencias"][$key]["pzs"]	=	0;
					$comparativaIndexada[$comparativa[$i]->codigo]["existencias"][$key]["cja"]	=	0;
					$comparativaIndexada[$comparativa[$i]->codigo]["existencias"][$key]["ped"]	=	0;
				}
				if (isset($comparativaIndexada[$comparativa[$i]->codigo]["existencias"][$comparativa[$i]->id_tienda])) {
					$comparativaIndexada[$comparativa[$i]->codigo]["existencias"][$comparativa[$i]->id_tienda]["pzs"]	=	$comparativa[$i]->piezas;
					$comparativaIndexada[$comparativa[$i]->codigo]["existencias"][$comparativa[$i]->id_tienda]["cja"]	=	$comparativa[$i]->cajas;
					$comparativaIndexada[$comparativa[$i]->codigo]["existencias"][$comparativa[$i]->id_tienda]["ped"]	=	$comparativa[$i]->pedido;
				}
			}
		}
		if ($comparativaIndexada) {
			if (is_array($where)) {
				return $comparativaIndexada;
			} else {
				return $comparativaIndexada;
			}
		} else {
			return false;
		}
	}

}

/* End of file Proveedores_model.php */
/* Location: ./application/models/Proveedores_model.php */
