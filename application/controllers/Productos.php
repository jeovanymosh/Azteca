<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Productos extends MY_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model("Productos_model", "pro_md");
		$this->load->model("Familias_model", "fam_md");
		$this->load->model("Cambios_model", "cambio_md");
	}

	public function index(){
		$data['links'] = [
			'/assets/css/plugins/dataTables/dataTables.bootstrap',
			'/assets/css/plugins/dataTables/dataTables.responsive',
			'/assets/css/plugins/dataTables/dataTables.tableTools.min',
			'/assets/css/plugins/dataTables/buttons.dataTables.min',
		];

		$data['scripts'] = [
			'/scripts/productos',
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
		// $data["productos"]=$this->pro_md->getProductos();
		$this->estructura("Productos/table_productos", $data);
	}

	public function productos_dataTable(){
		$search = ["productos.codigo", "productos.nombre", "fam.nombre"];

		$columns = "productos.id_producto, productos.nombre AS producto, productos.codigo, fam.nombre AS familia,productos.color";

		$joins = [
			["table"	=>	"familias fam",	"ON"	=>	"productos.id_familia = fam.id_familia",	"clausula"	=>	"INNER"]
		];

		$group ="productos.id_producto";
		$order="productos.id_producto";

		$where = [
				["clausula"	=>	"productos.estatus <>",	"valor"	=>	0]
		];

		$productos = $this->pro_md->get_pagination($columns, $joins, $where, $search, $group, $order);

		$data =[];
		$no = $_POST["start"];
		if ($productos) {
			foreach ($productos as $key => $value) {
				$no ++;
				$row = [];
				$row[] = '<b>'.$value->id_producto.'</b>';
				$row[] = $value->codigo;
				$row[] = $value->producto;
				$row[] = $value->familia;
				$row[] = $this->column_buttons($value->id_producto);
				$data[] = $row;
			}
		}
		$salida = [
			"draw"				=>	$_POST['draw'],
			"recordsTotal"		=>	$this->pro_md->count_filtered("productos.id_producto", $where, $search, $joins),
			"recordsFiltered"	=>	$this->pro_md->count_filtered("productos.id_producto", $where, $search, $joins),
			"data" => $data];
		$this->jsonResponse($salida);
	}

	private function column_buttons($id_producto){
		$botones = "";
		$botones.='<button id="update_producto" class="btn btn-info" data-toggle="tooltip" title="Editar" data-id-producto="'.$id_producto.'">
						<i class="fa fa-pencil"></i>
					</button>';
		$botones.='&nbsp;<button id="delete_producto" class="btn btn-warning" data-toggle="tooltip" title="Eliminar" data-id-producto="'.$id_producto.'">
							<i class="fa fa-trash"></i>
						</button>';
		return $botones;
	}

	public function add_producto(){
		$data["title"]="REGISTRAR PRODUCTOS";
		$data["familias"] = $this->fam_md->get();
		$data["view"] =$this->load->view("Productos/new_producto", $data, TRUE);
		$data["button"]="<button class='btn btn-success new_producto' type='button'>
							<span class='bold'><i class='fa fa-floppy-o'></i></span> &nbsp;Guardar
						</button>";
		$this->jsonResponse($data);
	}

	

	public function get_update($id){
		$data["title"]="ACTUALIZAR DATOS DEL PRODUCTO";
		$data["producto"] = $this->pro_md->get(NULL, ['id_producto'=>$id])[0];
		$data["familias"] = $this->fam_md->get();
		$data["view"] =$this->load->view("Productos/edit_producto", $data, TRUE);
		$data["button"]="<button class='btn btn-success update_producto' type='button'>
							<span class='bold'><i class='fa fa-floppy-o'></i></span> &nbsp;Guardar cambios
						</button>";
		$this->jsonResponse($data);
	}

	public function get_delete($id){
		$data["title"]="PRODUCTO A ELIMINAR";
		$data["producto"] = $this->pro_md->get(NULL, ['id_producto'=>$id])[0];
		$data["view"] = $this->load->view("Productos/delete_producto", $data,TRUE);
		$data["button"]="<button class='btn btn-danger delete_producto' type='button'>
							<span class='bold'><i class='fa fa-times'></i></span> &nbsp;Aceptar
						</button>";
		$this->jsonResponse($data);
	}

	public function accion($param){
		$user = $this->session->userdata();
		$producto = ['codigo'	=>	$this->input->post('codigo'),
					'nombre'	=>	strtoupper($this->input->post('nombre')),
					'estatus'	=>	$this->input->post('estatus'),
					'colorp'	=>	$this->input->post('colorp'),
					'id_familia'=>	($this->input->post('id_familia') !="-1") ? $this->input->post('id_familia') : NULL
		];
		$getProducto = $this->pro_md->get(NULL, ['codigo'=>$producto['codigo']])[0];
		switch ($param) {
			case (substr($param, 0, 1) === 'I'):
				if (sizeof($getProducto) == 0) {
					$cambios = [
							"id_usuario" => $user["id_usuario"],
							"fecha_cambio" => date('Y-m-d H:i:s'),
							"antes" => "Registro de nuevo producto",
							"despues" => "Código: ".$producto['codigo']." /Nombre: ".$producto['nombre']." /Familia: ".$producto['id_familia']];
					$data['cambios'] = $this->cambio_md->insert($cambios);
					$data ['id_producto']=$this->pro_md->insert($producto);
					$mensaje = ["id" 	=> 'Éxito',
								"desc"	=> 'Producto registrado correctamente',
								"type"	=> 'success'];
				}else{
					$mensaje = ["id" 	=> 'Alerta',
								"desc"	=> 'El código ya esta registrada en el Sistema',
								"type"	=> 'warning'];
				}
				break;

			case (substr($param, 0, 1) === 'U'):
				$antes = $this->pro_md->get(NULL, ['id_producto'=>$this->input->post('id_producto')])[0];
				$data ['id_producto'] = $this->pro_md->update($producto, $this->input->post('id_producto'));
				$cambios = [
						"id_usuario" => $user["id_usuario"],
						"fecha_cambio" => date('Y-m-d H:i:s'),
						"antes" => "id: ".$antes->id_producto." /Código: ".$antes->codigo." /Nombre: ".$antes->nombre." /Familia: ".$antes->id_familia,
						"despues" => "Nuevos datos -> Código: ".$producto['codigo']." /Nombre: ".$producto['nombre']." /Familia: ".$producto['id_familia']];
				$data['cambios'] = $this->cambio_md->insert($cambios);
				$mensaje = [
					"id" 	=> 'Éxito',
					"desc"	=> 'Producto actualizado correctamente',
					"type"	=> 'success'
				];
				break;

			default:
				$antes = $this->pro_md->get(NULL, ['id_producto'=>$this->input->post('id_producto')])[0];
				$data ['id_producto'] = $this->pro_md->update(["estatus" => 0], $this->input->post('id_producto'));
				$cambios = [
						"id_usuario" => $user["id_usuario"],
						"fecha_cambio" => date('Y-m-d H:i:s'),
						"antes" => "id: ".$antes->id_producto." /Código: ".$antes->codigo." /Nombre: ".$antes->nombre." /Familia: ".$antes->id_familia,
						"despues" => "Producto eliminado"];
				$data['cambios'] = $this->cambio_md->insert($cambios);
				$mensaje = [
					"id" 	=> 'Éxito',
					"desc"	=> 'Producto eliminado correctamente',
					"type"	=> 'success'
				];
				break;
		}
		$this->jsonResponse($mensaje);
	}

	public function print_productos(){
		ini_set("memory_limit", "-1");
		$this->load->library("excelfile");
		$hoja = $this->excelfile->getActiveSheet();
		$hoja->getDefaultStyle()
		    ->getBorders()
		    ->getTop()
		        ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$hoja->getDefaultStyle()
		    ->getBorders()
		    ->getBottom()
		        ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$hoja->getDefaultStyle()
		    ->getBorders()
		    ->getLeft()
		        ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$hoja->getDefaultStyle()
		    ->getBorders()
		    ->getRight()
		        ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

		$this->cellStyle("A1:B2", "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
		$border_style= array('borders' => array('right' => array('style' =>
			PHPExcel_Style_Border::BORDER_THIN,'color' => array('argb' => '000000'),)));

		$hoja->setCellValue("B1", "DESCRIPCIÓN SISTEMA")->getColumnDimension('B')->setWidth(70);

		$hoja->setCellValue("A2", "CÓDIGO")->getColumnDimension('A')->setWidth(30); //Nombre y ajuste de texto a la columna
		$hoja->mergeCells('E1:F1');
		$productos = $this->pro_md->getProdFam(NULL,0);
		$row_print = 2;
		if ($productos){
			foreach ($productos as $key => $value){
				$hoja->setCellValue("B{$row_print}", $value['familia']);
				$this->cellStyle("B{$row_print}", "000000", "FFFFFF", TRUE, 12, "Franklin Gothic Book");
				$hoja->setCellValue("B{$row_print}", $value['familia']);
				$row_print +=1;
				if ($value['articulos']) {
					foreach ($value['articulos'] as $key => $row){
						if($row['color'] == '#92CEE3'){
							$this->cellStyle("A{$row_print}", "92CEE3", "000000", FALSE, 10, "Franklin Gothic Book");
						}else{
							$this->cellStyle("A{$row_print}", "FFFFFF", "000000", FALSE, 10, "Franklin Gothic Book");
						}
						$hoja->setCellValue("A{$row_print}", $row['codigo'])->getStyle("A{$row_print}")->getNumberFormat()->setFormatCode('# ???/???');//Formato de fraccion
						$hoja->getStyle("A{$row_print}")->applyFromArray($border_style);
						$hoja->setCellValue("B{$row_print}", $row['producto']);
						if($row['estatus'] == 2){
							$this->cellStyle("B{$row_print}", "00B0F0", "000000", FALSE, 10, "Franklin Gothic Book");
						}
						if($row['estatus'] == 3){
							$this->cellStyle("B{$row_print}", "FFF900", "000000", FALSE, 10, "Franklin Gothic Book");
						}

						$hoja->getStyle("B{$row_print}")->applyFromArray($border_style);

						if($this->weekNumber($row['fecha_registro']) >= ($this->weekNumber() -1)){
							$this->cellStyle("A{$row_print}", "FF7F71", "000000", FALSE, 10, "Franklin Gothic Book");
							$this->cellStyle("B{$row_print}", "FF7F71", "000000", FALSE, 10, "Franklin Gothic Book");
							$hoja->setCellValue("C{$row_print}", "NUEVO");
						}
						$row_print++;
					}
				}
			}
		}
		$hoja->getStyle("A3:H{$row_print}")
                 ->getAlignment()
                 ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$hoja->getStyle("B3:B{$row_print}")
                 ->getAlignment()
                 ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $dias = array("DOMINGO","LUNES","MARTES","MIÉRCOLES","JUEVES","VIERNES","SÁBADO");
		$meses = array("ENERO","FEBRERO","MARZO","ABRIL","MAYO","JUNIO","JULIO","AGOSTO","SEPTIEMBRE","OCTUBRE","NOVIEMBRE","DICIEMBRE");

		$fecha =  $dias[date('w')]." ".date('d')." DE ".$meses[date('n')-1]. " DEL ".date('Y') ;
		$file_name = "PRODUCTOS ACTIVOS ".$fecha.".xlsx"; //Nombre del documento con extención
		header("Content-Type: application/vnd.ms-excel; charset=utf-8");
		header("Content-Disposition: attachment;filename=".$file_name);
		header("Cache-Control: max-age=0");
		$excel_Writer = PHPExcel_IOFactory::createWriter($this->excelfile, "Excel2007");
		$excel_Writer->save("php://output");

	}

	public function upload_productos(){
		$proveedor = $this->session->userdata('id_usuario');

		$cfile =  $this->usua_mdl->get(NULL, ['id_usuario' => $proveedor])[0];
		$nams = preg_replace('/\s+/', '_', $cfile->nombre);
		$filen = "Productos por ".$nams."".rand();


		$config['upload_path']          = './assets/uploads/cotizaciones/';
        $config['allowed_types']        = 'xlsx|xls';
        $config['max_size']             = 100;
        $config['max_width']            = 1024;
        $config['max_height']           = 768;



        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->do_upload('file_productos',$filen);
		$this->load->library("excelfile");
		ini_set("memory_limit", -1);
		$file = $_FILES["file_prod"]["tmp_name"];
		$filename=$_FILES['file_prod']['name'];
		$sheet = PHPExcel_IOFactory::load($file);
		$objExcel = PHPExcel_IOFactory::load($file);
		$sheet = $objExcel->getSheet(0);
		$num_rows = $sheet->getHighestDataRow();

		for ($i=3; $i<=$num_rows; $i++) {
			if($sheet->getCell('C'.$i)->getValue() > 0){
				$productos = $this->prod_mdl->get("id_producto",['codigo'=> htmlspecialchars($sheet->getCell('A'.$i)->getValue(), ENT_QUOTES, 'UTF-8')])[0];
				if (sizeof($productos) > 0) {
					$precio=0; $column_one=0; $column_two=0; $descuento=0; $precio_promocion=0;
					$precio = str_replace("$", "", str_replace(",", "replace", $sheet->getCell('C'.$i)->getValue()));
					$column_one = $sheet->getCell('E'.$i)->getValue();
					$column_two = $sheet->getCell('F'.$i)->getValue();
					$descuento = $sheet->getCell('G'.$i)->getValue();

					if ($column_one ==1 && $column_two ==1) {
						$precio_promocion = (($precio * $column_two)/($column_one+$column_two));
					}elseif ($column_one >=1 && $column_two >1) {
						$precio_promocion = (($precio * $column_two)/($column_one+$column_two));
					}elseif ($descuento >0) {
						$precio_promocion = ($precio - ($precio * ($descuento/100)));
					}else{
						$precio_promocion = $precio;
					}
					$antes =  $this->falt_mdl->get(NULL, ['id_producto' => $productos->id_producto, 'fecha_termino > ' => date("Y-m-d H:i:s"), 'id_proveedor' => $proveedor])[0];
					$cotiz =  $this->ct_mdl->get(NULL, ['id_producto' => $productos->id_producto, 'WEEKOFYEAR(fecha_registro)' => $this->weekNumber($fecha->format('Y-m-d H:i:s')), 'id_proveedor' => $proveedor])[0];
					if($antes){
						$new_cotizacion=[
							"id_producto"		=>	$productos->id_producto,
							"id_proveedor"		=>	$proveedor,//Recupera el id_usuario activo
							"precio"			=>	$precio,
							"num_one"			=>	$column_one,
							"num_two"			=>	$column_two,
							"descuento"			=>	$descuento,
							"precio_promocion"	=>	$precio_promocion,
							"fecha_registro"	=>	$fecha->format('Y-m-d H:i:s'),
							"observaciones"		=>	$sheet->getCell('D'.$i)->getValue(),
							"estatus" => 0];
						if($cotiz){
							$data['cotizacion']=$this->ct_mdl->update($new_cotizacion, ['id_cotizacion' => $cotiz->id_cotizacion]);
						}else{
							$data['cotizacion']=$this->ct_mdl->insert($new_cotizacion);
						}
					}else{
						$new_cotizacion=[
							"id_producto"		=>	$productos->id_producto,
							"id_proveedor"		=>	$proveedor,//Recupera el id_usuario activo
							"precio"			=>	$precio,
							"num_one"			=>	$column_one,
							"num_two"			=>	$column_two,
							"descuento"			=>	$descuento,
							"precio_promocion"	=>	$precio_promocion,
							"fecha_registro"	=>	$fecha->format('Y-m-d H:i:s'),
							"observaciones"		=>	$sheet->getCell('D'.$i)->getValue()
						];
						if($cotiz){
							$data['cotizacion']=$this->ct_mdl->update($new_cotizacion, ['id_cotizacion' => $cotiz->id_cotizacion]);
						}else{
							$data['cotizacion']=$this->ct_mdl->insert($new_cotizacion);
						}
					}

				}
			}
		}
		if (!isset($new_cotizacion)) {
			$mensaje=[	"id"	=>	'Error',
						"desc"	=>	'El Archivo esta sin precios',
						"type"	=>	'error'];
		}else{
			if (sizeof($new_cotizacion) > 0) {
				$aprov = $this->usua_mdl->get(NULL, ['id_usuario'=>$proveedor])[0];
				$cambios=[
						"id_usuario"		=>	$this->session->userdata('id_usuario'),
						"fecha_cambio"		=>	date("Y-m-d H:i:s"),
						"antes"			=>	"El usuario sube archivo de cotizaciones de ".$aprov->nombre,
						"despues"			=>	"assets/uploads/cotizaciones/".$filen.".xlsx",
						"accion"			=>	"Sube Archivo"
					];
				$data['cambios']=$this->cambio_md->insert($cambios);
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

}

/* End of file Productos.php */
/* Location: ./application/controllers/Productos.php */