$(window).on('beforeunload', function(){
	var c=confirm();
	if( $(".checkhim").css('display') == 'block'){
		if(c){
	  		return true;
		}else
			return false;
	}
	/*getit(JSON.stringify(values)).done(function(resp){
		$(".yy").html(resp)
	})*/
});
var obj = [];
var folis = "";
var tiendis =  "";
var many = "";

var tiendas = {87:"cedis",57:"abarrotes",90:"villas",58:"tienda",59:"ultra",60:"trincheras",61:"mercado",62:"tenencia",63:"tijeras"}
$(document).off("change", "#proveedor").on("change", "#proveedor", function (){
	/*getit(JSON.stringify(values)).done(function(resp){
		$(".yy").html(resp)
	})*/
	event.preventDefault();
	var provs = $("#proveedor option:selected");
	var values = {"proveedor":provs.val()};
	var pedido = "";
	if (provs !== "0" || provs !== 0) {
		getPedidos(JSON.stringify(values)).done(function(resp){
			if (resp) {
				$.each(resp,function (indx,val) {
					val.promocion = val.promocion == null ? "" : val.promocion; 
					pedido += "<div class='divcont divcont"+val.codigo+" d"+indx+"' data-id-codigo='"+val.codigo+"'><div class='divcontaindiv' style='width:5%'>"+(indx+1)+
								"</div><div class='divcontaindiv' style='width:15%'>"+val.codigo+"</div><div class='divcontaindiv' style='width:50%'>"+val.nombre+
								"</div><div class='divcontaindiv' style='width:10%'>"+formatMoney(val.totalp,0)+"</div><div class='divcontaindiv' style='width:20%'>"+
								val.promocion+"</div></div>"
				})
				$(".bbtotal").html("NÚMERO DE PEDIDOS "+resp.length);
				$(".tablepeds-body").html(pedido);
			}else{
				toastr.error("Seleccione otro proveedor", "Proveedor sin pedidos");
				$(".bbtotal").html("PROVEEDOR SIN PEDIDOS ");
			}
			
		})
	}
})


function getPedidos(values){
    return $.ajax({
        url: site_url+"Facturas/getPedidos",
        type: "POST",
        dataType: "JSON",
        data: {
            values : values,
        },
    });
}

function getit(){
    return $.ajax({
        url: site_url+"Facturas/getit",
        type: "POST",
        dataType: "JSON",
    });
}


