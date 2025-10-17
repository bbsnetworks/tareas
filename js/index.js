document.addEventListener('DOMContentLoaded', function () {
    let modalEvent;
    const sliderContainer = document.getElementById('sliderContainer');
    const eventDetails = document.getElementById('eventDetails');
    const submitSlider = document.getElementById('submitSlider');
    const backButton = document.getElementById('backButton');

    const calendarEl = document.getElementById('calendar');
    const initialView = window.innerWidth < 768 ? 'listWeek' : 'dayGridMonth';
    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: initialView,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        events: {
            url: '../ordenes/php/eventos.php',
            method: 'GET',
            failure: function (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los eventos.',
                });
                console.error('Error al cargar eventos:', error);
            },
        },
        editable: true,
        selectable: true,
        eventClick: function (info) {
            modalEvent = info.event;
            if (modalEvent.extendedProps.tipo === 'vacaciones') {
                showVacationModal(modalEvent);
            } else {
                showEventModal(modalEvent);
            }
        },
        windowResize: function () {
            if (window.innerWidth < 768) {
                calendar.changeView('listWeek');
            } else {
                calendar.changeView('dayGridMonth');
            }
        },
        eventDidMount: function(info) {
            // Aplicar opacidad a eventos para que los colores se vean más tenues
            info.el.style.opacity = '0.75';
            info.el.style.filter = 'saturate(70%)';
        }
    });

    calendar.render();
    

