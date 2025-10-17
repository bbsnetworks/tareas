document.addEventListener("DOMContentLoaded", () => {
    cargarSolicitudes();
    mostrarDiasDisponibles();

    // Capturar el envío del formulario
    document.getElementById("vacacionesForm").addEventListener("submit", function (event) {
        event.preventDefault(); // Evita que la página se recargue

        let idUser = document.getElementById("user").value;
        let inicio = document.getElementById("fecha_inicio").value;
        let fin = document.getElementById("fecha_fin").value;
        let tipo = document.getElementById("tipo").value;
        const agregar = document.getElementById("agregar");
        agregar.disabled = true;

        if (!inicio || !fin) {
            Swal.fire("Error", "Las fechas son obligatorias", "error");
            return;
        }

        let data = {
            idUser: idUser,
            inicio: inicio,
            fin: fin,
            tipo: tipo
        };

        fetch("../php/agregar_vacaciones.php", {
            method: "POST",
            body: JSON.stringify(data),
            headers: {
                "Content-Type": "application/json"
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                Swal.fire("Éxito", "Vacaciones registradas correctamente", "success");
                document.getElementById("vacacionesForm").reset();
                cargarSolicitudes();
                mostrarDiasDisponibles();
                agregar.disabled = false;
            } else {
                Swal.fire("Error", result.error, "error");
                console.log(result.datos);
                console.log(result.dias);
                agregar.disabled = false;
            }
        })
        .catch(error => {
            console.error("Error en fetch:", error);
            Swal.fire("Error", "Hubo un problema al registrar las vacaciones", "error");
            agregar.disabled = false;
        });
    });
});

function cargarSolicitudes() {
    let fechaFiltro = document.getElementById("filtroFecha").value;
    let [anio, mes] = fechaFiltro ? fechaFiltro.split("-") : ["", ""];

    fetch(`../php/obtener_solicitudes.php?anio=${anio}&mes=${mes}`)
        .then(response => response.json())
        .then(data => {
            console.log("Respuesta del servidor:", data); // 🔍 DEBUG

            if (!Array.isArray(data)) {
                console.error("Error: La respuesta no es un array", data);
                return;
            }

            let tabla = document.getElementById("vacacionesTableBody");
            tabla.innerHTML = "";

            data.forEach(solicitud => {
                let fila = `
                    <tr class="hover:bg-gray-700 transition-colors duration-150">
                        <td class="px-4 py-2 border border-gray-700 text-center">${solicitud.id}</td>
                        <td class="px-4 py-2 border border-gray-700 text-center">${solicitud.nombre}</td>
                        <td class="px-4 py-2 border border-gray-700 text-center">${solicitud.ingreso}</td>
                        <td class="px-4 py-2 border border-gray-700 text-center">${solicitud.inicio}</td>
                        <td class="px-4 py-2 border border-gray-700 text-center">${solicitud.fin}</td>
                        <td class="px-4 py-2 border border-gray-700 text-center">${solicitud.tipo}</td>`;

                if(solicitud.estado == "1"){
                    fila += `<td class="border border-gray-300 p-2 text-blue-500">No Iniciado <i class="bi bi-calendar2-check-fill"></i></td>`;
                } else if(solicitud.estado == "2"){
                    fila += `<td class="border border-gray-300 p-2 text-yellow-500">En Proceso <i class="bi bi-calendar2-check-fill"></i></td>`;
                } else if(solicitud.estado == "3"){
                    fila += `<td class="border border-gray-300 p-2 text-green-500">Finalizado <i class="bi bi-calendar2-check-fill"></i></td>`;
                }

                // Mostrar botones solo para usuario 20
                if (usuarioActual === '20') {
                    fila += `
                        <td class="border border-gray-300 p-2">
                            <button onclick="editarSolicitud(${solicitud.id})" class="bg-blue-500 text-white px-2 py-1 rounded cursor-pointer">Editar</button>
                        </td>
                        <td class="border border-gray-300 p-2">
                            <button onclick="eliminarSolicitud(${solicitud.id})" class="bg-red-500 text-white px-2 py-1 rounded ml-2 cursor-pointer">Eliminar</button>
                        </td>
                    `;
                } else {
                    fila += `<td class="border border-gray-300 p-2"></td><td class="border border-gray-300 p-2"></td>`;
                }

                fila += `</tr>`;
                tabla.innerHTML += fila;
            });
        })
        .catch(error => console.error("Error en fetch:", error));
}