$(document).off("change", "#file_factura").on("change", "#file_factura", function(event) {
	event.preventDefault();
	
	if ($(this).val() !== ""){
		if($("#proveedor option:selected").val() !== 0 && $("#proveedor option:selected").val() !== "0") {
			blockPage();
			var fdata = new FormData($("#upload_facturas")[0]);
			uploadFactura(fdata,$("#proveedor option:selected").val(),tiendis,tiendas[tiendis])
			.done(function (resp) {
				if (resp.type == 'error'){
					unblockPage();
					toastr.error(resp.desc, user_name)
				}else{
					unblockPage();
					$(".checkhim").css("display","block");
					$(document.body).css("overflow-y","hidden");
					toastr.success(resp.desc, user_name)
					folis = resp[3];
					$(".h1folio").html("RESULTADOS DE LA FACTURA CON FOLIO '"+resp[3]+"' - "+tiendas[tiendis].toUpperCase()+" - "+$("#proveedor option:selected").text());
					var bod = "";var bods = "";
					$.each(resp[0],function(indx,val) {
						val.codigo = val.codigo == null ? "" : val.codigo;
						val.nombre = val.nombre == null ? "" : val.nombre;
						val.costo  = val.costo == null ? "" : val.costo;
						val.total = val.total == null ? "" : val.total;
						val.promocion = val.promocion == null ? "" : val.promocion;
						if (val.costo == "") {
							bod+= '<div class="col-md-12 col-lg-12 cuerpodiv" id="cuerpodiv'+indx+'" style="padding:0;display:inline-flex;"><div class="gifted">'+
							'<i '+'class="fa fa-gift" aria-hidden="true"></i></div><div class="devolucion"><i '+
							'class="fa fa-retweet" aria-hidden="true" id="idev"></i><input type="text" name="difis" id="difis" value="" /></div><div class="col-md-2 col-lg-2 body2">'+val.factu+
							'</div><div class="col-md-3 col-lg-3 body3">'+val.descripcion+'</div><div class="col-md-2 col-lg-2 body2" style="font-size:20px;font-weight:bold;" id="precio">$ '+
							formatMoney(val.precio,2)+'</div><div class="col-md-1 col-lg-1 body1" style="font-size:20px;font-weight:bold;">'+formatMoney(val.cantidad,1)+
							'</div><div class="col-md-4 col-lg-4 body4" ondrop="drop(event)" ondragover="allowDrop(event)" id="pedidodiv">SOLTAR RECUADRO AQUÍ</div></div>';
						} else {
							var col1 = "#FFF";var col2 = "#FFF";
							col1 = cantidades(parseFloat(val.cantidad),parseFloat(val.total));
							col2 = costos(parseFloat(val.precio),parseFloat(val.costo));
							
							bod+= '<div class="col-md-12 col-lg-12 cuerpodiv" id="cuerpodiv'+indx+'" style="padding:0;display:inline-flex;"><div class="gifted"><i '+'class="fa fa-gift"'+
							' aria-hidden="true"></i></div><div class="devolucion"><i class="fa fa-retweet" aria-hidden="true" id="idev"></i><input type="text"'+
							' name="difis" id="difis" value=""></div><div class="col-md-2 col-lg-2 body2">'+val.factu+'</div><div class="'+
							'col-md-3 col-lg-3 body3">'+val.descripcion+'</div><div class="col-md-2 col-lg-2 body2" style="font-size:20px;font-weight:bold;background:'+col2+
							';" id="precio">$ '+formatMoney(val.precio)+'<br><div style="color:white;background:#000;border-radius:30px">DIF: '+formatMoney((parseFloat(val.precio)-parseFloat(val.costo)),2)+'</div></div><div class="col-md-1 col-lg-1 body1" style="font-size:20px;font-weight:bold;background:'+col1+
							';">'+formatMoney(val.cantidad,1)+'<br><div style="color:white;background:#000;border-radius:30px">DIF: '+(parseFloat(val.cantidad) - parseFloat(val.total))+'</div></div><div class="col-md-4 col-lg-4 body4" ondrop="drop(event)" ondragover="allowDrop(event)"'+
							' id="pedidodiv"><div class="col-lg-12 col-md-12 pedsist" ondragstart="drag(event)" id="'+val.codigo+'" style="padding:5px"><h4>'+val.codigo+' - '+val.nombre+
							'</h4><div class="col-md-6 col-lg-6"><input class="costod" type="text" name="costo'+val.codigo+'" placeholder="'+val.costo+'" value="'+val.costo+'" id="costo'+val.codigo+'" style="width:100%"></div><div class='+
							'"col-md-6 col-lg-6 cantu">Cantidad: '+formatMoney(val.total,1)+'</div><div class="col-md-12 col-lg-12">Promoción: '+val.promocion+'</div><div class="cerra"'+
							' id="cerra'+val.codigo+'" style="display:block"><i class="fa fa-times" aria-hidden="true"></i></div></div></div></div>';
						}
					})
					$("#cuerpo").html(bod);
					$.each(resp[1],function(index,vals) {
						vals.promocion = vals.promocion == null ? "" : vals.promocion;
						bods+= '<div class="col-lg-12 col-md-12 pedsist pedsi" draggable="true" ondragstart="drag(event)" id="'+vals.codigo+'" style="padding:5px"><h4>'+vals.codigo+
								' - '+vals.nombre+'</h4><div class="col-md-6 col-lg-6"><input class="costod" type="text" name="costo'+vals.codigo+'" placeholder="'+vals.costo+'" value="'+vals.costo+'" id="costo'+vals.codigo+
								'" style="width:100%"></div><div class="col-md-6 col-lg-6 cantu">Cantidad: '+formatMoney(vals.total,1)+'</div><div class="col-md-12 col-lg-12">Promoción: '
								+vals.promocion+'</div><div class="cerra" id="cerra'+vals.codigo+'"><i class="fa fa-times" aria-hidden="true"></i></div></div>'
					})
					$("#cuerpo2").html(bods);
					//setTimeout("location.reload()", 700, toastr.success(resp.desc, user_name), "");
				}
			});

		}else{
			toastr.error("Por favor seleccione un proveedor e intente nuevamente", user_name);
			$("#file_factura").val("");
		}
		
	}
});

function allowDrop(ev) {
  ev.preventDefault();
}

function drag(ev) {
  ev.dataTransfer.setData("text", ev.target.id);
}

function drop(ev) {
	ev.preventDefault();
	ev.target.innerHTML = '';
	var data = ev.dataTransfer.getData("text");
	var onj = document.getElementById(data);
	ev.target.appendChild(onj);
	onj.removeAttribute("draggable");
	onj.classList.remove("pedsi");
	ev.target.removeAttribute("ondrop");
	$("#"+data+" .cerra").css("display","block");
	var pedsist = $("#"+data+" .cerra").closest(".pedsist");
	var precio = pedsist.closest(".body4").closest(".col-md-12").find("#precio");
	var prec = precio.html().substring(2, precio.html().lenght);
	precio.css("background",costos(parseFloat(prec.replace(",","")),parseFloat($("#costo"+data).val())));

	precio.html(precio.html()+" <br><div style='color:white;background:#000;border-radius:30px'>DIF: "+formatMoney((parseFloat(prec.replace(",",""))-parseFloat($("#costo"+data).val())),2)+"</div>")
	var cantidad = pedsist.closest(".body4").closest(".col-md-12").find(".body1");
	var cantu = $("#costo"+data).closest(".pedsist").find(".cantu");
	cantidad.css("background",cantidades(parseFloat(cantidad.html()),parseFloat( cantu.html().substring(10,cantu.html().length) )));

	cantidad.html(cantidad.html()+" <br><div style='color:white;background:#000;border-radius:30px'>DIF: "+(parseFloat(cantidad.html()) - parseFloat( cantu.html().substring(10,cantu.html().length)))+"</div>")
	if ($("#costo"+data).val() === "") {
		precio.html("$ "+prec+" <br><div style='color:white;background:#000;border-radius:30px'>DIF: "+formatMoney(parseFloat(prec.replace(",","")),2)+"</div>")
		precio.css("background",costos(parseFloat(prec.replace(",","")),0));
	}
	console.log($("#cuerpo").length)
}

