 <?php
	defined('BASEPATH') OR exit('No direct script access allowed');
?>
<?php 
if(!$this->session->userdata("username") || $this->session->userdata("id_grupo") == 2){
	redirect("Compras/Login", "");
}
?>
<style type="text/css" media="screen">
	.preciomas{
		background-color: #ea9696;
	    color: red;
	    font-weight: bold;
	    text-align: center;
	}
	.preciomenos{
		background-color: #96eaa8;
	    color: green;
	    font-weight: bold;
	    text-align: center;
	}
	.filts{
	    margin-left: 10rem;
	    background-color: #24C6C8;
	    color: white;
	    border-radius: 5px;
	    padding: 1rem;
	    margin-top: 7px;
	}
	.btng{
	    display:  inline-flex;
	    margin-left:  5rem;
	}
	.slct{
		border: 2px solid #24C6C8;
	    border-top-left-radius: 5px;
	    border-bottom-left-radius: 5px;
	    padding: 1rem;
	    height: 5rem
	}
	.filtro{
		padding: 2rem;
	    border: 2px solid #24C6C8;
	    height: 5rem
	}
	.btsrch{
	    background-color: #1D84C6;
	    color: #fff;
	    height: 5rem;
	    width: 7rem;
	}
	div#table_cot_admin_processing {
	    position: absolute;
	    left: 38%;
	    top: 10%;
	}
	.btng1{
		display: inline-flex;
	    background-color: #23c6c8;
	    border-radius: 5px;
	    color: #FFF;
	    margin-left: 3rem;
    	padding: 0;
    	padding-top: 4px;
    	padding-right: 5px;
	}
	.lblget{
		font-family: inherit;
	    font-weight: normal;
	    font-size: 14px;
	    padding: 7px;
	}
	tr:hover {background-color: #21b9bb !important;}

	select#id_proves2{display: none}
	.fill_form{display: none}
	select#id_proves {color: #000;}

	.spinns{width:35rem;height:25rem;background-color: rgba(255,255,255,0.5);
	    padding: 10rem;
	    color: #FF6805;
	    border: 2px solid #FF6805;
	    border-radius: 5px;
	    margin-left: 35%}
	.fa-spin{margin-left: 4rem}
	input.form-control.precio_sistema.numeric {
	    width: 90px !important;
	}
	input.form-control.precio_four.numeric {
	    width: 90px !important;
	}
</style>
<div class="wrapper wrapper-content animated fadeInRight" style="padding-left: 0;padding-right: 0">
	<div class="row">
		<div class="col-lg-12" style="padding: 0">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>LISTADO DE COTIZACIONES EN VOLÚMENES</h5>
				</div>
				<div class="ibox-content" style="padding-top: 4rem;">
					<div class="btn-group">
						<?php echo form_open("Cotizaciones/fill_excelV", array("id" => 'reporte_cotizaciones', "target" => '_blank')); ?>
							<button class="btn btn-primary" name="excel" data-toggle="tooltip" title="Exportar a Excel" type="submit">
								<i class="fa fa-file-excel-o"></i> Descargar Excel Volúmenes
							</button>
						<?php echo form_close(); ?>
					</div>
					<div class="btn-group">
						<div class="col-sm-2">
							<a href="../Cotizaciones/proveedor" rel="external-new-window" id="proveedorCotz"><button id="ver_proveedor" class="btn btn-info" data-toggle="tooltip" title="Filtrar 1 proveedor">
								<i class="fa fa-eye"></i> Cotizaciones por proveedor
							</button></a>
						</div>
					</div>

					<div class="table-responsive"> 
						<table class="table table-striped table-bordered table-hover" id="table_dir">
							<thead>
								<tr class="trdirectos">
									<th>FAMILIA</th>
									<th>CÓDIGO</th>
									<th>DESCRIPCIÓN</th>
									<th>SISTEMA</th>
									<th>PRECIO 4</th>
									<th>FACTURA</th>
									<th>C/PROMOCIÓN</th>
									<th>PROVEEDOR</th>
									<th>OBSERVACIÓN</th>
									<th>PRECIO MAXIMO</th>
									<th>PRECIO PROMEDIO</th>
									<th>FACTURA</th>
									<th>C/PROMOCIÓN</th>
									<th>2DO PROVEEDOR</th>
									<th>OBSERVACIÓN</th>
									<th>ACCIÓN</th>
								</tr>
							</thead>
							<tbody class="tabledirectos">

							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
