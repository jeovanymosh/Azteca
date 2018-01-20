<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cotizaciones extends MY_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model("Cotizaciones_model", "ct_mdl");
		$this->load->model("Productos_model", "prod_mdl");
	}

	public function index(){
		$data['links'] = [
			'/assets/css/plugins/dataTables/dataTables.bootstrap',
			'/assets/css/plugins/dataTables/dataTables.responsive',
			'/assets/css/plugins/dataTables/dataTables.tableTools.min',
			'/assets/css/plugins/dataTables/buttons.dataTables.min',
		];
		$data['scripts'] = [
			'/scripts/cotizaciones',
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
		$user = $this->ion_auth->user()->row();//Obtenemos el usuario logeado 
		$where = [];
		if(! $this->ion_auth->is_admin()){//Solo mostrar sus Productos cotizados cuando es proveedor
			$where = [
				"cotizaciones.id_proveedor" => $user->id,
				"WEEKOFYEAR(cotizaciones.fecha_registro) >=" => $this->weekNumber()
			];
			$data["cotizaciones"] = $this->ct_mdl->getCotizaciones($where);
		}
		$week=["WEEKOFYEAR(ctz_first.fecha_registro) >=" => ($this->weekNumber()-1)];//Semana actual
		$data["cotizacionesProveedor"] = $this->ct_mdl->comparaCotizaciones($week);
		$this->estructura("Cotizaciones/table_cotizaciones", $data, FALSE);
	}

	public function add_cotizacion(){
		$data["title"]="REGISTRAR COTIZACIONES";
		$data["productos"] = $this->prod_mdl->get("id_producto, nombre");
		$data["view"]=$this->load->view("Cotizaciones/new_cotizacion", $data, TRUE);
		$data["button"]="<button class='btn btn-success new_cotizacion' type='button'>
							<span class='bold'><i class='fa fa-floppy-o'></i></span> &nbsp;Guardar
						</button>";
		$this->jsonResponse($data);
	}

	public function save(){
		$cotizacion = [
			'nombre'			=>	strtoupper($this->input->post('nombre')),
			'id_producto'		=>	$this->input->post('id_producto'),
			'id_proveedor'		=>	$this->ion_auth->user()->row()->id,
			'num_one'			=>	str_replace(',', '', $this->input->post('num_one')),
			'num_two'			=>	str_replace(',', '', $this->input->post('num_two')),
			'precio'			=>	str_replace(',', '', $this->input->post('precio')),//precio base
			'precio_promocion'	=>	($this->input->post('precio_promocion') > 0) ? str_replace(',', '', $this->input->post('precio_promocion')) : str_replace(',', '', $this->input->post('precio')),//precio con promoción
			'descuento'			=>	str_replace(',', '', $this->input->post('porcentaje')),
			'fecha_registro'	=>	date('Y-m-d H:i:s'),
			'fecha_caduca'		=>	($this->input->post('fecha_caducidad') !='') ? date('Y-m-d', strtotime($this->input->post('fecha_caducidad'))) : NULL,
			'existencias'		=>	str_replace(',', '',$this->input->post('existencias')),
			'observaciones'		=>	strtoupper($this->input->post('observaciones'))
		];
		$data ['id_cotizacion']=$this->ct_mdl->insert($cotizacion);
		$mensaje = [
			"id" 	=> 'Éxito',
			"desc"	=> 'Cotización registrada correctamente',
			"type"	=> 'success'
		];
		$this->jsonResponse($mensaje);
	}

	public function update(){
		$cotizacion = [
			'nombre'			=>	strtoupper($this->input->post('nombre')),
			'num_one'			=>	str_replace(',', '', $this->input->post('num_one')),
			'num_two'			=>	str_replace(',', '', $this->input->post('num_two')),
			'precio'			=>	str_replace(',', '', $this->input->post('precio')),
			'precio_promocion'	=>	($this->input->post('precio_promocion') > 0) ? str_replace(',', '', $this->input->post('precio_promocion')) : str_replace(',', '', $this->input->post('precio')),
			'descuento'			=>	str_replace(',', '', $this->input->post('porcentaje')),
			'fecha_cambio'		=>	date('Y-m-d H:i:s'),
			'fecha_caduca'		=>	date('Y-m-d', strtotime($this->input->post('fecha_caducidad'))),
			'existencias'		=>	str_replace(',', '',$this->input->post('existencias')),
			'observaciones'		=>	strtoupper($this->input->post('observaciones'))
		];
		$data ['id_cotizacion'] = $this->ct_mdl->update($cotizacion, $this->input->post('id_cotizacion'));
		$mensaje = [
			"id" 	=> 'Éxito',
			"desc"	=> 'Cotización actualizada correctamente',
			"type"	=> 'success'
		];
		$this->jsonResponse($mensaje);
	}

	public function delete(){
		$data ['id_cotizacion'] = $this->ct_mdl->update(["estatus" => 0], $this->input->post('id_cotizacion'));
		$mensaje = [
			"id" 	=> 'Éxito',
			"desc"	=> 'Cotización eliminada correctamente',
			"type"	=> 'success'
		];
		$this->jsonResponse($mensaje);
	}

	public function get_update($id){
		$data["title"]="ACTUALIZAR COTIZACIÓN";
		$data["cotizacion"] = $this->ct_mdl->getCotizaciones(['id_cotizacion'=>$id])[0];
		$data["productos"] = $this->prod_mdl->get("id_producto, nombre");
		$data["view"]=$this->load->view("Cotizaciones/edit_cotizacion", $data, TRUE);
		$data["button"]="<button class='btn btn-success update_cotizacion' type='button'>
							<span class='bold'><i class='fa fa-floppy-o'></i></span> &nbsp;Guardar cambios
						</button>";
		$this->jsonResponse($data);
	}

	public function get_delete($id){
		$data["title"]="COTIZACIÓN A ELIMINAR";
		$data["cotizacion"] = $this->ct_mdl->get(NULL, ['id_cotizacion'=>$id])[0];
		$data["producto"] = $this->prod_mdl->get(NULL, ['id_producto'=>$data["cotizacion"]->id_producto])[0];
		$data["view"]=$this->load->view("Cotizaciones/delete_cotizacion", $data, TRUE);
		$data["button"]="<button class='btn btn-danger delete_cotizacion' type='button'>
							<span class='bold'><i class='fa fa-times'></i></span> &nbsp;Aceptar
						</button>";
		$this->jsonResponse($data);
	}
	
	public function upload_precios(){
		set_time_limit(300);
		ini_get("memory_limit");
		ini_set("memory_limit","512M");
		ini_get("memory_limit");
		$this->load->helper("path");
		$this->load->library("upload");

		$config=["upload_path"	=>	'./assets/uploads/',
				"file_name"		=>	$_FILES['file_precios']['name'],
				"allowed_types"	=>	'csv',
				"remove_spaces"	=>	TRUE,
				"overwrite"		=>	TRUE ];
		
		$this->upload->initialize($config);

		if (! $this->upload->do_upload('file_precios')){
			$mensaje= [	"id" 	=>	'Error',
						"desc"	=>	$this->upload->display_errors(),
						"type"	=>	 'error'];
		}else{
			$file_name = $this->upload->data('full_path');
			$cont =0;
			$open_file = fopen($file_name,'r') or die("No se puede abrir el archivo");
			while(($file_csv = fgetcsv($open_file, 0,",","\n")) !== FALSE){
				if($cont === 0){//Son los encabezados del archivo
				
				}else{
					if($file_csv[1] !== ''){
						$productos = $this->prod_mdl->get("id_producto",['nombre'=> htmlspecialchars($file_csv[1], ENT_QUOTES, 'UTF-8')])[0];
						if(sizeof($productos) > 0){
							$new_precios=[	"precio_sistema"=>	$file_csv[2],
											"precio_four"	=>	$file_csv[3] ];
						}
						$data['cotizacion']=$this->ct_mdl->update($new_precios, 
							['fecha_registro >=' =>'2018-01-14', 'id_producto'=>$productos->id_producto]);
							// ['fecha_registro >=' =>'2018-01-14', 'id_producto'=>$this->prod_mdl->get("id_producto",['nombre'=> htmlspecialchars($file_csv[1], ENT_QUOTES, 'UTF-8')])[0]->id_producto]);
						echo "{$this->db->last_query()} <br>";
					}
				}
				$cont++;
			}
			fclose($open_file);//Cerramos el archivo
			// $data['cotizacion']=$this->ct_mdl->update_batch($new_precios, 'id_producto');
			$mensaje=[	"id"	=>	'Éxito',
						"desc"	=>	'Precios cargados correctamente en el Sistema',
						"type"	=>	'success'];
		}
		$this->jsonResponse($mensaje);
	}

	public function fill_excel(){
		ini_get("memory_limit");
		ini_set("memory_limit","256M");
		ini_get("memory_limit");
		$this->load->library("excelfile");

		$this->excelfile->setActiveSheetIndex(0)->mergeCells('A1:C1')->setCellValue("A1", 'LISTADO DE COTIZACIONES');//Título del archivo
		$hoja = $this->excelfile->getActiveSheet();
		
		$hoja->setCellValue("A2", "FAMILIAS")->getColumnDimension('A')->setWidth(20); //Nombre y ajuste de texto a la columna
		$hoja->setCellValue("B2", "CÓDIGO")->getColumnDimension('B')->setWidth(20);
		$hoja->setCellValue("C2", "DESCRIPCIÓN")->getColumnDimension('C')->setWidth(40);
		$hoja->setCellValue("D2", "SISTEMA")->getColumnDimension('D')->setWidth(15);
		$hoja->setCellValue("E2", "PRECIO 4")->getColumnDimension('E')->setWidth(15);
		$hoja->setCellValue("F2", "PRECIO MENOR")->getColumnDimension('F')->setWidth(15);
		$hoja->setCellValue("G2", "PROVEEDOR")->getColumnDimension('G')->setWidth(20);
		$hoja->setCellValue("H2", "PRECIO MAXIMO")->getColumnDimension('H')->setWidth(15);
		$hoja->setCellValue("I2", "PRECIO PROMEDIO")->getColumnDimension('I')->setWidth(15);
		$hoja->setCellValue("J2", "PRECIO 2DO")->getColumnDimension('J')->setWidth(15);
		$hoja->setCellValue("K2", "2DO PROVEEDOR")->getColumnDimension('K')->setWidth(20);
		$hoja->setCellValue("L2", "PROMOCIÓN")->getColumnDimension('L')->setWidth(35);

		$week=["WEEKOFYEAR(ctz_first.fecha_registro) >=" => ($this->weekNumber()-1)];//Semana actual
		$cotizacionesProveedor = $this->ct_mdl->comparaCotizaciones($week);

		$row_print =3; $merge =3;
		if ($cotizacionesProveedor){
			foreach ($cotizacionesProveedor as $key => $value){
				$hoja->setCellValue("A{$row_print}", $value['familia'])->getStyle("A{$row_print}")->getAlignment()->setWrapText(true);
				$begin = $row_print;
				$merge = 0;
				if ($value['articulos']) {
					foreach ($value['articulos'] as $key => $row){
						$hoja->setCellValue("B{$row_print}", htmlspecialchars($row['codigo'], ENT_QUOTES,'UTF-8'))->getStyle("B{$row_print}")->getAlignment()->setWrapText(true);
						$hoja->setCellValue("C{$row_print}", $row['producto'])->getStyle("C{$row_print}")->getAlignment()->setWrapText(true);
						$hoja->setCellValue("D{$row_print}", number_format($row['precio_sistema'],2,'.',','))->getStyle("D{$row_print}")->getAlignment()->setWrapText(true);
						$hoja->setCellValue("E{$row_print}", number_format($row['precio_four'],2,'.',','))->getStyle("E{$row_print}")->getAlignment()->setWrapText(true);
						$hoja->setCellValue("F{$row_print}", number_format($row['precio_first'], 2, '.', ','))->getStyle("F{$row_print}")->getAlignment()->setWrapText(true);
						$hoja->setCellValue("G{$row_print}", $row['proveedor_first'])->getStyle("G{$row_print}")->getAlignment()->setWrapText(true);
						$hoja->setCellValue("H{$row_print}", number_format($row['precio_maximo'], 2, '.', ','))->getStyle("H{$row_print}")->getAlignment()->setWrapText(true);
						$hoja->setCellValue("I{$row_print}", number_format($row['precio_promedio'], 2, '.', ','))->getStyle("I{$row_print}")->getAlignment()->setWrapText(true);
						$hoja->setCellValue("J{$row_print}", number_format($row['precio_next'], 2, '.', ','))->getStyle("J{$row_print}")->getAlignment()->setWrapText(true);
						$hoja->setCellValue("K{$row_print}", $row['proveedor_next'])->getStyle("K{$row_print}")->getAlignment()->setWrapText(true);
						$hoja->setCellValue("L{$row_print}", $row['promocion_first'])->getStyle("L{$row_print}")->getAlignment()->setWrapText(true);
						$row_print ++;
						$merge ++;
					}
					$merge +=($begin -1);
				}
				$hoja->mergeCells("A{$begin}:A".$merge)->getStyle("A{$begin}")->getAlignment()->setWrapText(true);
			}
		}
		$row_print +=1;
		$file_name = "Cotizaciones.xls"; //Nombre del documento con extención
		header("Content-Type: application/vnd.ms-excel; charset=utf-8");
		header("Content-Disposition: attachment;filename=".$file_name);
		header("Cache-Control: max-age=0");
		$excel_Writer = PHPExcel_IOFactory::createWriter($this->excelfile, "Excel5");
		$excel_Writer->save("php://output");
	}

	public function upload_cotizaciones(){
		$this->load->library("excelfile");
		ini_set("memory_limit", "-1");
		$file = $_FILES["file_cotizaciones"]["tmp_name"];
		$sheet = PHPExcel_IOFactory::load($file);
		$sheet->setActiveSheetIndex(0);
		$num_rows = $sheet->setActiveSheetIndex(0)->getHighestDataRow();
		for ($i=3; $i<=$num_rows; $i++) { 
			if($sheet->getActiveSheet()->getCell('B'.$i)->getCalculatedValue() !=''){
				$productos = $this->prod_mdl->get("id_producto",['nombre'=> htmlspecialchars($sheet->getActiveSheet()->getCell('A'.$i)->getCalculatedValue(), ENT_QUOTES, 'UTF-8')])[0];
				if (sizeof($productos) > 0) {
					$new_cotizacion[$i]=[
						"id_producto"		=>	$productos->id_producto,
						"id_proveedor"		=>	$this->ion_auth->user()->row()->id,
						"precio"			=>	str_replace("$", "", str_replace(",", "replace", $sheet->getActiveSheet()->getCell('B'.$i)->getCalculatedValue())),
						"precio_promocion"	=>	($sheet->getActiveSheet()->getCell('C'.$i)->getCalculatedValue() == '') ? NULL : str_replace("$", "", str_replace(",", "replace", $sheet->getActiveSheet()->getCell('B'.$i)->getCalculatedValue())),
						"fecha_registro"	=>	date('Y-m-d H:i:s'),
						"observaciones"		=>	$sheet->getActiveSheet()->getCell('C'.$i)->getCalculatedValue()
					];
				}
			}
		}
		if (sizeof($new_cotizacion) > 0) {
			$data['cotizacion']=$this->ct_mdl->insert_batch($new_cotizacion);
			$mensaje=[	"id"	=>	'Éxito',
						"desc"	=>	'Cotizaciones cargadas correctamente en el Sistema',
						"type"	=>	'success'];
		}else{
			$mensaje=[	"id"	=>	'Error',
						"desc"	=>	'Las Cotizaciones no se cargaron al Sistema',
						"type"	=>	'error'];
		}
		$this->jsonResponse($mensaje);
	}

}

/* End of file Cotizaciones.php */
/* Location: ./application/controllers/Cotizaciones.php */