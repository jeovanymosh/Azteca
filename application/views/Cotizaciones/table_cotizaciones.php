<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="wrapper wrapper-content animated fadeInRight">
	<div class="row">
		<div class="col-lg-12">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>Listado de Cotizaciones</h5>
				</div>
				<div class="ibox-content">
					<div class="btn-group">
						<a data-toggle="modal" data-tooltip="tooltip" title="Registrar" class="btn btn-primary tool btn-modal" href="<?php echo site_url('Cotizaciones/add_cotizacion'); ?>" data-target="#myModal">
							<i class="fa fa-plus"></i>
						</a>
					</div>
						<table class="table table-striped table-bordered table-hover" id="table_cotizaciones">
							<thead>
								<tr>
									<th>NO</th>
									<th>PROVEEDOR</th>
									<th>CÓDIGO</th>
									<th>PRODUCTO</th>
									<th>PRECIO</th>
									<th>ACCIÓN</th>
								</tr>
							</thead>
							<tbody>
								<?php if ($cotizaciones): ?>
									<?php foreach ($cotizaciones as $key => $value): ?>
										<tr>
											<th><?php echo $value->id_producto_proveedor ?></th>
											<td><?php echo strtoupper($value->first_name.' '.$value->last_name) ?></td>
											<td><?php echo $value->codigo ?></td>
											<td><?php echo strtoupper($value->producto) ?></td>
											<td>$<?php echo number_format($value->precio,2,'.',',') ?></td>
											<td>
												<a data-toggle="modal" data-tooltip="tooltip" title="Editar"  class="btn tool btn-info btn-modal" href="<?php echo site_url('#');?>" data-target="#myModal" ><i class="fa fa-pencil"></i></a>
												<a data-toggle="modal" data-tooltip="tooltip" title="Eliminar"  class="btn tool btn-warning btn-modal" href="<?php echo site_url('#');?>" data-target="#myModal" ><i class="fa fa-trash"></i></a>
											</td>
										</tr>
									<?php endforeach ?>
								<?php endif ?>
							</tbody>
						</table>
				</div>
			</div>
		</div>
	</div>
</div>
