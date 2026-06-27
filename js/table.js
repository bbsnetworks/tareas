const CATEGORY_STYLES = {
  Cobertura: { chip: "bg-blue-500/20 text-blue-300 ring-1 ring-blue-500/30" },
  Instalación: {
    chip: "bg-green-500/20 text-green-300 ring-1 ring-green-500/30",
  },
  Reporte: {
    chip: "bg-orange-500/20 text-orange-300 ring-1 ring-orange-500/30",
  },
  "Sin Servicio": { chip: "bg-red-500/20 text-red-300 ring-1 ring-red-500/30" },
  "Cambio de domicilio": {
    chip: "bg-purple-500/20 text-purple-300 ring-1 ring-purple-500/30",
  },
  Cancelación: { chip: "bg-red-500/20 text-red-300 ring-1 ring-red-500/30" },
  Servicios: { chip: "bg-cyan-500/20 text-cyan-300 ring-1 ring-cyan-500/30" },
  Camaras: {
    chip: "bg-indigo-500/20 text-indigo-300 ring-1 ring-indigo-500/30",
  },
  Torniquetes: {
    chip: "bg-yellow-500/20 text-yellow-300 ring-1 ring-yellow-500/30",
  },
  Otros: { chip: "bg-gray-500/20 text-gray-300 ring-1 ring-gray-500/30" },
};

let date = moment().format("YYYY-MM");

const inputFecha = document.getElementById("fecha");
if (inputFecha) {
  inputFecha.value = date;
}

let mes = moment().format("MM");
let year = moment().format("YYYY");

let tablaOriginalRows = [];
let tablaFiltradaRows = [];
let paginaActual = 1;
let registrosPorPagina = 10;
let columnaOrdenActual = null;
let direccionOrden = "asc";

let modalEvent;
let editClienteTimer = null;

function hideEditClienteResults() {
  const box = document.getElementById("editClienteResults");
  if (!box) return;
  box.classList.add("hidden");
  box.innerHTML = "";
}
function escapeHTML(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function getEstadoChip(estado) {
  const estados = {
    creado: {
      label: "Creado",
      icon: "bi-plus-circle-fill",
      cls: "bg-blue-500/20 text-blue-300 border-blue-400/30",
    },
    proceso: {
      label: "Proceso",
      icon: "bi-hammer",
      cls: "bg-yellow-500/20 text-yellow-200 border-yellow-400/30",
    },
    terminado: {
      label: "Terminado",
      icon: "bi-check-circle-fill",
      cls: "bg-green-500/20 text-green-300 border-green-400/30",
    },
    cancelado: {
      label: "Cancelado",
      icon: "bi-x-circle-fill",
      cls: "bg-red-500/20 text-red-300 border-red-400/30",
    },
  };

  const item = estados[estado] || {
    label: "Sin estado",
    icon: "bi-question-circle-fill",
    cls: "bg-slate-500/20 text-slate-300 border-slate-400/30",
  };

  return `
    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-sm font-black border ${item.cls}">
      <i class="bi ${item.icon}"></i>
      ${item.label}
    </span>
  `;
}

function getCategoriaChip(categoria) {
  const categoriaRaw = categoria || "Otros";
  const catStyle =
    CATEGORY_STYLES?.[categoriaRaw]?.chip ||
    CATEGORY_STYLES?.["Otros"]?.chip ||
    "bg-slate-500/20 text-slate-300 ring-1 ring-slate-500/30";

  return `
    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-sm font-black ${catStyle}">
      <i class="bi bi-tag-fill"></i>
      ${escapeHTML(categoriaRaw)}
    </span>
  `;
}

function getEstadoOptions(estadoActual) {
  const estados = [
    { value: "creado", label: "Creado" },
    { value: "proceso", label: "En proceso" },
    { value: "terminado", label: "Terminado" },
    { value: "cancelado", label: "Cancelado" },
  ];

  return estados
    .map((estado) => {
      const selected = estado.value === estadoActual ? "selected" : "";
      return `<option value="${estado.value}" ${selected}>${estado.label}</option>`;
    })
    .join("");
}

function renderEditClienteResults(clientes) {
  const box = document.getElementById("editClienteResults");
  const inputVisible = document.getElementById("editClienteSearch");
  const inputHidden = document.getElementById("editCliente");

  if (!box || !inputVisible || !inputHidden) return;

  if (!clientes.length) {
    box.innerHTML = `<div class="px-4 py-3 text-sm text-gray-300">No se encontraron clientes</div>`;
    box.classList.remove("hidden");
    return;
  }

  box.innerHTML = clientes
    .map(
      (cliente) => `
    <button
      type="button"
      class="w-full text-left px-4 py-3 border-b border-gray-700 hover:bg-[#4c566a]"
      data-id="${cliente.idcliente}"
      data-nombre="${cliente.nombre}"
    >
      <div class="text-white font-semibold">${cliente.idcliente} - ${cliente.nombre}</div>
    </button>
  `,
    )
    .join("");

  box.classList.remove("hidden");

  box.querySelectorAll("button[data-id]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.getAttribute("data-id");
      const nombre = btn.getAttribute("data-nombre");

      inputHidden.value = id;
      inputVisible.value = `${id} - ${nombre}`;
      hideEditClienteResults();
    });
  });
}

