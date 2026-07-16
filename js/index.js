// 🎨 Colores por categoría (reutilizable en toda la app)
const CATEGORY_STYLES = {
  Cobertura: {
    chip: "bg-blue-500/20 text-blue-300 ring-1 ring-blue-500/30",
    card: "bg-blue-500/10 border-blue-500/30 hover:bg-blue-500/15",
  },
  Instalación: {
    chip: "bg-green-500/20 text-green-300 ring-1 ring-green-500/30",
    card: "bg-green-500/10 border-green-500/30 hover:bg-green-500/15",
  },
  Reporte: {
    chip: "bg-orange-500/20 text-orange-300 ring-1 ring-orange-500/30",
    card: "bg-orange-500/10 border-orange-500/30 hover:bg-orange-500/15",
  },
  "Sin Servicio": {
    chip: "bg-red-500/20 text-red-300 ring-1 ring-red-500/30",
    card: "bg-red-500/10 border-red-500/30 hover:bg-red-500/15",
  },
  "Cambio de domicilio": {
    chip: "bg-purple-500/20 text-purple-300 ring-1 ring-purple-500/30",
    card: "bg-purple-500/10 border-purple-500/30 hover:bg-purple-500/15",
  },
  Cancelación: {
    chip: "bg-red-500/20 text-red-300 ring-1 ring-red-500/30",
    card: "bg-red-500/10 border-red-500/30 hover:bg-red-500/15",
  },
  Servicios: {
    chip: "bg-cyan-500/20 text-cyan-300 ring-1 ring-cyan-500/30",
    card: "bg-cyan-500/10 border-cyan-500/30 hover:bg-cyan-500/15",
  },
  Camaras: {
    chip: "bg-indigo-500/20 text-indigo-300 ring-1 ring-indigo-500/30",
    card: "bg-indigo-500/10 border-indigo-500/30 hover:bg-indigo-500/15",
  },
  Torniquetes: {
    chip: "bg-yellow-500/20 text-yellow-300 ring-1 ring-yellow-500/30",
    card: "bg-yellow-500/10 border-yellow-500/30 hover:bg-yellow-500/15",
  },
  Otros: {
    chip: "bg-gray-500/20 text-gray-300 ring-1 ring-gray-500/30",
    card: "bg-gray-700/30 border-gray-700 hover:bg-gray-700/40",
  },
};

