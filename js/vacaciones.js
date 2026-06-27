document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("vacacionesForm");
  const selUsuario = document.getElementById("usuario");
  const selTipo = document.getElementById("tipo");
  const filtroFecha = document.getElementById("filtroFecha");

  // Poner mes actual si el input está vacío
  if (filtroFecha && !filtroFecha.value) {
    const hoy = new Date();
    const anio = hoy.getFullYear();
    const mes = String(hoy.getMonth() + 1).padStart(2, "0");
    filtroFecha.value = `${anio}-${mes}`;
  }

  if (filtroFecha) {
    filtroFecha.addEventListener("change", () => {
      console.log("Mes seleccionado:", filtroFecha.value);
      cargarSolicitudes();
    });
  }

  if (selUsuario) {
    selUsuario.addEventListener("change", mostrarDiasDisponibles);
  }

  if (selTipo) {
    selTipo.addEventListener("change", mostrarDiasDisponibles);
  }

  if (form) {
    form.addEventListener("submit", function (event) {
      event.preventDefault();

      const agregar = document.getElementById("agregar");
      if (agregar) agregar.disabled = true;

      let idUser = document.getElementById("user")?.value || "";
      let inicio = document.getElementById("fecha_inicio")?.value || "";
      let fin = document.getElementById("fecha_fin")?.value || "";
      let tipo = document.getElementById("tipo")?.value || "";

      if (!inicio || !fin) {
        Swal.fire("Error", "Las fechas son obligatorias", "error");
        if (agregar) agregar.disabled = false;
        return;
      }

      let data = {
        idUser: idUser,
        inicio: inicio,
        fin: fin,
        tipo: tipo,
      };

      fetch("../php/agregar_vacaciones.php", {
        method: "POST",
        body: JSON.stringify(data),
        headers: {
          "Content-Type": "application/json",
        },
      })
        .then((response) => response.json())
        .then((result) => {
          if (result.success) {
            Swal.fire("Éxito", "Vacaciones registradas correctamente", "success");
            form.reset();

            if (filtroFecha) {
              const hoy = new Date();
              const anio = hoy.getFullYear();
              const mes = String(hoy.getMonth() + 1).padStart(2, "0");
              filtroFecha.value = `${anio}-${mes}`;
            }

            cargarSolicitudes();
            mostrarDiasDisponibles();
          } else {
            Swal.fire("Error", result.error, "error");
            console.log(result.datos);
            console.log(result.dias);
          }
        })
        .catch((error) => {
          console.error("Error en fetch:", error);
          Swal.fire("Error", "Hubo un problema al registrar las vacaciones", "error");
        })
        .finally(() => {
          if (agregar) agregar.disabled = false;
        });
    });
  } else {
    console.warn("vacacionesForm no existe en esta vista; submit listener omitido.");
  }

  cargarSolicitudes();
  mostrarDiasDisponibles();
});