async function buscarEditClientes(q) {
  try {
    const response = await fetch(
      `../php/buscar_clientes.php?q=${encodeURIComponent(q)}`,
    );
    const data = await response.json();

    if (!response.ok || !data.success) {
      hideEditClienteResults();
      return;
    }

    renderEditClienteResults(data.clientes || []);
  } catch (error) {
    console.error("Error buscando clientes:", error);
    hideEditClienteResults();
  }
}

if (inputFecha) {
  inputFecha.addEventListener("change", () => {
    const valorFecha = inputFecha.value;

    if (!valorFecha) return;

    year = valorFecha.split("-")[0];
    mes = valorFecha.split("-")[1];

    paginaActual = 1;
    cargarTabla(mes, year);
  });
}

async function cargarTabla(mesSeleccionado, yearSeleccionado) {
  const tablaContainer = document.getElementById("tabla");

  if (!tablaContainer) return;

  const formData = new FormData();
  formData.append("mes", mesSeleccionado);
  formData.append("year", yearSeleccionado);

  tablaContainer.innerHTML = `
    <div class="p-8 text-center text-slate-300">
      <i class="bi bi-arrow-repeat animate-spin text-3xl text-cyan-300"></i>
      <p class="mt-3">Cargando tareas...</p>
    </div>
  `;

  try {
    const response = await fetch("../php/cargarTabla.php", {
      method: "POST",
      body: formData,
    });

    if (!response.ok) {
      throw new Error(`Error HTTP ${response.status}`);
    }

    const html = await response.text();
    tablaContainer.innerHTML = html;

    prepararTablaPersonalizada();
  } catch (error) {
    console.error("Error al cargar la tabla:", error);

    tablaContainer.innerHTML = `
      <div class="p-8 text-center text-red-300">
        <i class="bi bi-exclamation-triangle text-3xl"></i>
        <p class="mt-3">Error al cargar la tabla.</p>
        <p class="text-sm text-red-200 mt-1">${error.message}</p>
      </div>
    `;
  }
}

cargarTabla(mes, year);

async function editGI(id) {
  const modal2 = document.getElementById("modal2");
  const resultado = document.getElementById("resultado");

  const formData = new FormData();
  formData.append("id", id);

  try {
    const response = await fetch("../php/editarGI.php", {
      method: "POST",
      body: formData,
    });

    if (!response.ok) {
      throw new Error(`Error HTTP ${response.status}`);
    }

    const html = await response.text();

    if (modal2) {
      modal2.innerHTML = html;
    }
  } catch (error) {
    console.error("Error al editar el registro:", error);

    if (resultado) {
      resultado.innerHTML = `Error al editar el registro: ${error.message}`;
    }
  }
}