document.addEventListener("DOMContentLoaded", function () {
  let modalEvent;
  const sliderContainer = document.getElementById("sliderContainer");
  const eventDetails = document.getElementById("eventDetails");
  const submitSlider = document.getElementById("submitSlider");
  const backButton = document.getElementById("backButton");

  const calendarEl = document.getElementById("calendar");
  const initialView = window.innerWidth < 768 ? "listWeek" : "dayGridMonth";

  // ✅ FullCalendar maneja "end" como fecha exclusiva.
  // Para vacaciones necesitamos pintar también el último día seleccionado.
  function addOneDayToDateOnly(dateStr) {
    if (!dateStr) return dateStr;

    // Acepta "2026-06-02" o "2026-06-02 00:00:00"
    const cleanDate = String(dateStr).split(" ")[0].split("T")[0];

    const [year, month, day] = cleanDate.split("-").map(Number);

    if (!year || !month || !day) return dateStr;

    const date = new Date(year, month - 1, day);
    date.setDate(date.getDate() + 1);

    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, "0");
    const dd = String(date.getDate()).padStart(2, "0");

    return `${yyyy}-${mm}-${dd}`;
  }
  const calendar = new FullCalendar.Calendar(calendarEl, {
    locale: "es",
    initialView: initialView,

    eventDataTransform: function (eventData) {
      const tipo = eventData.tipo || eventData.extendedProps?.tipo || "";

      if (tipo === "vacaciones" && eventData.end) {
        const fechaFinReal = eventData.end;

        eventData.extendedProps = {
          ...(eventData.extendedProps || {}),
          fechaFinReal: fechaFinReal,
        };

        eventData.end = addOneDayToDateOnly(eventData.end);
        eventData.allDay = true;
      }

      return eventData;
    },

    events: {
      url: "../tareas/php/eventos.php",
      method: "GET",
      failure: function () {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "No se pudieron cargar los eventos.",
        });
      },
    },

    eventClick: function (info) {
      modalEvent = info.event;

      if (info.event.extendedProps?.tipo === "vacaciones") {
        showVacationModal(info.event);
      } else {
        showEventModal(info.event);
      }
    },
  });

  window.calendar = calendar;
  calendar.render();

  // Mostrar slider al hacer clic en el botón "Completado"
  window.showSlider = function showSlider() {
    const date = moment().format("YYYY-MM-DDTHH:mm");
    document.getElementById("fin").value = date;
    //document.querySelector("#fin").innerHTML = moment().format("DD/MM/yyyy");
    eventDetails.classList.add("-translate-x-full");
    sliderContainer.classList.remove("hidden");
    setTimeout(() => {
      sliderContainer.classList.remove("translate-x-full");
    }, 10); // Permitir que la transición ocurra
  };
  function showVacationModal(event) {
    const fechaFinReal = event.extendedProps?.fechaFinReal || event.end;

    document.getElementById("vacationTitle").innerHTML =
      `<i class="bi bi-calendar"></i> ${event.title}`;

    document.getElementById("vacationDate").innerHTML =
      `<i class="bi bi-clock"></i> Desde: ${event.start.toLocaleDateString("es-MX")} 
     <br> 
     <i class="bi bi-clock-fill"></i> Hasta: ${
       fechaFinReal
         ? new Date(
             String(fechaFinReal).split(" ")[0] + "T00:00:00",
           ).toLocaleDateString("es-MX")
         : "No especificado"
     }`;

    document.getElementById("vacationModal").classList.remove("hidden");
  }

  document
    .getElementById("closeVacationModal")
    .addEventListener("click", function () {
      document.getElementById("vacationModal").classList.add("hidden");
    });
  // Ocultar slider
  function hideSlider() {
    sliderContainer.classList.add("hidden");
    document.getElementById("evidence").value = ""; // Limpiar campo de evidencia
    document.getElementById("comments").value = ""; // Limpiar campo de comentarios
  }
  // Volver a la sección inicial
  backButton.addEventListener("click", function () {
    sliderContainer.classList.add("translate-x-full");
    setTimeout(() => {
      sliderContainer.classList.add("hidden");
      eventDetails.classList.remove("-translate-x-full");
    }, 500); // Esperar a que termine la transición
  });

  // Enviar datos del slider
  submitSlider.addEventListener("click", function () {
    const comments = document.getElementById("comments").value.trim();
    const fin = document.getElementById("fin").value.trim();
    const evidence = document.getElementById("evidence").files;

    if (!comments) {
      Swal.fire({
        icon: "error",
        title: "Faltan comentarios",
        text: "El campo de comentarios es obligatorio.",
      });
      return;
    }

    const formData = new FormData();
    formData.append("id", modalEvent.id);
    formData.append("estado", "terminado");
    formData.append("comments", comments);
    formData.append("fin", fin);

    if (evidence.length > 0) {
      for (let i = 0; i < evidence.length; i++) {
        formData.append("evidence[]", evidence[i]); // Añadir cada archivo al FormData
      }
    }

    fetch("../tareas/php/terminado.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          Swal.fire({
            icon: "success",
            title: "Evento actualizado",
            text: "El evento fue actualizado con éxito.",
          });
          calendar.refetchEvents();
          document.getElementById("comments").value = "";
          document.getElementById("evidence").value = "";
          backButton.click(); // Volver automáticamente después del envío
          closeModalHandler();
        } else {
          Swal.fire({
            icon: "error",
            title: "Error al actualizar el evento",
            text: data.error || "Error desconocido.",
          });
        }
      })
      .catch((error) => {
        Swal.fire({
          icon: "error",
          title: "Error al actualizar el evento",
          text: error.message,
        });
        console.error("Error:", error);
      });
  });

  // Mapa en el formulario
  const formMap = L.map("map").setView([20.12933, -101.17979], 13); // Coordenadas iniciales
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "&copy; OpenStreetMap contributors",
  }).addTo(formMap);

  // ===============================
  // MAPA GRANDE (MODAL)
  // ===============================
  let mapModalInstance = null;
  let mapModalMarker = null;

  const mapModal = document.getElementById("mapModal");
  const openMapModal = document.getElementById("openMapModal");
  const closeMapModal = document.getElementById("closeMapModal");
  const confirmMapLocation = document.getElementById("confirmMapLocation");

  function abrirMapaGrande() {
    const currentLatLng = formMarker.getLatLng();
    // Mostrar dirección actual al abrir
    const currentAddress = document.getElementById("here-autocomplete").value;
    const modalAddress = document.getElementById("mapSelectedAddress");

    if (modalAddress) {
      modalAddress.textContent =
        currentAddress || "Selecciona una ubicación...";
    }

    mapModal.classList.remove("hidden");

    setTimeout(() => {
      if (!mapModalInstance) {
        mapModalInstance = L.map("mapModalContainer").setView(
          [currentLatLng.lat, currentLatLng.lng],
          formMap.getZoom(),
        );

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          attribution: "&copy; OpenStreetMap contributors",
        }).addTo(mapModalInstance);

        mapModalMarker = L.marker([currentLatLng.lat, currentLatLng.lng], {
          draggable: true,
        }).addTo(mapModalInstance);

        mapModalMarker.on("moveend", function (e) {
          const { lat, lng } = e.target.getLatLng();

          formMarker.setLatLng([lat, lng]);
          formMap.setView([lat, lng], mapModalInstance.getZoom());

          updateLocation(lat, lng);
        });

        mapModalInstance.on("click", function (e) {
          const { lat, lng } = e.latlng;

          mapModalMarker.setLatLng([lat, lng]);
          formMarker.setLatLng([lat, lng]);
          formMap.setView([lat, lng], mapModalInstance.getZoom());

          updateLocation(lat, lng);
        });
      } else {
        mapModalInstance.setView(
          [currentLatLng.lat, currentLatLng.lng],
          formMap.getZoom(),
        );
        mapModalMarker.setLatLng([currentLatLng.lat, currentLatLng.lng]);
      }

      mapModalInstance.invalidateSize();
    }, 150);
  }

  function cerrarMapaGrande() {
    mapModal.classList.add("hidden");
    formMap.invalidateSize();
  }

  // Eventos
  openMapModal.addEventListener("click", abrirMapaGrande);
  closeMapModal.addEventListener("click", cerrarMapaGrande);
  confirmMapLocation.addEventListener("click", cerrarMapaGrande);

  const formMarker = L.marker([20.12933, -101.17979], {
    draggable: true,
  }).addTo(formMap);
  const form = document.getElementById("eventForm");

  const clienteSearch = document.getElementById("clienteSearch");
  const clienteInput = document.getElementById("cliente");
  const clienteResults = document.getElementById("clienteResults");
  const btnLimpiarUbicacion = document.getElementById("btnLimpiarUbicacion");
  const ubicacionHelp = document.getElementById("ubicacionHelp");
  const btnLimpiarFormulario = document.getElementById("btnLimpiarFormulario");

  const DEFAULT_LAT = 20.12933;
  const DEFAULT_LNG = -101.17979;

  let clienteTimer = null;

  function hideClienteResults() {
    if (!clienteResults) return;
    clienteResults.classList.add("hidden");
    clienteResults.innerHTML = "";
  }
  function mostrarControlesUbicacion(mostrar = false, desdeContrato = false) {
    if (btnLimpiarUbicacion) {
      btnLimpiarUbicacion.classList.toggle("hidden", !mostrar);
    }

    if (ubicacionHelp) {
      ubicacionHelp.classList.toggle("hidden", !desdeContrato);
    }
  }

  function limpiarUbicacionTarea() {
    const inputUbicacion = document.getElementById("here-autocomplete");
    const inputLat = document.getElementById("lat");
    const inputLng = document.getElementById("lng");
    const modalAddress = document.getElementById("mapSelectedAddress");

    if (inputUbicacion) inputUbicacion.value = "";
    if (inputLat) inputLat.value = "";
    if (inputLng) inputLng.value = "";
    if (modalAddress) modalAddress.textContent = "Selecciona una ubicación...";

    formMarker.setLatLng([DEFAULT_LAT, DEFAULT_LNG]);
    formMap.setView([DEFAULT_LAT, DEFAULT_LNG], 13);

    if (mapModalMarker) {
      mapModalMarker.setLatLng([DEFAULT_LAT, DEFAULT_LNG]);
    }

    mostrarControlesUbicacion(false, false);
    formMap.invalidateSize();
  }

  function setUbicacionTarea(
    lat,
    lng,
    texto = "Ubicación seleccionada",
    desdeContrato = false,
  ) {
    const inputUbicacion = document.getElementById("here-autocomplete");
    const inputLat = document.getElementById("lat");
    const inputLng = document.getElementById("lng");
    const modalAddress = document.getElementById("mapSelectedAddress");

    if (!lat || !lng) return;

    const latNum = parseFloat(lat);
    const lngNum = parseFloat(lng);

    if (Number.isNaN(latNum) || Number.isNaN(lngNum)) return;

    if (inputUbicacion) inputUbicacion.value = texto;
    if (inputLat) inputLat.value = latNum;
    if (inputLng) inputLng.value = lngNum;
    if (modalAddress) modalAddress.textContent = texto;

    formMarker.setLatLng([latNum, lngNum]);
    formMap.setView([latNum, lngNum], 16);

    if (mapModalMarker) {
      mapModalMarker.setLatLng([latNum, lngNum]);
    }

    mostrarControlesUbicacion(true, desdeContrato);
    formMap.invalidateSize();
  }

  function cargarUbicacionDesdeCliente(cliente) {
    if (!cliente) return;

    const lat = cliente.ubicacion_lat;
    const lng = cliente.ubicacion_lng;

    if (lat && lng) {
      const textoUbicacion =
        cliente.ubicacion_texto ||
        `Ubicación del contrato de ${cliente.nombre || "cliente"}`;

      setUbicacionTarea(lat, lng, textoUbicacion, true);
    } else {
      mostrarControlesUbicacion(false, false);

      Swal.fire({
        icon: "info",
        title: "Cliente sin ubicación",
        text: "Este cliente no tiene una ubicación guardada en su contrato.",
        timer: 2500,
        showConfirmButton: false,
      });
    }
  }

  function limpiarClienteSeleccionado() {
    if (clienteInput) clienteInput.value = "";
    if (clienteSearch) clienteSearch.value = "";
    hideClienteResults();
  }

  function limpiarFormularioTarea() {
    form.reset();
    limpiarClienteSeleccionado();
    limpiarUbicacionTarea();
  }
  function renderClienteResults(clientes) {
    if (!clienteResults) return;

    if (!clientes.length) {
      clienteResults.innerHTML = `
      <div class="px-4 py-3 text-sm text-slate-400">
        No se encontraron clientes
      </div>
    `;
      clienteResults.classList.remove("hidden");
      return;
    }

    clienteResults.innerHTML = clientes
      .map(
        (cliente) => `
        <button
  type="button"
  class="w-full text-left px-4 py-3 border-b border-slate-800 last:border-b-0 hover:bg-slate-800 transition"
  data-id="${cliente.idcliente}"
  data-nombre="${escapeHtml(cliente.nombre)}"
  data-lat="${cliente.ubicacion_lat || ""}"
  data-lng="${cliente.ubicacion_lng || ""}"
  data-ubicacion="${escapeHtml(cliente.ubicacion_texto || "")}"
>
  <div class="text-white font-semibold">${cliente.idcliente} - ${escapeHtml(cliente.nombre)}</div>

  ${
    cliente.ubicacion_lat && cliente.ubicacion_lng
      ? `<div class="text-xs text-cyan-300 mt-1">
          <i class="bi bi-geo-alt-fill"></i> Tiene ubicación guardada
        </div>`
      : `<div class="text-xs text-slate-500 mt-1">
          <i class="bi bi-geo-alt"></i> Sin ubicación guardada
        </div>`
  }
</button>
      `,
      )
      .join("");

    clienteResults.classList.remove("hidden");

    clienteResults.querySelectorAll("button[data-id]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const id = btn.getAttribute("data-id");
        const nombre = btn.getAttribute("data-nombre");
        const lat = btn.getAttribute("data-lat");
        const lng = btn.getAttribute("data-lng");
        const ubicacionTexto = btn.getAttribute("data-ubicacion");

        clienteInput.value = id;
        clienteSearch.value = `${id} - ${nombre}`;

        hideClienteResults();

        cargarUbicacionDesdeCliente({
          idcliente: id,
          nombre,
          ubicacion_lat: lat,
          ubicacion_lng: lng,
          ubicacion_texto: ubicacionTexto,
        });
      });
    });
  }

  async function buscarClientes(q) {
    try {
      const response = await fetch(
        `../tareas/php/buscar_clientes.php?q=${encodeURIComponent(q)}`,
        {
          method: "GET",
          headers: { Accept: "application/json" },
        },
      );

      const data = await response.json().catch(() => ({}));

      if (!response.ok || !data.success) {
        hideClienteResults();
        return;
      }

      renderClienteResults(data.clientes || []);
    } catch (error) {
      console.error("Error buscando clientes:", error);
      hideClienteResults();
    }
  }

  if (clienteSearch && clienteInput && clienteResults) {
    clienteSearch.addEventListener("input", () => {
      const q = clienteSearch.value.trim();

      // Si vuelve a escribir, se limpia el cliente real
      clienteInput.value = "";

      // Si borra el cliente, también se borra la ubicación
      if (q === "") {
        limpiarUbicacionTarea();
        hideClienteResults();
        return;
      }

      clearTimeout(clienteTimer);

      if (q.length < 2) {
        hideClienteResults();
        return;
      }

      clienteTimer = setTimeout(() => {
        buscarClientes(q);
      }, 250);
    });

    clienteSearch.addEventListener("focus", () => {
      const q = clienteSearch.value.trim();
      if (q.length >= 2 && clienteResults.innerHTML.trim() !== "") {
        clienteResults.classList.remove("hidden");
      }
    });

    document.addEventListener("click", (e) => {
      if (!clienteSearch.parentElement.contains(e.target)) {
        hideClienteResults();
      }
    });
  }
  if (btnLimpiarUbicacion) {
    btnLimpiarUbicacion.addEventListener("click", () => {
      limpiarUbicacionTarea();
    });
  }

  if (btnLimpiarFormulario) {
    btnLimpiarFormulario.addEventListener("click", () => {
      limpiarFormularioTarea();
    });
  }
  form.addEventListener("submit", function (event) {
    event.preventDefault();

    const title = document.getElementById("title").value.trim();
    const start = document.getElementById("start").value;
    const end = document.getElementById("end").value || null;
    const location = document.getElementById("here-autocomplete").value.trim();
    const lat = document.getElementById("lat").value;
    const lng = document.getElementById("lng").value;
    const categoria = document.getElementById("categoria").value;
    const cliente = document.getElementById("cliente").value.trim();

    if (!title || !start || !categoria) {
      Swal.fire({
        icon: "error",
        title: "Campos incompletos",
        text: "Por favor, completa todos los campos obligatorios.",
      });
      return;
    }
    if (
      clienteSearch &&
      clienteSearch.value.trim() !== "" &&
      !clienteInput.value.trim()
    ) {
      Swal.fire({
        icon: "error",
        title: "Cliente inválido",
        text: "Selecciona un cliente válido de la lista.",
      });
      return;
    }

    const newEvent = {
      title,
      start,
      end,
      categoria,
      location,
      lat,
      lng,
      cliente: cliente ? parseInt(cliente) : null,
    };

    fetch("../tareas/php/eventos.php", {
      method: "POST",
      body: JSON.stringify(newEvent),
      headers: {
        "Content-Type": "application/json",
      },
    })
      .then((response) =>
        response
          .json()
          .then((data) => ({ status: response.status, body: data })),
      )
      .then(({ status, body }) => {
        if (status !== 200) {
          throw new Error(body.error || "Error desconocido");
        }
        calendar.refetchEvents();
        limpiarFormularioTarea();
        Swal.fire({
          icon: "success",
          title: "Evento agregado",
          text: "El evento fue agregado con éxito.",
        });
      })
      .catch((error) => {
        Swal.fire({
          icon: "error",
          title: "Error al guardar el evento",
          text: error.message,
        });
        console.error("Error:", error);
      });
  });
  // Actualizar ubicación en el formulario
  async function updateLocation(lat, lng) {
    try {
      const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`;
      const response = await fetch(url);
      const data = await response.json();

      const address = data.display_name || "Ubicación desconocida";

      document.getElementById("here-autocomplete").value = address;
      document.getElementById("lat").value = lat;
      document.getElementById("lng").value = lng;

      const modalAddress = document.getElementById("mapSelectedAddress");
      if (modalAddress) {
        modalAddress.textContent = address;
      }

      mostrarControlesUbicacion(true, false);
    } catch (error) {
      console.error("Error obteniendo dirección:", error);

      document.getElementById("here-autocomplete").value =
        "Ubicación seleccionada";
      document.getElementById("lat").value = lat;
      document.getElementById("lng").value = lng;

      mostrarControlesUbicacion(true, false);
    }
  }

  formMarker.on("moveend", function (e) {
    const { lat, lng } = e.target.getLatLng();

    updateLocation(lat, lng);

    if (mapModalMarker) {
      mapModalMarker.setLatLng([lat, lng]);
    }

    formMap.invalidateSize();
  });

  // Mapa en el modal
  let eventMap; // Mapa del modal
  let eventMarker; // Marcador del modal

  const modal = document.getElementById("eventModal");
  const closeModal = document.getElementById("closeModal");
  const closeModalButton = document.getElementById("closeModalButton");
  const eventModalOverlay = document.getElementById("eventModalOverlay");

  function showEventModal(event) {
    document.getElementById("idTitle").textContent = "ID : " + event.id;

    const categoria =
      event.extendedProps?.categoria || event.categoria || "Otros";
    const catStyle =
      CATEGORY_STYLES[categoria]?.chip || CATEGORY_STYLES["Otros"].chip;

    const estadoRaw = event.extendedProps?.estado || event.estado || "creado";
    const estadoClass = getEstadoBadgeClass(estadoRaw);

    const clienteRaw = event.extendedProps?.cliente ?? event.cliente ?? null;
    const cliente =
      clienteRaw !== null && clienteRaw !== "" ? escapeHtml(clienteRaw) : null;

    const clienteNombreRaw =
      event.extendedProps?.cliente_nombre ?? event.cliente_nombre ?? null;

    const clienteNombre =
      clienteNombreRaw !== null && clienteNombreRaw !== ""
        ? escapeHtml(clienteNombreRaw)
        : null;
    const clienteDireccionRaw =
      event.extendedProps?.cliente_direccion ?? event.cliente_direccion ?? "";

    const clienteDireccion = clienteDireccionRaw
      ? escapeHtml(clienteDireccionRaw)
      : null;

    const clienteTelefonoRaw =
      event.extendedProps?.cliente_telefono ?? event.cliente_telefono ?? "";

    const clienteTelefono = clienteTelefonoRaw
      ? escapeHtml(clienteTelefonoRaw)
      : null;

    // Versión segura para usar en el enlace tel:
    const clienteTelefonoEnlace = clienteTelefonoRaw
      ? String(clienteTelefonoRaw).replace(/[^0-9+]/g, "")
      : "";
    const ubicacionRaw = event.extendedProps?.location ?? event.location ?? "";
    const ubicacion = ubicacionRaw
      ? escapeHtml(ubicacionRaw)
      : "Sin ubicación registrada";

    const inicioTexto = formatDateTime(event.start);
    const finTexto = event.end ? formatDateTime(event.end) : "No especificado";

    document.getElementById("eventTitle").innerHTML = `
    <div class="grid gap-4">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
          <div class="text-white text-xl font-extrabold flex items-center gap-2">
            <i class="bi bi-clipboard2-fill text-cyan-300"></i>
            <span class="break-words">${escapeHtml(event.title || "Sin título")}</span>
          </div>
          <div class="text-sm text-slate-300 mt-2">
            Información general de la tarea seleccionada.
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <span class="px-3 py-1 rounded-lg text-xs font-semibold ${catStyle}">
            ${escapeHtml(categoria)}
          </span>
          <span class="px-3 py-1 rounded-lg text-xs font-semibold ${estadoClass}">
            ${escapeHtml(estadoRaw)}
          </span>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div class="rounded-xl border border-cyan-500/10 bg-slate-900/60 p-3">
          <div class="text-xs uppercase tracking-wide text-slate-400 mb-1">Inicio</div>
          <div class="text-sm font-semibold text-cyan-300">
            <i class="bi bi-clock mr-1"></i>${escapeHtml(inicioTexto)}
          </div>
        </div>

        <div class="rounded-xl border border-cyan-500/10 bg-slate-900/60 p-3">
          <div class="text-xs uppercase tracking-wide text-slate-400 mb-1">Fin</div>
          <div class="text-sm font-semibold text-red-300">
            <i class="bi bi-clock-fill mr-1"></i>${escapeHtml(finTexto)}
          </div>
        </div>
      </div>

      ${
        cliente
          ? `
  <div class="rounded-xl border border-cyan-500/10 bg-slate-900/60 p-4">
    <div class="text-xs uppercase tracking-wide text-slate-400 mb-3">
      Datos del cliente
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

      <div>
        <div class="text-xs text-slate-400 mb-1">
          Número de cliente
        </div>

        <div class="text-sm font-semibold text-white">
          <i class="bi bi-person-vcard mr-1 text-cyan-300"></i>
          ${cliente}
        </div>
      </div>

      ${
        clienteNombre
          ? `
        <div>
          <div class="text-xs text-slate-400 mb-1">
            Nombre
          </div>

          <div class="text-sm font-semibold text-white">
            <i class="bi bi-person mr-1 text-cyan-300"></i>
            ${clienteNombre}
          </div>
        </div>
      `
          : ""
      }

      ${
        clienteTelefono
          ? `
        <div>
          <div class="text-xs text-slate-400 mb-1">
            Teléfono
          </div>

          <a
            href="tel:${clienteTelefonoEnlace}"
            class="text-sm font-semibold text-cyan-300 hover:text-cyan-200 transition"
          >
            <i class="bi bi-telephone-fill mr-1"></i>
            ${clienteTelefono}
          </a>
        </div>
      `
          : ""
      }

      ${
        clienteDireccion
          ? `
        <div class="md:col-span-2">
          <div class="text-xs text-slate-400 mb-1">
            Dirección del cliente
          </div>

          <div class="text-sm font-semibold text-slate-200 break-words">
            <i class="bi bi-house-door-fill mr-1 text-cyan-300"></i>
            ${clienteDireccion}
          </div>
        </div>
      `
          : ""
      }

    </div>
  </div>
`
          : ""
      }
    </div>
  `;

    document.getElementById("eventDate").innerHTML = "";
    document.getElementById("eventAdress").innerHTML = `
    <div class="rounded-xl border border-cyan-500/10 bg-slate-900/60 p-3">
      <div class="text-xs uppercase tracking-wide text-slate-400 mb-1">Ubicación</div>
      <a
        href="https://www.google.com/maps/dir/?api=1&destination=${event.extendedProps.lat},${event.extendedProps.lng}"
        target="_blank"
        class="text-sm font-semibold text-cyan-300 hover:text-cyan-200 transition break-words"
      >
        <i class="bi bi-pin-map-fill mr-1"></i>${ubicacion}
      </a>
    </div>
  `;

    document.getElementById("eventStatus").innerHTML = `
    <div class="rounded-xl border border-cyan-500/10 bg-slate-900/60 p-3">
      <div class="text-xs uppercase tracking-wide text-slate-400 mb-1">Estado actual</div>
      <div>
        <span class="inline-flex px-3 py-1 rounded-lg text-sm font-semibold ${estadoClass}">
          ${escapeHtml(estadoRaw)}
        </span>
      </div>
    </div>
  `;

    const lat = event.extendedProps.lat || 20.12933;
    const lng = event.extendedProps.lng || -101.17979;

    const eventMapContainer = document.getElementById("eventMap");
    const botones = document.getElementById("botones");
    const cancelar = document.getElementById("botonCancelar");
    botones.innerHTML = "";
    eventMapContainer.innerHTML = "";

    switch (event.extendedProps.estado) {
      case "creado":
        botones.innerHTML = `
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <button
          id="statusCreated"
          class="w-full min-h-[56px] px-4 py-3 rounded-xl font-semibold flex items-center justify-center text-center leading-tight text-sm md:text-base bg-slate-700 text-white"
          disabled
        >
          Creado
        </button>
        <button
          id="statusInProcess"
          class="w-full min-h-[56px] px-4 py-3 rounded-xl font-semibold flex items-center justify-center text-center leading-tight text-sm md:text-base bg-yellow-500 text-white"
          onclick="proceso(${event.id}, 'proceso')"
        >
          En Proceso
        </button>
        <button
          id="statusCompleted"
          class="w-full min-h-[56px] px-4 py-3 rounded-xl font-semibold flex items-center justify-center text-center leading-tight text-sm md:text-base bg-slate-700 text-white"
          disabled
        >
          Completado
        </button>
      </div>
    `;
        cancelar.innerHTML = `
      <button
        id="statusCanceled"
        class="w-full min-h-[56px] px-4 py-3 rounded-xl font-semibold flex items-center justify-center text-center leading-tight text-sm md:text-base bg-red-500 text-white"
        onclick="confirmarCancelacion(${event.id})"
      >
        Cancelar <i class="bi bi-x ml-2"></i>
      </button>
    `;
        break;

      case "proceso":
        botones.innerHTML = `
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <button
          id="statusCreated"
          class="w-full min-h-[56px] px-4 py-3 rounded-xl font-semibold flex items-center justify-center text-center leading-tight text-sm md:text-base bg-slate-700 text-white"
          disabled
        >
          Creado
        </button>
        <button
          id="statusInProcess"
          class="w-full min-h-[56px] px-4 py-3 rounded-xl font-semibold flex items-center justify-center text-center leading-tight text-sm md:text-base bg-slate-700 text-white"
          disabled
        >
          En Proceso
        </button>
        <button
          id="statusCompleted"
          class="w-full min-h-[56px] px-4 py-3 rounded-xl font-semibold flex items-center justify-center text-center leading-tight text-sm md:text-base bg-yellow-500 text-white"
          onclick="showSlider()"
        >
          Completado
        </button>
      </div>
    `;
        cancelar.innerHTML = `
      <button
        id="statusCanceled"
        class="w-full min-h-[56px] px-4 py-3 rounded-xl font-semibold flex items-center justify-center text-center leading-tight text-sm md:text-base bg-red-500 text-white"
        onclick="confirmarCancelacion(${event.id})"
      >
        Cancelar <i class="bi bi-x ml-2"></i>
      </button>
    `;
        break;

      case "terminado":
        botones.innerHTML = `
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <button
          id="statusCreated"
          class="w-full min-h-[56px] px-4 py-3 rounded-xl font-semibold flex items-center justify-center text-center leading-tight text-sm md:text-base bg-slate-700 text-white"
          disabled
        >
          Creado
        </button>
        <button
          id="statusInProcess"
          class="w-full min-h-[56px] px-4 py-3 rounded-xl font-semibold flex items-center justify-center text-center leading-tight text-sm md:text-base bg-slate-700 text-white"
          disabled
        >
          En Proceso
        </button>
        <button
          id="statusCompleted"
          class="w-full min-h-[56px] px-4 py-3 rounded-xl font-semibold flex items-center justify-center text-center leading-tight text-sm md:text-base bg-slate-700 text-white"
          disabled
        >
          Terminado
        </button>
      </div>
    `;
        cancelar.innerHTML = `
      <button
        id="statusCanceled"
        class="w-full min-h-[56px] px-4 py-3 rounded-xl font-semibold flex items-center justify-center text-center leading-tight text-sm md:text-base bg-slate-700 text-white"
        disabled
      >
        Cancelar <i class="bi bi-x ml-2"></i>
      </button>
    `;
        break;

      case "cancelado":
        botones.innerHTML = `
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <button
          id="statusCreated"
          class="w-full min-h-[56px] px-4 py-3 rounded-xl font-semibold flex items-center justify-center text-center leading-tight text-sm md:text-base bg-slate-700 text-white"
          disabled
        >
          Creado
        </button>
        <button
          id="statusInProcess"
          class="w-full min-h-[56px] px-4 py-3 rounded-xl font-semibold flex items-center justify-center text-center leading-tight text-sm md:text-base bg-slate-700 text-white"
          disabled
        >
          En Proceso
        </button>
        <button
          id="statusCompleted"
          class="w-full min-h-[56px] px-4 py-3 rounded-xl font-semibold flex items-center justify-center text-center leading-tight text-sm md:text-base bg-slate-700 text-white"
          disabled
        >
          Terminado
        </button>
      </div>
    `;
        cancelar.innerHTML = `
      <button
        id="statusCanceled"
        class="w-full min-h-[56px] px-4 py-3 rounded-xl font-semibold flex items-center justify-center text-center leading-tight text-sm md:text-base bg-slate-700 text-white"
        disabled
      >
        Cancelar <i class="bi bi-x ml-2"></i>
      </button>
    `;
        break;
    }

    modal.classList.remove("hidden");

    setTimeout(() => {
      eventMap = L.map("eventMap").setView([lat, lng], 13);
      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap contributors",
      }).addTo(eventMap);

      eventMarker = L.marker([lat, lng]).addTo(eventMap);
      eventMap.invalidateSize();
    }, 200);
  }
  window.confirmarCancelacion = function confirmarCancelacion(id) {
    Swal.fire({
      title: "¿Estás seguro?",
      text: "Esto marcará el evento como cancelado.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Sí, cancelar",
      cancelButtonText: "No, volver",
    }).then((result) => {
      if (result.isConfirmed) {
        proceso(id, "cancelado");
      }
    });
  };
  function closeModalHandler() {
    modal.classList.add("hidden");
    if (eventMap) {
      eventMap.remove();
      eventMap = null;
    }
  }
  function deleteEvent(eventId) {
    fetch(`../tareas/php/eventos.php?id=${eventId}`, {
      method: "DELETE",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          calendar.refetchEvents();
          Swal.fire({
            icon: "success",
            title: "Evento eliminado",
            text: "El evento fue eliminado con éxito.",
          });
          closeModalHandler();
        } else {
          throw new Error(data.error || "Error desconocido");
        }
      })
      .catch((error) => {
        Swal.fire({
          icon: "error",
          title: "Error al eliminar el evento",
          text: error.message,
        });
      });
  }
  window.proceso = function proceso(id, estado) {
    const formData = new FormData();
    formData.append("id", id);
    formData.append("estado", estado);

    fetch("../tareas/php/proceso.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        //console.log(data); // Confirmamos la respuesta en consola
        if (data.success === true) {
          // Verificamos correctamente la propiedad success
          Swal.fire({
            icon: "success",
            title: "Evento Actualizado",
            text: data.message || "El evento fue actualizado con éxito.",
          });
          closeModalHandler();
          calendar.refetchEvents();
        } else {
          Swal.fire({
            icon: "error",
            title: "Error al cambiar el evento",
            text: data.error || "Error desconocido.",
          });
        }
      })
      .catch((error) => {
        console.error("Ocurrió un error:", error);
        Swal.fire({
          icon: "error",
          title: "Error en la solicitud",
          text: "Ocurrió un error al comunicarse con el servidor.",
        });
      });
  };

  closeModal.addEventListener("click", closeModalHandler);
  closeModalButton.addEventListener("click", closeModalHandler);

  if (eventModalOverlay) {
    eventModalOverlay.addEventListener("click", closeModalHandler);
  }

  // ===============================
  // Modal "Tareas del día" (FAB)
  // ===============================
  const openDayTasksBtn = document.getElementById("openDayTasks");
  const dayTasksModal = document.getElementById("dayTasksModal");
  const closeDayTasksBtn = document.getElementById("closeDayTasks");
  const closeDayTasksFooterBtn = document.getElementById("closeDayTasksFooter");
  const dayTasksDate = document.getElementById("dayTasksDate");
  const dayTasksSearch = document.getElementById("dayTasksSearch");
  const dayTasksList = document.getElementById("dayTasksList");
  const dayTasksSubtitle = document.getElementById("dayTasksSubtitle");
  const refreshDayTasksBtn = document.getElementById("refreshDayTasks");

  const pad2 = (n) => String(n).padStart(2, "0");
  const toISODate = (d) =>
    `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;
  const escapeHtml = (s) =>
    String(s ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");

  function fmtTime(d) {
    if (!(d instanceof Date) || isNaN(d)) return "—";
    return `${pad2(d.getHours())}:${pad2(d.getMinutes())}`;
  }
  function formatDateTime(value) {
    if (!value) return "No especificado";
    const d = value instanceof Date ? value : new Date(value);
    if (isNaN(d)) return "No especificado";

    return d.toLocaleString("es-MX", {
      year: "numeric",
      month: "2-digit",
      day: "2-digit",
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  function getEstadoBadgeClass(estadoRaw) {
    switch (estadoRaw) {
      case "creado":
        return "bg-green-600/20 text-green-300 ring-1 ring-green-600/30";
      case "proceso":
        return "bg-yellow-600/20 text-yellow-300 ring-1 ring-yellow-600/30";
      case "terminado":
        return "bg-red-600/20 text-red-300 ring-1 ring-red-600/30";
      case "cancelado":
        return "bg-gray-600/30 text-gray-200 ring-1 ring-gray-600/30";
      default:
        return "bg-gray-700 text-gray-200";
    }
  }

  function renderDayTasksLoading() {
    dayTasksList.innerHTML = `
    <div class="space-y-3">
      ${Array.from({ length: 4 })
        .map(
          () => `
        <div class="border border-gray-700 rounded-xl p-4 bg-gray-900/40 animate-pulse">
          <div class="h-4 bg-gray-700 rounded w-1/2 mb-2"></div>
          <div class="h-3 bg-gray-700 rounded w-1/3 mb-2"></div>
          <div class="h-3 bg-gray-700 rounded w-2/3"></div>
        </div>
      `,
        )
        .join("")}
    </div>
  `;
  }

  function renderDayTasksEmpty(msg = "No hay tareas para este día.") {
    dayTasksList.innerHTML = `
    <div class="border border-gray-700 rounded-xl p-5 bg-gray-900/50 text-center">
      <i class="bi bi-calendar2-x text-3xl text-gray-400"></i>
      <div class="mt-2 text-gray-200 font-semibold">${escapeHtml(msg)}</div>
      <div class="text-sm text-gray-400 mt-1">Tip: cambia la fecha o limpia el buscador.</div>
    </div>
  `;
  }

  function getEventsForSelectedDay(selectedDate) {
    const cal = window.calendar; // ya lo guardamos
    if (!cal || typeof cal.getEvents !== "function") return [];

    const events = cal.getEvents();
    const dayStart = new Date(selectedDate);
    dayStart.setHours(0, 0, 0, 0);

    const dayEnd = new Date(selectedDate);
    dayEnd.setHours(23, 59, 59, 999);

    const out = [];
    events.forEach((ev) => {
      if (!ev.start) return;
      const start = ev.start;
      const end = ev.end ? ev.end : ev.start;
      const overlaps = start <= dayEnd && end >= dayStart;
      if (overlaps) out.push(ev);
    });

    out.sort((a, b) => (a.start?.getTime() ?? 0) - (b.start?.getTime() ?? 0));
    return out;
  }

  function renderDayTasksList(events) {
    const q = (dayTasksSearch.value || "").trim().toLowerCase();

    const filtered = events.filter((ev) => {
      const title = (ev.title ?? "").toLowerCase();

      const locRaw = ev.extendedProps?.location ?? ev.location ?? "";
      const loc = String(locRaw).toLowerCase();

      const catRaw = ev.extendedProps?.categoria ?? ev.categoria ?? "Otros";
      const cat = String(catRaw).toLowerCase();

      const clienteRaw = ev.extendedProps?.cliente ?? ev.cliente ?? "";
      const cliente = String(clienteRaw).toLowerCase();

      return (
        !q ||
        title.includes(q) ||
        loc.includes(q) ||
        cat.includes(q) ||
        cliente.includes(q)
      );
    });

    if (!filtered.length) {
      renderDayTasksEmpty(
        q
          ? "No hay coincidencias con tu búsqueda."
          : "No hay tareas para este día.",
      );
      return;
    }

    dayTasksList.innerHTML = filtered
      .map((ev) => {
        const id = escapeHtml(ev.id);

        const title = escapeHtml(ev.title || "Sin título");

        const start = ev.start instanceof Date ? fmtTime(ev.start) : "—";
        const end = ev.end instanceof Date ? fmtTime(ev.end) : "";
        const range = end ? `${start} - ${end}` : start;

        const locationRaw = ev.extendedProps?.location ?? ev.location ?? "";
        const location = locationRaw ? escapeHtml(locationRaw) : "";

        const estadoRaw = ev.extendedProps?.estado ?? ev.estado ?? "";
        const estado = estadoRaw ? escapeHtml(estadoRaw) : "";

        const tipo = ev.extendedProps?.tipo || ev.tipo || "evento";

        const clienteRaw = ev.extendedProps?.cliente ?? ev.cliente ?? "";
        const cliente =
          clienteRaw !== null && clienteRaw !== ""
            ? escapeHtml(clienteRaw)
            : "";
        const icon =
          tipo === "vacaciones" ? "bi bi-airplane-fill" : "bi bi-check2-square";

        // ✅ categoria
        const categoriaRaw =
          ev.extendedProps?.categoria ?? ev.categoria ?? "Otros";
        const categoria = escapeHtml(categoriaRaw || "Otros");

        const catStyle =
          CATEGORY_STYLES[categoriaRaw]?.chip || CATEGORY_STYLES["Otros"].chip;
        const cardStyle =
          CATEGORY_STYLES[categoriaRaw]?.card || CATEGORY_STYLES["Otros"].card;

        // chip estado
        let estadoClass = "bg-gray-700 text-gray-200";
        if (estadoRaw === "creado")
          estadoClass =
            "bg-green-600/20 text-green-300 ring-1 ring-green-600/30";
        if (estadoRaw === "proceso")
          estadoClass =
            "bg-yellow-600/20 text-yellow-300 ring-1 ring-yellow-600/30";
        if (estadoRaw === "terminado")
          estadoClass = "bg-red-600/20 text-red-300 ring-1 ring-red-600/30";
        if (estadoRaw === "cancelado")
          estadoClass = "bg-gray-600/30 text-gray-200 ring-1 ring-gray-600/30";

        return `
        <button
          class="w-full text-left border rounded-xl p-4 transition ${cardStyle}"
          data-event-id="${id}">
          <div class="flex items-start gap-3">
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <div class="font-semibold text-white flex items-center gap-2 min-w-0">
                    <i class="${icon} text-cyan-300"></i>
                    <span class="truncate">${title}</span>
                  </div>

                  <div class="text-sm text-cyan-300 font-semibold mt-1">
                    <i class="bi bi-clock"></i> ${escapeHtml(range)}
                  </div>

                  ${
                    location
                      ? `<div class="text-sm text-gray-200 mt-1"><i class="bi bi-geo-alt"></i> ${location}</div>`
                      : ""
                  }

${
  cliente
    ? `<div class="text-sm text-slate-300 mt-1"><i class="bi bi-person-vcard"></i> Cliente: ${cliente}</div>`
    : ""
}
                </div>

                <div class="flex items-center gap-2 shrink-0">
                  <span class="text-xs px-2 py-1 rounded-lg whitespace-nowrap ${catStyle}">
                    ${categoria}
                  </span>
                  ${
                    estado
                      ? `<span class="text-xs px-2 py-1 rounded-lg ${estadoClass} whitespace-nowrap">${estado}</span>`
                      : ""
                  }
                </div>
              </div>
            </div>
          </div>
        </button>
      `;
      })
      .join("");

    // Click en item => abre tu modal existente
    dayTasksList.querySelectorAll("button[data-event-id]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const id = btn.getAttribute("data-event-id");
        const cal = window.calendar;

        const ev =
          cal && typeof cal.getEvents === "function"
            ? cal.getEvents().find((e) => String(e.id) === String(id))
            : null;

        if (!ev) return;

        modalEvent = ev;
        closeDayTasksModal();

        if (ev.extendedProps?.tipo === "vacaciones") showVacationModal(ev);
        else showEventModal(ev);
      });
    });
  }

  async function refreshDayTasks() {
    if (!dayTasksDate || !dayTasksList) return;

    const iso = dayTasksDate.value || toISODate(new Date());
    const d = new Date(iso + "T00:00:00");
    if (dayTasksSubtitle)
      dayTasksSubtitle.textContent = `Mostrando tareas del ${d.toLocaleDateString("es-MX")}`;

    renderDayTasksLoading();

    try {
      const r = await fetch(
        `../tareas/php/eventos_dia.php?date=${encodeURIComponent(iso)}`,
        {
          method: "GET",
          headers: { Accept: "application/json" },
        },
      );

      const data = await r.json().catch(() => ({}));
      if (!r.ok || !data.success) {
        throw new Error(
          data.error || "No se pudieron cargar las tareas del día.",
        );
      }

      // Convertimos strings a Date para reusar renderDayTasksList tal cual
      const events = (data.events || []).map((ev) => ({
        ...ev,
        start: ev.start ? new Date(ev.start.replace(" ", "T")) : null,
        end: ev.end ? new Date(ev.end.replace(" ", "T")) : null,
        // Asegura extendedProps
        extendedProps: ev.extendedProps || {},
      }));

      renderDayTasksList(events);
    } catch (e) {
      console.error(e);
      renderDayTasksEmpty("Ocurrió un error al cargar la lista.");
      Swal.fire({ icon: "error", title: "Error", text: e.message });
    }
  }

  function openDayTasksModal() {
    if (!dayTasksModal) return;
    dayTasksModal.classList.remove("hidden");
    if (dayTasksDate && !dayTasksDate.value)
      dayTasksDate.value = toISODate(new Date());
    refreshDayTasks();
  }

  function closeDayTasksModal() {
    if (!dayTasksModal) return;
    dayTasksModal.classList.add("hidden");
  }

  // Wiring
  if (openDayTasksBtn && dayTasksModal) {
    openDayTasksBtn.addEventListener("click", openDayTasksModal);
    if (closeDayTasksBtn)
      closeDayTasksBtn.addEventListener("click", closeDayTasksModal);
    if (closeDayTasksFooterBtn)
      closeDayTasksFooterBtn.addEventListener("click", closeDayTasksModal);

    // click fuera para cerrar
    dayTasksModal.addEventListener("click", (e) => {
      const overlay = dayTasksModal.querySelector(".absolute.inset-0");
      if (e.target === overlay) closeDayTasksModal();
    });

    if (refreshDayTasksBtn)
      refreshDayTasksBtn.addEventListener("click", refreshDayTasks);
    if (dayTasksDate) dayTasksDate.addEventListener("change", refreshDayTasks);

    let t = null;
    if (dayTasksSearch) {
      dayTasksSearch.addEventListener("input", () => {
        clearTimeout(t);
        t = setTimeout(refreshDayTasks, 200);
      });
    }
  }
});
