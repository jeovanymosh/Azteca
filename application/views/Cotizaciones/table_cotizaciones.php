<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="wrapper wrapper-content animated fadeInRight">
	<div class="row">
		<div class="col-lg-12">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>LISTADO DE COTIZACIONES</h5>
				</div>
				<?php if ($this->ion_auth->is_admin()): ?>
					<div class="ibox-content">
						<div class="btn-group">
							<div class="col-sm-2">
								<a href="<?php echo base_url('./assets/uploads/Formato.csv'); ?>" target="_blank" data-toggle="tooltip" title="Decargar Formato de Cotizaciones" class="btn btn-info"><i class="fa fa-cloud-download"></i><span class="nav-label" download></span> </a>
							</div>
						</div>
						<div class="btn-group">
							<?php echo form_open_multipart("", array('id' => 'upload_file')); ?>
								<div class="col-sm-4">
									<input class="btn btn-info" type="file" id="file_csv" name="file_csv" value=""/>
								</div>
								<input type="hidden" id="name_user" value="<?php echo strtoupper($usuario->username) ?>"/>
							<?php echo form_close(); ?>
						</div>
						<div class="table-responsive">
							<table class="table table-striped table-bordered table-hover" id="table_cot_admin">
								<thead>
									<tr>
										<th>CÓDIGO</th>
										<th>DESCRIPCIÓN</th>
										<th>SISTEMA</th>
										<th>PRECIO 4</th>
										<th>PRECIO MENOR</th>
										<th>PROVEEDOR</th>
										<th>PRECIO MAXIMO</th>
										<th>PRECIO PROMEDIO</th>
										<th>PRECIO 2</th>
										<th>2DO PROVEEDOR</th>
										<th>PROMOCIÓN</th>
										<th>ACCIÓN</th>
									</tr>
								</thead>
								<tbody>
									<?php if ($cotizacionesProveedor): foreach ($cotizacionesProveedor as $key => $value): ?>
										<tr>
											<td><?php echo $value->codigo ?></td>
											<td><?php echo $value->producto ?></td>
											<td><?php echo '' ?></td>
											<td><?php echo '' ?></td>
											<td><?php echo '$ '.number_format($value->precio_minimo,2,'.',',') ?></td>
											<td><?php echo $value->proveedor_minimo ?></td>
											<td><?php echo '' ?></td>
											<td><?php echo isset($value->precio_factura_minimo) ? '$ '.number_format($value->precio_factura_minimo,2,'.',',') : '' ?></td>
											<td><?php echo isset($value->precio_siguiente) ? '$ '.number_format($value->precio_siguiente,2,'.',',') : '' ?></td>
											<td><?php echo $value->proveedor_siguiente ?></td>
											<td><?php echo '' ?></td>
											<td>
												<button id="update_cotizacion" class="btn btn-info" data-toggle="tooltip" title="Editar" data-id-promocion="<?php echo $value->id_cotizacion ?>">
													<i class="fa fa-pencil"></i>
												</button>
												<button id="delete_cotizacion" class="btn btn-warning" data-toggle="tooltip" title="Eliminar" data-id-promocion="<?php echo $value->id_cotizacion ?>">
													<i class="fa fa-trash"></i>
												</button>
											</td>
										</tr>
									<?php endforeach;  endif ?>
								</tbody>
							</table>
						</div>
					</div>
				<?php else: ?>
					<div class="ibox-content">
						<div class="btn-group">
							<button class="btn btn-primary" data-toggle="tooltip" title="Registrar" id="new_cotizacion">
								<i class="fa fa-plus"></i>
							</button>
						</div>
						<div class="btn-group">
							<div class="col-sm-2">
								<a href="<?php echo base_url('./assets/uploads/Formato_cotizacion.csv'); ?>" target="_blank" data-toggle="tooltip" title="Decargar Formato de Cotizaciones" class="btn btn-info"><i class="fa fa-cloud-download"></i><span class="nav-label" download></span> </a>
							</div>
						</div>
						<div class="btn-group">
							<?php echo form_open_multipart("", array('id' => 'upload_file')); ?>
								<div class="col-sm-4">
									<input class="btn btn-info" type="file" id="file_csv" name="file_csv" value=""/>
								</div>
								<input type="hidden" id="name_user" value="<?php echo strtoupper($usuario->username) ?>"/>
							<?php echo form_close(); ?>
						</div>
						<div class="table-responsive">
							<table class="table table-striped table-bordered table-hover" id="table_cot_proveedores">
								<thead>
									<tr>
										<th>DESCRIPCIÓN</th>
										<th>FECHA REGISTRO</th>
										<th>FECHA CADUCIDAD</th>
										<th>EXISTENCIAS</th>
										<th>PRECIO FACTURA</th>
										<th>PRECIO FACTURA C/PROMOCIÓN</th>
										<th>DESCUENTO ADICIONAL</th>
										<th>PROMOCIÓN</th>
										<th></th>
										<th>OBSERVACIONES</th>
										<th>ACCIÓN</th>
									</tr>
								</thead>
								<tbody>
									<?php if ($cotizaciones): foreach ($cotizaciones as $key => $value): ?>
											<tr>
												<td><?php echo strtoupper($value->producto) ?></td>
												<td><?php echo date('d-m-Y', strtotime($value->fecha_registro)) ?></td>
												<td><?php echo ($value->fecha_caduca != '') ? date('d-m-Y', strtotime($value->fecha_caduca)) : '' ?></td>
												<td><?php echo ($value->existencias > 0) ? number_format($value->existencias,2,'.',',') : '' ?></td>
												<td><?php echo ($value->precio >0) ? '$ '.number_format($value->precio,2,'.',',') : '' ?></td>
												<td><?php echo ($value->precio_promocion >0) ? '$ '.number_format($value->precio_promocion,2,'.',',') : ''?></td>
												<td><?php echo ($value->descuento > 0) ? number_format($value->descuento,0,'.',',').' %' : ''  ?></td>
												<td><?php echo ($value->num_one > 0 && $value->num_two > 0) ? $value->num_one.'&nbsp; EN &nbsp;'.$value->num_two : '' ?></td>
												<td><?php echo $value->promocion ?></td>
												<td><?php echo $value->observaciones ?></td>
												<td>
													<button id="update_cotizacion" class="btn btn-info" data-toggle="tooltip" title="Editar" data-id-promocion="<?php echo $value->id_cotizacion ?>">
														<i class="fa fa-pencil"></i>
													</button>
													<button id="delete_cotizacion" class="btn btn-warning" data-toggle="tooltip" title="Eliminar" data-id-promocion="<?php echo $value->id_cotizacion ?>">
														<i class="fa fa-trash"></i>
													</button>
												</td>
											</tr>
									<?php endforeach; endif ?>
								</tbody>
							</table>
						</div>
					</div>
				<?php endif ?>
			</div>
		</div>
	</div>
</div>