$(document).off("keyup", ".costod").on("keyup", ".costod", function (){
	event.preventDefault();
	var precio = $(this).closest(".pedsist").closest(".body4").closest(".col-md-12").find("#precio");
	precio.css({"background":"white","color":"black"});
	precio.css("background",costos(parseFloat(precio.html().substring(2, precio.html().length)),parseFloat($(this).val())));

	var prec = precio.html().substring(2, precio.html().lenght);
	precio.html("$ "+parseFloat(prec.replace(",",""))+" <br><div style='color:white;background:#000;border-radius:30px'>DIF: "+formatMoney((parseFloat(prec.replace(",",""))-parseFloat($(this).val())),2)+"</div>")
	if ($(this).val() === "") {
		precio.css("background","#ff000080");
		precio.html("$ "+parseFloat(prec.replace(",",""))+" <br><div style='color:white;background:#000;border-radius:30px'>DIF: "+formatMoney((parseFloat(prec.replace(",",""))),2)+"</div>")
	}
})



function uploadFactura(formData,cual,tienda,tend) {
	return $.ajax({
		url: site_url+"Facturas/uploadFacturas/"+cual+"/"+tienda+"/"+tend,
		type: "POST",
		cache: false,
		contentType: false,
		processData:false,
		dataType:"JSON",
		data: formData,
	});
}

$(document).off("click", ".tienda").on("click", ".tienda", function (){
	$(".elvis").css("display","block");
	$(".elvis2").css("display","block");
	$(".factdetails").css("display","none");
	if ($(this).is(":checked")) {
		$(".facture").html("");
		tiendis = $(this).attr('id').substring(6, $(this).attr('id').length);
		getFacturas($(this).attr('id').substring(6, $(this).attr('id').length))
		.done(function (resp) {
			if (resp) {
				$(".facture").html("<h2>Seleccione una factura</h2>");
				$.each(resp,function(indx,val){
					$(".facture").append('<div class="col-md-3 col-lg-2"><div class="form-check"><input class="form-check-input facty" type="checkbox" value="'+val.id_proveedor+'" '+
						'id="'+val.folio+'"><label class="form-check-label" for="facty'+val.folio+'" style="color:#000;background:#FFF !important;">'+val.folio+' - '+val.nombre+'</label></div></div>')
				});
			} else {
				$(".facture").html("<h2>Sin facturas</h2>");
			}
		})
	}else{
		tiendis = 0;
		$(".facture").html("");
	}
})

$(document).off("keyup", ".costable").on("keyup", ".costable", function (){
	event.preventDefault();
	calculaFactura();
	var values = {"costo":$(this).val()};
	console.log($(this).val().substring(0,1));
	if ($(this).val().substring(0,1) !== "=" || $(this).val().substring(0,1) !== "+") {
		updateCosto($(this).attr("name"),values);	
	}else{
		//console.log($(this).val().split('/')[0])
	}
})
function updateCosto(inp,values){
	 return $.ajax({
        url: site_url+"/Facturas/updateCosto/"+inp,
        type: "POST",
        dataType: "JSON",
        data : {values: values}
	});
}

function calculaFactura(){
	var tot=0;var cred=0;var difer=0;var devuel=0;var totis=0;var totis0=0;var diferencia=0;var diferencia0=0;var credito0=0;var credito=0;

	for (var i = 0; i < many; i++) {
		if ($("#direc"+i).val() === "DIRECTO") {
			diferencia = (parseFloat($("#factp"+i).html()) - parseFloat($("#costable"+i).val()));
			totis = (parseFloat($("#cantis"+i).html()) * parseFloat($("#costable"+i).val()));

			if ($("#direc0"+i).val() === "DEVUELTO") {
				diferencia0 = (parseFloat($("#factp0"+i).html()) - parseFloat($("#costable0"+i).val()));
				totis0 = 0;
				credito0 = (parseFloat($("#factp0"+i).html()) * parseFloat($("#cantis0"+i).html()));

				$("#diftable0"+i).val(formatMoney(diferencia0));
				$("#cretable0"+i).val(formatMoney(credito0));
				$("#tottable0"+i).val(formatMoney(totis0));
				cred = parseFloat(cred) + parseFloat(credito0);
				difer = parseFloat(difer) + parseFloat(credito0);
				devuel = parseFloat(devuel) + (parseFloat($("#factp0"+i).html()) * parseFloat($("#cantis0"+i).html()));
			}
		}else{
			if ($("#direc0"+i).val() === "DEVUELTO") {
				diferencia0 = (parseFloat($("#factp0"+i).html()) - parseFloat($("#costable0"+i).val()));
				totis0 = 0;
				credito0 = (parseFloat($("#factp0"+i).html()) * parseFloat($("#cantis0"+i).html()));
				$("#diftable0"+i).val(formatMoney(diferencia0));
				$("#cretable0"+i).val(formatMoney(credito0));
				$("#tottable0"+i).val(formatMoney(totis0));

				cred = parseFloat(cred) + parseFloat(credito0);
				
				difer = parseFloat(difer) + parseFloat(credito0);
				devuel = parseFloat(devuel) + (parseFloat($("#factp0"+i).html()) * parseFloat($("#cantis0"+i).html()));
			}else{
				diferencia = (parseFloat($("#factp"+i).html()));
				totis = 0;
				devuel = parseFloat(devuel) + (diferencia * parseFloat($("#cantis"+i).html()));
			}
		}

		if ($("#direc"+i).length && $("#direc"+i).val() !== "DEVUELTO") {
			credito = (diferencia * parseFloat($("#cantis"+i).html()));
			$("#diftable"+i).val(formatMoney(diferencia));
			$("#cretable"+i).val(formatMoney(credito));
			$("#tottable"+i).val(formatMoney(totis));

			cred = parseFloat(cred) + parseFloat(credito);
			tot = parseFloat(tot) + totis;
			difer = parseFloat(difer) + parseFloat(credito);
		}
	}
	$(".totfact").html("$ "+formatMoney((parseFloat(tot)+parseFloat(cred)),2));
	$(".sumnota").html("$ "+formatMoney(cred,2));
	$(".sumtotal").html("$ "+formatMoney(tot,2));
	$(".devuel").html("$ "+formatMoney(devuel,2));
	$(".difer").html("$ "+formatMoney(difer,2));
}

