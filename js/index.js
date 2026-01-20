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
  const calendar = new FullCalendar.Calendar(calendarEl, {
    locale: "es",
    initialView: initialView,
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth,timeGridWeek,timeGridDay",
    },
    events: {
      url: "../tareas/php/eventos.php",
      method: "GET",
      failure: function (error) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "No se pudieron cargar los eventos.",
        });
        console.error("Error al cargar eventos:", error);
      },
    },
    editable: true,
    selectable: true,
    eventClick: function (info) {
      modalEvent = info.event;
      if (modalEvent.extendedProps.tipo === "vacaciones") {
        showVacationModal(modalEvent);
      } else {
        showEventModal(modalEvent);
      }
    },
    windowResize: function () {
      if (window.innerWidth < 768) {
        calendar.changeView("listWeek");
      } else {
        calendar.changeView("dayGridMonth");
      }
    },
    eventDidMount: function (info) {
      // Aplicar opacidad a eventos para que los colores se vean más tenues
      info.el.style.opacity = "0.75";
      info.el.style.filter = "saturate(70%)";
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
    document.getElementById("vacationTitle").innerHTML =
      `<i class="bi bi-calendar"></i> ${event.title}`;
    document.getElementById("vacationDate").innerHTML =
      `<i class="bi bi-clock"></i> Desde: ${event.start.toLocaleString()} 
            <br> <i class="bi bi-clock-fill"></i> Hasta: ${event.end ? event.end.toLocaleString() : "No especificado"}`;

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

  const formMarker = L.marker([20.12933, -101.17979], {
    draggable: true,
  }).addTo(formMap);
  const form = document.getElementById("eventForm");

  form.addEventListener("submit", function (event) {
    event.preventDefault();

    const title = document.getElementById("title").value.trim();
    const start = document.getElementById("start").value;
    const end = document.getElementById("end").value || null;
    const color = document.getElementById("color").value;
    const location = document.getElementById("here-autocomplete").value.trim();
    const lat = document.getElementById("lat").value;
    const lng = document.getElementById("lng").value;
    const categoria = document.getElementById("categoria").value;

    if (!title || !start || !categoria) {
      Swal.fire({
        icon: "error",
        title: "Campos incompletos",
        text: "Por favor, completa todos los campos obligatorios.",
      });
      return;
    }

    const newEvent = {
      title,
      start,
      end,
      color,
      categoria,
      location,
      lat,
      lng,
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
        form.reset();
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
    const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`;
    const response = await fetch(url);
    const data = await response.json();
    const address = data.display_name || "Ubicación desconocida";
    document.getElementById("here-autocomplete").value = address;
    document.getElementById("lat").value = lat;
    document.getElementById("lng").value = lng;
  }

  formMarker.on("moveend", function (e) {
    const { lat, lng } = e.target.getLatLng();
    updateLocation(lat, lng);
    formMap.invalidateSize(); // Asegurar el renderizado al mover el marcador
  });

  // Mapa en el modal
  let eventMap; // Mapa del modal
  let eventMarker; // Marcador del modal

  const modal = document.getElementById("eventModal");
  const closeModal = document.getElementById("closeModal");
  const closeModalButton = document.getElementById("closeModalButton");

  function showEventModal(event) {
    document.getElementById("idTitle").textContent = "ID : " + event.id;
    const categoria =
      event.extendedProps?.categoria || event.categoria || "Otros";
    const catStyle =
      CATEGORY_STYLES[categoria]?.chip || CATEGORY_STYLES["Otros"].chip;

    document.getElementById("eventTitle").innerHTML = `
  <span class="mb-2">
    <i class="bi bi-clipboard2-fill"></i> Titulo: ${event.title}
  </span>
  <span class="mb-2 flex items-center gap-2">
    <i class="bi bi-tag-fill"></i> Categoría:
    <span class="px-2 py-1 rounded text-xs font-semibold ${catStyle}">
      ${categoria}
    </span>
  </span>
`;

    //document.getElementById('eventDate').innerHTML = `<span class="text-blue-500">Inicio: ${event.start.toLocaleString()}</span> <span class="text-red-500">${
    //event.end ? ` Fin: ${event.end.toLocaleString()}</span>` : ''
    //}`;
    document.getElementById("eventDate").innerHTML =
      `<span class="text-blue-500 mb-2"><i class="bi bi-clock"></i> Inicio: ${event.start.toLocaleString()}</span> <span class="text-red-500">${
        event.end
          ? `<i class="bi bi-clock-fill"></i> Fin: ${event.end.toLocaleString()}</span>`
          : ""
      }`;
    document.getElementById("eventAdress").innerHTML =
      `<a href="https://www.google.com/maps/dir/?api=1&destination=${event.extendedProps.lat},${event.extendedProps.lng}" target="_blank"><i class="bi bi-pin-map-fill"></i> ${event.extendedProps.location}</a>`;
    console.log("inicio:" + event.start + " y fin: " + event.end);
    switch (event.extendedProps.estado) {
      case "creado":
        document.getElementById("eventStatus").innerHTML =
          `Estado: <span class="text-green-500">${event.extendedProps.estado}</span>`;
        break;
      case "proceso":
        document.getElementById("eventStatus").innerHTML =
          `Estado: <span class="text-yellow-500">En ${event.extendedProps.estado}</span>`;
        break;
      case "terminado":
        document.getElementById("eventStatus").innerHTML =
          `Estado: <span class="text-red-500">${event.extendedProps.estado}</span>`;
        break;
      case "cancelado":
        document.getElementById("eventStatus").innerHTML =
          `Estado: <span class="text-red-500">${event.extendedProps.estado}</span>`;
        break;
    }

    const lat = event.extendedProps.lat || 20.12933; // Coordenadas predeterminadas
    const lng = event.extendedProps.lng || -101.17979;

    const eventMapContainer = document.getElementById("eventMap");
    const botones = document.getElementById("botones");
    const cancelar = document.getElementById("botonCancelar");
    botones.innerHTML = "";
    eventMapContainer.innerHTML = "";
    //console.log(event.extendedProps.estado);
    //console.log(event.id);
    switch (event.extendedProps.estado) {
      case "creado":
        botones.innerHTML = `
                <button id="statusCreated" class="bg-gray-500 text-white px-4 py-2 rounded" disabled>Creado</button>
                <button id="statusInProcess" class="bg-yellow-500 text-white px-4 py-2 rounded" onclick="proceso(${event.id}, 'proceso')">En Proceso</button>
                <button id="statusCompleted" class="bg-gray-500 text-white px-4 py-2 rounded" disabled>Completado</button>
              `;
        cancelar.innerHTML = `
                <button id="statusCanceled" class="bg-red-500 text-white px-4 py-2 rounded w-36" onclick="confirmarCancelacion(${event.id})">Cancelar <i class="bi bi-x"></i></button>
              `;
        break;

      case "proceso":
        botones.innerHTML = `
                <button id="statusCreated" class="bg-gray-500 text-white px-4 py-2 rounded" disabled>Creado</button>
                <button id="statusInProcess" class="bg-gray-500 text-white px-4 py-2 rounded" disabled>En Proceso</button>
                <button id="statusCompleted" class="bg-yellow-500 text-white px-4 py-2 rounded" onclick="showSlider()">Completado</button>
              `;
        cancelar.innerHTML = `
                <button id="statusCanceled" class="bg-red-500 text-white px-4 py-2 rounded w-36" onclick="confirmarCancelacion(${event.id})">Cancelar <i class="bi bi-x"></i></button>
              `;
        break;

      case "terminado":
        botones.innerHTML = `
                <button id="statusCreated" class="bg-gray-500 text-white px-4 py-2 rounded" disabled>Creado</button>
                <button id="statusInProcess" class="bg-gray-500 text-white px-4 py-2 rounded" disabled>En Proceso</button>
                <button id="statusCompleted" class="bg-gray-500 text-white px-4 py-2 rounded" disabled>Terminado</button>
              `;
        cancelar.innerHTML = `
                <button id="statusCanceled" class="bg-gray-500 text-white px-4 py-2 rounded w-36" disabled>Cancelar <i class="bi bi-x"></i></button>
              `;
        break;

      case "cancelado":
        botones.innerHTML = `
                <button id="statusCreated" class="bg-gray-500 text-white px-4 py-2 rounded" disabled>Creado</button>
                <button id="statusInProcess" class="bg-gray-500 text-white px-4 py-2 rounded" disabled>En Proceso</button>
                <button id="statusCompleted" class="bg-gray-500 text-white px-4 py-2 rounded" disabled>Terminado</button>
              `;
        cancelar.innerHTML = `
                <button id="statusCanceled" class="bg-gray-500 text-white px-4 py-2 rounded w-36" disabled>Cancelar <i class="bi bi-x"></i></button>
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

  document.getElementById("deleteEventButton").addEventListener("click", () => {
    if (modalEvent) {
      Swal.fire({
        title: "¿Estás seguro?",
        text: "Este evento será eliminado permanentemente.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Sí, eliminar",
      }).then((result) => {
        if (result.isConfirmed) {
          deleteEvent(modalEvent.id);
        }
      });
    } else {
      console.error("No se ha seleccionado ningún evento para eliminar.");
    }
  });
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

    // ✅ Buscar por: titulo, ubicacion y categoria
    return !q || title.includes(q) || loc.includes(q) || cat.includes(q);
  });

  if (!filtered.length) {
    renderDayTasksEmpty(
      q ? "No hay coincidencias con tu búsqueda." : "No hay tareas para este día."
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
      const icon = tipo === "vacaciones" ? "bi bi-airplane-fill" : "bi bi-check2-square";

      // ✅ categoria
      const categoriaRaw = ev.extendedProps?.categoria ?? ev.categoria ?? "Otros";
      const categoria = escapeHtml(categoriaRaw || "Otros");

      const catStyle = CATEGORY_STYLES[categoriaRaw]?.chip || CATEGORY_STYLES["Otros"].chip;
      const cardStyle = CATEGORY_STYLES[categoriaRaw]?.card || CATEGORY_STYLES["Otros"].card;

      // chip estado
      let estadoClass = "bg-gray-700 text-gray-200";
      if (estadoRaw === "creado")
        estadoClass = "bg-green-600/20 text-green-300 ring-1 ring-green-600/30";
      if (estadoRaw === "proceso")
        estadoClass = "bg-yellow-600/20 text-yellow-300 ring-1 ring-yellow-600/30";
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

      const ev = cal && typeof cal.getEvents === "function"
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
