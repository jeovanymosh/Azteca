<style type="text/css">
	.font{
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
		color: #000;
		}
		.row.col-sm-12.tblm {
		    margin-left: -8rem;
		    background-color: white;
		    width: 100vw;
		}
</style>
<div class="row col-sm-12">
	<label>USUARIO: </label> <?php echo $user['username'] ?> <br>
	<label>FECHA: </label> <?php echo $fecha ?> <br>
	<label>SEMANA: </label> <?php echo $semana ?>
</div>
<?php if ($cotizacionesProveedor): ?>
	<div class="row col-sm-12 tblm">
		<table class="table table-bordered table-striped table-bordered table-hover font" border="1">
			<thead>
				<tr>
					<th>FAMILIAS</th>
					<th>CÓDIGO</th>
					<th>DESCRIPCIÓN</th>
					<th>SISTEMA</th>
					<th>PRECIO 4</th>
					<th>PRECIO MAXIMO</th>
					<th>PRECIO PROMEDIO</th>
					<th>PROVEEDOR</th>
					<th>OBSERVACIÓN</th>
					<th>PRECIO MENOR</th>
					<th>2DO PROVEEDOR</th>
					<th>2DO PRECIO</th>
					<th>OBSERVACIÓN</th>
				</tr>
			</thead>
			<tbody>
				<?php if ($cotizacionesProveedor): ?>
					<?php foreach ($cotizacionesProveedor as $key => $value): ?>
						<tr>
							<td rowspan="<?php echo sizeof($value['articulos']) +1 ?>" bgcolor="SILVER"> <b><?php echo $value['familia'] ?> </b> </td>
							<?php if ($value['articulos']): foreach ($value['articulos'] as $key => $val): ?>
								<tr>
									<td><?php echo $val['codigo'] ?></td>
									<td><?php echo $val['producto'] ?></td>
									<td><?php echo '$ '.number_format($val['precio_sistema'],2,'.',',') ?></td>
									<td><?php echo '$ '.number_format($val['precio_four'],2,'.',',') ?></td>
									<td><?php echo '$ '.number_format($val['precio_maximo'],2,'.',',') ?></td>
									<td><?php echo '$ '.number_format($val['precio_promedio'],2,'.',',') ?></td>
									<td><?php echo $val['proveedor_first'] ?></td>
									<td><?php echo '$ '.number_format($val['precio_first'],2,'.',',') ?></td>
									<td><?php echo $val['promocion_first'] ?></td>
									<td><?php echo $val['proveedor_next']?></td>
									<td><?php echo '$ '.number_format($val['precio_next'],2,'.',',') ?></td>
									<td><?php echo $val['promocion_next'] ?></td>
								</tr>
							<?php endforeach; endif ?>
						</tr>
						<tr>
							<td colspan="12"></td>
						</tr>
					<?php endforeach ?>
				<?php endif ?>
			</tbody>
		</table>
	</div>
<?php else: ?>
	<div class="row col-sm-12">
		<div class="alert alert-warning">
			<h2>SIN DATOS A MOSTRAR</h2>
		</div>
	</div>
<?php endif ?>