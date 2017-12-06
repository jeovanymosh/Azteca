jQuery(document).ready(function($) {

	$(document).off("click", ".save").on("click", ".save", function(event) {
		event.preventDefault();
		sendDatos("Familias/accion/I", $("#form_familia_new"));
	});

	$(document).off("click", ".update").on("click", ".update", function(event) {
		event.preventDefault();
		sendDatos("Familias/accion/U/",$("#form_familia_edit"));
	});

	$(document).off("click", ".delete").on("click", ".delete", function(event) {
		event.preventDefault();
		sendDatos("Familias/accion/D", $("#form_familia_delete"));
	});

});