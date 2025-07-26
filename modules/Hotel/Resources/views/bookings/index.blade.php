@extends('tenant.layouts.app')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="mb-0">Visualización de Reservas</h3>
    <div>
      <button type="button" class="btn btn-primary" id="openNewBookingModalBtn">
        <i class="fas fa-plus"></i> Nueva Reserva
      </button>
    </div>
  </div>
  <div class="card-body">
    <!-- Leyenda de colores -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="d-flex flex-wrap gap-3">
          <div class="d-flex align-items-center">
            <div style="width: 10px; height: 10px; background-color: #FF5252; margin-right: 6px; border-radius: 10%;"></div>
            <span>Entrada</span>
          </div>
          <div class="d-flex align-items-center">
            <div style="width: 10px; height: 10px; background-color: #2196F3; margin-right: 6px; margin-left: 6px; border-radius: 10%;"></div>
            <span>Reservado</span>
          </div>
          <div class="d-flex align-items-center">
            <div style="width: 10px; height: 10px; background-color: #9E9E9E; margin-right: 6px; margin-left: 6px; border-radius: 10%;"></div>
            <span>Finalizado</span>
          </div>
        </div>
      </div>
    </div>

    <div class="row align-items-center mb-4">
      <div class="col-12 col-md-8 mb-3 mb-md-0">
        <p class="mb-0">
          Vista de reservas por habitación. Puede arrastrar y redimensionar las reservas para modificarlas.
          <small class="text-muted">Mostrando datos desde {{ \Carbon\Carbon::now()->subDays(7)->format('d/m/Y') }} hasta {{ \Carbon\Carbon::now()->addDays(30)->format('d/m/Y') }}</small>
        </p>
      </div>
      <div class="col-12 col-md-4">
        <div class="d-flex flex-wrap gap-2 mb-2">
          <div class="btn-group w-100" role="group">
            <select class="btn btn-outline-secondary btn-sm flex-fill" id="roomFilter">
              <option value="">Todas las habitaciones</option>
              @foreach($rooms->sortBy('id') as $room)
                <option value="{{ $room->id }}">{{ $room->name }} - {{ $room->category->description }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="d-flex flex-wrap gap-1 btn-group" role="group" aria-label="Vista de calendario">
          <button type="button" scale="day" class="btn btn-outline-secondary btn-sm flex-fill" onclick="changeView('day')">Día</button>
          <button type="button" scale="3days" class="btn btn-outline-secondary btn-sm flex-fill" onclick="changeView('3days')">3 Días</button>
          <button type="button" scale="week" class="btn btn-outline-secondary btn-sm flex-fill" onclick="changeView('week')">Semana</button>
          <button type="button" scale="2weeks" class="btn btn-outline-secondary btn-sm flex-fill" onclick="changeView('2weeks')">2 Semanas</button>
          <button type="button" scale="month" class="btn btn-outline-secondary btn-sm flex-fill" onclick="changeView('month')">Mes</button>
        </div>
      </div>
    </div>
    
    <!-- Visualización del timeline -->
    <div id="visualization" style="height: auto;"></div>
    
    <!-- Modal para nueva reserva (grande) -->
    <div id="newBookingModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Nueva Reserva</h5>
        <span class="closeModal" id="closeModalBtn" >&times;</span>
        </div>
        <div class="modal-body">
          <!-- Paso 1: Seleccionar habitación -->
          <div id="room-selection-container" class="p-3">
            <div class="form-group">
              <label for="room_id"><strong>Seleccione una habitación para continuar:</strong></label>
              <select class="form-control" id="room_id" name="room_id" required>
                <option value="">-- Seleccionar habitación --</option>
                @foreach($rooms as $room)
                  <option value="{{ $room->id }}">{{ $room->name }} - {{ $room->category->description }} ({{ $room->status }})</option>
                @endforeach
              </select>
            </div>
            <div class="text-center mt-3">
              <button type="button" class="btn btn-primary" id="continueWithRoomBtn">Continuar</button>
            </div>
          </div>
          
          <!-- Paso 2: Iframe con la vista de la habitación seleccionada -->
          <div id="room-detail-container" style="display: none; height: 100%;">
            <iframe id="roomDetailFrame" src="" style="width: 100%; height: 100%; border: none;"></iframe>
          </div>
        </div>
            <!-- Modal de Edición de Reserva con iframe -->
    <div id="editBookingModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
      <div class="modal-content" style="background-color: #fefefe; margin: 2% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 1200px; height: 90%; border-radius: 5px; display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
          <h2 style="margin: 0;">Editar Reserva</h2>
          <span class="close" style="color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
        </div>
        <div style="flex: 1; position: relative;">
          <iframe id="editBookingFrame" style="width: 100%; height: 100%; border: none; border-radius: 4px;"></iframe>
        </div>
      </div>
    </div>
    

  </div>
</div>
@endsection

@push('styles')
<link href="https://unpkg.com/vis-timeline@latest/styles/vis-timeline-graph2d.min.css" rel="stylesheet" type="text/css" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style type="text/css">
  /* Estilos para el select de habitaciones */
  #roomFilter {
    border-color: #e0e0e0;
    transition: all 0.3s ease;
    background-color: #fff;
  }
  
  #roomFilter:focus {
    border-color: #2196F3;
    box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
    background-color: #fff;
  }
  
  /* Estilos para el contenedor principal */
  #visualization {
    width: 100%;
    height: 600px;
    margin: 20px 0;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  }
  
  /* Estilos para la columna de grupos */
  .vis-panel.vis-left {
    width: 70px !important;
    border-right: 1px solid #e0e0e0;
  }
  
  /* Estilos para los grupos de categorías */
  .vis-group.category-group {
    background-color: #f8f9fa !important;
    font-weight: bold;
    border-bottom: 1px solid #e0e0e0 !important;
    padding: 10px 15px !important;
    color: #2c3e50;
    font-size: 1.1em;
  }
  
  /* Estilos para los grupos de habitaciones */
  .vis-group.room-group {
    background-color: #fff !important;
    padding: 8px 15px 8px 30px !important;
    border-bottom: 1px solid #f0f0f0 !important;
    color: #34495e;
    position: relative;
  }
  
  /* Estilo para el encabezado de categoría */
  .category-header {
    font-weight: 600;
    color: #2c3e50;
    padding: 5px 0;
  }
  
  /* Mejorar la legibilidad al pasar el ratón */
  .vis-group.room-group:hover {
    background-color: #f8f9fa !important;
  }
  .category-group{
    background-color: #f8f9fa !important;
    
  }
  /* Ajustar el ancho del contenido */
  .vis-panel.vis-center, 
  .vis-panel.vis-right {
    left: 70px !important;
  }
  
  /* Estilos modernos para los items */
  .vis-item {
    border-radius: 0 !important;
    padding: 8px 4px !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    font-size: 14px;
    font-weight: 500;
    min-width: 4px !important; /* Ancho mínimo para elementos cortos */
    overflow: visible !important;
    white-space: nowrap;
  }
  
  .vis-item.green { 
    background-color:rgb(238, 41, 41); 
    border: none !important;  
    color:rgb(255, 255, 255);
    border-radius: 0 !important;
  }
  
  .vis-item.blue { 
    background-color:rgb(49, 140, 224); 
    border: none !important;  
    color:rgb(255, 255, 255);
    border-radius: 0 !important;
  }
  
  .vis-item.orange { 
    background-color:rgb(255, 206, 47); 
    border: none !important;  
    color:rgb(255, 255, 255);
    border-radius: 0 !important;
  }
  .vis-item.gray { 
    background-color:rgb(163, 163, 163); 
    border: none !important;  
    color:rgb(255, 255, 255);
    border-radius: 0 !important;
  }
  
  /* Estilos para el botón de eliminar */
  .btn-delete-booking {
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
    padding: 2px 5px;
    margin-left: 5px;
    font-size: 12px;
    border-radius: 3px;
    transition: all 0.2s ease;
  }
  
  .btn-delete-booking:hover {
    color: #fff;
    background-color: #dc3545;
  }
  
  /* Mejoras para la tabla/timeline */
  /* Fondo y bordes */
  .vis-timeline {
    border: none !important;
    background-color: #f8fafc !important;
  }
  
  /* Estilos para los grupos (nombres de habitaciones) */
  .vis-label {
    font-weight: 600 !important;
    color: #2c3e50 !important;
    padding: 7px !important;
    border-bottom: 1px solid #e2e8f0 !important;
  }
  
  .vis-labelset .vis-label {
    border-bottom: 1px solid #e2e8f0 !important;
  }
  
  /* Mejora de las líneas de división */
  .vis-grid.vis-vertical {
    border-left: 1px solid rgb(0, 0, 0) !important;
  }
  
  .vis-grid.vis-horizontal {
    border-top: 1px solid #e2e8f0 !important;
  }
  
  /* Mejora del eje de tiempo (días) */
  .vis-time-axis .vis-text {
    color: #475569 !important;
    font-weight: 500 !important;
    padding: 10px !important;
  }
  
  .vis-time-axis .vis-grid.vis-minor {
    border-width: 1px !important;
    border-color: #f1f5f9 !important;
  }
  
  .vis-time-axis .vis-grid.vis-major {
    border-width: 2px !important;
    border-color: #e2e8f0 !important;
  }
  

  
  /* Mejora del panel de navegación */
  .vis-panel.vis-bottom, .vis-panel.vis-center, .vis-panel.vis-left, .vis-panel.vis-right, .vis-panel.vis-top {
    border-color: #e2e8f0 !important;
  }
  
  /* Mejora de la interacción */
  .vis-item.vis-selected {
    border-color: #757575 !important;
    box-shadow: 0 0 0 2px rgba(117, 117, 117, 0.3) !important;
    background-color: rgba(189, 189, 189, 0.5) !important;
  }
  
  /* Mejora del estilo al pasar el cursor */
  .vis-item:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
    z-index: 10 !important;
  }
  
  /* Estilos para los grupos */
  .vis-group { 
    font-weight: 500; 
    font-size: 15px;
    padding: 10px 0;
  }
  
  .vis-labelset .vis-label {
    padding: 12px 10px;
    border-bottom: 1px solid #f0f0f0;
  }
  
  /* Estilos para el eje de tiempo */
  .vis-time-axis .vis-text {
    padding: 8px 0;
    color: #495057;
    font-size: 13px;
  }
  
  .vis-time-axis .vis-grid.vis-minor {
    border-color: #f0f0f0;
  }
  
  .vis-time-axis .vis-grid.vis-major {
    border-color: #e0e0e0;
  }
  
  /* Estilos para el log y hoveredItem */
  #hoveredItem,  #log {
    margin-top: 20px;
    padding: 15px;
    max-height: 200px;
    overflow-y: auto;
    background-color: #f8f9fa;
    border-radius: 8px;
    font-size: 14px;
  }
  
  #log div {
    margin-bottom: 8px;
    padding: 8px;
    background-color: white;
    border-radius: 6px;
    border-left: 3px solid #dee2e6;
  }
  
  /* Estilos para el modal */
  .modal {
    display: none; /* Oculto por defecto */
    position: fixed; /* Permanece en su lugar */
    z-index: 1050; /* Se sitúa por encima de otros elementos */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background-color: rgba(0, 0, 0, 0.6);
  }
  
  .modal-content {
    background-color: #fefefe;
    margin: 2% auto;
    padding: 0;
    border: 1px solid #888;
    width: 90%;
    max-width: 1200px;
    height: 90%;
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
    border-radius: 5px;
    display: flex;
    flex-direction: column;
  }
  
  .modal-header {
    padding: 15px 20px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
  }
  
  .modal-body {
    padding: 0;
    overflow: auto;
    height: calc(100% - 120px);
    flex-grow: 1;
  }
  
  .modal-footer {
    padding: 15px 20px;
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: flex-end;
    flex-shrink: 0;
  }
  
  .close , .closeModal{
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
  }
  
  .close:hover,
  .close:focus ,
  .closeModal:hover,
  .closeModal:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
  }
  
  #room-selection-container {
    padding: 30px;
    max-width: 600px;
    margin: 0 auto;
  }
  
  #roomDetailFrame {
    width: 100%;
    height: 100%;
    border: none;
    display: block;
  }