$(document).off("click", ".facty").on("click", ".facty", function (){
	if ($(this).is(":checked")) {
		$(".factdetails").css("display","block");
		$(".totfact").html("");
		$(".sumnota").html("");
		$(".sumtotal").html("");
		$(".devuel").html("");
		$(".difer").html("");
		var compara = 0;
		var folicho = $(this).attr('id');
		var provicho = $(this).val();
		var values = {"proveedor":$(this).val(),"folio":$(this).attr('id'),"tienda":tiendis,"which":tiendas[tiendis]};
		var diferencia = 0;var credito = 0;var totis = 0;var devuel = 0;var cred = 0;var tot = 0;var difer = 0;
		getDetails(JSON.stringify(values))
		.done(function (resp) {
			many = resp.length;
			var colis="black";var backis="white";
			$(".headfact").html(resp[0].tienda+" - GRUPO AZTECA, S.A DE C.V");
			$(".headfact").css("background",resp[0].color);
			$(".subheadfact").html("REPORTE "+resp[0].prove);
			$(".fecharep").html(resp[0].fecha);
			$(".fechafact").html(resp[0].fecha_factura);
			$(".factlist").html("");
			$(".notafolio").html(resp[0].folio);
			$.each(resp,function (indx,val) {

				val.cantidad = val.cuantos;
				compara = val.id_comparacion;
				val.descripcion = val.pprod === null ? val.descripcion : val.pprod;
				

				if ((val.devolucion === 0 || val.devolucion === "0") && (val.gift === 0 || val.gift === "0")) {
					$(".factlist").append('<div class="col-md-12 col-lg-12 factlisty" style="padding:0"><div class="col-lg-4 col-md-4 factlistItem" style="line-height:normal;"><div class="gifted2 col-md-1" name="'+
						val.id_comparacion+'" data-id-cant="'+val.cantidad+'"><i class="fa fa-gift" aria-hidden="true"></i></div>'+
						'<div class="devolucion2 col-md-2"><i class="fa fa-retweet" aria-hidden="true" name="'+val.id_comparacion+'" id="idev2" data-id-cant="'+val.cantidad+'"></i><input type="text" name="'+val.id_comparacion+'" id="difis2" value=""/></div>'+
						'<div class="col-md-9">'+val.descripcion+'</div></div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0;"><input type="text" id="direc'+indx+
						'" value="DIRECTO" class="inputtab" readonly/></div><div class="col-md-1 col-lg-1 factlistItem"'+
						' style="border-left:0"><input type="text" name="'+val.id_comparacion+'" id="costable'+indx+'" value="'+val.costo+'" class="costable'+
						' inputtab" placeholder="'+val.costo+'"/></div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0" id="wey'+indx+
						'">'+formatMoney(val.wey,0)+'</div><div class="col-md-1'+
						' col-lg-1 factlistItem" style="border-left:0" id="cantis'+indx+'">'+val.cantidad+'</div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0" id="factp'+indx+'">'+
						val.precio+'</div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0"><input type="text" name="diftable" id="diftable'+
						indx+'" value="" class="diftable inputtab" placeholder="" readonly/></div><div class="col-md-1'+
						' col-lg-1 factlistItem" style="border-left:0"><input type="text" name="cretable" id="cretable'+indx+'" value="" class="cretable'+
						' inputtab" placeholder="" readonly/></div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0"><input type="text" name="tottable"'+
						' id="tottable'+indx+'" value="" class="tottable inputtab" placeholder="" readonly/></div></div>')
				}else{
					if (val.gift === 1 || val.gift === "1") {
						$(".factlist").append('<div class="col-md-12 col-lg-12 factlisty" style="padding:0"><div class="col-lg-4 col-md-4 factlistItem" style="line-height:normal;background:#c388e8;"><div class="gifted2 col-md-1" name="'+
						val.id_comparacion+'" data-id-cant="'+val.cantidad+'"><i class="fa fa-gift" aria-hidden="true"></i></div>'+
						'<div class="devolucion2 col-md-2" style="display:none"><i class="fa fa-retweet" aria-hidden="true" name="'+val.id_comparacion+'" id="idev2" data-id-cant="'+val.cantidad+'"></i><input type="text" name="'+val.id_comparacion+'" id="difis2" value=""/></div>'+
						'<div class="col-md-9">'+val.descripcion+'</div></div><div class="col-md-1 col-lg-1 factlistItem" style=";background:#e08989;color:blue;border-left:0;"><input type="text"'+
						' id="direc'+indx+'" value="S/C"	class="inputtab" style=";background:#e08989;color:blue;border-left:0;" readonly/></div><div class="col-md-1 col-lg-1'+
						' factlistItem" style="border-left:0"><input type="text" name="'+val.id_comparacion+'" id="costable'+indx+'" value="'+val.costo+'" class="costable'+
						' inputtab" placeholder="'+val.costo+'"/></div><div class="col-md-1 col-lg-1 factlistItem" '+
						'style="border-left:0" id="wey'+indx+
						'">0</div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0" id="cantis'+indx+'">'+val.cantidad+'</div><div class="col-md-1'+
						' col-lg-1 factlistItem" style="border-left:0" id="factp'+indx+'">'+val.precio+'</div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0"><input type="text" name="diftable" id="diftable'+
						indx+'" value="" class="diftable inputtab" placeholder="" readonly/></div><div class="col-md-1'+
						' col-lg-1 factlistItem" style="border-left:0"><input type="text" name="cretable" id="cretable'+indx+'" value="" class="cretable'+
						' inputtab" placeholder="" readonly/></div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0"><input type="text" name="tottable"'+
						' id="tottable'+indx+'" value="" class="tottable inputtab" placeholder="" readonly/></div></div>')
					} else {
						if (val.cantidad === val.devueltos) {
							$(".factlist").append('<div class="col-md-12 col-lg-12 factlisty" style="padding:0"><div class="col-lg-4 col-md-4 factlistItem" style="line-height:normal;background:#efff00;"><div class="gifted2 col-md-1" name="'+
						val.id_comparacion+'" data-id-cant="'+val.cantidad+'" style="display:none"><i class="fa fa-gift" aria-hidden="true"></i></div>'+
						'<div class="devolucion2 col-md-2"><i class="fa fa-retweet" aria-hidden="true" name="'+val.id_comparacion+'" id="idev2" data-id-cant="'+val.cantidad+'"></i><input type="text" name="'+
						val.id_comparacion+'" id="difis2" value="'+val.cantidad+'" style="display:block"/></div>'+
						'<div class="col-md-9">'+val.descripcion+'</div></div>  <div class="col-md-1 col-lg-1 factlistItem" style="border-left:0;color:red;background:#e08989;"><input type="text"'+
						' id="direc0'+indx+'" value="DEVUELTO"	class="inputtab" style="border-left:0;color:red;background:#e08989;" readonly/></div>'+
							'<div class="col-md-1 col-lg-1 factlistItem" style="border-left:0"><input type="text" name="'+val.id_comparacion+'" id="costable0'+indx+'" value="'+val.costo+'" class="costable'+
						' inputtab" placeholder="'+val.costo+'"/></div><div class="col-md-1 col-lg-1 factlistItem" '+
							'style="border-left:0" id="wey0'+indx+
						'">0</div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0" id="cantis0'+indx+'">'+val.cantidad+'</div><div class="col-md-1'+
							' col-lg-1 factlistItem" style="border-left:0" id="factp0'+indx+'">'+val.precio+'</div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0"><input type="text" name="diftable" id="diftable0'+
						indx+'" value="" class="diftable inputtab" placeholder="" readonly/></div><div class="col-md-1'+
						' col-lg-1 factlistItem" style="border-left:0"><input type="text" name="cretable" id="cretable0'+indx+'" value="" class="cretable'+
						' inputtab" placeholder="" readonly/></div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0"><input type="text" name="tottable"'+
						' id="tottable0'+indx+'" value="" class="tottable inputtab" placeholder="" readonly/></div></div>')
						} else {
							$(".factlist").append('<div class="col-md-12 col-lg-12 factlisty" style="padding:0"><div class="col-lg-4 col-md-4 factlistItem" style="line-height:normal;background:#efff00;"><div class="gifted2 col-md-1" name="'+
						val.id_comparacion+'" data-id-cant="'+val.cantidad+'" style="display:none"><i class="fa fa-gift" aria-hidden="true"></i></div>'+
						'<div class="devolucion2 col-md-2"><i class="fa fa-retweet" aria-hidden="true" name="'+val.id_comparacion+'" id="idev2" data-id-cant="'+val.cantidad+'"></i><input type="text" name="'+
						val.id_comparacion+'" id="difis2" value="'+parseFloat(val.devueltos)+'" style="display:block"/></div>'+
						'<div class="col-md-9">'+val.descripcion+'</div></div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0;color:black;background:white;"><input type="text"'+
						' id="direc'+indx+'" value="DIRECTO"	class="inputtab" readonly/></div>'+
							'<div class="col-md-1 col-lg-1 factlistItem" style="border-left:0"><input type="text" name="'+val.id_comparacion+'" id="costable'+indx+'" value="'+val.costo+'" class="costable'+
						' inputtab" placeholder="'+val.costo+'"/></div><div class="col-md-1 col-lg-1 factlistItem" '+
							'style="border-left:0" id="wey'+indx+
						'">'+formatMoney(val.wey,0)+'</div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0" id="cantis'+indx+'">'+
							(parseFloat(val.cantidad)-parseFloat(val.devueltos))+'</div><div class="col-md-1'+
							' col-lg-1 factlistItem" style="border-left:0" id="factp'+indx+'">'+val.precio+'</div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0"><input type="text" name="diftable" id="diftable'+
						indx+'" value="" class="diftable inputtab" placeholder="" readonly/></div><div class="col-md-1'+
						' col-lg-1 factlistItem" style="border-left:0"><input type="text" name="cretable" id="cretable'+indx+'" value="" class="cretable'+
						' inputtab" placeholder="" readonly/></div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0"><input type="text" name="tottable"'+
						' id="tottable'+indx+'" value="" class="tottable inputtab" placeholder="" readonly/></div></div>');

							$(".factlist").append('<div class="col-md-12 col-lg-12 factlisty" style="padding:0"><div class="col-lg-4 col-md-4 factlistItem">'+
							val.descripcion+'</div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0;color:red;background:#e08989;"><input type="text"'+
						' id="direc0'+indx+'" value="DEVUELTO"	class="inputtab" style="border-left:0;color:red;background:#e08989;" readonly/></div>'+
							'<div class="col-md-1 col-lg-1 factlistItem" style="border-left:0"><input type="text" name="'+val.id_comparacion+'" id="costable0'+indx+'" value="'+val.costo+'" class="costable'+
						' inputtab" placeholder="'+val.costo+'"/></div><div class="col-md-1 col-lg-1 factlistItem" '+
							'style="border-left:0" id="wey0'+indx+
						'">0</div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0" id="cantis0'+indx+'">'+val.devueltos+'</div><div class="col-md-1'+
							' col-lg-1 factlistItem" style="border-left:0" id="factp0'+indx+'">'+val.precio+'</div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0">'+
							'<input type="text" name="diftable" id="diftable0'+
						indx+'" value="" class="diftable inputtab" placeholder="" readonly/></div><div class="col-md-1'+
						' col-lg-1 factlistItem" style="border-left:0"><input type="text" name="cretable" id="cretable0'+indx+'" value="" class="cretable'+
						' inputtab" placeholder="" readonly/></div><div class="col-md-1 col-lg-1 factlistItem" style="border-left:0"><input type="text" name="tottable"'+
						' id="tottable0'+indx+'" value="" class="tottable inputtab" placeholder="" readonly/></div></div>')
						}
					}
				}

				
			})
			
			$(".BE1").html('<button type="button" class="btnExcel pedfact" id="'+compara+'"><i class="fa fa-download"'+
				' aria-hidden="true"></i> DESCARGAR FORMATO FACTURA</button>');
			$(".BE3").html('<button type="button" class="btnExcel elimbtn" data-id-folio="'+folicho+'" data-id-proveedor="'+provicho+'" style="background:#ec8b5c;border:0"><i class='+
				'"fa fa-download" aria-hidden="true"></i> ELIMINAR FACTURA</button>');
			calculaFactura();
		})
	}else{
		$(".totfact").html("");
		$(".sumnota").html("");
		$(".sumtotal").html("");
		$(".devuel").html("");
		$(".difer").html("");
		$(".factdetails").css("display","none");
	}
})

