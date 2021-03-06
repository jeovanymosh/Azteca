<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reyma extends MY_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model("Usuarios_model", "user_md");
		$this->load->model("Cambios_model", "cambio_md");
		$this->load->model("Prove_model", "prove_md");
		$this->load->model("Velaprod_model", "vprod_md");
		$this->load->model("Velasfam_model", "vfam_md");
		$this->load->model("Suclunes_model", "suc_md");
		$this->load->model("Exislunes_model", "ex_lun_md");
		$this->load->model("Productos_model", "prod_mdl");
		$this->load->model("Pendlunes_model", "pend_mdl");
	}

	public function productos(){
		$data['links'] = [
			'/assets/css/plugins/dataTables/dataTables.bootstrap',
			'/assets/css/plugins/dataTables/dataTables.responsive',
			'/assets/css/plugins/dataTables/dataTables.tableTools.min',
			'/assets/css/plugins/dataTables/buttons.dataTables.min',
		];

		$data['scripts'] = [
			'/scripts/velprod',
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

		$data["productos"] = $this->vprod_md->getProductos();
		$this->estructura("Reyma/table_productos", $data);
	}

	public function new_producto(){
		$data["title"]="REGISTRAR PRODUCTO REYMA";
		$user = $this->session->userdata();
		$data["familias"] = $this->vfam_md->get();
		$data["view"] = $this->load->view("Reyma/new_producto", $data, TRUE);
		$data["button"]="<button class='btn btn-success new_producto' type='button'>
							<span class='bold'><i class='fa fa-floppy-o'></i></span> &nbsp;Guardar
						</button>";
		$this->jsonResponse($data);
	}

	public function save_prod(){
		$producto = [
			"codpz"		=>	$this->input->post('codigo'),
			"codcaja"	=>	$this->input->post('caja'),
			"codprov"	=>	$this->input->post('proveedor'),
			"nombre"	=>	strtoupper($this->input->post('descripcion')),
			"unidad"	=>	$this->input->post('unidad'),
			"id_familia"	=>	$this->input->post('id_proveedor')
		];
		$getProducto = $this->vprod_md->get(NULL, ['nombre'=>$producto['nombre']])[0];

		if(sizeof($getProducto) == 0){
			$data['nombre'] = $this->vprod_md->insert($producto);
			$mensaje = ["id" 	=> 'Listo',
						"desc"	=> 'Producto registrado correctamente',
						"type"	=> 'success'];
			$user = $this->session->userdata();
			$cambios = [
				"id_usuario" => $user["id_usuario"],
				"fecha_cambio" => date('Y-m-d H:i:s'),
				"antes" => "Producto Reyma es nuevo",
				"despues" => "Código : ".$producto['codpz']." /Descripción: ".$producto['nombre']];
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
		$data["producto"] = $this->vprod_md->get(NULL, ['id_producto'=>$id])[0];
		$data["button"]="<button class='btn btn-danger delete_producto' type='button'>
							<span class='bold'><i class='fa fa-times'></i></span> &nbsp;Estoy segura(o) de eliminar
						</button>";
		$data["view"] = $this->load->view("Reyma/delete_producto", $data,TRUE);
		$this->jsonResponse($data);
	}

	public function delete_prod(){
		$user = $this->session->userdata();
		$antes = $this->vprod_md->get(NULL, ['id_producto'=>$this->input->post('id_producto')])[0];
		$cambios = [
				"id_usuario" => $user["id_usuario"],
				"fecha_cambio" => date('Y-m-d H:i:s'),
				"antes" => "Código : ".$antes->codpz." /Descripción: ".$antes->nombre,
				"despues" => "El Producto Reyma fue eliminado, se puede recuperar desde la BD"];
		$data['cambios'] = $this->cambio_md->insert($cambios);
		$data ['id_usuario'] = $this->vprod_md->update(["estatus" => 0], $this->input->post('id_producto'));
		$mensaje = ["id" 	=> 'Éxito',
					"desc"	=> 'Producto Reyma eliminado correctamente',
					"type"	=> 'success'];
		$this->jsonResponse($mensaje);
	}

	public function prod_update($id){
		$data["title"]="ACTUALIZAR DATOS DEL PRODUCTO";
		$data["producto"] = $this->vprod_md->get(NULL, ['id_producto'=>$id])[0];
		$data["familias"] = $this->vfam_md->get();
		$user = $this->session->userdata();
		$data["view"] =$this->load->view("Reyma/edit_producto", $data, TRUE);
		$data["button"]="<button class='btn btn-success update_producto' type='button'>
							<span class='bold'><i class='fa fa-floppy-o'></i></span> &nbsp;Guardar cambios
						</button>";
		$this->jsonResponse($data);
	}

	public function update_prod(){
		$user = $this->session->userdata();
		$antes = $this->vprod_md->get(NULL, ['id_producto'=>$this->input->post('id_producto')])[0];

		$producto = [
			"codpz"		=>	$this->input->post('codigo'),
			"codcaja"	=>	$this->input->post('caja'),
			"codprov"	=>	$this->input->post('proveedor'),
			"nombre"	=>	strtoupper($this->input->post('descripcion')),
			"unidad"	=>	$this->input->post('unidad'),
			"id_familia"	=>	$this->input->post('id_proveedor')
		];

		$data ['codigo'] = $this->vprod_md->update($producto,$this->input->post('id_producto'));
		$cambios = [
				"id_usuario" => $user["id_usuario"],
				"fecha_cambio" => date('Y-m-d H:i:s'),
				"antes" => "Código : ".$antes->codpz." /Descripción: ".$antes->nombre,
				"despues" => "Código : ".$producto['codpz']." /Descripción: ".$producto['nombre']];
		$data['cambios'] = $this->cambio_md->insert($cambios);
		$mensaje = ["id" 	=> 'Éxito',
					"desc"	=> 'Producto actualizado correctamente',
					"type"	=> 'success'];
		$this->jsonResponse($mensaje);
	}


	public function upload_prods(){
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
			if($sheet->getCell('B'.$i)->getValue() > 0){
				$codcaja=""; $codprov=""; $codigo=""; $nombre=""; $unidad=0;
				$codcaja = $sheet->getCell('A'.$i)->getValue();
				$codprov = $sheet->getCell('C'.$i)->getValue();
				$codigo = htmlspecialchars($sheet->getCell('B'.$i)->getValue(), ENT_QUOTES, 'UTF-8');
				$nombre = $sheet->getCell('D'.$i)->getValue();
				$unidad = $sheet->getCell('E'.$i)->getValue();
				$familia = $sheet->getCell('F'.$i)->getValue();
				$new_cotizacion=[
					"codpz"			=>	$codigo,
					"codcaja"		=>	$codcaja,//Recupera el id_usuario activo
					"codprov"			=>	$codprov,
					"nombre"			=>	$nombre,
					"id_familia"			=>	$familia,
					"unidad"			=>	$unidad];
				$data['cotizacion']=$this->vprod_md->insert($new_cotizacion);
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
	}






	public function existencias(){
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
		//$data["noprod"] = $this->prolu_md->getCount(NULL)[0];
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

	public function upload_sistema(){
		$fecha = new DateTime(date('Y-m-d H:i:s'));
		$filen = "Precios Sistema";
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
		for ($i=1; $i<=$num_rows; $i++) {
			if(strlen($sheet->getCell('A'.$i)->getValue()) > 0){
				$productos = $this->prolu_md->get("codigo",['codigo'=> htmlspecialchars($sheet->getCell('A'.$i)->getValue(), ENT_QUOTES, 'UTF-8')])[0];
				if (sizeof($productos) > 0) {
					$sistema = $sheet->getCell('C'.$i)->getValue();
					$new_existencia=[
							"sistema"		=>	$sistema,
							"fecha_sistema"	=>	$fecha->format('Y-m-d H:i:s'),
						];
					$data['existencia']=$this->prolu_md->update($new_existencia, ['codigo' => $productos->codigo]);
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
						"antes"			=>	"El usuario sube precio sitema lunes ",
						"despues"			=>	"assets/uploads/cotizaciones/Precios Sistema.xlsx",
						"accion"			=>	"Sube Archivo"
					];
				$data['cambios']=$this->cambio_md->insert($cambios);
				$mensaje=[	"id"	=>	'Éxito',
							"desc"	=>	'Precios sistema cargadas correctamente en el Sistema',
							"type"	=>	'success'];
			}else{
				$mensaje=[	"id"	=>	'Error',
							"desc"	=>	'Precios sistema no se cargaron al Sistema',
							"type"	=>	'error'];
			}
		}
		$this->jsonResponse($mensaje);
	}


	public function upload_precios(){
		$fecha = new DateTime(date('Y-m-d H:i:s'));
		$filen = "Precios Sistema Lunes";
		$config['upload_path']          = './assets/uploads/cotizaciones/';
        $config['allowed_types']        = 'xlsx|xls';
        $config['max_size']             = 1000;
        $config['max_width']            = 10204;
        $config['max_height']           = 7068;
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->do_upload('file_cotizaciones',$filen);
		$this->load->library("excelfile");
		ini_set("memory_limit", -1);
		$file = $_FILES["file_cotizaciones"]["tmp_name"];
		$filename=$_FILES['file_cotizaciones']['name'];
		$sheet = PHPExcel_IOFactory::load($file);
		$objExcel = PHPExcel_IOFactory::load($file);
		$sheet = $objExcel->getSheet(0);
		$num_rows = $sheet->getHighestDataRow();
		for ($i=1; $i<=$num_rows; $i++) {
			if(strlen($sheet->getCell('A'.$i)->getValue()) > 0){
				$productos = $this->prolu_md->get("codigo",['codigo'=> htmlspecialchars($sheet->getCell('A'.$i)->getValue(), ENT_QUOTES, 'UTF-8')])[0];
				if (sizeof($productos) > 0) {
					$sistema = $sheet->getCell('C'.$i)->getValue();
					$new_existencia=[
							"precio" =>	$sistema
						];
					$data['existencia']=$this->prolu_md->update($new_existencia, ['codigo' => $productos->codigo]);
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
						"antes"			=>	"El usuario sube precio sitema lunes ",
						"despues"			=>	"assets/uploads/cotizaciones/Precios Sistema.xlsx",
						"accion"			=>	"Sube Archivo"
					];
				$data['cambios']=$this->cambio_md->insert($cambios);
				$mensaje=[	"id"	=>	'Éxito',
							"desc"	=>	'Precios sistema cargadas correctamente en el Sistema',
							"type"	=>	'success'];
			}else{
				$mensaje=[	"id"	=>	'Error',
							"desc"	=>	'Precios sistema no se cargaron al Sistema',
							"type"	=>	'error'];
			}
		}
		$this->jsonResponse($mensaje);
	}

	public function excel_semana(){
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");
		$this->load->library("excelfile");
		$proveedor = $this->prove_md->get(NULL);
		$last = 1;
		$this->excelfile->createSheet();
		$totales = $this->excelfile->setActiveSheetIndex(0)->setTitle("TOTALES");
		foreach ($proveedor as $ke => $value) {
			$last++;
			$this->excelfile->createSheet();
        	$proveedor[$ke]->estatus = $this->excelfile->setActiveSheetIndex($ke+1)->setTitle($value->alias);
		}
		$this->excelfile->createSheet();
		$anterior = $this->excelfile->setActiveSheetIndex($last)->setTitle("ANTERIOR");

        $flag = 1; $flag1 = 1;
		$tiendas = $this->suc_md->getCount(NULL)[0];
        
        $this->excelfile->setActiveSheetIndex(0);

		$styleArray = array(
		  'borders' => array(
		    'allborders' => array(
		      'style' => PHPExcel_Style_Border::BORDER_THIN
		    )
		  )
		);
		$styleArray2 = array(
		  'borders' => array(
		    'allborders' => array(
		      'style' => PHPExcel_Style_Border::BORDER_MEDIUM
		    )
		  )
		);
		
		$proveedor[0]->estatus = $this->excelfile->getActiveSheet();

		
		//FECHA EN FORMATO COMPLETO PARA LOS TITULOS Y TABLAS
		$dias = array("DOMINGO","LUNES","MARTES","MIÉRCOLES","JUEVES","VIERNES","SÁBADO");
		$meses = array("ENERO","FEBRERO","MARZO","ABRIL","MAYO","JUNIO","JULIO","AGOSTO","SEPTIEMBRE","OCTUBRE","NOVIEMBRE","DICIEMBRE");
		$fecha =  $dias[date('w')]." ".date('d')." DE ".$meses[date('n')-1]. " DEL ".date('Y') ;
		$day = date('w');
		$week_start = date('d', strtotime('-'.($day).' days'));
		$week_end = date('d', strtotime('+'.(6-$day).' days'));

		$ced="=";$sup="=";$aba="=";$ped="=";$tie="=";$ult="=";$tri="=";$mer="=";$ten="=";$tij="=";

		foreach ($proveedor as $key => $va) {
			$infos = $this->prolu_md->printProdis(NULL,$va->id_proveedor,$tiendas->total);
			if ($infos) {
				if (1 == 1) {
					$this->excelfile->setActiveSheetIndex($key+1
					);
					$proveedor[$key]->estatus = $this->excelfile->getActiveSheet();
					$flag = 1;
					$proveedor[$key]->estatus->mergeCells('A'.$flag.':BU'.$flag);
					$this->cellStyle("A".$flag, "FFFFFF", "000000", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("A".$flag."", "CEDIS, ABARROTES,PEDREGAL, TIENDA, ULTRAMARINOS, TRINCHERAS, MERCADO, TIJERAS, Y TENENCIA AZTECA AUTOSERVICIOS SA. DE CV.");
					$this->excelfile->getActiveSheet()->getStyle('A'.$flag.':BU'.$flag)->applyFromArray($styleArray);
					$flag++;
					$this->excelfile->getActiveSheet()->getStyle('A'.$flag.':BU'.$flag)->applyFromArray($styleArray);
					$proveedor[$key]->estatus->getColumnDimension('A')->setWidth("25");
					$proveedor[$key]->estatus->getColumnDimension('B')->setWidth("70");
					$proveedor[$key]->estatus->getColumnDimension('F')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('L')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('R')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('X')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('AD')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('AJ')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('AP')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('AV')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('BB')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('BH')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('BN')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('BT')->setWidth("40");
					$proveedor[$key]->estatus->getColumnDimension('BU')->setWidth("40");
					$proveedor[$key]->estatus->getColumnDimension('C')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('D')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('E')->setWidth("15");
					$proveedor[$key]->estatus->mergeCells('A'.$flag.':B'.$flag);
					$this->cellStyle("A".$flag, "FFFFFF", "000000", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("A".$flag."", "PEDIDO A ".$va->nombre);
					$proveedor[$key]->estatus->mergeCells('C'.$flag.':E'.$flag);
					$this->cellStyle("C".$flag, "FFFFFF", "000000", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("C".$flag."", $fecha);

					$proveedor[$key]->estatus->mergeCells('F'.$flag.':K'.$flag);
					$this->cellStyle("F".$flag, "C00000", "000000", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("F".$flag, "CEDIS/SUPER");
					$proveedor[$key]->estatus->mergeCells('L'.$flag.':Q'.$flag);
					$this->cellStyle("L".$flag, "C2B90A", "000000", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("L".$flag, "SUMA CEDIS/SUPER");
					$proveedor[$key]->estatus->mergeCells('R'.$flag.':W'.$flag);
					$proveedor[$key]->estatus->setCellValue("R".$flag, "CD INDUSTRIAL");
					$this->cellStyle("R".$flag, "FF0066", "000000", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->mergeCells('X'.$flag.':AC'.$flag);
					$this->cellStyle("X".$flag, "01B0F0", "000000", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("X".$flag, "ABARROTES");
					$proveedor[$key]->estatus->mergeCells('AD'.$flag.':AI'.$flag);
					$this->cellStyle("AD".$flag, "FF0000", "000000", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AD".$flag, "PEDREGAL");
					$proveedor[$key]->estatus->mergeCells('AJ'.$flag.':AO'.$flag);
					$this->cellStyle("AJ".$flag, "E26C0B", "000000", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AJ".$flag, "TIENDA");
					$proveedor[$key]->estatus->mergeCells('AP'.$flag.':AU'.$flag);
					$this->cellStyle("AP".$flag, "C5C5C5", "000000", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AP".$flag, "ULTRAMARINOS");
					$proveedor[$key]->estatus->mergeCells('AV'.$flag.':BA'.$flag);
					$this->cellStyle("AV".$flag, "92D051", "000000", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AV".$flag, "TRINCHERAS");
					$proveedor[$key]->estatus->mergeCells('BB'.$flag.':BG'.$flag);
					$this->cellStyle("BB".$flag, "B1A0C7", "000000", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BB".$flag, "AZT MERCADO");
					$proveedor[$key]->estatus->mergeCells('BH'.$flag.':BM'.$flag);
					$this->cellStyle("BH".$flag, "DA9694", "000000", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BH".$flag, "TENENCIA");
					$proveedor[$key]->estatus->mergeCells('BN'.$flag.':BS'.$flag);
					$this->cellStyle("BN".$flag, "4CACC6", "000000", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BN".$flag, "TIJERAS");
					$this->cellStyle("BT".$flag, "FFFF00", "FF0000", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BT".$flag, "PROMOCIÓN");
					$this->cellStyle("BU".$flag, "92D050", "FF0000", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BU".$flag, "NOTA");

					$flag++;
					$this->excelfile->getActiveSheet()->getStyle('A'.$flag.':BU'.$flag)->applyFromArray($styleArray);
					$proveedor[$key]->estatus->mergeCells('A'.$flag.':E'.$flag);
					$this->cellStyle("A".$flag, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("A".$flag."", "DESCRIPCIÓN");
					$proveedor[$key]->estatus->mergeCells('F'.$flag.':K'.$flag);
					$this->cellStyle("F".$flag, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("F".$flag."", "EXISTENCIAS");
					$proveedor[$key]->estatus->mergeCells('L'.$flag.':Q'.$flag);
					$this->cellStyle("L".$flag, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("L".$flag."", "EXISTENCIAS");
					$proveedor[$key]->estatus->mergeCells('R'.$flag.':W'.$flag);
					$this->cellStyle("R".$flag, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("R".$flag."", "EXISTENCIAS");
					$proveedor[$key]->estatus->mergeCells('X'.$flag.':AC'.$flag);
					$this->cellStyle("X".$flag, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("X".$flag."", "EXISTENCIAS");
					$proveedor[$key]->estatus->mergeCells('AD'.$flag.':AI'.$flag);
					$this->cellStyle("AD".$flag, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AD".$flag."", "EXISTENCIAS");
					$proveedor[$key]->estatus->mergeCells('AJ'.$flag.':AO'.$flag);
					$this->cellStyle("AJ".$flag, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AJ".$flag."", "EXISTENCIAS");
					$proveedor[$key]->estatus->mergeCells('AP'.$flag.':AU'.$flag);
					$this->cellStyle("AP".$flag, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AP".$flag."", "EXISTENCIAS");
					$proveedor[$key]->estatus->mergeCells('AV'.$flag.':BA'.$flag);
					$this->cellStyle("AV".$flag, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AV".$flag."", "EXISTENCIAS");
					$proveedor[$key]->estatus->mergeCells('BB'.$flag.':BG'.$flag);
					$this->cellStyle("BB".$flag, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BB".$flag."", "EXISTENCIAS");
					$proveedor[$key]->estatus->mergeCells('BH'.$flag.':BM'.$flag);
					$this->cellStyle("BH".$flag, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BH".$flag."", "EXISTENCIAS");
					$proveedor[$key]->estatus->mergeCells('BN'.$flag.':BS'.$flag);
					$this->cellStyle("BN".$flag, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BN".$flag."", "EXISTENCIAS");
					$this->cellStyle("BT".$flag, "FFFF00", "FF0000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("BU".$flag, "92D050", "FF0000", TRUE, 12, "Franklin Gothic Book");
					
					$flag++;
					$this->excelfile->getActiveSheet()->getStyle('A'.$flag.':BU'.$flag)->applyFromArray($styleArray);
					$proveedor[$key]->estatus->mergeCells('A'.$flag.':B'.$flag);
					$this->cellStyle("A".$flag, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("C".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("C".$flag."", "PRECIO");
					$this->cellStyle("D".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("D".$flag."", "SISTEMA");
					$this->cellStyle("E".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("E".$flag."", "UM");
					$this->cellStyle("F".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("F".$flag."", "Pedido anterior");
					$this->cellStyle("G".$flag, "000000", "FF0000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("G".$flag."", "Sugerido");
					$this->cellStyle("H".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("H".$flag."", "Pendiente");
					$this->cellStyle("I".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("I".$flag."", "Cajas");
					$this->cellStyle("J".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("J".$flag."", "Pzs");
					$this->cellStyle("K".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("K".$flag."", "Pedido");
					$this->cellStyle("L".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("L".$flag."", "Pedido anterior");
					$this->cellStyle("M".$flag, "000000", "FF0000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("M".$flag."", "Sugerido");
					$this->cellStyle("N".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("N".$flag."", "Pendiente");
					$this->cellStyle("O".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("O".$flag."", "Cajas");
					$this->cellStyle("P".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("P".$flag."", "Pzs");
					$this->cellStyle("Q".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("Q".$flag."", "Pedido");
					$this->cellStyle("R".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("R".$flag."", "Pedido anterior");
					$this->cellStyle("S".$flag, "000000", "FF0000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("S".$flag."", "Sugerido");
					$this->cellStyle("T".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("T".$flag."", "Pendiente");
					$this->cellStyle("U".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("U".$flag."", "Cajas");
					$this->cellStyle("V".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("V".$flag."", "Pzs");
					$this->cellStyle("W".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("W".$flag."", "Pedido");
					$this->cellStyle("X".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("X".$flag."", "Pedido anterior");
					$this->cellStyle("Y".$flag, "000000", "FF0000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("Y".$flag."", "Sugerido");
					$this->cellStyle("Z".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("Z".$flag."", "Pendiente");
					$this->cellStyle("AA".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AA".$flag."", "Cajas");
					$this->cellStyle("AB".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AB".$flag."", "Pzs");
					$this->cellStyle("AC".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AC".$flag."", "Pedido");
					$this->cellStyle("AD".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AD".$flag."", "Pedido anterior");
					$this->cellStyle("AE".$flag, "000000", "FF0000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AE".$flag."", "Sugerido");
					$this->cellStyle("AF".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AF".$flag."", "Pendiente");
					$this->cellStyle("AG".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AG".$flag."", "Cajas");
					$this->cellStyle("AH".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AH".$flag."", "Pzs");
					$this->cellStyle("AI".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AI".$flag."", "Pedido");
					$this->cellStyle("AJ".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AJ".$flag."", "Pedido anterior");
					$this->cellStyle("AK".$flag, "000000", "FF0000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AK".$flag."", "Sugerido");
					$this->cellStyle("AL".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AL".$flag."", "Pendiente");
					$this->cellStyle("AM".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AM".$flag."", "Cajas");
					$this->cellStyle("AN".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AN".$flag."", "Pzs");
					$this->cellStyle("AO".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AO".$flag."", "Pedido");
					$this->cellStyle("AP".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AP".$flag."", "Pedido anterior");
					$this->cellStyle("AQ".$flag, "000000", "FF0000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AQ".$flag."", "Sugerido");
					$this->cellStyle("AR".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AR".$flag."", "Pendiente");
					$this->cellStyle("AS".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AS".$flag."", "Cajas");
					$this->cellStyle("AT".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AT".$flag."", "Pzs");
					$this->cellStyle("AU".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AU".$flag."", "Pedido");
					$this->cellStyle("AV".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AV".$flag."", "Pedido anterior");
					$this->cellStyle("AW".$flag, "000000", "FF0000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AW".$flag."", "Sugerido");
					$this->cellStyle("AX".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AX".$flag."", "Pendiente");
					$this->cellStyle("AY".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AY".$flag."", "Cajas");
					$this->cellStyle("AZ".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("AZ".$flag."", "Pzs");
					$this->cellStyle("BA".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BA".$flag."", "Pedido");
					$this->cellStyle("BB".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BB".$flag."", "Pedido anterior");
					$this->cellStyle("BC".$flag, "000000", "FF0000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BC".$flag."", "Sugerido");
					$this->cellStyle("BD".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BD".$flag."", "Pendiente");
					$this->cellStyle("BE".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BE".$flag."", "Cajas");
					$this->cellStyle("BF".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BF".$flag."", "Pzs");
					$this->cellStyle("BG".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BG".$flag."", "Pedido");
					$this->cellStyle("BH".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BH".$flag."", "Pedido anterior");
					$this->cellStyle("BI".$flag, "000000", "FF0000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BI".$flag."", "Sugerido");
					$this->cellStyle("BJ".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BJ".$flag."", "Pendiente");
					$this->cellStyle("BK".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BK".$flag."", "Cajas");
					$this->cellStyle("BL".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BL".$flag."", "Pzs");
					$this->cellStyle("BM".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BM".$flag."", "Pedido");
					$this->cellStyle("BN".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BN".$flag."", "Pedido anterior");
					$this->cellStyle("BO".$flag, "000000", "FF0000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BO".$flag."", "Sugerido");
					$this->cellStyle("BP".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BP".$flag."", "Pendiente");
					$this->cellStyle("BQ".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BQ".$flag."", "Cajas");
					$this->cellStyle("BR".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BR".$flag."", "Pzs");
					$this->cellStyle("BS".$flag, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BS".$flag."", "Pedido");
					$this->cellStyle("BT".$flag, "FFFF00", "FF0000", FALSE, 10, "Franklin Gothic Book");
					$this->cellStyle("BU".$flag, "92D050", "FF0000", TRUE, 12, "Franklin Gothic Book");

					$this->excelfile->getActiveSheet()->getStyle('BW'.$flag.':CG'.$flag)->applyFromArray($styleArray);

					$this->cellStyle("BW".$flag, "C00000", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("BX".$flag, "FF0066", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("BY".$flag, "01B0F0", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("BZ".$flag, "FF0000", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("CA".$flag, "E26C0B", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("CB".$flag, "C5C5C5", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("CC".$flag, "92D051", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("CD".$flag, "B1A0C7", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("CE".$flag, "DA9694", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("CF".$flag, "4CACC6", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("CG".$flag, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					
					$proveedor[$key]->estatus->setCellValue("CG".$flag."", "TOTAL");
					
					$proveedor[$key]->estatus->getColumnDimension('BW')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('BX')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('BY')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('BZ')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('CA')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('CB')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('CC')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('CD')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('CE')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('CF')->setWidth("15");
					$proveedor[$key]->estatus->getColumnDimension('CG')->setWidth("15");
				}

				if (2 == 2) {
					$this->excelfile->setActiveSheetIndex($last);
					$anterior = $this->excelfile->getActiveSheet();
					$anterior->getColumnDimension('A')->setWidth("25");
					$anterior->getColumnDimension('B')->setWidth("70");
					$anterior->getColumnDimension('BI')->setWidth("40");
					$anterior->getColumnDimension('BJ')->setWidth("40");
					$anterior->getColumnDimension('C')->setWidth("15");
					$anterior->getColumnDimension('D')->setWidth("15");
					$anterior->getColumnDimension('E')->setWidth("15");
					$anterior->getColumnDimension('AM')->setWidth("40");
					$anterior->getColumnDimension('AN')->setWidth("40");
					$anterior->mergeCells('A'.$flag1.':AN'.$flag1);
					$this->cellStyle("A".$flag1, "FFFFFF", "000000", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("A".$flag1."", "CEDIS, ABARROTES,PEDREGAL, TIENDA, ULTRAMARINOS, TRINCHERAS, MERCADO, TIJERAS, Y TENENCIA AZTECA AUTOSERVICIOS SA. DE CV.");
					$this->excelfile->getActiveSheet()->getStyle('A'.$flag1.':AN'.$flag1)->applyFromArray($styleArray);
					$flag1++;
					$this->excelfile->getActiveSheet()->getStyle('A'.$flag1.':AN'.$flag1)->applyFromArray($styleArray);
					$anterior->mergeCells('A'.$flag1.':B'.$flag1);
					$this->cellStyle("A".$flag1, "FFFFFF", "000000", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("A".$flag1."", "PEDIDO A ".$va->nombre);
					$anterior->mergeCells('C'.$flag1.':E'.$flag1);
					$this->cellStyle("C".$flag1, "FFFFFF", "000000", TRUE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("C".$flag1."", $fecha);

					$anterior->mergeCells('F'.$flag1.':H'.$flag1);
					$this->cellStyle("F".$flag1, "C00000", "000000", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("F".$flag1, "CEDIS/SUPER");
					$anterior->mergeCells('I'.$flag1.':K'.$flag1);
					$this->cellStyle("I".$flag1, "C2B90A", "000000", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("I".$flag1, "SUMA CEDIS/SUPER");
					$anterior->mergeCells('L'.$flag1.':N'.$flag1);
					$anterior->setCellValue("L".$flag1, "CD INDUSTRIAL");
					$this->cellStyle("L".$flag1, "FF0066", "000000", TRUE, 12, "Franklin Gothic Book");
					
					$anterior->mergeCells('O'.$flag1.':Q'.$flag1);
					$this->cellStyle("O".$flag1, "01B0F0", "000000", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("O".$flag1, "ABARROTES");
					$anterior->mergeCells('R'.$flag1.':T'.$flag1);
					$this->cellStyle("R".$flag1, "FF0000", "000000", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("R".$flag1, "PEDREGAL");
					$anterior->mergeCells('U'.$flag1.':W'.$flag1);
					$this->cellStyle("U".$flag1, "E26C0B", "000000", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("U".$flag1, "TIENDA");
					$anterior->mergeCells('X'.$flag1.':Z'.$flag1);
					$this->cellStyle("X".$flag1, "C5C5C5", "000000", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("X".$flag1, "ULTRAMARINOS");
					$anterior->mergeCells('AA'.$flag1.':AC'.$flag1);
					$this->cellStyle("AA".$flag1, "92D051", "000000", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("AA".$flag1, "TRINCHERAS");
					$anterior->mergeCells('AD'.$flag1.':AF'.$flag1);
					$this->cellStyle("AD".$flag1, "B1A0C7", "000000", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("AD".$flag1, "AZT MERCADO");
					$anterior->mergeCells('AG'.$flag1.':AI'.$flag1);
					$this->cellStyle("AG".$flag1, "DA9694", "000000", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("AG".$flag1, "TENENCIA");
					$anterior->mergeCells('AJ'.$flag1.':AL'.$flag1);
					$this->cellStyle("AJ".$flag1, "4CACC6", "000000", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("AJ".$flag1, "TIJERAS");
					$this->cellStyle("AM".$flag1, "FFFF00", "FF0000", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("AM".$flag1, "PROMOCIÓN");
					$this->cellStyle("AN".$flag1, "92D050", "FF0000", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("AN".$flag1, "NOTA");

					$flag1++;
					$this->excelfile->getActiveSheet()->getStyle('A'.$flag1.':AN'.$flag1)->applyFromArray($styleArray);
					$anterior->mergeCells('A'.$flag1.':E'.$flag1);
					$this->cellStyle("A".$flag1, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("A".$flag1."", "DESCRIPCIÓN");
					$anterior->mergeCells('F'.$flag1.':H'.$flag1);
					$this->cellStyle("F".$flag1, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("F".$flag1."", "EXISTENCIAS");
					$anterior->mergeCells('I'.$flag1.':K'.$flag1);
					$this->cellStyle("I".$flag1, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("I".$flag1."", "EXISTENCIAS");
					$anterior->mergeCells('L'.$flag1.':N'.$flag1);
					$this->cellStyle("L".$flag1, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("L".$flag1."", "EXISTENCIAS");
					$anterior->mergeCells('O'.$flag1.':Q'.$flag1);
					$this->cellStyle("O".$flag1, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("O".$flag1."", "EXISTENCIAS");
					$anterior->mergeCells('R'.$flag1.':T'.$flag1);
					$this->cellStyle("R".$flag1, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("R".$flag1."", "EXISTENCIAS");
					$anterior->mergeCells('U'.$flag1.':W'.$flag1);
					$this->cellStyle("U".$flag1, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("U".$flag1."", "EXISTENCIAS");
					$anterior->mergeCells('X'.$flag1.':Z'.$flag1);
					$this->cellStyle("X".$flag1, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("X".$flag1."", "EXISTENCIAS");
					$anterior->mergeCells('AA'.$flag1.':AC'.$flag1);
					$this->cellStyle("AA".$flag1, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("AA".$flag1."", "EXISTENCIAS");
					$anterior->mergeCells('AD'.$flag1.':AF'.$flag1);
					$this->cellStyle("AD".$flag1, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("AD".$flag1."", "EXISTENCIAS");
					$anterior->mergeCells('AG'.$flag1.':AI'.$flag1);
					$this->cellStyle("AG".$flag1, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("AG".$flag1."", "EXISTENCIAS");
					$anterior->mergeCells('AJ'.$flag1.':AL'.$flag1);
					$this->cellStyle("AJ".$flag1, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$anterior->setCellValue("AJ".$flag1."", "EXISTENCIAS");
					$this->cellStyle("AM".$flag1, "FFFF00", "FF0000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("AN".$flag1, "92D050", "FF0000", TRUE, 12, "Franklin Gothic Book");
					$flag1++;
					$this->excelfile->getActiveSheet()->getStyle('A'.$flag1.':AN'.$flag1)->applyFromArray($styleArray);
					$anterior->mergeCells('A'.$flag1.':B'.$flag1);
					$this->cellStyle("A".$flag1, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("C".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("C".$flag1."", "PRECIO");
					$this->cellStyle("D".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("D".$flag1."", "SISTEMA");
					$this->cellStyle("E".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("E".$flag1."", "UM");
					$this->cellStyle("F".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("F".$flag1."", "Cajas");
					$this->cellStyle("G".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("G".$flag1."", "Pzs");
					$this->cellStyle("H".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("H".$flag1."", "Pedido");
					$this->cellStyle("I".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("I".$flag1."", "Cajas");
					$this->cellStyle("J".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("J".$flag1."", "Pzs");
					$this->cellStyle("K".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("K".$flag1."", "Pedido");
					$this->cellStyle("L".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("L".$flag1."", "Cajas");
					$this->cellStyle("M".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("M".$flag1."", "Pzs");
					$this->cellStyle("N".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("N".$flag1."", "Pedido");
					$this->cellStyle("O".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("O".$flag1."", "Cajas");
					$this->cellStyle("P".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("P".$flag1."", "Pzs");
					$this->cellStyle("Q".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("Q".$flag1."", "Pedido");
					$this->cellStyle("R".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("R".$flag1."", "Cajas");
					$this->cellStyle("S".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("S".$flag1."", "Pzs");
					$this->cellStyle("T".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("T".$flag1."", "Pedido");
					$this->cellStyle("U".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("U".$flag1."", "Cajas");
					$this->cellStyle("V".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("V".$flag1."", "Pzs");
					$this->cellStyle("W".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("W".$flag1."", "Pedido");
					$this->cellStyle("X".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("X".$flag1."", "Cajas");
					$this->cellStyle("Y".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("Y".$flag1."", "Pzs");
					$this->cellStyle("Z".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("Z".$flag1."", "Pedido");
					$this->cellStyle("AA".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AA".$flag1."", "Cajas");
					$this->cellStyle("AB".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AB".$flag1."", "Pzs");
					$this->cellStyle("AC".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AC".$flag1."", "Pedido");
					$this->cellStyle("AD".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AD".$flag1."", "Cajas");
					$this->cellStyle("AE".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AE".$flag1."", "Pzs");
					$this->cellStyle("AF".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AF".$flag1."", "Pedido");
					$this->cellStyle("AG".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AG".$flag1."", "Cajas");
					$this->cellStyle("AH".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AH".$flag1."", "Pzs");
					$this->cellStyle("AI".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AI".$flag1."", "Pedido");
					$this->cellStyle("AJ".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AJ".$flag1."", "Cajas");
					$this->cellStyle("AK".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AK".$flag1."", "Pzs");
					$this->cellStyle("AL".$flag1, "000000", "FFFFFF", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AL".$flag1."", "Pedido");
					$this->cellStyle("AM".$flag1, "FFFF00", "FF0000", FALSE, 10, "Franklin Gothic Book");
					$this->cellStyle("AN".$flag1, "92D050", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					$this->excelfile->getActiveSheet()->getStyle('AP'.$flag1.':AZ'.$flag1)->applyFromArray($styleArray);
					$this->excelfile->getActiveSheet()->getStyle('BC'.$flag1.':BL'.$flag1)->applyFromArray($styleArray);

					$this->cellStyle("AP".$flag1, "C00000", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("AQ".$flag1, "FF0066", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("AR".$flag1, "01B0F0", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("AS".$flag1, "FF0000", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("AT".$flag1, "E26C0B", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("AU".$flag1, "C5C5C5", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("AV".$flag1, "92D051", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("AW".$flag1, "B1A0C7", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("AX".$flag1, "DA9694", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("AY".$flag1, "4CACC6", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("AZ".$flag1, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
					
					$anterior->setCellValue("AZ".$flag1."", "TOTAL");
					
					$anterior->getColumnDimension('AO')->setWidth("15");
					$anterior->getColumnDimension('AP')->setWidth("15");
					$anterior->getColumnDimension('AQ')->setWidth("15");
					$anterior->getColumnDimension('AR')->setWidth("15");
					$anterior->getColumnDimension('AS')->setWidth("15");
					$anterior->getColumnDimension('AT')->setWidth("15");
					$anterior->getColumnDimension('AU')->setWidth("15");
					$anterior->getColumnDimension('AV')->setWidth("15");
					$anterior->getColumnDimension('AW')->setWidth("15");
					$anterior->getColumnDimension('AX')->setWidth("15");
					$anterior->getColumnDimension('AY')->setWidth("15");
					$anterior->getColumnDimension('AZ')->setWidth("15");
					$anterior->getColumnDimension('BA')->setWidth("15");
					$anterior->getColumnDimension('BB')->setWidth("15");
					$anterior->getColumnDimension('BC')->setWidth("15");
					$anterior->getColumnDimension('BD')->setWidth("15");
					$anterior->getColumnDimension('BE')->setWidth("15");
					$anterior->getColumnDimension('BF')->setWidth("15");
					$anterior->getColumnDimension('BG')->setWidth("15");
					$anterior->getColumnDimension('BH')->setWidth("15");
					$anterior->getColumnDimension('BI')->setWidth("15");
					$anterior->getColumnDimension('BJ')->setWidth("15");
					$anterior->getColumnDimension('BK')->setWidth("15");
					$anterior->getColumnDimension('BL')->setWidth("15");

					$this->cellStyle("BC".$flag1, "C00000", "000000", TRUE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BC".$flag1."", "CEDIS");
					$this->cellStyle("BD".$flag1, "FF0066", "000000", TRUE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BD".$flag1."", "INDUSTRIAL");
					$this->cellStyle("BE".$flag1, "01B0F0", "000000", TRUE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BE".$flag1."", "ABARROTES");
					$this->cellStyle("BF".$flag1, "FF0000", "000000", TRUE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BF".$flag1."", "PEDREGAL");
					$this->cellStyle("BG".$flag1, "E26C0B", "000000", TRUE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BG".$flag1."", "TIENDA");
					$this->cellStyle("BH".$flag1, "C5C5C5", "000000", TRUE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BH".$flag1."", "ULTRAMARINOS");
					$this->cellStyle("BI".$flag1, "92D051", "000000", TRUE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BI".$flag1."", "TRINCHERAS");
					$this->cellStyle("BJ".$flag1, "B1A0C7", "000000", TRUE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BJ".$flag1."", "MERCADO");
					$this->cellStyle("BK".$flag1, "DA9694", "000000", TRUE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BK".$flag1."", "TENENCIA");
					$this->cellStyle("BL".$flag1, "4CACC6", "000000", TRUE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BL".$flag1."", "TIJERAS");
				}
				
				foreach ($infos as $keys => $v) {
					$this->excelfile->setActiveSheetIndex($last);
					$anterior = $this->excelfile->getActiveSheet();
					$flag1++;
					
					$this->excelfile->getActiveSheet()->getStyle('A'.$flag1.':AN'.$flag1)->applyFromArray($styleArray);
					$anterior->setCellValue("A".$flag1."", $v["codigo"])->getStyle("A{$flag1}")->getNumberFormat()->setFormatCode('# ???/???');
					$this->cellStyle("A".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("B".$flag1."", $v["descripcion"]);
					$this->cellStyle("B".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("C{$flag1}", $v["precio"])->getStyle("C{$flag1}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("C".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("D{$flag1}", $v["sistema"])->getStyle("D{$flag1}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("D".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("E{$flag1}", $v["unidad"])->getStyle("E{$flag1}");
					$this->cellStyle("E".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$this->excelfile->getActiveSheet()->getStyle('AP'.$flag1.':AZ'.$flag1)->applyFromArray($styleArray);
					$this->excelfile->getActiveSheet()->getStyle('BC'.$flag1.':BL'.$flag1)->applyFromArray($styleArray);
					$this->cellStyle("F{$flag1}:AN{$flag1}", "FFFFFF", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("AP".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AP{$flag1}", "=C{$flag1}*H{$flag1}")->getStyle("AP{$flag1}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("AQ".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AQ{$flag1}", "=C{$flag1}*N{$flag1}")->getStyle("AQ{$flag1}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("AR".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AR{$flag1}", "=C{$flag1}*Q{$flag1}")->getStyle("AR{$flag1}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("AS".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AS{$flag1}", "=C{$flag1}*T{$flag1}")->getStyle("AS{$flag1}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("AT".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AT{$flag1}", "=C{$flag1}*W{$flag1}")->getStyle("AT{$flag1}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("AU".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AU{$flag1}", "=C{$flag1}*Z{$flag1}")->getStyle("AU{$flag1}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("AV".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AV{$flag1}", "=C{$flag1}*AC{$flag1}")->getStyle("AV{$flag1}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("AW".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AW{$flag1}", "=C{$flag1}*AF{$flag1}")->getStyle("AW{$flag1}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("AX".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AX{$flag1}", "=C{$flag1}*AI{$flag1}")->getStyle("AX{$flag1}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("AY".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AY{$flag1}", "=C{$flag1}*AL{$flag1}")->getStyle("AY{$flag1}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("AZ".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("AZ{$flag1}", "=SUM(AP{$flag1}:AY{$flag1})")->getStyle("AZ{$flag1}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');


					$this->cellStyle("BC".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BC{$flag1}", "=(((F{$flag1}+H{$flag1})*E{$flag1})+G{$flag1})/E{$flag1}");
					$this->cellStyle("BD".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BD{$flag1}", "=(((L{$flag1}+N{$flag1})*E{$flag1})+M{$flag1})/E{$flag1}");
					$this->cellStyle("BE".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BE{$flag1}", "=(((O{$flag1}+Q{$flag1})*E{$flag1})+P{$flag1})/E{$flag1}");
					$this->cellStyle("BF".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BF{$flag1}", "=(((R{$flag1}+T{$flag1})*E{$flag1})+S{$flag1})/E{$flag1}");
					$this->cellStyle("BG".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BG{$flag1}", "=(((U{$flag1}+W{$flag1})*E{$flag1})+V{$flag1})/E{$flag1}");
					$this->cellStyle("BH".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BH{$flag1}", "=(((X{$flag1}+Z{$flag1})*E{$flag1})+Y{$flag1})/E{$flag1}");
					$this->cellStyle("BI".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BI{$flag1}", "=(((AA{$flag1}+AC{$flag1})*E{$flag1})+AB{$flag1})/E{$flag1}");
					$this->cellStyle("BJ".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BJ{$flag1}", "=(((AD{$flag1}+AF{$flag1})*E{$flag1})+AE{$flag1})/E{$flag1}");
					$this->cellStyle("BK".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BK{$flag1}", "=(((AG{$flag1}+AI{$flag1})*E{$flag1})+AH{$flag1})/E{$flag1}");
					$this->cellStyle("BL".$flag1, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$anterior->setCellValue("BL{$flag1}", "=(((AJ{$flag1}+AL{$flag1})*E{$flag1})+AM{$flag1})/E{$flag1}");

					$this->cellStyle("H".$flag1, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("K".$flag1, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("N".$flag1, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("Q".$flag1, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("T".$flag1, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("W".$flag1, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("Z".$flag1, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("AC".$flag1, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("AF".$flag1, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("AI".$flag1, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("AL".$flag1, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					

					$col = 5;
					 foreach ($v["exist"] as $k => $vl) {
						$anterior->setCellValueByColumnAndRow($col, $flag1, $vl["cja"]);
						$col++;
						$anterior->setCellValueByColumnAndRow($col, $flag1, $vl["pzs"]);
						$col++;
						$anterior->setCellValueByColumnAndRow($col, $flag1, $vl["ped"]);
						$col++;
					 }
					 


					$this->excelfile->setActiveSheetIndex($key+1);
					$proveedor[0]->estatus = $this->excelfile->getActiveSheet();
					$flag++;
					$this->excelfile->setActiveSheetIndex($key+1);
					$this->excelfile->getActiveSheet()->getStyle('A'.$flag.':BU'.$flag)->applyFromArray($styleArray);
					$proveedor[$key]->estatus->setCellValue("A".$flag."", $v["codigo"])->getStyle("A{$flag}")->getNumberFormat()->setFormatCode('# ???/???');
					$this->cellStyle("A".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("B".$flag."", $v["descripcion"]);
					$this->cellStyle("B".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("C{$flag}", $v["precio"])->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("C".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("D{$flag}", $v["sistema"])->getStyle("D{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("D".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("E{$flag}", $v["unidad"]);
					$this->cellStyle("E".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");



					$proveedor[$key]->estatus->setCellValue("F{$flag}", "=VLOOKUP(A{$flag},ANTERIOR!A:BL,55,FALSE)")->getStyle("F{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("R{$flag}", "=VLOOKUP(A{$flag},ANTERIOR!A:BL,56,FALSE)")->getStyle("R{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("X{$flag}", "=VLOOKUP(A{$flag},ANTERIOR!A:BL,57,FALSE)")->getStyle("X{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("AD{$flag}", "=VLOOKUP(A{$flag},ANTERIOR!A:BL,58,FALSE)")->getStyle("AD{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("AJ{$flag}", "=VLOOKUP(A{$flag},ANTERIOR!A:BL,59,FALSE)")->getStyle("AJ{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("AP{$flag}", "=VLOOKUP(A{$flag},ANTERIOR!A:BL,60,FALSE)")->getStyle("AP{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("AV{$flag}", "=VLOOKUP(A{$flag},ANTERIOR!A:BL,61,FALSE)")->getStyle("AV{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("BB{$flag}", "=VLOOKUP(A{$flag},ANTERIOR!A:BL,62,FALSE)")->getStyle("BB{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("BH{$flag}", "=VLOOKUP(A{$flag},ANTERIOR!A:BL,63,FALSE)")->getStyle("BH{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("BN{$flag}", "=VLOOKUP(A{$flag},ANTERIOR!A:BL,64,FALSE)")->getStyle("BN{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');

					$proveedor[$key]->estatus->setCellValue("G{$flag}", "=((F{$flag}*E{$flag})-(((I{$flag}*E{$flag})+J{$flag})))/E{$flag}")->getStyle("G{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("S{$flag}", "=((R{$flag}*E{$flag})-(((U{$flag}*E{$flag})+V{$flag})))/E{$flag}")->getStyle("S{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("Y{$flag}", "=((X{$flag}*E{$flag})-(((AA{$flag}*E{$flag})+AB{$flag})))/E{$flag}")->getStyle("Y{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("AE{$flag}", "=((AD{$flag}*E{$flag})-(((AG{$flag}*E{$flag})+AH{$flag})))/E{$flag}")->getStyle("AE{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("AK{$flag}", "=((AJ{$flag}*E{$flag})-(((AM{$flag}*E{$flag})+AN{$flag})))/E{$flag}")->getStyle("AK{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("AQ{$flag}", "=((AP{$flag}*E{$flag})-(((AS{$flag}*E{$flag})+AT{$flag})))/E{$flag}")->getStyle("AQ{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("AW{$flag}", "=((AV{$flag}*E{$flag})-(((AY{$flag}*E{$flag})+AZ{$flag})))/E{$flag}")->getStyle("AW{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("BC{$flag}", "=((BB{$flag}*E{$flag})-(((BE{$flag}*E{$flag})+BF{$flag})))/E{$flag}")->getStyle("BC{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("BI{$flag}", "=((BH{$flag}*E{$flag})-(((BK{$flag}*E{$flag})+BL{$flag})))/E{$flag}")->getStyle("BI{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					$proveedor[$key]->estatus->setCellValue("BO{$flag}", "=((BN{$flag}*E{$flag})-(((BQ{$flag}*E{$flag})+BR{$flag})))/E{$flag}")->getStyle("BO{$flag}")->getNumberFormat()->setFormatCode('#,#0_-');
					
					

					$this->cellStyle("F{$flag}:BU{$flag}", "FFFFFF", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("K".$flag, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("Q".$flag, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("W".$flag, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("AC".$flag, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("AI".$flag, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("AO".$flag, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("AU".$flag, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("BA".$flag, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("BG".$flag, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("BM".$flag, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("BS".$flag, "DCE6F1", "000000", TRUE, 10, "Franklin Gothic Book");


					$this->cellStyle("G".$flag, "FFFF00", "FF0000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("M".$flag, "FFFF00", "FF0000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("S".$flag, "FFFF00", "FF0000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("Y".$flag, "FFFF00", "FF0000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("AE".$flag, "FFFF00", "FF0000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("AK".$flag, "FFFF00", "FF0000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("AQ".$flag, "FFFF00", "FF0000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("AW".$flag, "FFFF00", "FF0000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("BC".$flag, "FFFF00", "FF0000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("BI".$flag, "FFFF00", "FF0000", TRUE, 10, "Franklin Gothic Book");
					$this->cellStyle("BO".$flag, "FFFF00", "FF0000", TRUE, 10, "Franklin Gothic Book");

					$this->cellStyle("H".$flag, "92D050", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("N".$flag, "92D050", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("T".$flag, "92D050", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("Z".$flag, "92D050", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("AF".$flag, "92D050", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("AL".$flag, "92D050", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("AR".$flag, "92D050", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("AX".$flag, "92D050", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("BD".$flag, "92D050", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("BJ".$flag, "92D050", "000000", TRUE, 12, "Franklin Gothic Book");
					$this->cellStyle("BP".$flag, "92D050", "000000", TRUE, 12, "Franklin Gothic Book");

					$this->excelfile->getActiveSheet()->getStyle('BW'.$flag.':CG'.$flag)->applyFromArray($styleArray);
					$this->cellStyle("BW".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BW{$flag}", "=C{$flag}*K{$flag}")->getStyle("BW{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("BX".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BX{$flag}", "=C{$flag}*W{$flag}")->getStyle("BX{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("BY".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BY{$flag}", "=C{$flag}*AC{$flag}")->getStyle("BY{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("BZ".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("BZ{$flag}", "=C{$flag}*AI{$flag}")->getStyle("BZ{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("CA".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("CA{$flag}", "=C{$flag}*AO{$flag}")->getStyle("CA{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("CB".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("CB{$flag}", "=C{$flag}*AU{$flag}")->getStyle("CB{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("CC".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("CC{$flag}", "=C{$flag}*BA{$flag}")->getStyle("CC{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("CD".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("CD{$flag}", "=C{$flag}*BG{$flag}")->getStyle("CD{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("CE".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("CE{$flag}", "=C{$flag}*BM{$flag}")->getStyle("CE{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("CF".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("CF{$flag}", "=C{$flag}*BS{$flag}")->getStyle("CF{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
					$this->cellStyle("CG".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
					$proveedor[$key]->estatus->setCellValue("CG{$flag}", "=SUM(BW{$flag}:CF{$flag})")->getStyle("CG{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');

					$col = 8;
					 foreach ($v["existencias"] as $k => $vs) {
						$proveedor[$key]->estatus->setCellValueByColumnAndRow($col, $flag, $vs["cja"]);
						$col++;
						$proveedor[$key]->estatus->setCellValueByColumnAndRow($col, $flag, $vs["pzs"]);
						$col++;
						$proveedor[$key]->estatus->setCellValueByColumnAndRow($col, $flag, $vs["ped"]);
						$col+=4;
					 }
					 $col = 7;
					 

					 $proveedor[$key]->estatus->setCellValue("H{$flag}", $v["pend"][1]["pend"]);
					 $proveedor[$key]->estatus->setCellValue("Z{$flag}", $v["pend"][4]["pend"]);
					 $proveedor[$key]->estatus->setCellValue("AF{$flag}", $v["pend"][5]["pend"]);
					 $proveedor[$key]->estatus->setCellValue("AL{$flag}", $v["pend"][6]["pend"]);
					 $proveedor[$key]->estatus->setCellValue("AR{$flag}", $v["pend"][7]["pend"]);
					 $proveedor[$key]->estatus->setCellValue("AX{$flag}", $v["pend"][8]["pend"]);
					 $proveedor[$key]->estatus->setCellValue("BD{$flag}", $v["pend"][9]["pend"]);
					 $proveedor[$key]->estatus->setCellValue("BJ{$flag}", $v["pend"][10]["pend"]);
					 $proveedor[$key]->estatus->setCellValue("BP{$flag}", $v["pend"][11]["pend"]);



					 $proveedor[$key]->estatus->setCellValue("L{$flag}", "=F{$flag}+R{$flag}");
					 $proveedor[$key]->estatus->setCellValue("M{$flag}", "=G{$flag}+S{$flag}");
					 $proveedor[$key]->estatus->setCellValue("N{$flag}", "=I{$flag}+U{$flag}");
					 $proveedor[$key]->estatus->setCellValue("O{$flag}", "=J{$flag}+V{$flag}");
					 $proveedor[$key]->estatus->setCellValue("P{$flag}", "=K{$flag}+W{$flag}");
					 $proveedor[$key]->estatus->setCellValue("N{$flag}", "=H{$flag}+T{$flag}");
					
				}
				$flag++;
				$this->excelfile->getActiveSheet()->getStyle('K'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("K".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("K{$flag}", "=SUM(K5:K".($flag-1).")")->getStyle("K{$flag}")->getNumberFormat()->setFormatCode('#0_-');
				$this->excelfile->getActiveSheet()->getStyle('Q'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("Q".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("Q{$flag}", "=SUM(Q5:Q".($flag-1).")")->getStyle("Q{$flag}")->getNumberFormat()->setFormatCode('#0_-');
				$this->excelfile->getActiveSheet()->getStyle('W'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("W".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("W{$flag}", "=SUM(W5:W".($flag-1).")")->getStyle("W{$flag}")->getNumberFormat()->setFormatCode('#0_-');
				$this->excelfile->getActiveSheet()->getStyle('AC'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("AC".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("AC{$flag}", "=SUM(AC5:AC".($flag-1).")")->getStyle("AC{$flag}")->getNumberFormat()->setFormatCode('#0_-');
				$this->excelfile->getActiveSheet()->getStyle('AI'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("AI".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("AI{$flag}", "=SUM(AI5:AI".($flag-1).")")->getStyle("AI{$flag}")->getNumberFormat()->setFormatCode('#0_-');
				$this->excelfile->getActiveSheet()->getStyle('AO'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("AO".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("AO{$flag}", "=SUM(AO5:AO".($flag-1).")")->getStyle("AO{$flag}")->getNumberFormat()->setFormatCode('#0_-');
				$this->excelfile->getActiveSheet()->getStyle('AU'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("AU".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("AU{$flag}", "=SUM(AU5:AU".($flag-1).")")->getStyle("AU{$flag}")->getNumberFormat()->setFormatCode('#0_-');
				$this->excelfile->getActiveSheet()->getStyle('BA'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("BA".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("BA{$flag}", "=SUM(BA5:BA".($flag-1).")")->getStyle("BA{$flag}")->getNumberFormat()->setFormatCode('#0_-');
				$this->excelfile->getActiveSheet()->getStyle('BG'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("BG".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("BG{$flag}", "=SUM(BG5:BG".($flag-1).")")->getStyle("BG{$flag}")->getNumberFormat()->setFormatCode('#0_-');
				$this->excelfile->getActiveSheet()->getStyle('BM'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("BM".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("BM{$flag}", "=SUM(BM5:BM".($flag-1).")")->getStyle("BM{$flag}")->getNumberFormat()->setFormatCode('#0_-');
				$this->excelfile->getActiveSheet()->getStyle('BS'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("BS".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("BS{$flag}", "=SUM(BS5:BS".($flag-1).")")->getStyle("BS{$flag}")->getNumberFormat()->setFormatCode('#0_-');
				$this->excelfile->getActiveSheet()->getStyle('BW'.$flag.':CG'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("BW".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("BW{$flag}", "=SUM(BW5:BW".($flag-1).")")->getStyle("BW{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$this->cellStyle("BX".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("BX{$flag}", "=SUM(BX5:BX".($flag-1).")")->getStyle("BX{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$this->cellStyle("BY".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("BY{$flag}", "=SUM(BY5:BY".($flag-1).")")->getStyle("BY{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$this->cellStyle("BZ".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("BZ{$flag}", "=SUM(BZ5:BZ".($flag-1).")")->getStyle("BZ{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$this->cellStyle("CA".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("CA{$flag}", "=SUM(CA5:CA".($flag-1).")")->getStyle("CA{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$this->cellStyle("CB".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("CB{$flag}", "=SUM(CB5:CB".($flag-1).")")->getStyle("CB{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$this->cellStyle("CC".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("CC{$flag}", "=SUM(CC5:CC".($flag-1).")")->getStyle("CC{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$this->cellStyle("CD".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("CD{$flag}", "=SUM(CD5:CD".($flag-1).")")->getStyle("CD{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$this->cellStyle("CE".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("CE{$flag}", "=SUM(CE5:CE".($flag-1).")")->getStyle("CE{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$this->cellStyle("CF".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("CF{$flag}", "=SUM(CF5:CF".($flag-1).")")->getStyle("CF{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$this->cellStyle("CG".$flag, "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("CG{$flag}", "=SUM(CG5:CG".($flag-1).")")->getStyle("CG{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$totis = $flag;
				$flag+=5;


				$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("B".$flag, "C00000", "000000", TRUE, 12, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("B".$flag, "CEDIS/SUPER");
				$proveedor[$key]->estatus->setCellValue("C{$flag}", "=BW{$totis}")->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$ced=$ced."".$va->alias."!C{$flag}+";
				$flag++;
				$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
				$proveedor[$key]->estatus->setCellValue("B".$flag, "CD INDUSTRIAL");
				$this->cellStyle("B".$flag, "FF0066", "000000", TRUE, 12, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("C{$flag}", "=BX{$totis}")->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$sup=$sup."".$va->alias."!C{$flag}+";
				$flag++;
				$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("B".$flag, "01B0F0", "000000", TRUE, 12, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("B".$flag, "ABARROTES");
				$proveedor[$key]->estatus->setCellValue("C{$flag}", "=BY{$totis}")->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$aba=$aba."".$va->alias."!C{$flag}+";
				$flag++;
				$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("B".$flag, "FF0000", "000000", TRUE, 12, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("B".$flag, "PEDREGAL");
				$proveedor[$key]->estatus->setCellValue("C{$flag}", "=BZ{$totis}")->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$ped=$ped."".$va->alias."!C{$flag}+";
				$flag++;
				$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("B".$flag, "E26C0B", "000000", TRUE, 12, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("B".$flag, "TIENDA");
				$proveedor[$key]->estatus->setCellValue("C{$flag}", "=CA{$totis}")->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$tie=$tie."".$va->alias."!C{$flag}+";
				$flag++;
				$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("B".$flag, "C5C5C5", "000000", TRUE, 12, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("B".$flag, "ULTRAMARINOS");
				$proveedor[$key]->estatus->setCellValue("C{$flag}", "=CB{$totis}")->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$ult=$ult."".$va->alias."!C{$flag}+";
				$flag++;
				$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("B".$flag, "92D051", "000000", TRUE, 12, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("B".$flag, "TRINCHERAS");
				$proveedor[$key]->estatus->setCellValue("C{$flag}", "=CC{$totis}")->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$tri=$tri."".$va->alias."!C{$flag}+";
				$flag++;
				$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("B".$flag, "B1A0C7", "000000", TRUE, 12, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("B".$flag, "AZT MERCADO");
				$proveedor[$key]->estatus->setCellValue("C{$flag}", "=CD{$totis}")->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$mer=$mer."".$va->alias."!C{$flag}+";
				$flag++;
				$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("B".$flag, "DA9694", "000000", TRUE, 12, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("B".$flag, "TENENCIA");
				$proveedor[$key]->estatus->setCellValue("C{$flag}", "=CE{$totis}")->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$ten=$ten."".$va->alias."!C{$flag}+";
				$flag++;
				$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("B".$flag, "4CACC6", "000000", TRUE, 12, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("B".$flag, "TIJERAS");
				$proveedor[$key]->estatus->setCellValue("C{$flag}", "=CF{$totis}")->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
				$tij=$tij."".$va->alias."!C{$flag}+";
				$flag++;
				$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
				$this->cellStyle("B".$flag, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
				$proveedor[$key]->estatus->setCellValue("B".$flag, "TOTAL");
				$proveedor[$key]->estatus->setCellValue("C{$flag}", "=SUM(C".($totis+5).":C".($flag-1).")")->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
			$flag1 += 5;
			}
		}

		$this->excelfile->setActiveSheetIndex(0);

		$flag = 2;
		$totales->getColumnDimension('B')->setWidth("40");
		$totales->getColumnDimension('C')->setWidth("20");
		$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
		$this->cellStyle("B".$flag, "C00000", "000000", TRUE, 12, "Franklin Gothic Book");
		$totales->setCellValue("B".$flag, "CEDIS/SUPER");
		$totales->setCellValue("C{$flag}", substr($ced, 0, -1))->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
		
		$flag++;
		$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
		$totales->setCellValue("B".$flag, "CD INDUSTRIAL");
		$this->cellStyle("B".$flag, "FF0066", "000000", TRUE, 12, "Franklin Gothic Book");
		$sup = substr_replace($sup ,"", -1);
		$totales->setCellValue("C{$flag}", substr($sup, 0, -1))->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
		
		$flag++;
		$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
		$this->cellStyle("B".$flag, "01B0F0", "000000", TRUE, 12, "Franklin Gothic Book");
		$totales->setCellValue("B".$flag, "ABARROTES");
		$totales->setCellValue("C{$flag}", substr($aba, 0, -1))->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
		
		$flag++;
		$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
		$this->cellStyle("B".$flag, "FF0000", "000000", TRUE, 12, "Franklin Gothic Book");
		$totales->setCellValue("B".$flag, "PEDREGAL");
		$totales->setCellValue("C{$flag}", substr($ped, 0, -1))->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
		
		$flag++;
		$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
		$this->cellStyle("B".$flag, "E26C0B", "000000", TRUE, 12, "Franklin Gothic Book");
		$totales->setCellValue("B".$flag, "TIENDA");
		$totales->setCellValue("C{$flag}", substr($tie, 0, -1))->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
		
		$flag++;
		$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
		$this->cellStyle("B".$flag, "C5C5C5", "000000", TRUE, 12, "Franklin Gothic Book");
		$totales->setCellValue("B".$flag, "ULTRAMARINOS");
		$totales->setCellValue("C{$flag}", substr($ult, 0, -1))->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
		
		$flag++;
		$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
		$this->cellStyle("B".$flag, "92D051", "000000", TRUE, 12, "Franklin Gothic Book");
		$totales->setCellValue("B".$flag, "TRINCHERAS");
		$totales->setCellValue("C{$flag}", substr($tri, 0, -1))->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
		
		$flag++;
		$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
		$this->cellStyle("B".$flag, "B1A0C7", "000000", TRUE, 12, "Franklin Gothic Book");
		$totales->setCellValue("B".$flag, "AZT MERCADO");
		$totales->setCellValue("C{$flag}", substr($mer, 0, -1))->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
		
		$flag++;
		$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
		$this->cellStyle("B".$flag, "DA9694", "000000", TRUE, 12, "Franklin Gothic Book");
		$totales->setCellValue("B".$flag, "TENENCIA");
		$totales->setCellValue("C{$flag}", substr($ten, 0, -1))->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
		
		$flag++;
		$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
		$this->cellStyle("B".$flag, "4CACC6", "000000", TRUE, 12, "Franklin Gothic Book");
		$totales->setCellValue("B".$flag, "TIJERAS");
		$totales->setCellValue("C{$flag}", substr($tij, 0, -1))->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
		
		$flag++;
		$this->excelfile->getActiveSheet()->getStyle('B'.$flag.':C'.$flag)->applyFromArray($styleArray);
		$this->cellStyle("B".$flag, "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
		$totales->setCellValue("B".$flag, "TOTAL");
		$totales->setCellValue("C{$flag}", "=SUM(C2:C11)")->getStyle("C{$flag}")->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
		//$this->jsonResponse($sup);

        $dias = array("DOMINGO","LUNES","MARTES","MIÉRCOLES","JUEVES","VIERNES","SÁBADO");
		$meses = array("ENERO","FEBRERO","MARZO","ABRIL","MAYO","JUNIO","JULIO","AGOSTO","SEPTIEMBRE","OCTUBRE","NOVIEMBRE","DICIEMBRE");
		$fecha =  $dias[date('w')]." ".date('d')." DE ".$meses[date('n')-1]. " DEL ".date('Y') ;
		$file_name = "FORMATO LUNES ".$fecha.".xlsx"; //Nombre del documento con extención
		header("Content-Type: application/vnd.ms-excel; charset=utf-8");
		header("Content-Disposition: attachment;filename=".$file_name);
		header("Cache-Control: max-age=0");
		$excel_Writer = PHPExcel_IOFactory::createWriter($this->excelfile, "Excel2007");
		$excel_Writer->save("php://output");
	}

	public function upload_pedidos(){
		$fecha = new DateTime(date('Y-m-d H:i:s'));
		$intervalo = new DateInterval('P1D');
		$fecha->add($intervalo);
		$this->load->library("excelfile");
		ini_set("memory_limit", -1);
		$file = $_FILES["file_cotizaciones"]["tmp_name"];
		$sheet = PHPExcel_IOFactory::load($file);
		$objExcel = PHPExcel_IOFactory::load($file);
		$sheet = $objExcel->getSheet(0);
		$num_rows = $sheet->getHighestDataRow();
		$tienda = $this->session->userdata('id_usuario');
		
		$cfile =  $this->user_md->get(NULL, ['id_usuario' => $tienda])[0];
		$nams = preg_replace('/\s+/', '_', $cfile->nombre);
		$filen = "PedidosLunes".$nams."".rand();
		$config['upload_path']          = base_url('/assets/uploads/pedidos/');
        $config['allowed_types']        = 'xlsx|xls';
        $config['max_size']             = 1000;
        $config['max_width']            = 10024;
        $config['max_height']           = 7680;
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->do_upload('file_cotizaciones',$filen);
		for ($i=1; $i<=$num_rows; $i++) {
			$productos = $this->prolu_md->get("codigo",['codigo'=> htmlspecialchars($sheet->getCell('D'.$i)->getValue(), ENT_QUOTES, 'UTF-8')])[0];
			if (sizeof($productos) > 0) {
				$exis = $this->ex_lun_md->get(NULL,["WEEKOFYEAR(fecha_registro)" => $this->weekNumber($fecha->format('Y-m-d H:i:s')),"id_tienda"=>$tienda,"id_producto"=>$productos->codigo])[0];
				$column_one=0; $column_two=0; $column_three=0;
				$column_one = $sheet->getCell('A'.$i)->getValue() == "" ? 0 : $sheet->getCell('A'.$i)->getValue();
				$column_two = $sheet->getCell('B'.$i)->getValue() == "" ? 0 : $sheet->getCell('B'.$i)->getValue();
				$column_three = $sheet->getCell('C'.$i)->getValue() == "" ? 0 : $sheet->getCell('C'.$i)->getValue();
				$new_existencias[$i]=[
					"id_producto"			=>	$productos->codigo,
					"id_tienda"			=>	$tienda,
					"cajas"			=>	$column_one,
					"piezas"			=>	$column_two,
					"pedido"	=>	$column_three,
					"fecha_registro"	=>	$fecha->format('Y-m-d H:i:s')
				];
				if($exis){
					//$data['cotizacion']=$this->ex_lun_md->update($new_existencias[$i], ['id_pedido' => $exis->id_existencia]);
				}else{
					$data['cotizacion']=$this->ex_lun_md->insert($new_existencias[$i]);
				}
			}
		}
		if (isset($new_existencias)) {
			$aprov = $this->user_md->get(NULL, ['id_usuario'=>$tienda])[0];
			$cambios=[
					"id_usuario"		=>	$this->session->userdata('id_usuario'),
					"fecha_cambio"		=>	date("Y-m-d H:i:s"),
					"antes"			=>	"El usuario sube archivo de pedidos de la tienda ".$aprov->nombre,
					"despues"			=>	"assets/uploads/pedidos/".$filen.".xlsx",
					"accion"			=>	"Sube existencias y pedidos formato LUNES"
				];
			$data['cambios']=$this->cambio_md->insert($cambios);
			$mensaje=[	"id"	=>	'Éxito',
						"desc"	=>	'Existencias y pedidos cargados correctamente en el Sistema',
						"type"	=>	'success'];
		}else{
			$mensaje=[	"id"	=>	'Error',
						"desc"	=>	'Existencias y pedidos no se cargaron al Sistema',
						"type"	=>	'error'];
		}
		$this->jsonResponse($mensaje);
	}

	public function lunpedido(){
		$user = $this->session->userdata();
		$data["title"]="LISTADO EXISTENCIAS FORMATO LUNES";
		$data["cuantas"] = $this->ex_lun_md->getCuantasTienda(NULL,$user["id_usuario"])[0];
		$data["noprod"] = $this->prolu_md->getCount(NULL)[0];
		$tienda = $this->suc_md->get(NULL,["sucu"=> $user["id_usuario"]])[0];
		$data["existencias"] = $this->ex_lun_md->getLunExist(NULL,$user["id_usuario"]);
		$data["existenciasnot"] = $this->ex_lun_md->getLunExistNot(NULL,$user["id_usuario"]);
		$data["view"]=$this->load->view("Lunes/lunpedido", $data, TRUE);
		
		$this->jsonResponse($data);
	}

	public function volpedido(){
		$user = $this->session->userdata();
		$data["title"]="LISTADO EXISTENCIAS VOLÚMENES";
		$data["noprod"] = $this->prod_mdl->getVolCount(NULL)[0];
		$data["cuantas"] = $this->ex_lun_md->getVolTienda(NULL,$user["id_usuario"])[0];
		$data["existencias"] = $this->ex_lun_md->getVolExist(NULL,$user["id_usuario"]);
		$data["existenciasnot"] = $this->ex_lun_md->getVolExistNot(NULL,$user["id_usuario"]);
		$data["view"]=$this->load->view("Lunes/volpedido", $data, TRUE);
		
		$this->jsonResponse($data);
	}

	public function allpedido(){
		$user = $this->session->userdata();
		$data["title"]="LISTADO EXISTENCIAS GENERAL";
		$data["noprod"] = $this->prod_mdl->getAllCount(NULL)[0];
		$data["cuantas"] = $this->ex_lun_md->getAllTienda(NULL,$user["id_usuario"])[0];
		$data["existencias"] = $this->ex_lun_md->getAllExist(NULL,$user["id_usuario"]);
		$data["existenciasnot"] = $this->ex_lun_md->getAllExistNot(NULL,$user["id_usuario"]);
		$data["view"]=$this->load->view("Lunes/allpedido", $data, TRUE);
		
		$this->jsonResponse($data);
	}

	public function pendientes(){
		$data['links'] = [
			'/assets/css/plugins/dataTables/dataTables.bootstrap',
			'/assets/css/plugins/dataTables/dataTables.responsive',
			'/assets/css/plugins/dataTables/dataTables.tableTools.min',
			'/assets/css/plugins/dataTables/buttons.dataTables.min',
		];

		$data['scripts'] = [
			'/scripts/pendlunes',
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
		$this->estructura("Lunes/pendientes", $data);
	}

	public function upload_pendientes(){
		$fecha = new DateTime(date('Y-m-d H:i:s'));
		$intervalo = new DateInterval('P2D');
		$fecha->add($intervalo);
		$this->load->library("excelfile");
		ini_set("memory_limit", -1);
		$filen = "Pedidos Pendientes".rand();
		$config['upload_path']          = base_url('/assets/uploads/pedidos/');
        $config['allowed_types']        = 'xlsx|xls';
        $config['max_size']             = 100;
        $config['max_width']            = 1024;
        $config['max_height']           = 768;
        $config['max_height']           = 768;
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->do_upload('file_pendientes',$filen);
        $file = $_FILES["file_pendientes"]["tmp_name"];
		$sheet = PHPExcel_IOFactory::load($file);
		$objExcel = PHPExcel_IOFactory::load($file);
		$sheet = $objExcel->getSheet(0);
		$new_pendientes = [];
		$num_rows = $sheet->getHighestDataRow();
		for ($i=3; $i<=$num_rows; $i++) {
			$productos = $this->prolu_md->get("codigo",['codigo'=> htmlspecialchars($sheet->getCell('A'.$i)->getValue(), ENT_QUOTES, 'UTF-8')])[0];

			if (sizeof($productos) > 0) {
				$exis = $this->pend_mdl->get(NULL,["WEEKOFYEAR(fecha_registro)" => $this->weekNumber($fecha->format('Y-m-d H:i:s')),"id_producto"=>$productos->codigo])[0];
				$cedis = $sheet->getCell('C'.$i)->getValue() == "" ? 0 : $sheet->getCell('C'.$i)->getValue();
				$abarrotes = $sheet->getCell('D'.$i)->getValue() == "" ? 0 : $sheet->getCell('D'.$i)->getValue();
				$pedregal = $sheet->getCell('E'.$i)->getValue() == "" ? 0 : $sheet->getCell('E'.$i)->getValue();
				$tienda = $sheet->getCell('F'.$i)->getValue() == "" ? 0 : $sheet->getCell('F'.$i)->getValue();
				$ultra = $sheet->getCell('G'.$i)->getValue() == "" ? 0 : $sheet->getCell('G'.$i)->getValue();
				$trincheras = $sheet->getCell('H'.$i)->getValue() == "" ? 0 : $sheet->getCell('H'.$i)->getValue();
				$mercado = $sheet->getCell('I'.$i)->getValue() == "" ? 0 : $sheet->getCell('I'.$i)->getValue();
				$tenencia = $sheet->getCell('J'.$i)->getValue() == "" ? 0 : $sheet->getCell('J'.$i)->getValue();
				$tijeras = $sheet->getCell('K'.$i)->getValue() == "" ? 0 : $sheet->getCell('K'.$i)->getValue();
				$new_pendientes[$i]=[
					"id_producto" => $productos->codigo,
					"cedis" => $cedis,
					"abarrotes" => $abarrotes,
					"pedregal" => $pedregal,
					"tienda" => $tienda,
					"trincheras" => $trincheras,
					"ultra" => $ultra,
					"mercado" => $mercado,
					"tenencia" => $tenencia,
					"tijeras" => $tijeras,
					"fecha_registro" => $fecha->format('Y-m-d H:i:s')
				];
				if($exis){
					$data['pendientes']=$this->pend_mdl->update($new_pendientes[$i], ['id_pendiente' => $exis->id_pendiente]);
				}else{
					$data['pendientes']=$this->pend_mdl->insert($new_pendientes[$i]);
				}
			}
		}
		if (sizeof($new_pendientes) > 0) {
			$cambios=[
					"id_usuario"		=>	$this->session->userdata('id_usuario'),
					"fecha_cambio"		=>	date("Y-m-d H:i:s"),
					"antes"			=>	"El usuario sube pedidos pendientes LUNES",
					"despues"			=>	"assets/uploads/pedidos/".$filen.".xlsx",
					"accion"			=>	"Sube Pedidos Pendientes"
				];
			$data['cambios']=$this->cambio_md->insert($cambios);
			$mensaje=[	"id"	=>	'Éxito',
						"desc"	=>	'Pedidos Pendientes LUNES cargados correctamente en el Sistema',
						"type"	=>	'success'];
		}else{
			$mensaje=[	"id"	=>	'Error',
						"desc"	=>	'Los Pedidos Pendientes LUNES no se cargaron al Sistema',
						"type"	=>	'error'];
		}
		$this->jsonResponse($mensaje);
	}

	public function getPendientes(){
		$fecha = new DateTime(date('Y-m-d H:i:s'));
		$intervalo = new DateInterval('P2D');
		$fecha->add($intervalo);
		$data["pendientes"] =  $this->pend_mdl->getThem(["WEEKOFYEAR(pp.fecha_registro)" => $this->weekNumber($fecha->format('Y-m-d H:i:s'))]);
		$this->jsonResponse($data);
	}

	public function lunpedid($id_tienda){
		$user = $this->session->userdata();
		$data["title"]="LISTADO EXISTENCIAS FORMATO LUNES";
		$data["cuantas"] = $this->ex_lun_md->getCuantasTienda(NULL,$id_tienda)[0];
		$data["noprod"] = $this->prolu_md->getCount(NULL)[0];
		$tienda = $this->suc_md->get(NULL,["sucu"=> $id_tienda])[0];
		$data["existencias"] = $this->ex_lun_md->getLunExist(NULL,$id_tienda);
		$data["existenciasnot"] = $this->ex_lun_md->getLunExistNot(NULL,$id_tienda);
		$data["view"]=$this->load->view("Lunes/lunpedido", $data, TRUE);
		$this->jsonResponse($data);
	}

	public function volpedid($id_tienda){
		$user = $this->session->userdata();
		$data["title"]="LISTADO EXISTENCIAS VOLÚMENES";
		$data["noprod"] = $this->prod_mdl->getVolCount(NULL)[0];
		$data["cuantas"] = $this->ex_lun_md->getVolTienda(NULL,$id_tienda)[0];
		$data["existencias"] = $this->ex_lun_md->getVolExist(NULL,$id_tienda);
		$data["existenciasnot"] = $this->ex_lun_md->getVolExistNot(NULL,$id_tienda);
		$data["view"]=$this->load->view("Lunes/volpedido", $data, TRUE);
		
		$this->jsonResponse($data);
	}

	public function allpedid($id_tienda){
		$user = $this->session->userdata();
		$data["title"]="LISTADO EXISTENCIAS GENERAL";
		$data["noprod"] = $this->prod_mdl->getAllCount(NULL)[0];
		$data["cuantas"] = $this->ex_lun_md->getAllTienda(NULL,$id_tienda)[0];
		$data["existencias"] = $this->ex_lun_md->getAllExist(NULL,$id_tienda);
		$data["existenciasnot"] = $this->ex_lun_md->getAllExistNot(NULL,$id_tienda);
		$data["view"]=$this->load->view("Lunes/allpedido", $data, TRUE);
		
		$this->jsonResponse($data);
	}
}

/* End of file Lunes.php */
/* Location: ./application/controllers/Lunes.php */