function closeModalHandler() {
  const modal = document.getElementById("eventModal");
  modal.classList.add("hidden");
  if (eventMap) {
    eventMap.remove();
  }
}
document.getElementById("closeModal").addEventListener("click", () => {
  const modal = document.getElementById("eventModal");
  modal.classList.add("hidden");
});
document.getElementById("closeModalButton").addEventListener("click", () => {
  const modal = document.getElementById("eventModal");
  modal.classList.add("hidden");
});
function prepararTablaPersonalizada() {
  const tabla = document.querySelector("#tabla table");

  if (!tabla) {
    actualizarResumenTabla(0, 0);
    return;
  }

  tabla.classList.add("w-full", "text-sm", "text-left", "border-collapse");

  const thead = tabla.querySelector("thead");
  const tbody = tabla.querySelector("tbody");

  if (!tbody) return;

  if (thead) {
    thead.classList.add("bg-[#081a30]", "text-slate-200");

    thead.querySelectorAll("th").forEach((th, index) => {
      const noOrdenar = th.dataset.noSort === "true";

      th.classList.add(
        "px-4",
        "py-4",
        "font-black",
        "text-sm",
        "border-b",
        "border-cyan-400/10",
        "select-none",
        "whitespace-nowrap",
      );

      if (noOrdenar) {
        th.classList.add("cursor-default");
        return;
      }

      th.classList.add("cursor-pointer");
      th.setAttribute("data-columna", index);

      if (!th.querySelector(".sort-icon")) {
        th.innerHTML = `
      <span class="inline-flex items-center gap-2">
        ${th.innerHTML}
        <i class="sort-icon bi bi-arrow-down-up text-slate-500 text-xs"></i>
      </span>
    `;
      }

      th.addEventListener("click", () => ordenarTabla(index));
    });
  }

  tablaOriginalRows = Array.from(tbody.querySelectorAll("tr"));

  tablaOriginalRows.forEach((row) => {
    row.classList.add(
      "border-b",
      "border-cyan-400/10",
      "hover:bg-[#0a213c]",
      "transition",
    );

    row.querySelectorAll("td").forEach((td) => {
      td.classList.add("px-4", "py-4", "text-slate-200", "align-middle");
    });
  });

  aplicarEstilosBotonesTabla();
  aplicarFiltrosTabla();
}

function aplicarEstilosBotonesTabla() {
  const tabla = document.querySelector("#tabla table");
  if (!tabla) return;

  tabla.querySelectorAll("button, a").forEach((el) => {
    const texto = el.textContent.toLowerCase();
    const html = el.innerHTML.toLowerCase();

    el.classList.add(
      "inline-flex",
      "items-center",
      "justify-center",
      "gap-2",
      "rounded-xl",
      "px-3",
      "py-2",
      "text-xs",
      "font-bold",
      "transition",
      "border",
    );

    if (texto.includes("editar") || html.includes("pencil")) {
      el.classList.add(
        "bg-blue-500/15",
        "text-blue-200",
        "border-blue-400/20",
        "hover:bg-blue-500/25",
      );
    } else if (texto.includes("ver") || html.includes("eye")) {
      el.classList.add(
        "bg-cyan-500/15",
        "text-cyan-200",
        "border-cyan-400/20",
        "hover:bg-cyan-500/25",
      );
    } else if (texto.includes("eliminar") || html.includes("trash")) {
      el.classList.add(
        "bg-red-500/15",
        "text-red-200",
        "border-red-400/20",
        "hover:bg-red-500/25",
      );
    } else {
      el.classList.add(
        "bg-white/10",
        "text-slate-200",
        "border-white/10",
        "hover:bg-white/15",
      );
    }
  });
}

function aplicarFiltrosTabla() {
  const buscador = document.getElementById("buscarTabla");
  const textoBusqueda = buscador ? buscador.value.trim().toLowerCase() : "";

  tablaFiltradaRows = tablaOriginalRows.filter((row) => {
    const textoFila = row.textContent.toLowerCase();
    return textoFila.includes(textoBusqueda);
  });

  if (columnaOrdenActual !== null) {
    ordenarFilasArray(columnaOrdenActual, false);
  }

  const totalPaginas = Math.max(
    1,
    Math.ceil(tablaFiltradaRows.length / registrosPorPagina),
  );

  if (paginaActual > totalPaginas) {
    paginaActual = totalPaginas;
  }

  renderizarTablaPaginada();
}