function getFacturas(values){
    return $.ajax({
        url: site_url+"/Facturas/getFacturas/"+values,
        type: "POST",
        dataType: "JSON",
    });
}

function getDetails(values){
    return $.ajax({
        url: site_url+"/Facturas/getDetails/",
        type: "POST",
        dataType: "JSON",
        data : {values: values}
    });
}
$(document).off("click", ".pedfact").on("click", ".pedfact", function (){
	var win = window.open(site_url+'Facturas/fill_excel/'+$(this).attr("id")+"/"+tiendas[tiendis], '_blank');
	if (win) {
		
	} else {
		alert('Por favor, activar la opción de abrir pestañas para este sitio.');
	}
})

$(document).off("click",".elimbtn").on("click",".elimbtn",function(){
	var values = {"proveedor":$(this).data("idProveedor"),"folio":$(this).data("idFolio")};
	eliminaFactura(JSON.stringify(values)).done(function(resp){
		toastr.success("Se elimino la factura", user_name);
			location.reload();
	})
})

function eliminaFactura(values){
    return $.ajax({
        url: site_url+"/Facturas/eliminaFactura",
        type: "POST",
        dataType: "JSON",
        data: {
            values : values
        },
    });
}


$(document).off("click", ".cerra").on("click", ".cerra", function (){
	var pedsist = $(this).closest(".pedsist");
	pedsist.addClass("pedsi")
	var uno = pedsist.closest(".body4").closest(".col-md-12").find("#precio");
	var dos = pedsist.closest(".body4").closest(".col-md-12").find(".body1");
	uno.css({"background":"transparent","color":"black"})
	dos.css({"background":"transparent","color":"black"})
	pedsist.attr("draggable","true");
	pedsist.closest(".body4").attr("ondrop","drop(event)");
	pedsist.closest(".body4").html("SOLTAR RECUADRO AQUÍ");
	uno.html(uno.html().substr(0, uno.html().indexOf('<')));
	dos.html(dos.html().substr(0, dos.html().indexOf('<'))); 
	$("#cuerpo2").prepend(pedsist);
	$(this).css("display","none");
	pedsist.closest(".col-md-12").find(".body1").css({"background":"white !important","color":"black !important"});
})