function eliminarSolicitud(id) {
    Swal.fire({
        title: "¿Seguro que quieres eliminar esta solicitud?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`../php/eliminar_solicitud.php?id=${id}`, { method: "DELETE" })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        Swal.fire("Eliminado", "Solicitud eliminada correctamente", "success");
                        cargarSolicitudes();
                        mostrarDiasDisponibles();
                    } else {
                        Swal.fire("Error", "Error al eliminar", "error");
                    }
                })
                .catch(error => console.error("Error en fetch:", error));
        }
    });
}

function editarSolicitud(id) {
    fetch(`../php/editar_vacaciones.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                Swal.fire("Error", data.error, "error");
                //console.log(data.datos);
                return;
            }

            // Obtener datos de la solicitud
            let solicitud = data.solicitud;

            // Mostrar modal de edición con SweetAlert2
            Swal.fire({
                title: "Editar Solicitud",
                html: `
                    <label class="swal2-input-label">Fecha de Inicio:</label>
                    <input type="date" id="editFechaInicio" class="swal2-input" value="${solicitud.inicio}">
                    
                    <label class="swal2-input-label">Fecha de Fin:</label>
                    <input type="date" id="editFechaFin" class="swal2-input" value="${solicitud.fin}">
                    
                    <label class="swal2-input-label">Tipo:</label>
                    <select id="editTipo" class="swal2-input">
                        <option value="vacaciones" ${solicitud.tipo === 'vacaciones' ? 'selected' : ''}>Vacaciones</option>
                        <option value="permiso" ${solicitud.tipo === 'permiso' ? 'selected' : ''}>Permiso</option>
                        <option value="boda" ${solicitud.tipo === 'boda' ? 'selected' : ''}>Boda</option>
                        <option value="mayor" ${solicitud.tipo === 'mayor' ? 'selected' : ''}>Fuerza Mayor</option>
                        <option value="enfermedad" ${solicitud.tipo === 'enfermedad' ? 'selected' : ''}>Enfermedad</option>
                    </select>
                `,
                showCancelButton: true,
                confirmButtonText: "Guardar Cambios",
                cancelButtonText: "Cancelar",
                preConfirm: () => {
                    return {
                        id: id,
                        inicio: document.getElementById("editFechaInicio").value,
                        fin: document.getElementById("editFechaFin").value,
                        tipo: document.getElementById("editTipo").value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    actualizarSolicitud(result.value);
                }
            });
        })
        .catch(error => {
            console.error("Error en fetch:", error);
            Swal.fire("Error", "No se pudo obtener la solicitud", "error");
        });
}

function actualizarSolicitud(data) {
    fetch("../php/actualizar_solicitud.php", {
        method: "POST",
        body: JSON.stringify(data),
        headers: { "Content-Type": "application/json" }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            Swal.fire("Éxito", "Solicitud actualizada correctamente", "success");
            cargarSolicitudes();
            mostrarDiasDisponibles();
        } else {
            Swal.fire("Error", result.error, "error");
        }
    })
    .catch(error => {
        console.error("Error en fetch:", error);
        Swal.fire("Error", "No se pudo actualizar la solicitud", "error");
    });
}
function mostrarDiasDisponibles() {
    let selectUsuario = document.getElementById("usuario");
    let idUser = selectUsuario.value;
    
    fetch(`../php/obtener_dias_usados.php?idUser=${idUser}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById("diasVacaciones").innerText = `${data.dias_restantes.vacaciones} días (usados: ${data.dias_usados.vacaciones})`;
            document.getElementById("diasPermiso").innerText = `${data.dias_restantes.permiso} días (usados: ${data.dias_usados.permiso})`;
            document.getElementById("diasBoda").innerText = `${data.dias_restantes.boda} días (usados: ${data.dias_usados.boda})`;
            document.getElementById("diasEmbarazo").innerText = `${data.dias_restantes.embarazo} días (usados: ${data.dias_usados.embarazo})`;
            document.getElementById("diasMayor").innerText = `Indefinido`;
            document.getElementById("diasEnfermedad").innerText = `${data.dias_restantes.enfermedad} días (usados: ${data.dias_usados.enfermedad})`;
        })
        .catch(error => console.error("Error al obtener días usados:", error));
}

document.getElementById("usuario").addEventListener("change", mostrarDiasDisponibles);

document.getElementById("usuario").addEventListener("change", mostrarDiasDisponibles);
document.getElementById("tipo").addEventListener("change", mostrarDiasDisponibles);