function renderizarTablaPaginada() {
  const tabla = document.querySelector("#tabla table");
  if (!tabla) return;

  const tbody = tabla.querySelector("tbody");
  if (!tbody) return;

  tbody.innerHTML = "";

  const inicio = (paginaActual - 1) * registrosPorPagina;
  const fin = inicio + registrosPorPagina;
  const filasPagina = tablaFiltradaRows.slice(inicio, fin);

  if (!filasPagina.length) {
    const totalColumnas = tabla.querySelectorAll("thead th").length || 1;

    tbody.innerHTML = `
      <tr>
        <td colspan="${totalColumnas}" class="px-4 py-10 text-center text-slate-400">
          <i class="bi bi-inbox text-4xl block mb-3 text-slate-500"></i>
          No se encontraron tareas con los filtros actuales.
        </td>
      </tr>
    `;
  } else {
    filasPagina.forEach((row) => tbody.appendChild(row));
  }

  actualizarResumenTabla(tablaFiltradaRows.length, tablaOriginalRows.length);
  renderizarPaginacion();
}

function ordenarTabla(index) {
  if (columnaOrdenActual === index) {
    direccionOrden = direccionOrden === "asc" ? "desc" : "asc";
  } else {
    columnaOrdenActual = index;
    direccionOrden = "asc";
  }

  ordenarFilasArray(index, true);
  actualizarIconosOrden(index);
  renderizarTablaPaginada();
}

function ordenarFilasArray(index, resetPagina = true) {
  tablaFiltradaRows.sort((a, b) => {
    const textoA = obtenerTextoCelda(a, index);
    const textoB = obtenerTextoCelda(b, index);

    const fechaA = Date.parse(textoA);
    const fechaB = Date.parse(textoB);

    let comparacion = 0;

    if (!isNaN(fechaA) && !isNaN(fechaB)) {
      comparacion = fechaA - fechaB;
    } else if (!isNaN(parseFloat(textoA)) && !isNaN(parseFloat(textoB))) {
      comparacion = parseFloat(textoA) - parseFloat(textoB);
    } else {
      comparacion = textoA.localeCompare(textoB, "es", {
        numeric: true,
        sensitivity: "base",
      });
    }

    return direccionOrden === "asc" ? comparacion : -comparacion;
  });

  if (resetPagina) {
    paginaActual = 1;
  }
}

function obtenerTextoCelda(row, index) {
  const cell = row.children[index];
  return cell ? cell.textContent.trim() : "";
}

function actualizarIconosOrden(indexActivo) {
  document.querySelectorAll("#tabla thead th").forEach((th, index) => {
    const icon = th.querySelector(".sort-icon");
    if (!icon) return;

    icon.className = "sort-icon bi text-xs";

    if (index === indexActivo) {
      icon.classList.add(
        direccionOrden === "asc" ? "bi-sort-down" : "bi-sort-up",
        "text-cyan-300",
      );
    } else {
      icon.classList.add("bi-arrow-down-up", "text-slate-500");
    }
  });
}

function renderizarPaginacion() {
  const contenedor = document.getElementById("paginacionTabla");
  const info = document.getElementById("paginacionInfo");

  if (!contenedor || !info) return;

  const totalPaginas = Math.max(
    1,
    Math.ceil(tablaFiltradaRows.length / registrosPorPagina),
  );

  const inicio =
    tablaFiltradaRows.length === 0
      ? 0
      : (paginaActual - 1) * registrosPorPagina + 1;

  const fin = Math.min(
    paginaActual * registrosPorPagina,
    tablaFiltradaRows.length,
  );

  info.textContent = `Mostrando ${inicio} a ${fin} de ${tablaFiltradaRows.length} registros`;

  contenedor.innerHTML = "";

  const crearBoton = (texto, pagina, disabled = false, activo = false) => {
    const btn = document.createElement("button");
    btn.type = "button";
    btn.innerHTML = texto;

    btn.className = `
      min-w-10 h-10 px-3 rounded-xl border text-sm font-bold transition
      ${
        activo
          ? "bg-cyan-400 text-slate-950 border-cyan-300"
          : "bg-white/10 text-slate-200 border-white/10 hover:bg-white/15"
      }
      ${disabled ? "opacity-40 cursor-not-allowed hover:bg-white/10" : ""}
    `;

    btn.disabled = disabled;

    if (!disabled) {
      btn.addEventListener("click", () => {
        paginaActual = pagina;
        renderizarTablaPaginada();
      });
    }

    return btn;
  };

  contenedor.appendChild(
    crearBoton(
      '<i class="bi bi-chevron-left"></i>',
      paginaActual - 1,
      paginaActual === 1,
    ),
  );

  const maxBotones = 5;
  let inicioPaginas = Math.max(1, paginaActual - 2);
  let finPaginas = Math.min(totalPaginas, inicioPaginas + maxBotones - 1);

  if (finPaginas - inicioPaginas < maxBotones - 1) {
    inicioPaginas = Math.max(1, finPaginas - maxBotones + 1);
  }

  for (let i = inicioPaginas; i <= finPaginas; i++) {
    contenedor.appendChild(crearBoton(i, i, false, i === paginaActual));
  }

  contenedor.appendChild(
    crearBoton(
      '<i class="bi bi-chevron-right"></i>',
      paginaActual + 1,
      paginaActual === totalPaginas,
    ),
  );
}