function cantidades(uno,dos){
	if(uno > dos){
		return "#ff000080";
	}else{
		if(uno < dos){
			return "rgb(204,153,255)";
		}else{
			if(uno === dos){
				return "rgb(255,242,204)";
			}
		}
	}
	return "white"; 
}

function costos(uno,dos) {
	if(uno > dos){
		return "#ff000080";
	}else if(uno < dos){
		return "#00800080";
	}
}

$(document).off("click", ".btnnel").on("click", ".btnnel", function (){
	event.preventDefault();
	location.reload()	
})

$(document).off("click", ".btnsalvar").on("click", ".btnsalvar", function (){
	event.preventDefault();
	$(document.body).css("overflow-y","scroll");
	$("#file_factura").val("");
	var devs = 0;var costu = null;var produ = null;var body4 = "";var devos = 0;var gift = 0;var gifted=0;var cuantos = 0;
	obj = [];
	for (var i = 0; i < $(".cuerpodiv").length; i++) {
		if ($("#cuerpodiv"+i).css('background') === "rgba(0, 0, 0, 0) none repeat scroll 0% 0% / auto padding-box border-box" || $("#cuerpodiv"+i).css('background') === "rgb(255, 255, 255) none repeat scroll 0% 0% / auto padding-box border-box") {
			devs = 0;
			devos = 0;
			gift = 0;
		}else{
			if ($("#cuerpodiv"+i).css('background') === "rgb(195, 136, 232) none repeat scroll 0% 0% / auto padding-box border-box"){
				gift = 1;
			}else{
				devs = 1;
				devos = $("#cuerpodiv"+i).find(".devolucion").find("#difis").val();
			}
		}
		devos = $("#cuerpodiv"+i).find(".devolucion").find("#difis").val();
		var uno = $("#cuerpodiv"+i).find(".devolucion").closest(".cuerpodiv").find(".body1");
		if ($("#cuerpodiv"+i).find(".devolucion").closest(".cuerpodiv").find(".body4").html() == "SOLTAR RECUADRO AQUÍ"){
			gifted = uno.html();
		}else{
			gifted = uno.html().substr(0, uno.html().indexOf('<'));
		}
		cuantos = gifted;
		body4 = $("#cuerpodiv"+i).find(".body4");
		if (body4.html() === "SOLTAR RECUADRO AQUÍ") {
			costu = 0;
			produ = "FACTURA"
		}else{
			produ = body4.find(".pedsist").attr('id');
			costu = body4.find(".pedsist").find(".costod").val();
		}	
		obj.push({
			"folio":folis,
			"factura":$("#cuerpodiv"+i).find(".body2").html(),
			"descripcion":$("#cuerpodiv"+i).find(".body3").html(),
			"producto":produ,
			"id_tienda":tiendis,
			"id_proveedor":$("#proveedor option:selected").val(),
			"costo":costu,
			"devolucion":devs,
			"devueltos":devos,
			"gift":gift,
			"gifted":gifted,
			"cuantos":cuantos
		})
	}

	guardaComparacion(JSON.stringify(obj)).done(function(resp){
		toastr.success("Se han guardado con exito", user_name);
		$(".checkhim").css("display","none");
		$("#cuerpo").html("");
		$("#cuerpo2").html("");
	})
	var sThisVal = "";
	$('input:checkbox.tienda').each(function () {
		if (this.checked) {
			sThisVal = $(this).attr('id')	
		}
	 });
	$(".facture").html("");
	tiendis = sThisVal.substring(6, sThisVal.length);
	getFacturas(sThisVal.substring(6, sThisVal.length))
	.done(function (resp) {
		if (resp) {
			$(".facture").html("<h2>Seleccione una factura</h2>");
			$.each(resp,function(indx,val){
				$(".facture").append('<div class="col-md-3 col-lg-2"><div class="form-check"><input class="form-check-input facty" type="checkbox" value="'+val.id_proveedor+'" '+
					'id="'+val.folio+'"><label class="form-check-label" for="facty'+val.folio+'" style="color:#000;background:#FFF !important;">'+val.folio+' - '+val.nombre+'</label></div></div>')
			});
		} else {
			$(".facture").html("<h2>Sin facturas</h2>");
		}
	})
});