</style>

<!-- Modal de Edición de Reserva con iframe -->
<div id="editBookingModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 40px; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
  <div class="modal-content" style="background-color: #fefefe; margin: 2% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 1200px; height: 90%; border-radius: 5px; display: flex; flex-direction: column;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
      <h2 style="margin: 0;">Editar Reserva</h2>
      <span class="close" onclick="closeEditModal()" style="color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
    </div>
    <div style="flex: 1; position: relative;">
      <iframe id="editBookingFrame" style="width: 100%; height: 100%; border: none; border-radius: 4px;"></iframe>
    </div>
  </div>
</div>

@endpush

@push('scripts')
<!-- No cargar librerías adicionales para evitar conflictos -->
<script type="text/javascript" src="https://visjs.github.io/vis-timeline/standalone/umd/vis-timeline-graph2d.min.js"></script>
<script type="text/javascript">
    function updateBookingOnServer(item) {
      // Mostrar indicador de carga
      console.log(item);
      var loadingMessage = document.createElement('div');
      loadingMessage.className = 'alert alert-info';
      loadingMessage.innerHTML = 'Actualizando reserva #' + item.data[0].id + '...';
      
      console.log(loadingMessage);
      
      // Preparar los datos para enviar
      var startDate = item.data[0].start;
      var endDate = item.data[0].end;
      var roomId = item.data[0].group;
      console.log(startDate);
      console.log(endDate);
      console.log(roomId);
      console.log(JSON.stringify({
          start: startDate,
          end: endDate,
          room_id: roomId
        }))
      // Enviar la solicitud al servidor
      fetch('{{ route("hotel.bookings.update-reservation", "__id__") }}'.replace('__id__', item.data[0].id), {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          start: startDate,
          end: endDate,
          room_id: roomId
        })
      })
      .then(response => response.json())
      .then(data => {
        // Eliminar el mensaje de carga
        loadingMessage.remove();
        
        if (data.success) {
          // Mostrar mensaje de éxito
          var successMessage = document.createElement('div');
          successMessage.className = 'alert alert-success';
          successMessage.innerHTML = 'Reserva #' + item.id + ' actualizada correctamente';
          document.getElementById('log').prepend(successMessage);
          
          
          // Eliminar el mensaje de éxito después de 3 segundos
          setTimeout(function() {
            successMessage.remove();
          }, 3000);
        } else {
          // Mostrar mensaje de error
          var errorMessage = document.createElement('div');
          errorMessage.className = 'alert alert-danger';
          errorMessage.innerHTML = 'Error al actualizar la reserva: ' + (data.message || 'Error desconocido');
          document.getElementById('log').prepend(errorMessage);
          
        }
      })
      .catch(error => {
        // Eliminar el mensaje de carga
        loadingMessage.remove();
        
        // Mostrar mensaje de error
        console.error('Error:', error);
        var errorMessage = document.createElement('div');
        errorMessage.className = 'alert alert-danger';
        errorMessage.innerHTML = 'Error al conectar con el servidor';
        document.getElementById('log').prepend(errorMessage);
        
        // Cancelar el cambio en el timeline
        callback(null);
      });
    }
  function closeCreateBookinModal() {
    document.getElementById('newBookingModal').style.display = 'none';
    resetModal();
  }

  function openEditModal(itemData) {
      // Obtener el ítem completo del DataSet
      console.log('modal', itemData);
      // Construir la URL para el iframe
      var editUrl = `{{ url('hotels/reception') }}/${itemData.id}/rent?view=modal&mode=edit`;

      // Establecer la fuente del iframe
      document.getElementById('editBookingFrame').src = editUrl;
      
      // Mostrar el modal
      document.getElementById('editBookingModal').style.display = 'block';
      
      // Hacer scroll al inicio del modal
      document.getElementById('editBookingModal').scrollTop = '30px';
    }
    // Función para cerrar el modal
    function closeEditModal() {
      document.getElementById('editBookingModal').style.display = 'none';
      // Limpiar el iframe al cerrar
      document.getElementById('editBookingFrame').src = 'about:blank';
    }
    function IframeUrlChangeDetection() {
        console.log('gaaaa');
        const iframe = document.getElementById('editBookingFrame');
        if (!iframe) return;
        
        // Guardar la URL inicial
        let lastUrl = '';
        
        // Función para verificar cambios en la URL
        function checkUrlEDIT() {
          console.log('gaaaa');
          if (!iframe.contentWindow) return;
          
          try {
            // Intentar obtener la URL actual (puede fallar por políticas de seguridad)
            const currentUrl = iframe.contentWindow.location.href;
            
            // Si la URL ha cambiado y no es la URL inicial de carga
            if (lastUrl && currentUrl !== lastUrl && lastUrl.includes('/rent')) {
              console.log('Detectado cambio de URL en iframe:', currentUrl);
              
              // Si la URL nueva no contiene 'rent', significa que hubo una redirección
              if (!currentUrl.includes('/rent')) {
                console.log('Redirección detectada, cerrando modal...');
                
                // Cerrar el modal
                document.getElementById('editBookingModal').style.display = 'none';
                
                // Recargar la página principal para mostrar los cambios
                setTimeout(function() {
                  window.location.reload();
                }, 500);
                
                return; // Detener la verificación
              }
            }
            
            // Actualizar la última URL conocida
            if (currentUrl && currentUrl !== 'about:blank') {
              lastUrl = currentUrl;
            }
          } catch (e) {
            // Error al acceder a la URL (probablemente por seguridad cross-origin)
            console.log('No se puede acceder a la URL del iframe:', e);
          }
        }
        
        // Verificar cada segundo
        const urlCheckInterval = setInterval(checkUrlEDIT, 1000);
        
        // Limpiar el intervalo cuando se cierra el modal
        function clearUrlCheck() {
          clearInterval(urlCheckInterval);
          console.log('Intervalo limpiado');
        }
        
        // Añadir eventos para limpiar el intervalo
        span.addEventListener('click', clearUrlCheck);
        
        // También limpiar cuando se hace clic fuera del modal
        window.addEventListener('click', function(event) {
          if (event.target == modal) {
            clearUrlCheck();
          }
        });
      }
      
  document.addEventListener('DOMContentLoaded', function() {
       // Modal de edición de reserva
    
    // Función para abrir el modal de edición
    
    // Inicializar el modal y las fechas con JavaScript puro
    console.log('DOM listo, inicializando eventos...');
      
      // Obtener el modal
      var modal = document.getElementById('newBookingModal');
      
      // Obtener el botón que abre el modal
      var btn = document.getElementById('openNewBookingModalBtn');
      
      // Obtener el elemento <span> que cierra el modal
      var span = document.getElementById('closeModalBtn');

      
      // Obtener el botón cancelar
      
      // Cuando el usuario hace clic en el botón, abrir el modal
      btn.onclick = function() {
        console.log('Abriendo modal...');
        modal.style.display = 'block';
      }
      
      // Event listener para el botón de continuar con la habitación seleccionada
      document.getElementById('continueWithRoomBtn').addEventListener('click', function() {
        const roomId = document.getElementById('room_id').value;
        if (!roomId) {
          alert('Por favor, seleccione una habitación para continuar.');
          return;
        }
        
        // Mostrar el contenedor del iframe y ocultar la selección de habitación
        document.getElementById('room-selection-container').style.display = 'none';
        document.getElementById('room-detail-container').style.display = 'block';
        
        // Cargar la vista de la habitación en el iframe
        const iframe = document.getElementById('roomDetailFrame');
        iframe.src = `{{ url('hotels/reception') }}/${roomId}/rent?view=modal`;
        
        // Cambiar el título del modal
        document.querySelector('.modal-title').textContent = 'Reservar Habitación';
      });
      
      // Función para restablecer el modal a su estado inicial
      
      
      // Escuchar mensajes del iframe
      window.addEventListener('message', function(event) {
        // Verificar el origen del mensaje (opcional para mayor seguridad)
        // if (event.origin !== window.location.origin) return;
        
        const data = event.data;
        
        // Si el iframe indica que se ha completado una reserva
        if (data && data.action === 'closeModal') {
          console.log('Mensaje recibido del iframe:', data);
          
          // Cerrar el modal
          modal.style.display = 'none';
          resetModal();
          
          // Si la reserva fue exitosa, recargar la página para mostrar la nueva reserva
          if (data.success) {
            setTimeout(function() {
              window.location.reload();
            }, 500);
          }
        }
      });
      function resetModal() {
        // Ocultar el iframe y mostrar la selección de habitación
        document.getElementById('room-detail-container').style.display = 'none';
        document.getElementById('room-selection-container').style.display = 'block';
        
        // Limpiar el iframe
        document.getElementById('roomDetailFrame').src = '';
        
        // Restablecer el título del modal
        document.querySelector('.modal-title').textContent = 'Nueva Reserva';
        

      }

      // Detectar cambios de URL en el iframe
      function setupIframeUrlChangeDetection() {
        const iframe = document.getElementById('roomDetailFrame');
        if (!iframe) return;
        
        // Guardar la URL inicial
        let lastUrl = '';
        
        // Función para verificar cambios en la URL
        function checkUrl() {
          if (!iframe.contentWindow) return;
          
          try {
            // Intentar obtener la URL actual (puede fallar por políticas de seguridad)
            const currentUrl = iframe.contentWindow.location.href;
            
            // Si la URL ha cambiado y no es la URL inicial de carga
            if (lastUrl && currentUrl !== lastUrl && lastUrl.includes('/rent')) {
              console.log('Detectado cambio de URL en iframe:', currentUrl);
              
              // Si la URL nueva no contiene 'rent', significa que hubo una redirección
              if (!currentUrl.includes('/rent')) {
                console.log('Redirección detectada, cerrando modal...');
                
                // Cerrar el modal
                modal.style.display = 'none';
                resetModal();
                
                // Recargar la página principal para mostrar los cambios
                setTimeout(function() {
                  window.location.reload();
                }, 500);
                
                return; // Detener la verificación
              }
            }
            
            // Actualizar la última URL conocida
            if (currentUrl && currentUrl !== 'about:blank') {
              lastUrl = currentUrl;
            }
          } catch (e) {
            // Error al acceder a la URL (probablemente por seguridad cross-origin)
            console.log('No se puede acceder a la URL del iframe:', e);
          }
        }
        
        // Verificar cada segundo
        const urlCheckInterval = setInterval(checkUrl, 1000);
        
        // Limpiar el intervalo cuando se cierra el modal
        function clearUrlCheck() {
          clearInterval(urlCheckInterval);
          console.log('Intervalo limpiado');
        }
        
        // Añadir eventos para limpiar el intervalo
        span.addEventListener('click', clearUrlCheck);
        
        // También limpiar cuando se hace clic fuera del modal
        window.addEventListener('click', function(event) {
          if (event.target == modal) {
            clearUrlCheck();
          }
        });
      }
      
      // Configurar la detección cuando se carga el iframe
      document.getElementById('continueWithRoomBtn').addEventListener('click', function() {
        // Esperar a que el iframe esté cargado
        setTimeout(setupIframeUrlChangeDetection, 1000);
      });


      
      // Configurar la detección cuando se carga el iframe

      
      // Verificar al cargar la página si hay una señal para cerrar el modal
      window.addEventListener('load', function() {
        if (localStorage.getItem('closeHotelBookingModal') === 'true') {
          console.log('Detectada señal para cerrar modal');
          localStorage.removeItem('closeHotelBookingModal');
          
          // Si estamos en la página principal después de una redirección
          if (window.location.href.indexOf('/hotels/bookings') > -1) {
            // Recargar la página para mostrar la nueva reserva
            window.location.reload();
          }
        }
      });
      
      // Cuando el usuario hace clic en <span> (x), cerrar el modal
      span.onclick = function() {
        console.log('Cerrando modal...');
        modal.style.display = 'none';
        resetModal();
      }
      
      // Cuando el usuario hace clic en cualquier lugar fuera del modal, cerrarlo
      window.onclick = function(event) {
        if (event.target == modal) {
          modal.style.display = 'none';
          resetModal();
        }
      }
      
      

      

  });
  
  // Función para manejar la eliminación de un ítem
  function handleItemDelete(properties) {
    if (!properties.items || properties.items.length === 0) return;
    
    const itemId = properties.items[0];
    const item = items.get(itemId);
    
    if (!item || !confirm('¿Está seguro de que desea eliminar esta reserva?')) {
      return;
    }
    
    // Mostrar indicador de carga
    var loadingMessage = document.createElement('div');
    loadingMessage.className = 'alert alert-info';
    loadingMessage.innerHTML = 'Eliminando reserva #' + itemId + '...';
    document.getElementById('log').prepend(loadingMessage);
    
    // Enviar la solicitud de eliminación al servidor
    fetch('{{ route("hotel.bookings.destroy", "") }}/' + itemId, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    .then(response => response.json())
    .then(data => {
      // Eliminar el mensaje de carga
      loadingMessage.remove();
      
      if (data.success) {
        // Mostrar mensaje de éxito
        var successMessage = document.createElement('div');
        successMessage.className = 'alert alert-success';
        successMessage.innerHTML = 'Reserva #' + itemId + ' eliminada correctamente';
        document.getElementById('log').prepend(successMessage);
        
        // El ítem ya fue eliminado por el evento de vis.js
        
        // Eliminar el mensaje de éxito después de 3 segundos
        setTimeout(function() {
          successMessage.remove();
        }, 3000);
      } else {
        // Mostrar mensaje de error
        var errorMessage = document.createElement('div');
        errorMessage.className = 'alert alert-danger';
        errorMessage.innerHTML = 'Error al eliminar la reserva: ' + (data.message || 'Error desconocido');
        document.getElementById('log').prepend(errorMessage);
        
        // Recargar el timeline para restaurar el ítem eliminado
        items.get();
      }
    })
    .catch(error => {
      // Eliminar el mensaje de carga
      loadingMessage.remove();
      
      // Mostrar mensaje de error
      console.error('Error:', error);
      var errorMessage = document.createElement('div');
      errorMessage.className = 'alert alert-danger';
      errorMessage.innerHTML = 'Error al conectar con el servidor';
      document.getElementById('log').prepend(errorMessage);
      
      // Recargar el timeline para restaurar el ítem eliminado
      items.get();
    });
  }
  
  document.addEventListener('DOMContentLoaded', function() {
    // Definir grupos (habitaciones) desde datos reales y ordenar por ID
    var groupsData = [];
    @foreach($rooms->sortBy('id') as $room)
      groupsData.push({
        id: {{ $room->id }},
        content: '{{ $room->name }} / {{ $room->category->description }}',
      });
    @endforeach
    
    var groups = new vis.DataSet(groupsData);
    
    // Definir items (reservas) desde datos reales usando formattedBookings
    var itemsData = [];
    @foreach($formattedBookings as $booking)
      @php
        $rent = $bookings->firstWhere('id', $booking['id']);
        $customer = $rent->customer ?? null;
        $room = $rent->room ?? null;
      @endphp
      itemsData.push({
        id: {{ $booking['id'] }},
        group: {{ $booking['room_id'] }},
        selectable: {{ $booking['is_booking'] == 1 ? 'true' : 'false' }},
        content: '{{ $booking['customer_name'] }}',
        @php
            $inputDateTime = \Carbon\Carbon::parse($rent->input_date ?? $booking['start_date'])->format('Y-m-d') . ' ' . ($rent->input_time ?? '14:00');
            $outputDateTime = \Carbon\Carbon::parse($rent->output_date ?? $booking['end_date'])->format('Y-m-d') . ' ' . ($rent->output_time ?? '12:00');
        @endphp
        title: '{{ $booking['customer_name'] }} -Reserva #{{ $booking['id'] }}: Check-in: {{ \Carbon\Carbon::parse($inputDateTime)->format('d/m H:i') }}, Check-out: {{ \Carbon\Carbon::parse($outputDateTime)->format('d/m H:i') }}',
        start: '{{ $inputDateTime }}',
        end: '{{ $outputDateTime }}',
        className: '{{ $booking['className'] }}',
        // Datos adicionales para el formulario de edición
        customer: {
          name: '{{ $customer->name ?? 'Sin cliente' }}',
          number: '{{ $customer->number ?? '' }}'
        },
        room: {
          id: {{ $room->id ?? 0 }},
          name: '{{ $room->name ?? 'Sin habitación' }}'
        },
        input_date: '{{ $rent->input_date ?? $booking['start_date'] }}',
        input_time: '{{ $rent->input_time ?? '14:00' }}',
        output_date: '{{ $rent->output_date ?? $booking['end_date'] }}',
        output_time: '{{ $rent->output_time ?? '12:00' }}',
        quantity_persons: {{ $rent->quantity_persons ?? 1 }},
        towels: {{ $rent->towels ?? 0 }},
        status: '{{ $rent->status ?? 'RESERVADO' }}',
        editable: true,
        is_booking: {{ isset($booking['is_booking']) ? $booking['is_booking'] : 0 }}
      });
    @endforeach
    var items = new vis.DataSet(itemsData);
    var container = document.getElementById('visualization');
    // Configuración de fecha máxima (1 año en el futuro)
    var maxDate = new Date();
    maxDate.setFullYear(maxDate.getFullYear() + 1); // 1 año en el futuro

    var options = {
      // Configuración para manejar elementos cortos
      min: 1000 * 60 * 60, // 1 hora en milisegundos
      minHeight: 30, // Altura mínima para los elementos
      // Forzar un ancho mínimo para los elementos
      minItemWidth: 4, // Ancho mínimo de 4px para elementos cortos
      // Ordenar por categoría y luego por ID de habitación
      groupOrder: function (a, b) {
        // Si ambos tienen orden definido, usarlo
        if (a.order !== undefined && b.order !== undefined) {
          return a.order.toString().localeCompare(b.order.toString());
        }
        // Si solo uno tiene orden definido, ponerlo primero
        if (a.order !== undefined) return -1;
        if (b.order !== undefined) return 1;
        // Orden por defecto
        return a.id - b.id;
      },
      // Mostrar las categorías como grupos principales
      groupTemplate: function(group) {
        if (group.id.toString().startsWith('cat-')) {
          return '<div class="category-header">' + group.content + '</div>';
        }
        return group.content;
      },
      // Configuración para mostrar elementos cortos
      margin: {
        item: {
          horizontal: 0, // Sin margen horizontal entre elementos
          vertical: 2    // Margen vertical mínimo
        }
      },
      // Configuración de zoom para mejor visualización de elementos cortos
      zoomMin: 1000 * 60 * 60 * 0.5, // 30 minutos como zoom mínimo
      zoomMax: 1000 * 60 * 60 * 24 * 31 * 3, // 3 meses como zoom máximo
      // Asegurar que los elementos cortos sean visibles
      stack: false, // Desactivar apilamiento para mejor visualización de elementos cortos
      // Ajustar cómo se muestran los elementos cortos
      showCurrentTime: true,
      showMajorLabels: true,
      showMinorLabels: true,
      editable: {
        add: false,           // Deshabilitar la adición de nuevos items
        updateTime: true,     // Permitir cambiar el tiempo (arrastrar)
        updateGroup: true,    // Permitir cambiar el grupo (habitación)
        remove: true,         // Habilitar la eliminación de items
        overrideItems: false  // No permitir sobrescribir items
      },
      onMove: function (item, callback) {
        // Mostrar confirmación antes de mover un ítem
        var title = '¿Desea mover la reserva a la siguiente fecha?\n' +
                   'Inicio: ' + item.start.toLocaleString() + '\n' +
                   'Fin: ' + (item.end ? item.end.toLocaleString() : item.start.toLocaleString());

        if (confirm(title)) {
          // Confirmar el movimiento
          callback(item);
          logEvent('itemUpdated', item);
        } else {
          // Cancelar el movimiento
          callback(null);
        }
      },
      onMoving: function (item, callback) {
        // Validar que las fechas no superen la fecha máxima
        if (item.start > maxDate) item.start = new Date(maxDate);
        if (item.end && item.end > maxDate) item.end = new Date(maxDate);
        
        // Actualizar el ítem en el DataSet para reflejar los cambios en la UI
        var itemData = items.get(item.id);
        if (itemData) {
          itemData.start = item.start;
          itemData.end = item.end || item.start;
          items.update(itemData);
        }
        
        // Continuar con el movimiento
        callback(item);
      },
      onUpdate: function (item, callback) {
        // Obtener los datos actuales del ítem
        var itemData = items.get(item.id);
        if (!itemData) return callback(null);
        
        // Actualizar las propiedades del ítem
        itemData.start = item.start;
        itemData.end = item.end || item.start;
        
        // Si hay un cambio de grupo (habitación)
        if (item.group !== undefined) {
          itemData.group = item.group;
          itemData.room_id = item.group;
        }
        
        // Actualizar el ítem en el DataSet
        items.update(itemData);
        
        // Loguear el cambio
        console.log('Reserva actualizada:', itemData);
        
        // Confirmar la actualización
        callback(itemData);
      },
      onRemove: function (item, callback) {
        // Mostrar confirmación antes de eliminar
        if (confirm('¿Está seguro de eliminar esta reserva?')) {
          // Confirmar eliminación
          callback(item);
        } else {
          // Cancelar eliminación
          callback(null);
        }
      },
      onInitialDrawComplete: function() { logEvent('Timeline inicial completado', {}); },
      orientation: {
        axis: 'top',     // Coloca el eje de tiempo en la parte superior
        item: 'bottom'  // Coloca los elementos en la parte inferior
      },
      timeAxis: { scale: 'day', step: 1 },  // Configura la escala de tiempo para mostrar días
      min: '{{ \Carbon\Carbon::now()->subYear(1)->format("Y-m-d") }}',  // Fecha máxima (un mes adelante)
      groupOrder: 'id',
      zoomMin: 7 * 24 * 3600 * 1000, // 1 hora en milisegundos
      zoomMax: 7 * 24 * 3600 * 1000, // 1 semana en milisegundos
      stack: true,            // Apila los items dentro de cada grupo
      groupEditable: true,    // Permite editar los grupos
      margin: { item: 5, axis: 5 },  // Margen entre items y ejes
      groupHeightMode: 'fixed',    // Modo de altura fija para los grupos
      zoomable: true,        // Deshabilita el zoom
      moveable: true,         // Permite mover la línea de tiempo
      tooltip: {
        followMouse: true,
        overflowMethod: 'cap'
      },
      snap: function(date, scale, step) {
        // Ajustar al día completo
        var snapDate = new Date(date);
        snapDate.setHours(0, 0, 0, 0);
        return snapDate;
      },
      horizontalScroll: true, // Permite scroll horizontal
      verticalScroll: true,   // Permite scroll vertical
      showMajorLabels: true,  // Muestra etiquetas principales
      showMinorLabels: true,  // Muestra etiquetas secundarias
      zoomKey: '',            // Deshabilita la tecla para zoom
      format: {
        minorLabels: {
          day: 'D MMM',
          hour: 'HH:mm'
        },
        majorLabels: {
          day: 'ddd D MMM',
          hour: 'ddd D MMM'
        }
      }
    };
    var timeline = new vis.Timeline(container, items, groups, options);
    
    // Función para filtrar habitaciones
    function filterRooms(roomId) {
      var filteredGroups = new vis.DataSet([]);
      var filteredItems = new vis.DataSet([]);
      
      // Si no hay filtro (todas las habitaciones)
      if (!roomId) {
        filteredGroups = groups;
        filteredItems = items;
      } else {
        // Filtrar grupos
        groups.forEach(function(group) {
          if (group.id == roomId) {
            filteredGroups.add(group);
          }
        });
        
        // Filtrar items
        items.forEach(function(item) {
          if (item.group == roomId) {
            filteredItems.add(item);
          }
        });
      }
      
      // Actualizar el timeline
      timeline.setGroups(filteredGroups);
      timeline.setItems(filteredItems);
    }
    
    // Evento para el select de filtro
    document.getElementById('roomFilter').addEventListener('change', function() {
      filterRooms(this.value);
    });
    
    // Evento para ajustar las fechas cuando se redimensiona un elemento
    timeline.on('changing', function (item, callback) {
      // Verificar si el elemento tiene is_booking en 0
      var itemData = items.get(item.id);
      if (itemData && itemData.is_booking === 0) {
        return callback(null); // Cancelar la operación
      }
      
      // Ajustar la fecha de inicio al inicio del día
      var startDate = new Date(item.start);
      startDate.setHours(0, 0, 0, 0);
      item.start = startDate;
      
      // Ajustar la fecha de fin al final del día (23:59:59)
      var endDate = new Date(item.end);
      endDate.setHours(23, 59, 59, 999);
      item.end = endDate;
      
      logEvent('changing', item);
      callback(item);
    });

    
    // Evento al hacer doble clic para abrir el modal de edición
    timeline.on('doubleClick', function (properties) {
        // Verificar si se hizo clic en un ítem
        if (!properties.item) {
            return; // No hacer nada si no hay ítem
        }
        
        // Obtener los datos de la reserva
        var item = timeline.itemsData.get(properties.item);
        console.log('item doubleClick', item);
        if (item && item.is_booking === 1) {
          openEditModal(item);
          setTimeout(IframeUrlChangeDetection, 1000);
        }
    });
    
    // Función para cambiar la vista del timeline
    window.changeView = function(scale) {
        var options = {};
        var now = new Date();
        var start, end;
        
        switch(scale) {
            case 'day':
                // Ajustar para que empiece a las 0:00 del día actual
                start = new Date(now);
                start.setHours(0, 0, 0, 0);
                end = new Date(start);
                end.setDate(start.getDate() + 1); // Mostrar 24 horas completas
                if(window.innerWidth <= 768) {
                  options = {
                    zoomable: true,
                    zoomMin: 24 *60 * 60 * 1000,
                    zoomMax: 24 *60 * 60 * 1000,
                    timeAxis: {
                        scale: 'hour',
                        step: 4
                      },
                    format: {
                        minorLabels: {
                            day: 'ddd D',
                            hour: 'HH:mm'
                        } 
                    } // Mostrar cada 6 horas
                    
                  }; // Mostrar 3 días completos en celular
                } else {
                    options = {
                        zoomable: true,
                        zoomMin: 24 *60 * 60 * 1000,
                        zoomMax: 24 *60 * 60 * 1000,
                    timeAxis: {
                        scale: 'hour',
                        step: 2
                      },
                    format: {
                        minorLabels: {
                            day: 'ddd D',
                            hour: 'HH:mm'
                        } 
                    } // Mostrar cada 6 horas
                
                };                  
                }
                break;
                
            case '3days':
                start = new Date(now);
                start.setHours(0, 0, 0, 0);
                end = new Date(start);
                end.setDate(start.getDate() + 3); // Mostrar 3 días
                options = {
                    zoomable: true,
                    zoomMin: 3 *24 *60 * 60 * 1000,
                    zoomMax: 3 *24 *60 * 60 * 1000,
                    timeAxis: {
                        scale: 'day',
                        step: 1
                      }, // Mostrar cada 6 horas
                    format: {
                        minorLabels: {
                            day: 'ddd D',
                            hour: 'HH'
                        }
                    }
                };
                break;
                
            case 'week':
                start = new Date(now);
                start.setHours(0, 0, 0, 0);
                start.setDate(start.getDate() - start.getDay()); // Inicio de la semana (domingo)
                end = new Date(start);
                end.setDate(start.getDate() + 7); // Mostrar 1 semana
                options = {
                    zoomable: true,
                    zoomMin: 7 *24 *60 * 60 * 1000,
                    zoomMax: 7 *24 *60 * 60 * 1000,
                    timeAxis: {
                        scale: 'day',
                        step: 1
                      }, // Mostrar cada 6 horas
                    format: {
                        minorLabels: {
                            day: 'ddd D',
                            hour: 'HH'
                        }
                    }
                };
                break;
                
            case '2weeks':
                start = new Date(now);
                start.setHours(0, 0, 0, 0);
                start.setDate(start.getDate() - 7); // Una semana atrás
                end = new Date(start);
                end.setDate(start.getDate() + 14); // Mostrar 2 semanas
                options = {
                    zoomable: true,
                    zoomMin: 14 *24 *60 * 60 * 1000,
                    zoomMax: 14 *24 *60 * 60 * 1000,
                    timeAxis: {
                        scale: 'day',
                        step: 1
                      }, // Mostrar cada 6 horas
                    format: {
                        minorLabels: {
                            day: 'D',
                            weekday: 'ddd'
                        }
                    }
                };
                break;
                
            case 'month':
                start = new Date(now.getFullYear(), now.getMonth(), 1); // Inicio del mes
                end = new Date(now.getFullYear(), now.getMonth() + 1, 0); // Fin del mes
                options = {
                    zoomable: true,
                    zoomMin: 30 *24 *60 * 60 * 1000,
                    zoomMax: 30 *24 *60 * 60 * 1000,
                    timeAxis: {
                        scale: 'day',
                        step: 1
                      }, // Mostrar cada 6 horas
                    format: {
                        minorLabels: {
                            day: 'D',
                            month: 'MMM'
                        }
                    }
                };
                break;
        }
        
        // Aplicar las opciones de vista
        timeline.setOptions(options);
        
        // Establecer el rango de fechas
        if (start && end) {
            timeline.setWindow(start, end, {animation: true});
        }
        
        // Actualizar los botones activos
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            const btnScale = btn.getAttribute('scale');
            if (btnScale === scale) {
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-primary');
            } else {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-secondary');
            }
        });
    };
    
    // Establecer la vista por defecto (semana)
    window.onload = function() {
      changeView('week');
    };
    


    // Evento de menú contextual
    timeline.on('contextmenu', function (properties) {
      //logEvent('contextmenu', properties);
    });

 
    
    // Cerrar el modal al hacer clic en la X
    document.querySelector('#editBookingModal .close').onclick = closeEditModal;
    
    // Cerrar el modal al hacer clic fuera del contenido
    window.onclick = function(event) {
      if (event.target == editModal) {
        closeEditModal();
      }
    };
    
    // Escuchar mensajes del iframe de edición
    window.addEventListener('message', function(event) {
      const data = event.data;
      
      // Si el iframe de edición indica que se ha completado la edición
      if (data && data.action === 'closeModal') {
        console.log('Mensaje recibido del iframe de edición:', data);
        
        // Cerrar el modal
        closeEditModal();
        
        // Si la edición fue exitosa, recargar la página para mostrar los cambios
        if (data.success) {
          setTimeout(function() {
            window.location.reload();
          }, 500);
        }
      }
    });

    // Eventos de ratón
    timeline.on('mouseDown', function (properties) {
      //logEvent('mouseDown', properties);
    });

    timeline.on('mouseUp', function (properties) {
      //logEvent('mouseUp', properties);
    });

    // other possible events:

    // timeline.on('mouseOver', function (properties) {
    //   logEvent('mouseOver', properties);
    // });

    // timeline.on("mouseMove", function(properties) {
    //   logEvent('mouseMove', properties);
    // });

    items.on('*', function (event, properties) {
      logEvent(event, properties);
    });

    function stringifyObject (object) {
      if (!object) return;
      var replacer = function(key, value) {
        if (value && value.tagName) {
          return "DOM Element";
        } else {
          return value;
        }
      }
      return JSON.stringify(object, replacer)
    }

    function logEvent(event, properties) {
      // Registrar en la consola
      console.log('Evento:', event, 'Propiedades:', properties);

      if (event === 'itemUpdated' ) {
        // Si es un evento de actualización, extraer el item de las propiedades si es necesario
        const item = properties.item || properties;
        updateBookingOnServer({ data: [item] });
      } else if (event === 'remove') {
        // Obtener el ID del ítem eliminado
        const itemId = properties.items[0];
        const item = properties.oldData[0];
        console.log(item);
        // Verificar si el ítem es una reserva (no un bloqueo de mantenimiento)
        if (item && item.is_booking === 1) {
          // Mostrar confirmación al usuario
          if (!confirm('¿Está seguro de que desea eliminar esta reserva?')) {
            // Si el usuario cancela, cancelar la eliminación
            items.add(item);
            return;
          }
          
          // Mostrar indicador de carga
          const loadingMessage = document.createElement('div');
          loadingMessage.className = 'alert alert-info';
          loadingMessage.innerHTML = 'Eliminando reserva #' + itemId + '...';
          console.log(loadingMessage);          
          // Enviar la solicitud de eliminación al servidor
          fetch('{{ route("hotel.bookings.destroy", "") }}/' + itemId, {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
          })
          .then(response => response.json())
          .then(data => {
            // Eliminar el mensaje de carga
            loadingMessage.remove();
            
            if (data.success) {
              // Mostrar mensaje de éxito
              const successMessage = document.createElement('div');
              successMessage.className = 'alert alert-success';
              successMessage.innerHTML = 'Reserva #' + itemId + ' eliminada correctamente';
              document.getElementById('log').prepend(successMessage);
              
              // Eliminar el mensaje de éxito después de 3 segundos
              setTimeout(() => successMessage.remove(), 3000);
            } else {
              // Mostrar mensaje de error
              const errorMessage = document.createElement('div');
              errorMessage.className = 'alert alert-danger';
              errorMessage.innerHTML = 'Error al eliminar la reserva: ' + (data.message || 'Error desconocido');
              document.getElementById('log').prepend(errorMessage);
              
              // Restaurar el ítem si hay un error
              items.add(item);
            }
          })
          .catch(error => {
            // Eliminar el mensaje de carga
            loadingMessage.remove();
            
            // Mostrar mensaje de error
            console.error('Error:', error);
            const errorMessage = document.createElement('div');
            errorMessage.className = 'alert alert-danger';
            errorMessage.innerHTML = 'Error al conectar con el servidor';
            document.getElementById('log').prepend(errorMessage);
            
            // Restaurar el ítem si hay un error de conexión
            items.add(item);
          });
        }
      }
    }


  });

  // Close modals on URL change
  
</script>
@endpush