function actualizarResumenTabla(filtrados, total) {
  const resumen = document.getElementById("tablaResumen");
  if (!resumen) return;

  resumen.innerHTML = `
    <span class="font-bold text-white">${filtrados}</span>
    tarea${filtrados === 1 ? "" : "s"} visible${filtrados === 1 ? "" : "s"}
    <span class="text-slate-500">/ ${total} total</span>
  `;
}

let eventMap;
let eventMarker;

function showEventModal(id) {
  console.log("ID recibido:", id);

  const modal = document.getElementById("eventModal");
  const eventMapContainer = document.getElementById("eventMap");

  if (!modal) {
    console.error("No existe el modal eventModal.");
    return;
  }

  const formData = new FormData();
  formData.append("id", id);

  fetch("../php/mostrar.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Error HTTP ${response.status}`);
      }

      return response.json();
    })
    .then((data) => {
      if (!data.success) {
        Swal.fire({
          icon: "error",
          title: "Error al mostrar la tarea",
          text: data.error || "Error desconocido.",
        });
        return;
      }

      const tareaId = escapeHTML(data.id);
      const titulo = escapeHTML(data.titulo || "Sin título");
      const ubicacion = escapeHTML(data.ubicacion || "Sin ubicación");
      const comentarios = escapeHTML(data.comentarios || "Sin comentarios");
      const inicio = escapeHTML(data.inicio || "Sin fecha");
      const fin = data.fin ? escapeHTML(data.fin) : "";
      const categoria = data.categoria || "Otros";
      const estado = data.estado || "";

      const lat = parseFloat(data.lat) || 20.12933;
      const lng = parseFloat(data.lng) || -101.17979;

      const idTitle = document.getElementById("idTitle");
      const eventTitle = document.getElementById("eventTitle");
      const eventCategoria = document.getElementById("eventCategoria");
      const eventAdress = document.getElementById("eventAdress");
      const eventDate = document.getElementById("eventDate");
      const eventStatus = document.getElementById("eventStatus");
      const comentariosEl = document.getElementById("comentarios");

      if (idTitle) {
        idTitle.innerHTML = `
    <span class="inline-flex items-center gap-2">
      <span class="text-cyan-200">#${tareaId}</span>
      <span>Tarea</span>
    </span>
  `;
      }

      if (eventTitle) {
        eventTitle.innerHTML = `
    <div>
      <span class="block text-xs uppercase tracking-widest text-slate-500 font-black mb-1">
        Título
      </span>
      <span class="inline-flex items-start gap-2 text-white font-bold">
        <i class="bi bi-clipboard2-fill text-cyan-300 mt-1"></i>
        ${titulo}
      </span>
    </div>
  `;
      }

      if (eventCategoria) {
        eventCategoria.innerHTML = getCategoriaChip(categoria);
      }

      if (eventStatus) {
        eventStatus.innerHTML = getEstadoChip(estado);
      }

      if (eventDate) {
        eventDate.innerHTML = `
          <div class="space-y-2">
            <div class="flex items-center gap-2 text-slate-200">
              <i class="bi bi-clock text-cyan-300"></i>
              <span class="text-slate-400 font-bold">Inicio:</span>
              <span class="font-bold">${inicio}</span>
            </div>

            ${
              fin
                ? `
                  <div class="flex items-center gap-2 text-slate-200">
                    <i class="bi bi-clock-fill text-red-300"></i>
                    <span class="text-slate-400 font-bold">Fin:</span>
                    <span class="font-bold">${fin}</span>
                  </div>
                `
                : `
                  <div class="flex items-center gap-2 text-slate-400">
                    <i class="bi bi-dash-circle"></i>
                    <span class="font-bold">Sin fecha de fin</span>
                  </div>
                `
            }
          </div>
        `;
      }

      if (eventAdress) {
        eventAdress.innerHTML = `
          <a
            href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-start gap-3 hover:text-cyan-100 transition"
          >
            <i class="bi bi-pin-map-fill text-cyan-300 mt-1"></i>
            <span>${ubicacion}</span>
          </a>
        `;
      }

      if (comentariosEl) {
        comentariosEl.innerHTML = `
          <div class="flex items-start gap-3">
            <i class="bi bi-chat-left-text-fill text-blue-300 mt-1"></i>
            <div class="whitespace-pre-wrap">${comentarios}</div>
          </div>
        `;
      }

      if (eventMapContainer) {
        eventMapContainer.innerHTML = "";
      }

      modal.classList.remove("hidden");

      if (eventMap) {
        eventMap.remove();
        eventMap = null;
      }

      setTimeout(() => {
        eventMap = L.map("eventMap").setView([lat, lng], 15);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          attribution: "&copy; OpenStreetMap contributors",
        }).addTo(eventMap);

        eventMarker = L.marker([lat, lng]).addTo(eventMap);

        eventMarker.bindPopup(`
          <strong>${titulo}</strong><br>
          ${ubicacion}
        `);

        eventMap.invalidateSize();
      }, 250);
    })
    .catch((error) => {
      console.error("Error:", error);

      Swal.fire({
        icon: "error",
        title: "Error al mostrar la tarea",
        text: error.message,
      });
    });
}