calendar.render();

    // Mostrar slider al hacer clic en el botón "Completado"
        window.showSlider = function showSlider() {
            const date = moment().format("YYYY-MM-DDTHH:mm");
            document.getElementById("fin").value = date;
        //document.querySelector("#fin").innerHTML = moment().format("DD/MM/yyyy"); 
        eventDetails.classList.add('-translate-x-full');
        sliderContainer.classList.remove('hidden');
        setTimeout(() => {
            sliderContainer.classList.remove('translate-x-full');
        }, 10); // Permitir que la transición ocurra
    };
    function showVacationModal(event) {
        document.getElementById('vacationTitle').innerHTML = `<i class="bi bi-calendar"></i> ${event.title}`;
        document.getElementById('vacationDate').innerHTML = `<i class="bi bi-clock"></i> Desde: ${event.start.toLocaleString()} 
            <br> <i class="bi bi-clock-fill"></i> Hasta: ${event.end ? event.end.toLocaleString() : 'No especificado'}`;
    
        document.getElementById('vacationModal').classList.remove('hidden');
    }
    
    document.getElementById('closeVacationModal').addEventListener('click', function () {
        document.getElementById('vacationModal').classList.add('hidden');
    });
    // Ocultar slider
    function hideSlider() {
        sliderContainer.classList.add('hidden');
        document.getElementById('evidence').value = ''; // Limpiar campo de evidencia
        document.getElementById('comments').value = ''; // Limpiar campo de comentarios
    }
    // Volver a la sección inicial
    backButton.addEventListener('click', function () {
        sliderContainer.classList.add('translate-x-full');
        setTimeout(() => {
            sliderContainer.classList.add('hidden');
            eventDetails.classList.remove('-translate-x-full');
        }, 500); // Esperar a que termine la transición
    });

    

    // Enviar datos del slider
    submitSlider.addEventListener('click', function () {
        const comments = document.getElementById('comments').value.trim();
        const fin = document.getElementById('fin').value.trim();
        const evidence = document.getElementById('evidence').files;
    
        if (!comments) {
            Swal.fire({
                icon: 'error',
                title: 'Faltan comentarios',
                text: 'El campo de comentarios es obligatorio.',
            });
            return;
        }
    
        const formData = new FormData();
        formData.append('id', modalEvent.id);
        formData.append('estado', 'terminado');
        formData.append('comments', comments);
        formData.append('fin', fin);
    
        if (evidence.length > 0) {
            for (let i = 0; i < evidence.length; i++) {
                formData.append('evidence[]', evidence[i]); // Añadir cada archivo al FormData
            }
        }
    
        fetch('../ordenes/php/terminado.php', {
            method: 'POST',
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Evento actualizado',
                        text: 'El evento fue actualizado con éxito.',
                    });
                    calendar.refetchEvents();
                    document.getElementById('comments').value = '';
                    document.getElementById('evidence').value = '';
                    backButton.click(); // Volver automáticamente después del envío
                    closeModalHandler();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al actualizar el evento',
                        text: data.error || 'Error desconocido.',
                    });
                }
            })
            .catch((error) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al actualizar el evento',
                    text: error.message,
                });
                console.error('Error:', error);
            });
    });
    


     // Mapa en el formulario
    const formMap = L.map('map').setView([20.12933, -101.17979], 13); // Coordenadas iniciales
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(formMap);





    const formMarker = L.marker([20.12933, -101.17979], { draggable: true }).addTo(formMap);
    const form = document.getElementById('eventForm');
    
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const title = document.getElementById('title').value.trim();
        const start = document.getElementById('start').value;
        const end = document.getElementById('end').value || null;
        const color = document.getElementById('color').value;
        const location = document.getElementById('here-autocomplete').value.trim();
        const lat = document.getElementById('lat').value;
        const lng = document.getElementById('lng').value;

        if (!title || !start) {
            Swal.fire({
                icon: 'error',
                title: 'Campos incompletos',
                text: 'Por favor, completa todos los campos obligatorios.',
            });
            return;
        }

        const newEvent = {
            title,
            start,
            end,
            color,
            location,
            lat,
            lng,
        };

        fetch('../ordenes/php/eventos.php', {
            method: 'POST',
            body: JSON.stringify(newEvent),
            headers: {
                'Content-Type': 'application/json',
            },
        })
            .then((response) => response.json().then((data) => ({ status: response.status, body: data })))
            .then(({ status, body }) => {
                if (status !== 200) {
                    throw new Error(body.error || 'Error desconocido');
                }
                calendar.refetchEvents();
                form.reset();
                Swal.fire({
                    icon: 'success',
                    title: 'Evento agregado',
                    text: 'El evento fue agregado con éxito.',
                });
            })
            .catch((error) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al guardar el evento',
                    text: error.message,
                });
                console.error('Error:', error);
            });
    });
    // Actualizar ubicación en el formulario
    async function updateLocation(lat, lng) {
        const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`;
        const response = await fetch(url);
        const data = await response.json();
        const address = data.display_name || 'Ubicación desconocida';
        document.getElementById('here-autocomplete').value = address;
        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;
    }

    formMarker.on('moveend', function (e) {
        const { lat, lng } = e.target.getLatLng();
        updateLocation(lat, lng);
        formMap.invalidateSize(); // Asegurar el renderizado al mover el marcador
    });

    // Mapa en el modal
    let eventMap; // Mapa del modal
    let eventMarker; // Marcador del modal

    calendar.render();

    const modal = document.getElementById('eventModal');
    const closeModal = document.getElementById('closeModal');
    const closeModalButton = document.getElementById('closeModalButton');

    function showEventModal(event) {
        document.getElementById('idTitle').textContent = 'ID : '+event.id;
        document.getElementById('eventTitle').innerHTML = `<span class="mb-2"><i class="bi bi-clipboard2-fill"></i> Titulo: ${event.title}</span>`;
        //document.getElementById('eventDate').innerHTML = `<span class="text-blue-500">Inicio: ${event.start.toLocaleString()}</span> <span class="text-red-500">${
            //event.end ? ` Fin: ${event.end.toLocaleString()}</span>` : ''
        //}`;
        document.getElementById('eventDate').innerHTML = `<span class="text-blue-500 mb-2"><i class="bi bi-clock"></i> Inicio: ${event.start.toLocaleString()}</span> <span class="text-red-500">${
            event.end ? `<i class="bi bi-clock-fill"></i> Fin: ${event.end.toLocaleString()}</span>` : ''
            }`;
        document.getElementById('eventAdress').innerHTML = `<a href="https://www.google.com/maps/dir/?api=1&destination=${event.extendedProps.lat},${event.extendedProps.lng}" target="_blank"><i class="bi bi-pin-map-fill"></i> ${event.extendedProps.location}</a>`;
        console.log("inicio:"+event.start+" y fin: "+event.end);
        switch (event.extendedProps.estado) {
            case "creado":
                document.getElementById('eventStatus').innerHTML = `Estado: <span class="text-green-500">${event.extendedProps.estado}</span>`;
                break;
            case "proceso":
                document.getElementById('eventStatus').innerHTML = `Estado: <span class="text-yellow-500">En ${event.extendedProps.estado}</span>`;
                break;
            case "terminado":
                document.getElementById('eventStatus').innerHTML = `Estado: <span class="text-red-500">${event.extendedProps.estado}</span>`;
                break;
            case "cancelado":
                document.getElementById('eventStatus').innerHTML = `Estado: <span class="text-red-500">${event.extendedProps.estado}</span>`;
                break;    
        }
        

        const lat = event.extendedProps.lat || 20.12933; // Coordenadas predeterminadas
        const lng = event.extendedProps.lng || -101.17979;

        const eventMapContainer = document.getElementById('eventMap');
        const botones = document.getElementById('botones');
        const cancelar = document.getElementById('botonCancelar');
        botones.innerHTML = ""
        eventMapContainer.innerHTML = '';
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
          
        
        modal.classList.remove('hidden');

        setTimeout(() => {
            eventMap = L.map('eventMap').setView([lat, lng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
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
    }
    function closeModalHandler() {
        modal.classList.add('hidden');
        if (eventMap) {
            eventMap.remove();
        }
    }
    function deleteEvent(eventId) {
        fetch(`../ordenes/php/eventos.php?id=${eventId}`, {
            method: 'DELETE',
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    calendar.refetchEvents();
                    Swal.fire({
                        icon: 'success',
                        title: 'Evento eliminado',
                        text: 'El evento fue eliminado con éxito.',
                    });
                    closeModalHandler();
                } else {
                    throw new Error(data.error || 'Error desconocido');
                }
            })
            .catch((error) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al eliminar el evento',
                    text: error.message,
                });
            });
    }
    window.proceso = function proceso(id, estado) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('estado', estado);
    
        fetch('../ordenes/php/proceso.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            //console.log(data); // Confirmamos la respuesta en consola
            if (data.success === true) { // Verificamos correctamente la propiedad success
                Swal.fire({
                    icon: 'success',
                    title: 'Evento Actualizado',
                    text: data.message || 'El evento fue actualizado con éxito.',
                });
                closeModalHandler();
                calendar.refetchEvents();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al cambiar el evento',
                    text: data.error || 'Error desconocido.',
                });
            }
        })
        .catch(error => {
            console.error('Ocurrió un error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error en la solicitud',
                text: 'Ocurrió un error al comunicarse con el servidor.',
            });
        });
    };
    
    
    
    closeModal.addEventListener('click', closeModalHandler);
    closeModalButton.addEventListener('click', closeModalHandler);

    document.getElementById('deleteEventButton').addEventListener('click', () => {
        if (modalEvent) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Este evento será eliminado permanentemente.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteEvent(modalEvent.id);
                }
            });
        } else {
            console.error('No se ha seleccionado ningún evento para eliminar.');
        }
    });
});