function guardaComparacion(values){
    return $.ajax({
        url: site_url+"/Facturas/guardaComparacion",
        type: "POST",
        dataType: "JSON",
        data: {
            values : values
        },
    });
}



$(document).off("click", "#idev").on("click", "#idev", function (){
	event.preventDefault();
	var uno = $(this).closest(".devolucion").closest(".cuerpodiv").find(".body1");
	if ($(this).closest(".devolucion").closest(".cuerpodiv").find(".body4").html() == "SOLTAR RECUADRO AQUÍ"){
		uno = uno.html();
	}else{
		uno = uno.html().substr(0, uno.html().indexOf('<'));
	}
	
	if($(this).closest(".cuerpodiv").css('background') === "rgba(0, 0, 0, 0) none repeat scroll 0% 0% / auto padding-box border-box" || $(this).closest(".cuerpodiv").css('background') === "rgb(255, 255, 255) none repeat scroll 0% 0% / auto padding-box border-box"){
		$(this).closest(".cuerpodiv").css("background","#efff00");
		$(this).closest(".devolucion").find("#difis").css("display","block");
		$(this).closest(".devolucion").find("#difis").val(formatMoney(uno,0));
		$(this).closest(".cuerpodiv").find(".gifted").css("display","none");
	}else{
		$(this).closest(".cuerpodiv").css("background","#FFF");
		$(this).closest(".devolucion").find("#difis").css("display","none");
		$(this).closest(".devolucion").find("#difis").val("");
		$(this).closest(".cuerpodiv").find(".gifted").css("display","block");
	}
})

