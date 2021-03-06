<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
?>
<?php 
if(!$this->session->userdata("username") || $this->session->userdata("id_grupo") == 2){
	redirect("Compras/Login", "");
}
?>
<div class="wrapper wrapper-content animated fadeInRight">
	<div class="row">
		<div class="col-lg-12">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>LISTADO DE USUARIOS</h5>
				</div>
				<div class="ibox-content">
					<div class="btn-group">
						<button class="btn btn-primary" data-toggle="tooltip" title="Registrar" id="new_usuario">
							<i class="fa fa-plus"></i>
						</button>

					</div>
					<div class="btn-group">
						<?php echo form_open("Compras/print_usuarios", array("id" => 'reporte_cotizaciones', "target" => '_blank')); ?>
							<button class="btn btn-primary" name="excel" data-toggle="tooltip" title="Exportar a Excel" type="submit">
								<i class="fa fa-file-excel-o"></i> Descargar Usuarios & Contraseñas
							</button>
						<?php echo form_close(); ?>
					</div>
					<table class="table table-striped table-bordered table-hover" id="table_usuarios">
						<thead>
							<tr>
								<th>NO</th>
								<th>EMPRESA</th>
								<th>TELÉFONO</th>
								<th>CORREO</th>
								<th>CONTRASEÑA</th>
								<th>TIPO</th>
								<th>ACCIÓN</th>
							</tr>
						</thead>
						<tbody>
							<?php if ($usuarios): ?>
								<?php foreach ($usuarios as $key => $value): ?>
									<tr>
										<th><?php echo $value->id_usuario ?></th>
										<td><?php echo $value->nombre.' '.$value->apellido ?></td>
										<td><?php echo $value->telefono ?></td>
										<td><?php echo $value->email ?></td>
										<td><?php echo $value->password ?></td>
										<td><?php echo $value->grupo ?></td>
										<td>
										<?php if ($value->nombre=='MASTER' && $value->grupo=='ADMINISTRADOR'): ?>
											<!--Le ocultamos las opciones por ser el Usuario Master -->
										<?php elseif($value->grupo == 'AZTECA' && $this->session->userdata("id_usuario") <> $value->id_usuario && $this->session->userdata("id_usuario") <> 1): ?>
											
										<?php else: ?>
											<button id="update_usuario" class="btn btn-info" data-toggle="tooltip" title="Editar" data-id-usuario="<?php echo $value->id_usuario ?>">
												<i class="fa fa-pencil"></i>
											</button>
											<button id="show_usuario" class="btn btn-success" data-toggle="tooltip" title="Ver" data-id-usuario="<?php echo $value->id_usuario ?>">
												<i class="fa fa-eye"></i>
											</button>
											<button id="delete_usuario" class="btn btn-warning" data-toggle="tooltip" title="Eliminar" data-id-usuario="<?php echo $value->id_usuario ?>">
												<i class="fa fa-trash"></i>
											</button>
										<?php endif ?>
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