let editMap = null;
let editMarker = null;

function openEditModal(id) {
  fetch("../php/editarE.php", {
    method: "POST",
    body: new URLSearchParams({ id }),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Error HTTP ${response.status}`);
      }

      return response.json();
    })
    .then((data) => {
      if (!data.success) {
        Swal.fire(
          "Error",
          data.error || "No se pudo cargar la tarea.",
          "error",
        );
        return;
      }

      const modal = document.getElementById("editModal");
      const eventData = data.data;

      if (!modal) {
        console.error("No existe el modal editModal.");
        return;
      }

      const editId = document.getElementById("editId");
      const editTitle = document.getElementById("editTitle");
      const editCategoria = document.getElementById("editCategoria");
      const editStart = document.getElementById("editStart");
      const editEnd = document.getElementById("editEnd");
      const editLocation = document.getElementById("editLocation");
      const editLat = document.getElementById("editLat");
      const editLng = document.getElementById("editLng");
      const editComentarios = document.getElementById("editComentarios");
      const editEvidencia = document.getElementById("editEvidencia");
      const editCliente = document.getElementById("editCliente");
      const editClienteSearch = document.getElementById("editClienteSearch");
      const editEstado = document.getElementById("editEstado");

      if (editId) editId.value = eventData.id || "";
      if (editTitle) editTitle.value = eventData.title || "";
      if (editCategoria) editCategoria.value = eventData.categoria || "Otros";

      if (editStart) {
        editStart.value = eventData.start
          ? eventData.start.replace(" ", "T").slice(0, 16)
          : "";
      }

      if (editEnd) {
        if (eventData.end && eventData.end !== "2000-01-01 01:01:00") {
          editEnd.value = eventData.end.replace(" ", "T").slice(0, 16);
        } else {
          editEnd.value = "";
        }
      }

      if (editLocation) editLocation.value = eventData.location || "";
      if (editLat) editLat.value = eventData.lat || "";
      if (editLng) editLng.value = eventData.lng || "";
      if (editComentarios) editComentarios.value = eventData.comentarios || "";
      if (editEvidencia) editEvidencia.value = eventData.evidencia || "";

      if (editCliente && editClienteSearch) {
        if (eventData.cliente && eventData.cliente !== "0") {
          editCliente.value = eventData.cliente;

          editClienteSearch.value = eventData.cliente_nombre
            ? `${eventData.cliente} - ${eventData.cliente_nombre}`
            : eventData.cliente;
        } else {
          editCliente.value = "";
          editClienteSearch.value = "";
        }
      }

      if (editEstado) {
        editEstado.innerHTML = getEstadoOptions(eventData.estado || "creado");
      }

      const lat = parseFloat(eventData.lat) || 20.12933;
      const lng = parseFloat(eventData.lng) || -101.17979;

      modal.classList.remove("hidden");

      if (editMap) {
        editMap.remove();
        editMap = null;
      }

      if (editMarker) {
        editMarker = null;
      }

      setTimeout(() => {
        editMap = L.map("editMap").setView([lat, lng], 15);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          attribution: "&copy; OpenStreetMap contributors",
        }).addTo(editMap);

        editMarker = L.marker([lat, lng], {
          draggable: true,
        }).addTo(editMap);

        editMarker.bindPopup("Arrastra el marcador para cambiar la ubicación.");

        editMarker.on("moveend", function (e) {
          const position = e.target.getLatLng();

          const newLat = position.lat.toFixed(8);
          const newLng = position.lng.toFixed(8);

          const latInput = document.getElementById("editLat");
          const lngInput = document.getElementById("editLng");

          if (latInput) latInput.value = newLat;
          if (lngInput) lngInput.value = newLng;

          editMap.setView([newLat, newLng], 15);

          if (typeof updateLocation === "function") {
            updateLocation(newLat, newLng);
          }
        });

        editMap.invalidateSize();
      }, 250);
    })
    .catch((error) => {
      console.error("Error:", error);

      Swal.fire({
        icon: "error",
        title: "Error al cargar la tarea",
        text: error.message,
      });
    });
}

async function updateLocation(lat, lng) {
  try {
    const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`;
    const response = await fetch(url);
    const data = await response.json();
    const address = data.display_name || "Ubicación desconocida";
    const locationInput = document.getElementById("editLocation");
    if (locationInput) {
      locationInput.value = address;
    }
  } catch (error) {
    console.error("Error al obtener la dirección:", error);
  }
}

document.getElementById("editForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch("../php/updateEvent.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        Swal.fire({
          icon: "success",
          title: "Cambios guardados",
          text: "La tarea se actualizó correctamente.",
          confirmButtonColor: "#2563eb",
        }).then(() => {
          document.getElementById("editModal").classList.add("hidden");
          cargarTabla(mes, year);
        });
      } else {
        Swal.fire(
          "Error",
          data.error || "No se pudo actualizar la tarea.",
          "error",
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      Swal.fire("Error", "Ocurrió un error al actualizar la tarea.", "error");
    });
});
const editClienteSearch = document.getElementById("editClienteSearch");
const editClienteInput = document.getElementById("editCliente");
const clearEditCliente = document.getElementById("clearEditCliente");

