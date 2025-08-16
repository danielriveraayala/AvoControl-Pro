/**
 * DataTables Spanish language configuration
 * Solves CORS issues with external CDN language files
 */

window.DataTablesSpanish = {
    decimal: "",
    emptyTable: "No hay información disponible",
    info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
    infoEmpty: "Mostrando 0 a 0 de 0 entradas",
    infoFiltered: "(filtrado de _MAX_ entradas totales)",
    infoPostFix: "",
    thousands: ",",
    lengthMenu: "Mostrar _MENU_ entradas",
    loadingRecords: "Cargando...",
    processing: "Procesando...",
    search: "Buscar:",
    zeroRecords: "No se encontraron registros que coincidan",
    paginate: {
        first: "Primero",
        last: "Último",
        next: "Siguiente",
        previous: "Anterior"
    },
    aria: {
        sortAscending: ": activar para ordenar la columna de manera ascendente",
        sortDescending: ": activar para ordenar la columna de manera descendente"
    }
};

// Helper function to get Spanish language config
function getDataTablesSpanishConfig() {
    return window.DataTablesSpanish;
}