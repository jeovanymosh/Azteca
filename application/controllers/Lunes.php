<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lunes extends MY_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model("Usuarios_model", "user_md");
		$this->load->model("Cambios_model", "cambio_md");
		$this->load->model("Prove_model", "prove_md");
		$this->load->model("Prolunes_model", "prolu_md");
		$this->load->model("Suclunes_model", "suc_md");
		$this->load->model("Exislunes_model", "ex_lun_md");
	}

	public function index(){
		$data['links'] = [
			'/assets/css/plugins/dataTables/dataTables.bootstrap',
			'/assets/css/plugins/dataTables/dataTables.responsive',
			'/assets/css/plugins/dataTables/dataTables.tableTools.min',
			'/assets/css/plugins/dataTables/buttons.dataTables.min',
		];

		$data['scripts'] = [
			'/scripts/provel',
			'/assets/js/plugins/dataTables/jquery.dataTables.min',
			'/assets/js/plugins/dataTables/jquery.dataTables',
			'/assets/js/plugins/dataTables/dataTables.buttons.min',
			'/assets/js/plugins/dataTables/buttons.flash.min',
			'/assets/js/plugins/dataTables/jszip.min',
			'/assets/js/plugins/dataTables/pdfmake.min',
			'/assets/js/plugins/dataTables/vfs_fonts',
			'/assets/js/plugins/dataTables/buttons.html5.min',
			'/assets/js/plugins/dataTables/buttons.print.min',
			'/assets/js/plugins/dataTables/dataTables.bootstrap',
			'/assets/js/plugins/dataTables/dataTables.responsive',
			'/assets/js/plugins/dataTables/dataTables.tableTools.min',
		];

		$data["usuarios"] = $this->user_md->getUsuarios();
		$this->estructura("Lunes/table_proveedores", $data);
	}

	public function proveedores(){
		$data['links'] = [
			'/assets/css/plugins/dataTables/dataTables.bootstrap',
			'/assets/css/plugins/dataTables/dataTables.responsive',
			'/assets/css/plugins/dataTables/dataTables.tableTools.min',
			'/assets/css/plugins/dataTables/buttons.dataTables.min',
		];

		$data['scripts'] = [
			'/scripts/provel',
			'/assets/js/plugins/dataTables/jquery.dataTables.min',
			'/assets/js/plugins/dataTables/jquery.dataTables',
			'/assets/js/plugins/dataTables/dataTables.buttons.min',
			'/assets/js/plugins/dataTables/buttons.flash.min',
			'/assets/js/plugins/dataTables/jszip.min',
			'/assets/js/plugins/dataTables/pdfmake.min',
			'/assets/js/plugins/dataTables/vfs_fonts',
			'/assets/js/plugins/dataTables/buttons.html5.min',
			'/assets/js/plugins/dataTables/buttons.print.min',
			'/assets/js/plugins/dataTables/dataTables.bootstrap',
			'/assets/js/plugins/dataTables/dataTables.responsive',
			'/assets/js/plugins/dataTables/dataTables.tableTools.min',
		];

		$data["proveedores"] = $this->prove_md->getProveedores();
		$this->estructura("Lunes/table_proveedores", $data);
	}

	public function new_proveedor(){
		$data["title"]="REGISTRAR PROVEEDOR";
		$user = $this->session->userdata();
		$data["view"] = $this->load->view("Lunes/new_proveedor", $data, TRUE);
		$data["button"]="<button class='btn btn-success new_proveedor' type='button'>
							<span class='bold'><i class='fa fa-floppy-o'></i></span> &nbsp;Guardar
						</button>";
		$this->jsonResponse($data);
	}

	public function save_prove(){
		$proveedor = [
			"nombre"	=>	strtoupper($this->input->post('nombre')),
			"alias"	=>	strtoupper($this->input->post('apellido')),
		];
		$getUsuario = $this->prove_md->get(NULL, ['nombre'=>$proveedor['nombre']])[0];

		if(sizeof($getUsuario) == 0){
			$data ['id_proveedor'] = $this->prove_md->insert($proveedor);
			$mensaje = ["id" 	=> 'Éxito',
						"desc"	=> 'Proveedor registrado correctamente',
						"type"	=> 'success'];
			$user = $this->session->userdata();
			$cambios = [
				"id_usuario" => $user["id_usuario"],
				"fecha_cambio" => date('Y-m-d H:i:s'),
				"antes" => "Proveedor Lunes es nuevo",
				"despues" => "Nombre : ".$proveedor['nombre']." /Alias: ".$proveedor['alias']];
			$data['cambios'] = $this->cambio_md->insert($cambios);
		}else{
			$mensaje = [
				"id" 	=> 'Alerta',
				"desc"	=> 'El Proveedor ['.$proveedor['nombre'].'] está registrado en el Sistema',
				"type"	=> 'warning'
			];
		}
		$this->jsonResponse($mensaje);
	}

	public function prove_update($id){
		$data["title"]="ACTUALIZAR DATOS DEL PROVEEDOR";
		$data["proveedor"] = $this->prove_md->get(NULL, ['id_proveedor'=>$id])[0];
		$user = $this->session->userdata();
		$data["view"] =$this->load->view("Lunes/edit_proveedor", $data, TRUE);
		$data["button"]="<button class='btn btn-success update_proveedor' type='button'>
							<span class='bold'><i class='fa fa-floppy-o'></i></span> &nbsp;Guardar cambios
						</button>";
		$this->jsonResponse($data);
	}

	public function update_prove(){
		$user = $this->session->userdata();
		$antes = $this->prove_md->get(NULL, ['id_proveedor'=>$this->input->post('id_proveedor')])[0];

		$proveedor = [
			"nombre"	=>	strtoupper($this->input->post('nombre')),
			"alias"	=>	strtoupper($this->input->post('apellido')),
		];

		$data ['id_proveedor'] = $this->prove_md->update($proveedor, $this->input->post('id_proveedor'));
		$cambios = [
				"id_usuario" => $user["id_usuario"],
				"fecha_cambio" => date('Y-m-d H:i:s'),
				"antes" => "Nombre : ".$antes->nombre." /Alias: ".$antes->alias,
				"despues" => "Nombre : ".$proveedor['nombre']." /Alias: ".$proveedor['alias']];
		$data['cambios'] = $this->cambio_md->insert($cambios);
		$mensaje = ["id" 	=> 'Éxito',
					"desc"	=> 'Proveedor actualizado correctamente',
					"type"	=> 'success'];
		$this->jsonResponse($mensaje);
	}

	public function prove_delete($id){
		$data["title"]="PROVEEDOR A ELIMINAR";
		$data["proveedor"] = $this->prove_md->get(NULL, ['id_proveedor'=>$id])[0];
		$data["view"] = $this->load->view("Lunes/delete_proveedor", $data,TRUE);
		$data["button"]="<button class='btn btn-danger delete_proveedor' type='button'>
							<span class='bold'><i class='fa fa-times'></i></span> &nbsp;Estoy segura(o) de eliminar
						</button>";
		$this->jsonResponse($data);
	}

	public function delete_prove(){
		$user = $this->session->userdata();
		$antes = $this->prove_md->get(NULL, ['id_proveedor'=>$this->input->post('id_proveedor')])[0];
		$cambios = [
				"id_usuario" => $user["id_usuario"],
				"fecha_cambio" => date('Y-m-d H:i:s'),
				"antes" => "Nombre : ".$antes->nombre." /Alias: ".$antes->alias,
				"despues" => "El Proveedor fue eliminado, se puede recuperar desde la BD"];
		$data['cambios'] = $this->cambio_md->insert($cambios);
		$data ['id_usuario'] = $this->prove_md->update(["estatus" => 0], $this->input->post('id_proveedor'));
		$mensaje = ["id" 	=> 'Éxito',
					"desc"	=> 'Proveedor eliminado correctamente',
					"type"	=> 'success'];
		$this->jsonResponse($mensaje);
	}

	public function productos(){
		$data['links'] = [
			'/assets/css/plugins/dataTables/dataTables.bootstrap',
			'/assets/css/plugins/dataTables/dataTables.responsive',
			'/assets/css/plugins/dataTables/dataTables.tableTools.min',
			'/assets/css/plugins/dataTables/buttons.dataTables.min',
		];

		$data['scripts'] = [
			'/scripts/produl',
			'/assets/js/plugins/dataTables/jquery.dataTables.min',
			'/assets/js/plugins/dataTables/jquery.dataTables',
			'/assets/js/plugins/dataTables/dataTables.buttons.min',
			'/assets/js/plugins/dataTables/buttons.flash.min',
			'/assets/js/plugins/dataTables/jszip.min',
			'/assets/js/plugins/dataTables/pdfmake.min',
			'/assets/js/plugins/dataTables/vfs_fonts',
			'/assets/js/plugins/dataTables/buttons.html5.min',
			'/assets/js/plugins/dataTables/buttons.print.min',
			'/assets/js/plugins/dataTables/dataTables.bootstrap',
			'/assets/js/plugins/dataTables/dataTables.responsive',
			'/assets/js/plugins/dataTables/dataTables.tableTools.min',
		];

		$data["productos"] = $this->prolu_md->getProductos();
		$this->estructura("Lunes/table_productos", $data);
	}

	public function new_producto(){
		$data["title"]="REGISTRAR PRODUCTO";
		$user = $this->session->userdata();
		$data["proveedores"] = $this->prove_md->getProveedores();
		$data["view"] = $this->load->view("Lunes/new_producto", $data, TRUE);
		$data["button"]="<button class='btn btn-success new_producto' type='button'>
							<span class='bold'><i class='fa fa-floppy-o'></i></span> &nbsp;Guardar
						</button>";
		$this->jsonResponse($data);
	}

	public function save_prod(){
		$producto = [
			"codigo"	=>	strtoupper($this->input->post('codigo')),
			"descripcion"	=>	strtoupper($this->input->post('descripcion')),
			"precio"	=>	$this->input->post('precio'),
			"sistema"	=>	$this->input->post('sistema'),
			"id_proveedor"	=>	$this->input->post('id_proveedor'),
			"unidad"	=>	$this->input->post('unidad'),
		];
		$getProducto = $this->prolu_md->get(NULL, ['codigo'=>$producto['codigo']])[0];

		if(sizeof($getProducto) == 0){
			$data['codigo'] = $this->prolu_md->insert($producto);
			$mensaje = ["id" 	=> 'Éxito',
						"desc"	=> 'Producto registrado correctamente',
						"type"	=> 'success'];
			$user = $this->session->userdata();
			$cambios = [
				"id_usuario" => $user["id_usuario"],
				"fecha_cambio" => date('Y-m-d H:i:s'),
				"antes" => "Producto Lunes es nuevo",
				"despues" => "Código : ".$producto['codigo']." /Descripción: ".$producto['descripcion']];
			$data['cambios'] = $this->cambio_md->insert($cambios);
		}else{
			$mensaje = [
				"id" 	=> 'Alerta',
				"desc"	=> 'El Producto ['.$producto['nombre'].'] está registrado en el Sistema',
				"type"	=> 'warning'
			];
		}
		$this->jsonResponse($mensaje);
	}

	public function prod_delete($id){
		$data["title"]="PRODUCTO A ELIMINAR";
		$data["producto"] = $this->prolu_md->get(NULL, ['codigo'=>$id])[0];
		$data["proveedor"] = $this->prove_md->get(NULL, ["id_proveedor"=>$data["producto"]->id_proveedor])[0];
		$data["view"] = $this->load->view("Lunes/delete_producto", $data,TRUE);
		$data["button"]="<button class='btn btn-danger delete_proveedor' type='button'>
							<span class='bold'><i class='fa fa-times'></i></span> &nbsp;Estoy segura(o) de eliminar
						</button>";
		$this->jsonResponse($data);
	}

	public function delete_prod(){
		$user = $this->session->userdata();
		$antes = $this->prolu_md->get(NULL, ['codigo'=>$this->input->post('codigo')])[0];
		$cambios = [
				"id_usuario" => $user["id_usuario"],
				"fecha_cambio" => date('Y-m-d H:i:s'),
				"antes" => "Código : ".$antes->codigo." /Descripción: ".$antes->descripcion,
				"despues" => "El Producto fue eliminado, se puede recuperar desde la BD"];
		$data['cambios'] = $this->cambio_md->insert($cambios);
		$data ['id_usuario'] = $this->prolu_md->update(["estatus" => 0], $this->input->post('codigo'));
		$mensaje = ["id" 	=> 'Éxito',
					"desc"	=> 'Producto eliminado correctamente',
					"type"	=> 'success'];
		$this->jsonResponse($mensaje);
	}

	public function prod_update($id){
		$data["title"]="ACTUALIZAR DATOS DEL PRODUCTO";
		$data["producto"] = $this->prolu_md->get(NULL, ['codigo'=>$id])[0];
		$data["proveedores"] = $this->prove_md->getProveedores();
		$user = $this->session->userdata();
		$data["view"] =$this->load->view("Lunes/edit_producto", $data, TRUE);
		$data["button"]="<button class='btn btn-success update_producto' type='button'>
							<span class='bold'><i class='fa fa-floppy-o'></i></span> &nbsp;Guardar cambios
						</button>";
		$this->jsonResponse($data);
	}

	public function update_prod(){
		$user = $this->session->userdata();
		$antes = $this->prolu_md->get(NULL, ['codigo'=>$this->input->post('codigos')])[0];

		$producto = [
			"codigo"	=>	strtoupper($this->input->post('codigo')),
			"descripcion"	=>	strtoupper($this->input->post('descripcion')),
			"precio"	=>	$this->input->post('precio'),
			"sistema"	=>	$this->input->post('sistema'),
			"id_proveedor"	=>	$this->input->post('id_proveedor'),
			"unidad"	=>	$this->input->post('unidad'),
		];

		$data ['codigo'] = $this->prolu_md->update($producto, $this->input->post('codigos'));
		$cambios = [
				"id_usuario" => $user["id_usuario"],
				"fecha_cambio" => date('Y-m-d H:i:s'),
				"antes" => "Código : ".$antes->codigo." /Descripción: ".$antes->descripcion,
				"despues" => "Código : ".$producto['codigo']." /Descripción: ".$producto['descripcion']];
		$data['cambios'] = $this->cambio_md->insert($cambios);
		$mensaje = ["id" 	=> 'Éxito',
					"desc"	=> 'Producto actualizado correctamente',
					"type"	=> 'success'];
		$this->jsonResponse($mensaje);
	}


	/*public function upload_prods(){
		$nams = preg_replace('/\s+/', '_', "Topazo");
		$filen = "Cotizacion".$nams."".rand();
		$config['upload_path']          = './assets/uploads/cotizaciones/';
        $config['allowed_types']        = 'xlsx|xls';
        $config['max_size']             = 100;
        $config['max_width']            = 1024;
        $config['max_height']           = 768;
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->do_upload('file_otizaciones',$filen);
		$this->load->library("excelfile");
		ini_set("memory_limit", -1);
		$file = $_FILES["file_otizaciones"]["tmp_name"];
		$filename=$_FILES['file_otizaciones']['name'];
		$sheet = PHPExcel_IOFactory::load($file);
		$objExcel = PHPExcel_IOFactory::load($file);
		$sheet = $objExcel->getSheet(0);
		$num_rows = $sheet->getHighestDataRow();
		for ($i=2; $i<=$num_rows; $i++) {
			if($sheet->getCell('A'.$i)->getValue() > 0){
				$precio=0; $sistema=0; $codigo=""; $desc=""; $unidad=0;
				$precio = str_replace("$", "", str_replace(",", "replace", $sheet->getCell('C'.$i)->getValue()));
				$sistema = str_replace("$", "", str_replace(",", "replace", $sheet->getCell('D'.$i)->getValue()));
				$codigo = htmlspecialchars($sheet->getCell('A'.$i)->getValue(), ENT_QUOTES, 'UTF-8');
				$desc = $sheet->getCell('B'.$i)->getValue();
				$unidad = $sheet->getCell('E'.$i)->getValue();
				$prove = $sheet->getCell('F'.$i)->getValue();
				$new_cotizacion=[
					"codigo"			=>	$codigo,
					"id_proveedor"		=>	$prove,//Recupera el id_usuario activo
					"precio"			=>	$precio,
					"sistema"			=>	$sistema,
					"descripcion"			=>	$desc,
					"unidad"			=>	$unidad,
					"estatus" => 1];
				$data['cotizacion']=$this->prolu_md->insert($new_cotizacion);
			}
		}
		if (!isset($new_cotizacion)) {
			$mensaje=[	"id"	=>	'Error',
						"desc"	=>	'El Archivo esta sin precios',
						"type"	=>	'error'];
		}else{
			if (sizeof($new_cotizacion) > 0) {
				
				$mensaje=[	"id"	=>	'Éxito',
							"desc"	=>	'Cotizaciones cargadas correctamente en el Sistema',
							"type"	=>	'success'];
			}else{
				$mensaje=[	"id"	=>	'Error',
							"desc"	=>	'Las Cotizaciones no se cargaron al Sistema',
							"type"	=>	'error'];
			}
		}
		$this->jsonResponse($mensaje);
	}*/

	public function exislunes(){
		$data['links'] = [
			'/assets/css/plugins/dataTables/dataTables.bootstrap',
			'/assets/css/plugins/dataTables/dataTables.responsive',
			'/assets/css/plugins/dataTables/dataTables.tableTools.min',
			'/assets/css/plugins/dataTables/buttons.dataTables.min',
		];

		$data['scripts'] = [
			'/scripts/exilu',
			'/assets/js/plugins/dataTables/jquery.dataTables.min',
			'/assets/js/plugins/dataTables/jquery.dataTables',
			'/assets/js/plugins/dataTables/dataTables.buttons.min',
			'/assets/js/plugins/dataTables/buttons.flash.min',
			'/assets/js/plugins/dataTables/jszip.min',
			'/assets/js/plugins/dataTables/pdfmake.min',
			'/assets/js/plugins/dataTables/vfs_fonts',
			'/assets/js/plugins/dataTables/buttons.html5.min',
			'/assets/js/plugins/dataTables/buttons.print.min',
			'/assets/js/plugins/dataTables/dataTables.bootstrap',
			'/assets/js/plugins/dataTables/dataTables.responsive',
			'/assets/js/plugins/dataTables/dataTables.tableTools.min',
		];
		$data["dias"] = array("DOMINGO","LUNES","MARTES","MIÉRCOLES","JUEVES","VIERNES","SÁBADO");
		$data["meses"] = array("ENERO","FEBRERO","MARZO","ABRIL","MAYO","JUNIO","JULIO","AGOSTO","SEPTIEMBRE","OCTUBRE","NOVIEMBRE","DICIEMBRE");
		$data["fecha"] =  $data["dias"][date('w')]." ".date('d')." DE ".$data["meses"][date('n')-1]. " DEL ".date('Y') ;
		$data["cuantas"] = $this->ex_lun_md->getCuantas(NULL);
		$data["noprod"] = $this->prolu_md->getCount(NULL)[0];
		$data["tiendas"] = $this->suc_md->getByOrder(NULL);
		$this->estructura("Lunes/existencias", $data);
		//$this->jsonResponse($data["noprod"]);
	}

	public function buscaProdis(){
		$busca = $this->input->post("values");
		$tiendas = $this->suc_md->getCount(NULL)[0];
		$data["prods"] = $this->prolu_md->buscaProdis(NULL,$busca,(int)$tiendas->total);
		$this->jsonResponse($data);
	}

	public function getCuantas($tienda){
		$busca = $this->input->post("values");
		$data["noprod"] = $this->prolu_md->getCount(NULL)[0];
		$data["cuantas"] = $this->ex_lun_md->getCuanto(NULL,$tienda);
		$this->jsonResponse($data);
	}

	public function upload_existencias($idesp){
		$tienda = $idesp;
		$fecha = new DateTime(date('Y-m-d H:i:s'));
		$cfile =  $this->suc_md->get(NULL, ['id_sucursal' => $idesp])[0];
		$nams = preg_replace('/\s+/', '_', $cfile->nombre);
		$filen = "Existencias".$nams."".rand();
		$config['upload_path']          = './assets/uploads/cotizaciones/';
        $config['allowed_types']        = 'xlsx|xls';
        $config['max_size']             = 100;
        $config['max_width']            = 1024;
        $config['max_height']           = 768;
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->do_upload('file_otizaciones',$filen);
		$this->load->library("excelfile");
		ini_set("memory_limit", -1);
		$file = $_FILES["file_otizaciones"]["tmp_name"];
		$filename=$_FILES['file_otizaciones']['name'];
		$sheet = PHPExcel_IOFactory::load($file);
		$objExcel = PHPExcel_IOFactory::load($file);
		$sheet = $objExcel->getSheet(0);
		$num_rows = $sheet->getHighestDataRow();
		for ($i=2; $i<=$num_rows; $i++) {
			if(strlen($sheet->getCell('D'.$i)->getValue()) > 0){
				$productos = $this->prolu_md->get("codigo",['codigo'=> htmlspecialchars($sheet->getCell('D'.$i)->getValue(), ENT_QUOTES, 'UTF-8')])[0];
				if (sizeof($productos) > 0) {
					$caja=0; $pieza=0; $ped=0;
					$caja = $sheet->getCell('A'.$i)->getValue();
					$pieza = $sheet->getCell('B'.$i)->getValue();
					$ped = $sheet->getCell('C'.$i)->getValue();
					$codigo = htmlspecialchars($sheet->getCell('D'.$i)->getValue(), ENT_QUOTES, 'UTF-8');
					$exist =  $this->ex_lun_md->get(NULL, ['id_producto' => $codigo, 'WEEKOFYEAR(fecha_registro)' => $this->weekNumber($fecha->format('Y-m-d H:i:s')), 'id_tienda' => $idesp])[0];
					$new_existencia=[
							"id_producto"		=>	$codigo,
							"id_tienda"			=>	$idesp,
							"cajas"				=>	$caja,
							"piezas"		 	=>	$pieza,
							"pedido"			=>	$ped,
						];
					if($exist){
						$data['existencia']=$this->ex_lun_md->update($new_existencia, ['id_existencia' => $exist->id_existencia]);
					}else{
						$data['existencia']=$this->ex_lun_md->insert($new_existencia);
					}
				}
			}
		}
		if (!isset($new_existencia)) {
			$mensaje=[	"id"	=>	'Error',
						"desc"	=>	'El Archivo esta sin precios',
						"type"	=>	'error'];
		}else{
			if (sizeof($new_existencia) > 0) {
				$cambios=[
						"id_usuario"		=>	$this->session->userdata('id_usuario'),
						"fecha_cambio"		=>	date("Y-m-d H:i:s"),
						"antes"			=>	"El usuario sube archivo de existencias de ".$cfile->nombre,
						"despues"			=>	"assets/uploads/cotizaciones/".$filen.".xlsx",
						"accion"			=>	"Sube Archivo"
					];
				$data['cambios']=$this->cambio_md->insert($cambios);
				$mensaje=[	"id"	=>	'Éxito',
							"desc"	=>	'Existencias cargadas correctamente en el Sistema',
							"type"	=>	'success'];
			}else{
				$mensaje=[	"id"	=>	'Error',
							"desc"	=>	'Las Existencias no se cargaron al Sistema',
							"type"	=>	'error'];
			}
		}
		$this->jsonResponse($mensaje);
	}

	public function semapa(){
		$data['links'] = [
			'/assets/css/plugins/dataTables/dataTables.bootstrap',
			'/assets/css/plugins/dataTables/dataTables.responsive',
			'/assets/css/plugins/dataTables/dataTables.tableTools.min',
			'/assets/css/plugins/dataTables/buttons.dataTables.min',
		];

		$data['scripts'] = [
			'/scripts/semapa',
			'/assets/js/plugins/dataTables/jquery.dataTables.min',
			'/assets/js/plugins/dataTables/jquery.dataTables',
			'/assets/js/plugins/dataTables/dataTables.buttons.min',
			'/assets/js/plugins/dataTables/buttons.flash.min',
			'/assets/js/plugins/dataTables/jszip.min',
			'/assets/js/plugins/dataTables/pdfmake.min',
			'/assets/js/plugins/dataTables/vfs_fonts',
			'/assets/js/plugins/dataTables/buttons.html5.min',
			'/assets/js/plugins/dataTables/buttons.print.min',
			'/assets/js/plugins/dataTables/dataTables.bootstrap',
			'/assets/js/plugins/dataTables/dataTables.responsive',
			'/assets/js/plugins/dataTables/dataTables.tableTools.min',
		];
		$data["dias"] = array("DOMINGO","LUNES","MARTES","MIÉRCOLES","JUEVES","VIERNES","SÁBADO");
		$data["meses"] = array("ENERO","FEBRERO","MARZO","ABRIL","MAYO","JUNIO","JULIO","AGOSTO","SEPTIEMBRE","OCTUBRE","NOVIEMBRE","DICIEMBRE");
		$data["fecha"] =  $data["dias"][date('w')]." ".date('d')." DE ".$data["meses"][date('n')-1]. " DEL ".date('Y') ;
		$data["cuantas"] = $this->ex_lun_md->getCuantas(NULL);
		$data["noprod"] = $this->prolu_md->getCount(NULL)[0];
		$data["tiendas"] = $this->suc_md->getByOrder(NULL);
		$this->estructura("Lunes/semapa", $data);
		//$this->jsonResponse($data["noprod"]);
	}

	public function formlunes(){
		$data['links'] = [
			'/assets/css/plugins/dataTables/dataTables.bootstrap',
			'/assets/css/plugins/dataTables/dataTables.responsive',
			'/assets/css/plugins/dataTables/dataTables.tableTools.min',
			'/assets/css/plugins/dataTables/buttons.dataTables.min',
		];

		$data['scripts'] = [
			'/scripts/formlunes',
			'/assets/js/plugins/dataTables/jquery.dataTables.min',
			'/assets/js/plugins/dataTables/jquery.dataTables',
			'/assets/js/plugins/dataTables/dataTables.buttons.min',
			'/assets/js/plugins/dataTables/buttons.flash.min',
			'/assets/js/plugins/dataTables/jszip.min',
			'/assets/js/plugins/dataTables/pdfmake.min',
			'/assets/js/plugins/dataTables/vfs_fonts',
			'/assets/js/plugins/dataTables/buttons.html5.min',
			'/assets/js/plugins/dataTables/buttons.print.min',
			'/assets/js/plugins/dataTables/dataTables.bootstrap',
			'/assets/js/plugins/dataTables/dataTables.responsive',
			'/assets/js/plugins/dataTables/dataTables.tableTools.min',
		];
		$data["dias"] = array("DOMINGO","LUNES","MARTES","MIÉRCOLES","JUEVES","VIERNES","SÁBADO");
		$data["meses"] = array("ENERO","FEBRERO","MARZO","ABRIL","MAYO","JUNIO","JULIO","AGOSTO","SEPTIEMBRE","OCTUBRE","NOVIEMBRE","DICIEMBRE");
		$data["fecha"] =  $data["dias"][date('w')]." ".date('d')." DE ".$data["meses"][date('n')-1]. " DEL ".date('Y') ;
		$data["cuantas"] = $this->ex_lun_md->getCuantas(NULL);
		$data["noprod"] = $this->prolu_md->getCount(NULL)[0];
		$data["tiendas"] = $this->suc_md->getByOrder(NULL);
		$this->estructura("Lunes/formlunes", $data);
		//$this->jsonResponse($data["noprod"]);
	}

}

/* End of file Lunes.php */
/* Location: ./application/controllers/Lunes.php */