function cargarSolicitudes() {
  const filtroFecha = document.getElementById("filtroFecha");
  const tabla = document.getElementById("vacacionesTableBody");

  if (!tabla) {
    console.warn("vacacionesTableBody no existe en esta vista.");
    return;
  }

  let anio;
  let mes;

  if (filtroFecha && filtroFecha.value) {
    [anio, mes] = filtroFecha.value.split("-");
  } else {
    const hoy = new Date();
    anio = hoy.getFullYear();
    mes = String(hoy.getMonth() + 1).padStart(2, "0");

    if (filtroFecha) {
      filtroFecha.value = `${anio}-${mes}`;
    }
  }

  console.log("Enviando filtro:", { anio, mes });

  tabla.innerHTML = `
    <tr>
      <td colspan="9" class="px-4 py-8 text-center text-slate-400">
        <i class="bi bi-arrow-repeat animate-spin text-2xl text-cyan-300"></i>
        <p class="mt-2">Cargando registros...</p>
      </td>
    </tr>
  `;

  fetch(`../php/obtener_solicitudes.php?anio=${anio}&mes=${mes}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Error HTTP ${response.status}`);
      }

      return response.json();
    })
    .then((data) => {
      console.log("Respuesta del servidor:", data);

      if (!Array.isArray(data)) {
        console.error("Error: La respuesta no es un array", data);
        tabla.innerHTML = `
          <tr>
            <td colspan="9" class="px-4 py-10 text-center text-red-300">
              La respuesta del servidor no es válida.
            </td>
          </tr>
        `;
        return;
      }

      tabla.innerHTML = "";

      if (data.length === 0) {
        tabla.innerHTML = `
          <tr>
            <td colspan="9" class="px-4 py-10 text-center text-slate-400">
              <i class="bi bi-inbox text-4xl block mb-3 text-slate-500"></i>
              No hay registros para este mes.
            </td>
          </tr>
        `;
        return;
      }

      data.forEach((solicitud) => {
        let tipoChip = getTipoVacacionChip(solicitud.tipo);
        let estadoChip = getEstadoVacacionChip(solicitud.estado);

        let fila = `
          <tr class="bg-[#061426] hover:bg-[#0a213c] transition border-b border-cyan-400/10">
            <td class="px-4 py-4 font-black text-cyan-200">${solicitud.id}</td>
            <td class="px-4 py-4 font-bold text-white">${solicitud.nombre}</td>
            <td class="px-4 py-4 text-slate-300">${solicitud.ingreso}</td>
            <td class="px-4 py-4 font-mono text-xs text-slate-300 whitespace-nowrap">${solicitud.inicio}</td>
            <td class="px-4 py-4 font-mono text-xs text-slate-300 whitespace-nowrap">${solicitud.fin}</td>
            <td class="px-4 py-4">${tipoChip}</td>
            <td class="px-4 py-4">${estadoChip}</td>
        `;

        if (String(usuarioActual) === "20") {
          fila += `
            <td class="px-4 py-4 text-center">
              <button
                type="button"
                onclick="editarSolicitud(${solicitud.id})"
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-yellow-400/15 hover:bg-yellow-400/25 text-yellow-200 border border-yellow-300/30 transition"
                title="Editar"
              >
                <i class="bi bi-pencil-square"></i>
              </button>
            </td>

            <td class="px-4 py-4 text-center">
              <button
                type="button"
                onclick="eliminarSolicitud(${solicitud.id})"
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-red-400/15 hover:bg-red-400/25 text-red-200 border border-red-300/30 transition"
                title="Eliminar"
              >
                <i class="bi bi-trash3-fill"></i>
              </button>
            </td>
          `;
        } else {
          fila += `
            <td class="px-4 py-4 text-center text-slate-500">—</td>
            <td class="px-4 py-4 text-center text-slate-500">—</td>
          `;
        }

        fila += `</tr>`;

        tabla.insertAdjacentHTML("beforeend", fila);
      });
    })
    .catch((error) => {
      console.error("Error en fetch:", error);

      tabla.innerHTML = `
        <tr>
          <td colspan="9" class="px-4 py-10 text-center text-red-300">
            <i class="bi bi-exclamation-triangle text-4xl block mb-3"></i>
            Error al cargar registros.
            <br>
            <span class="text-sm">${error.message}</span>
          </td>
        </tr>
      `;
    });
}

function eliminarSolicitud(id) {
  Swal.fire({
    title: "¿Seguro que quieres eliminar esta solicitud?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      fetch(`../php/eliminar_solicitud.php?id=${id}`, { method: "DELETE" })
        .then((response) => response.json())
        .then((result) => {
          if (result.success) {
            Swal.fire("Eliminado", "Solicitud eliminada correctamente", "success");
            cargarSolicitudes();
            mostrarDiasDisponibles();
          } else {
            Swal.fire("Error", "Error al eliminar", "error");
          }
        })
        .catch((error) => console.error("Error en fetch:", error));
    }
  });
}

function editarSolicitud(id) {
  fetch(`../php/editar_vacaciones.php?id=${id}`)
    .then((response) => response.json())
    .then((data) => {
      if (!data.success) {
        Swal.fire("Error", data.error, "error");
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
              <option value="vacaciones" ${
                solicitud.tipo === "vacaciones" ? "selected" : ""
              }>Vacaciones</option>
              <option value="permiso" ${
                solicitud.tipo === "permiso" ? "selected" : ""
              }>Permiso</option>
              <option value="boda" ${
                solicitud.tipo === "boda" ? "selected" : ""
              }>Boda</option>
              <option value="mayor" ${
                solicitud.tipo === "mayor" ? "selected" : ""
              }>Fuerza Mayor</option>
              <option value="enfermedad" ${
                solicitud.tipo === "enfermedad" ? "selected" : ""
              }>Enfermedad</option>
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
            tipo: document.getElementById("editTipo").value,
          };
        },
      }).then((result) => {
        if (result.isConfirmed) {
          actualizarSolicitud(result.value);
        }
      });
    })
    .catch((error) => {
      console.error("Error en fetch:", error);
      Swal.fire("Error", "No se pudo obtener la solicitud", "error");
    });
}