$(document).off("click", ".gifted").on("click", ".gifted", function (){
	event.preventDefault();
	if($(this).closest(".cuerpodiv").css('background') === "rgba(0, 0, 0, 0) none repeat scroll 0% 0% / auto padding-box border-box" || $(this).closest(".cuerpodiv").css('background') === "rgb(255, 255, 255) none repeat scroll 0% 0% / auto padding-box border-box"){
		$(this).closest(".cuerpodiv").css("background","#c388e8");
		$(this).closest(".cuerpodiv").find(".devolucion").css("display","none");
	}else{
		$(this).closest(".cuerpodiv").css("background","#FFF");
		$(this).closest(".cuerpodiv").find(".devolucion").css("display","block");
	}
})

$('.tienda').on('change', function() {
    $('.tienda').not(this).prop('checked', false);  
});

$('.facty').on('change', function() {
    $('.facty').not(this).prop('checked', false);  
});

$(document).off("keyup", "#searchy").on("keyup", "#searchy", function (){
	$('.pedsi').hide();
    var txt = $('#searchy').val();
    $('.pedsi').each(function(){
       if($(this).text().toUpperCase().indexOf(txt.toUpperCase()) != -1){
           $(this).show();
       }
    });
})

$(document).off("click", ".btnExcel").on("click", ".btnExcel", function (){
	event.preventDefault();
})

$(document).off("focusout", ".costod").on("focusout", ".costod", function () {
	if ($(this).val().substring(0,1) === "=" || $(this).val().substring(0,1) === "+") {

	}
});


/*$(document).off("change", "#file_codes").on("change", "#file_codes", function(event) {
	event.preventDefault();
	blockPage();
	var fdata = new FormData($("#upload_codes")[0]);
	uploadExiaa(fdata)
		.done(function (resp) {

		});
});


function uploadExiaa(formData) {
	return $.ajax({
		url: site_url+"Facturas/uploadExi",
		type: "POST",
		cache: false,
		contentType: false,
		processData:false,
		dataType:"JSON",
		data: formData,
	});
}*/

$(document).off("click", ".sinFact").on("click", ".sinFact", function(event){
	event.preventDefault();
	getModal("Facturas/getnumeros", function (){
		
	});
});

$(document).off("click", ".gifted2").on("click", ".gifted2", function (){
	event.preventDefault();
	if($(this).closest(".factlistItem").css('background') === "rgba(0, 0, 0, 0) none repeat scroll 0% 0% / auto padding-box border-box" || $(this).closest(".factlistItem").css('background') === "rgb(255, 255, 255) none repeat scroll 0% 0% / auto padding-box border-box"){
		$(this).closest(".factlistItem").css("background","#c388e8");
		$(this).closest(".factlistItem").closest(".factlisty").css("background","#c388e8");
		$(this).closest(".factlistItem").find(".devolucion2").css("display","none");
		setGifted($(this).attr("name"),1,$(this).data("idCant"));
	}else{
		$(this).closest(".factlistItem").css("background","#FFF");
		$(this).closest(".factlistItem").closest(".factlisty").css("background","#FFF");
		$(this).closest(".factlistItem").find(".devolucion2").css("display","block");
		setGifted($(this).attr("name"),0,$(this).data("idCant"));
	}
})

$(document).off("click", "#idev2").on("click", "#idev2", function (){
	event.preventDefault();	
	if($(this).closest(".factlistItem").css('background') === "rgba(0, 0, 0, 0) none repeat scroll 0% 0% / auto padding-box border-box" || $(this).closest(".factlistItem").css('background') === "rgb(255, 255, 255) none repeat scroll 0% 0% / auto padding-box border-box"){
		$(this).closest(".factlistItem").css("background","#efff00");
		$(this).closest(".devolucion2").find("#difis2").css("display","block");
		$(this).closest(".devolucion2").find("#difis2").val(formatMoney($(this).data("idCant"),0));
		$(this).closest(".factlistItem").find(".gifted2").css("display","none");
		$(this).closest(".factlistItem").closest(".factlisty").css("background","#efff00");
		setDevolution($(this).attr("name"),1,$(this).closest(".devolucion2").find("#difis2").val());
	}else{
		$(this).closest(".factlistItem").css("background","#FFF");
		$(this).closest(".devolucion2").find("#difis2").css("display","none");
		$(this).closest(".devolucion2").find("#difis2").val("");
		$(this).closest(".factlistItem").find(".gifted2").css("display","block");
		$(this).closest(".factlistItem").closest(".factlisty").css("background","#FFF");
		setDevolution($(this).attr("name"),0,0);
	}
})

function setGifted(id_comparacion,estats,indxs){
	return $.ajax({
        url: site_url+"/Facturas/setGifted/"+id_comparacion+"/"+estats+"/"+indxs,
        type: "POST",
        dataType: "JSON",
    });
}

$(document).off("keyup", "#difis2").on("keyup", "#difis2", function (){
	setDevolution($(this).attr("name"),1,$(this).val());
});

function setDevolution(id_comparacion,estats,indxs){
	return $.ajax({
        url: site_url+"/Facturas/setDevolution/"+id_comparacion+"/"+estats+"/"+indxs,
        type: "POST",
        dataType: "JSON",
    });
}