if (editClienteSearch && editClienteInput) {
  editClienteSearch.addEventListener("input", () => {
    const q = editClienteSearch.value.trim();
    editClienteInput.value = "";

    clearTimeout(editClienteTimer);

    if (q.length < 2) {
      hideEditClienteResults();
      return;
    }

    editClienteTimer = setTimeout(() => {
      buscarEditClientes(q);
    }, 250);
  });
}

if (clearEditCliente && editClienteSearch && editClienteInput) {
  clearEditCliente.addEventListener("click", () => {
    editClienteSearch.value = "";
    editClienteInput.value = "";
    hideEditClienteResults();
  });
}
document.getElementById("cancelEdit").addEventListener("click", () => {
  document.getElementById("editModal").classList.add("hidden");
});
document.getElementById("closeEdit").addEventListener("click", () => {
  document.getElementById("editModal").classList.add("hidden");
});
const buscadorTabla = document.getElementById("buscarTabla");
const registrosSelect = document.getElementById("registrosPorPagina");
const btnLimpiarBusqueda = document.getElementById("btnLimpiarBusqueda");

if (buscadorTabla) {
  buscadorTabla.addEventListener("input", () => {
    paginaActual = 1;
    aplicarFiltrosTabla();
  });
}

if (registrosSelect) {
  registrosSelect.addEventListener("change", () => {
    registrosPorPagina = parseInt(registrosSelect.value, 10);
    paginaActual = 1;
    aplicarFiltrosTabla();
  });
}

if (btnLimpiarBusqueda) {
  btnLimpiarBusqueda.addEventListener("click", () => {
    if (buscadorTabla) {
      buscadorTabla.value = "";
    }

    paginaActual = 1;
    aplicarFiltrosTabla();
  });
}
