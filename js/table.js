date = moment().format("YYYY-MM");
date = date.toString();
document.getElementById("fecha").value = date;
mes = moment().format("MM");
mes = mes.toString();
year = moment().format("YYYY");
year = year.toString();

let modalEvent;
$("#fecha").on("change", function () {
  var valorFecha = $("#fecha").val();
  var yearFecha = valorFecha.split("-")[0];
  var mesFecha = valorFecha.split("-")[1];
  cargarTabla(mesFecha,yearFecha);
});

function cargarTabla(mes,year) {
  console.log(mes);
  console.log(year);
  var formData = new FormData();
  formData.append("mes", mes);
  formData.append("year", year);
  $.ajax({
      url: '../php/cargarTabla.php',
      data: formData,
      processData: false,
      contentType: false,
      type: 'POST',
      success: function(response) {
          $('#tabla').html(response);
      },
      error: function(jqXHR, textStatus, errorThrown) {
          $('#tabla').html('Error al cargar la tabla: ' + textStatus);
      }
  });
}

cargarTabla(mes,year);

function editGI(id){
  $.ajax({
      url: "../php/editarGI.php",
      type: "POST",
      data: { id:id },
      success: function (response) {
          $('#modal2').html(response);
      },
      error: function (jqXHR, textStatus, errorThrown) {
        $("#resultado").html("Error al editar el registro: " + textStatus);
      },
    });
}

function closeModalHandler() {
  const modal = document.getElementById('eventModal');
  modal.classList.add('hidden');
  if (eventMap) {
      eventMap.remove();
  }
}
document.getElementById('closeModal').addEventListener('click', () => {
  const modal = document.getElementById('eventModal');
  modal.classList.add('hidden');
});
document.getElementById('closeModalButton').addEventListener('click', () => {
  const modal = document.getElementById('eventModal');
  modal.classList.add('hidden');
});

let eventMap;
let eventMarker;

function showEventModal(id) {
  console.log("ID recibido:", id);
  const modal = document.getElementById('eventModal');
  const formData = new FormData();
  formData.append('id', id);

  fetch('../php/mostrar.php', {
      method: 'POST',
      body: formData,
  })
  .then((response) => response.json())
  .then((data) => {
      if (data.success) {
          document.getElementById('idTitle').textContent = data.id;
          document.getElementById('eventTitle').innerHTML = `<span class="mb-2"><i class="bi bi-clipboard2-fill"></i> Titulo: ${data.titulo}</span>`;
          document.getElementById('eventAdress').innerHTML = `<a href="https://www.google.com/maps/dir/?api=1&destination=${data.lat},${data.lng}" target="_blank"><i class="bi bi-pin-map-fill"></i> ${data.ubicacion}</a>`;
          document.getElementById('eventDate').innerHTML = `<span class="text-blue-500"><i class="bi bi-clock"></i> Inicio: ${data.inicio}</span> <span class="text-red-500">${data.fin ? `<i class="bi bi-clock-fill"></i> Fin: ${data.fin}</span>` : ''}`;
          switch (data.estado){
            case 'creado':
              document.getElementById('eventStatus').innerHTML = "Creado";
              break;
            case 'proceso':
              document.getElementById('eventStatus').innerHTML = "Proceso";
              break;
            case 'terminado':
              document.getElementById('eventStatus').innerHTML = "Terminado";
              break;
            case 'cancelado':
              document.getElementById('eventStatus').innerHTML = "Cancelado";
              break;    
          }
          document.getElementById('comentarios').innerHTML = `<span class="text-blue-500"><i class="bi bi-chat-left-text-fill"></i> Comentarios:<br> ${data.comentarios}</span>`;

          const lat = data.lat || 20.12933;
          const lng = data.lng || -101.17979;

          const eventMapContainer = document.getElementById('eventMap');
          eventMapContainer.innerHTML = '';
          modal.classList.remove('hidden');

          if (eventMap) {
              eventMap.remove();
              eventMap = null;
          }

          setTimeout(() => {
              eventMap = L.map('eventMap').setView([lat, lng], 13);
              L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                  attribution: '&copy; OpenStreetMap contributors',
              }).addTo(eventMap);
              eventMarker = L.marker([lat, lng]).addTo(eventMap);
              eventMap.invalidateSize();
          }, 200);
      } else {
          Swal.fire({ icon: 'error', title: 'Error al actualizar el evento', text: data.error || 'Error desconocido.' });
      }
  })
  .catch((error) => {
      Swal.fire({ icon: 'error', title: 'Error al actualizar el evento', text: error.message });
      console.error('Error:', error);
  });
}

