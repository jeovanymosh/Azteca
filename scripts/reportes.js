$(function($) {
	$("[data-toggle='tooltip']").tooltip({
		placement:'top'
	});
	fillDataTable('table_precios_iguales',50);
	datePicker();

});


$(document).off("click", "#filter_show").on("click", "#filter_show", function(event) {
	event.preventDefault();
	var tableAdmin = "";
	var fech = $("#fecha_registro").val();
	var formData = $("#consultar_cotizaciones").serializeArray();
	var prods = "0";
	var flag = 0;
	get_reporte(formData).done(function(response) {
		$(".whodid").html(response);
		$(".tblm").html('<table class="table table-striped table-bordered table-hover" border="1" id="table_anteriore">'+
						'<thead><tr><th>CÓDIGO</th><th>DESCRIPCIÓN</th><th>FAMILIA</th><th>SISTEMA</th><th>PRECIO 4</th>'+
						'<th>PRECIO</th><th>PRECIO PROMOCION</th><th>PROVEEDOR</th>'+
						'<th>DESCUENTO</th><th colspan="2">PROMOCION # EN #</th><th>OBSERVACIÓN</th>'+
						'</tr></thead><tbody class="body_anteriores"></tbody></table>');

		get_rpts($("#fecha_registro").val()).done(function(response) {
				$.each(response, function(indx, vals){
						$.each(vals, function(index, value){
							if(value.nombre != prods){
								prods = value.nombre;
								if (flag == 0) {
									tableAdmin += '<tr style="border-top:3px solid #549a73;border-left:3px solid #549a73;border-right:3px solid #549a73;">';
									flag = 1;
								}else{
									tableAdmin += '<tr style="border-top:3px solid #5f3ea0;border-left:3px solid #5f3ea0;border-right:3px solid #5f3ea0">';
									flag = 0;
								}
							}else{
								if (flag == 1) {
									tableAdmin += '<tr style="border-left:3px solid #549a73;border-right:3px solid #549a73;">';
								}else{
									tableAdmin += '<tr style="border-left:3px solid #5f3ea0;border-right:3px solid #5f3ea0">';
								}
							}
							value.precio = value.precio == null ? 0 : value.precio;
							value.precio_promocion = value.precio_promocion == null ? 0 : value.precio_promocion;
							value.precio_sistema = value.precio_sistema == null ? 0 : value.precio_sistema;
							value.precio_four = value.precio_four == null ? 0 : value.precio_four;
							value.descuento = value.descuento == null ? "" : value.descuento;
							value.num_one = value.num_one == null ? "" : value.num_one;
							value.num_two = value.num_two == null ? "" : value.num_two;
							value.proveedor = value.proveedor == null ? "" : value.proveedor;
							value.observaciones = value.observaciones == null ? "" : value.observaciones;
							
							value.familia;
							if(value.estatus == 2){
								tableAdmin += '<td style="background-color: #00b0f0">'+value.codigo+'</td><td style="background-color: #00b0f0">'+value.nombre+'</td>';
							}else if(value.status == 3){
								tableAdmin += '<td style="background-color: #fff900">'+value.codigo+'</td><td style="background-color: #fff900">'+value.nombre+'</td>';
							}else{
								tableAdmin += '<td>'+value.codigo+'</td><td>'+value.nombre+'</td>';
							}
							tableAdmin += '<td>'+value.familia+'</td>';
							tableAdmin += '<td>$ '+formatNumber(parseFloat(value.precio_sistema), 2)+'</td><td>$ '+formatNumber(parseFloat(value.precio_four), 2)+'</td><td>$ '+formatNumber(parseFloat(value.precio), 2)+'</td>';
							if(value.precio_promocion >= value.precio_sistema){
								tableAdmin += '<td><div class="preciomas">$ '+formatNumber(parseFloat(value.precio_promocion), 2)+'</div></td>';
							}else{
								tableAdmin += '<td><div class="preciomenos">$ '+formatNumber(parseFloat(value.precio_promocion), 2)+'</div></td>'
							}
							tableAdmin += '<td>'+value.proveedor+'</td><td>'+value.descuento+'</td>';	
							tableAdmin += '<td>'+value.num_one+'</td><td>'+value.num_two+'</td>';
							tableAdmin += '<td>'+value.observaciones+'</td></tr>';
						});
					});	
					$(".body_anteriores").html(tableAdmin);
					fillDataTable("table_anteriore", 50);
			});
	});
});


function get_reporte(formData) {
	return $.ajax({
		url: site_url+"Reportes/fill_table",
		type: "POST",
		cache: false,
		dataType:"HTML",
		data: formData,
	});
}

function get_rpts(fecha) {
	return $.ajax({
		url: site_url+"Reportes/fill_anterior",
		type: "POST",
		dataType:"JSON",
		data: {fecha},
	});
}

$(document).off("click", "#ver_cotizacion").on("click", "#ver_cotizacion", function(event){
	event.preventDefault();
	var d = new Date();
	var f = d.getMonth()+1+"";
	if( f.length == 1){
		f = "0"+f;
	}
	var fecha = d.getFullYear() + "-" + f + "-" + d.getDate();
	if ($("#fecha_registro").val().length !== 0) {
		fecha = $("#fecha_registro").val();
		fecha = fecha.split("-").reverse().join("-");
		console.log(fecha)
	}
	var id_cotizacion = $(this).closest("tr").find("#ver_cotizacion").data("idCotizacion");
	getModal("Cotizaciones/ver_cotizacion/"+id_cotizacion+"/"+fecha+"/", function (){ });
});

$(document).off("change", "#id_pro").on("change", "#id_pro", function() {
	event.preventDefault();
	$(".searchboxs").css("display","none")
	var proveedor = $("#id_pro option:selected").val();
	$(".cot-prov").html("");
	getProveedorCot(proveedor)
	.done(function (resp){
		if(resp.cotizaciones){
			$.each(resp.cotizaciones, function(indx, value){
				value.observaciones = value.observaciones == null ? "" : value.observaciones;
				$(".cot-prov").append('<tr><td><input type="checkbox" value="1" class=""></td><td>'+value.producto+'</td><td>'+value.codigo+'</td><td>'+value.precio+'</td><td>'+value.precio_promocion
					+'</td><td><div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span><input type="text" name="fecha_registro" id="fecha_registro" class="form-control datepicker" value="" placeholder="00-00-0000"></div></td><td><button id="update_cotizacion" class="btn btn-info" data-toggle="tooltip" title="Editar" data-id-cotizacion="95471"><i class="fa fa-pencil"></i></button></td></tr>')
			});
		}
		$(".searchboxs").css("display","block")
	});

});

function myFunction() {
  // Declare variables 
  var input, filter, table, tr, td, i;
  input = document.getElementById("myInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("table_prov_cots");
  tr = table.getElementsByTagName("tr");

  // Loop through all table rows, and hide those who don't match the search query
  for (i = 0; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td")[0];
    if (td) {
      if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    } 
  }
}

function getProveedorCot(id_prov) {
	return $.ajax({
		url: site_url+"/Cotizaciones/getProveedorCot/"+id_prov,
		type: "POST",
		dataType: "JSON"
	});
}

function myFunction2() {
  // Declare variables 
  var input, filter, table, tr, td, i;
  input = document.getElementById("myInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("table_anteriores");
  tr = table.getElementsByTagName("tr");

  // Loop through all table rows, and hide those who don't match the search query
  for (i = 0; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td")[1];
    if (td) {
      if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    } 
  }
}