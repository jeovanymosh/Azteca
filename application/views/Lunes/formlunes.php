<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
?>
<?php 
if(!$this->session->userdata("username") || $this->session->userdata("id_grupo") == 2){
	redirect("Compras/Login", "");
}
?>
<style>
	.buscale {width: 50vw;padding: 5px 10px;border: 2px solid #23c6c8;font-size: 16px;border-radius: 5px;}
	div#page-wrapper{background: #008b8b;}
	.top-navigation .nav>li>a{color:#000;background:#fff;}
	.white-bg .navbar-fixed-top, .white-bg .navbar-static-top{background: #fff;}
	.top-navigation .navbar-brand{background:#fff;color:#000;}
	#progress{color:#FFF !important;background:#008b8b !important;}
	.logo_img>img{border: 4px solid #008b8b;}
	.top-navigation .navbar-nav .dropdown-menu{background:#004479;color:#ffffff;}
	.top-navigation .wrapper.wrapper-content{padding:40px 0px !important;}
	td{font-family:sans-serif;font-size:14px}
	.table > thead > tr > th, .table > tbody > tr > th, .table > thead > tr > td, .table > tbody > tr > td{border: 1px solid #000000;}
	tr:hover {background: #c6e8ce !important;}
}
</style>
<div class="wrapper wrapper-content animated fadeInRight">
	<div class="row">
		<div class="col-lg-12" style="padding:0">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>LISTADO DE EXISTENCIAS</h5>
				</div>
				<div class="ibox-content" style="overflow-x:scroll;">
					<div class="btn-group col-sm-3" style="border:1px solid #bb2152;margin-right:30px;">
						<div class="col-sm-12" style="text-align:center;font-size:16px;color:#bb2152;margin-top: 2rem;">
							Subir precios de sistema<a href="print_precios" target="_blank"><i class='fa fa-download'></i> <span style="font-size:10px">(Descargar Formato)</span></a>
						</div>
						<?php echo form_open_multipart("", array('id' => 'upload_sistema')); ?>
							<div class="col-sm-4">
								<input class="btn btn-info" type="file" name="file_otizaciones" id="file_otizaciones" value="" style="background-color:#bb2152;border-color:#bb2152" />
							</div>
						<?php echo form_close(); ?>
					</div> 
					<div class="btn-group col-sm-3" style="border:1px solid #af23c8;margin-right:30px;">
						<div class="col-sm-12" style="text-align:center;font-size:16px;color:#af23c8;margin-top: 2rem;">
							Subir precios proveedor<a href="<?php echo base_url('assets/uploads/preciolunes.xlsx') ?>" target="_blank"><i class='fa fa-download'></i> <span style="font-size:10px">(Descargar Formato)</span></a>
						</div>
						<?php echo form_open_multipart("", array('id' => 'upload_precios')); ?>
							<div class="col-sm-4">
								<input class="btn btn-info file_cotizaciones" type="file" id="file_cotizaciones" name="file_cotizaciones" value="" style="background-color:#af23c8;border-color:#af23c8" />
							</div>
						<?php echo form_close(); ?>
					</div> 
					<div class="btn-group col-sm-3">
						<div class="col-sm-2">
							<?php echo form_open("Lunes/excel_semanon", array("id" => 'archivo_precios', "target" => '_blank')); ?>
								<button class="btn btn-info" name="excel" data-toggle="tooltip" title="Exportar a Excel" type="submit">
									<i class="fa fa-cloud-download"></i> Descargar formato
								</button>
							<?php echo form_close(); ?>
						</div>
					</div>
					<br>
					<div class="btn-group" style="margin-top:20px;margin-bottom:20px;">
						<input type="text" name="buscale" id="buscale" class="buscale" placeholder="Ingrese la descripción o código del producto"><br>
						<p>A partir de 4 caracteres se mostrarán los resultados en la tabla </p>
						<p>Sí el precio sistema aparece en <span style="color:red">rojo</span>, significará que el precio no se ha actualizado y se tomo el de la semana pasada.</p>
					</div>
					<table class="table table-striped table-bordered table-hover" style="font-size:12px;text-align:right;">
						<thead>
							<tr>
								<th>
									<div style="width:300px !important">
										<?php echo $fecha ?>		
									</div>
								</th>
								<th width="150px !important">PRECIO PROVEEDOR</th>
								<th width="100px !important">PRECIO SISTEMA</th>
								<th width="100px !important">UNIDAD MEDIDA</th>
								<?php foreach ($tiendas as $key => $value):?>
									<th colspan="5" style="text-align:center;border:1px solid <?php echo $value->color."99" ?>;background-color:<?php echo $value->color."99" ?>"><?php echo $value->nombre ?></th>
								<?php endforeach; ?>
							</tr>
							<tr>
								<th colspan="4" style="color:#FFF;background-color:#000"></th>
								<?php foreach ($tiendas as $key => $value):?>
									<th style="color:#FFF;background-color:#000">anterior</th>
									<th style="color:red;background-color:#000;font-weight:bold">sugerido</th>
									<th style="color:#FFF;background-color:#000">cajas</th>
									<th style="color:#FFF;background-color:#000">piezas</th>
									<th style="color:#FFF;background-color:#000">pedido</th>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tbody id="tbody_exist">
							<tr>
								<td colspan='59' style='text-align:left;font-size:24px;font-weight:bold;'>
									Introduzca mas de 4 caracteres en el recuadro de busqueda
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>