let editMap = null;

function openEditModal(id) {
  fetch('../php/editarE.php', {
    method: 'POST',
    body: new URLSearchParams({ id }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const modal = document.getElementById('editModal');
        const eventData = data.data;

        document.getElementById('editId').value = eventData.id;
        document.getElementById('editTitle').value = eventData.title;
        document.getElementById('editStart').value = eventData.start.replace(' ', 'T');
        document.getElementById('editEnd').value = eventData.end ? eventData.end.replace(' ', 'T') : '';
        document.getElementById('editLocation').value = eventData.location;
        document.getElementById('editLat').value = eventData.lat;
        document.getElementById('editLng').value = eventData.lng;
        document.getElementById('editComentarios').value = eventData.comentarios;

        let estado = '';
        switch(eventData.estado){
          case 'creado':
            estado = '<option value="creado" selected>Creado</option><option value="proceso">En proceso</option><option value="terminado">Terminado</option><option value="cancelado">Cancelado</option>';
            break;
          case 'proceso':
            estado = '<option value="creado">Creado</option><option value="proceso" selected>En proceso</option><option value="terminado">Terminado</option><option value="cancelado">Cancelado</option>';
            break; 
          case 'terminado':
            estado = '<option value="creado">Creado</option><option value="proceso">En proceso</option><option value="terminado" selected>Terminado</option><option value="cancelado">Cancelado</option>';
            break;
          case 'cancelado':
            estado = '<option value="creado">Creado</option><option value="proceso">En proceso</option><option value="terminado">Terminado</option><option value="cancelado" selected>Cancelado</option>';
            break;
        }
        document.getElementById('editEstado').innerHTML = estado;

        const lat = eventData.lat || 20.12933;
        const lng = eventData.lng || -101.17979;

        if (editMap) {
          editMap.remove();
          editMap = null;
        }

        editMap = L.map('editMap').setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; OpenStreetMap contributors',
        }).addTo(editMap);

        const editMarker = L.marker([lat, lng], { draggable: true }).addTo(editMap);
        editMap.setView([lat, lng], 13);

        setTimeout(() => {
          editMap.invalidateSize();
        }, 200);

        editMarker.on('moveend', function (e) {
          const { lat, lng } = e.target.getLatLng();
          editMap.setView([lat, lng], 13);

          const latInput = document.getElementById('editLat');
          const lngInput = document.getElementById('editLng');

          if (latInput && lngInput) {
            latInput.value = lat;
            lngInput.value = lng;
            updateLocation(lat, lng);
          } else {
            console.error('Los elementos editLat o editLng no existen en el DOM.');
          }
        });

        modal.classList.remove('hidden');
      } else {
        Swal.fire('Error', data.error, 'error');
      }
    })
    .catch((error) => console.error('Error:', error));
}

async function updateLocation(lat, lng) {
  try {
    const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`;
    const response = await fetch(url);
    const data = await response.json();
    const address = data.display_name || 'Ubicación desconocida';
    const locationInput = document.getElementById('editLocation');
    if (locationInput) {
      locationInput.value = address;
    }
  } catch (error) {
    console.error('Error al obtener la dirección:', error);
  }
}

document.getElementById('updateEvent').addEventListener('click', () => {
  const formData = new FormData(document.getElementById('editForm'));
  fetch('../php/updateEvent.php', {
    method: 'POST',
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        Swal.fire('Éxito', 'Evento actualizado correctamente.', 'success');
        document.getElementById('editModal').classList.add('hidden');
        cargarTabla(mes, year);
      } else {
        Swal.fire('Error', data.error, 'error');
      }
    })
    .catch((error) => console.error('Error:', error));
});

document.getElementById('cancelEdit').addEventListener('click', () => {
  document.getElementById('editModal').classList.add('hidden');
});
document.getElementById('closeEdit').addEventListener('click', () => {
  document.getElementById('editModal').classList.add('hidden');
});