function actualizarSolicitud(data) {
  fetch("../php/actualizar_solicitud.php", {
    method: "POST",
    body: JSON.stringify(data),
    headers: { "Content-Type": "application/json" },
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        Swal.fire("Éxito", "Solicitud actualizada correctamente", "success");
        cargarSolicitudes();
        mostrarDiasDisponibles();
      } else {
        Swal.fire("Error", result.error, "error");
      }
    })
    .catch((error) => {
      console.error("Error en fetch:", error);
      Swal.fire("Error", "No se pudo actualizar la solicitud", "error");
    });
}

function mostrarDiasDisponibles() {
  let selectUsuario = document.getElementById("usuario");
  if (!selectUsuario) return;

  let idUser = selectUsuario.value;

  fetch(`../php/obtener_dias_usados.php?idUser=${idUser}`)
    .then((response) => response.json())
    .then((data) => {
      // si tu backend llega a devolver success=false
      if (!data || data.success === false) {
        console.error("Backend respondió error:", data);
        return;
      }

      // ✅ Evita tronar si algún id no existe en la vista
      const elVac = document.getElementById("diasVacaciones");
      const elPer = document.getElementById("diasPermiso");
      const elBod = document.getElementById("diasBoda");
      const elEmb = document.getElementById("diasEmbarazo");
      const elMay = document.getElementById("diasMayor");
      const elEnf = document.getElementById("diasEnfermedad");

      if (elVac)
        elVac.innerText = `${data.dias_restantes.vacaciones} días (usados: ${data.dias_usados.vacaciones})`;
      if (elPer)
        elPer.innerText = `${data.dias_restantes.permiso} días (usados: ${data.dias_usados.permiso})`;
      if (elBod)
        elBod.innerText = `${data.dias_restantes.boda} días (usados: ${data.dias_usados.boda})`;

      // OJO: tu backend NO regresa "embarazo" en el PHP que me pasaste
      // así que lo pongo con fallback a 0 si no existe
      if (elEmb) {
        const restEmb = data.dias_restantes?.embarazo ?? 0;
        const usadosEmb = data.dias_usados?.embarazo ?? 0;
        elEmb.innerText = `${restEmb} días (usados: ${usadosEmb})`;
      }

      if (elMay) elMay.innerText = `Indefinido`;

      if (elEnf)
        elEnf.innerText = `${data.dias_restantes.enfermedad} días (usados: ${data.dias_usados.enfermedad})`;
    })
    .catch((error) => console.error("Error al obtener días usados:", error));
}
function getTipoVacacionChip(tipo) {
  const tipos = {
    vacaciones: {
      label: "Vacaciones",
      icon: "bi-suitcase-lg-fill",
      cls: "bg-blue-500/20 text-blue-300 ring-1 ring-blue-500/30",
    },
    permiso: {
      label: "Permiso",
      icon: "bi-calendar-check-fill",
      cls: "bg-yellow-500/20 text-yellow-200 ring-1 ring-yellow-500/30",
    },
    boda: {
      label: "Boda",
      icon: "bi-heart-fill",
      cls: "bg-green-500/20 text-green-300 ring-1 ring-green-500/30",
    },
    mayor: {
      label: "Fuerza Mayor",
      icon: "bi-exclamation-triangle-fill",
      cls: "bg-slate-500/20 text-slate-300 ring-1 ring-slate-500/30",
    },
    enfermedad: {
      label: "Enfermedad",
      icon: "bi-bandaid-fill",
      cls: "bg-red-500/20 text-red-300 ring-1 ring-red-500/30",
    },
  };

  const item = tipos[tipo] || {
    label: tipo || "Sin tipo",
    icon: "bi-question-circle-fill",
    cls: "bg-slate-500/20 text-slate-300 ring-1 ring-slate-500/30",
  };

  return `
    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-black ${item.cls} whitespace-nowrap">
      <i class="bi ${item.icon}"></i>
      ${item.label}
    </span>
  `;
}

function getEstadoVacacionChip(estado) {
  const estados = {
    "1": {
      label: "No iniciado",
      icon: "bi-calendar2-check-fill",
      cls: "bg-blue-500/20 text-blue-300 ring-1 ring-blue-500/30",
    },
    "2": {
      label: "En proceso",
      icon: "bi-hourglass-split",
      cls: "bg-yellow-500/20 text-yellow-200 ring-1 ring-yellow-500/30",
    },
    "3": {
      label: "Finalizado",
      icon: "bi-check-circle-fill",
      cls: "bg-green-500/20 text-green-300 ring-1 ring-green-500/30",
    },
  };

  const item = estados[String(estado)] || {
    label: "Sin estado",
    icon: "bi-question-circle-fill",
    cls: "bg-slate-500/20 text-slate-300 ring-1 ring-slate-500/30",
  };

  return `
    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-black ${item.cls} whitespace-nowrap">
      <i class="bi ${item.icon}"></i>
      ${item.label}
    </span>
  `;
}