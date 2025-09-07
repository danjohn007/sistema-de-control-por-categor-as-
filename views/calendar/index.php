<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-calendar-event"></i> Calendario de Actividades
    </h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#eventModal">
        <i class="bi bi-plus"></i> Nuevo Evento
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

<!-- Modal para evento -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalTitle">Nuevo Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="eventForm">
                    <input type="hidden" id="eventId" name="id">
                    
                    <div class="mb-3">
                        <label for="eventTitle" class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="eventTitle" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="eventDescription" class="form-label">Descripción</label>
                        <textarea class="form-control" id="eventDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="eventStartDate" class="form-label">Fecha de inicio <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="eventStartDate" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="eventEndDate" class="form-label">Fecha de fin</label>
                                <input type="date" class="form-control" id="eventEndDate" name="end_date">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="eventType" class="form-label">Tipo de evento</label>
                                <select class="form-select" id="eventType" name="event_type">
                                    <option value="other">Otro</option>
                                    <option value="payment">Pago</option>
                                    <option value="due_date">Vencimiento</option>
                                    <option value="reminder">Recordatorio</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="eventAmount" class="form-label">Monto (opcional)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="eventAmount" name="amount" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="completedContainer" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="eventCompleted" name="completed">
                            <label class="form-check-label" for="eventCompleted">
                                Marcar como completado
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="deleteEventBtn" style="display: none;">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveEventBtn">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
    const eventForm = document.getElementById('eventForm');
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        height: 'auto',
        events: function(info, successCallback, failureCallback) {
            fetch(`index.php?controller=calendar&action=getEvents&start=${info.startStr}&end=${info.endStr}`)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
        },
        eventClick: function(info) {
            // Cargar datos del evento en el modal
            const event = info.event;
            const props = event.extendedProps;
            
            document.getElementById('eventId').value = event.id;
            document.getElementById('eventTitle').value = event.title;
            document.getElementById('eventDescription').value = props.description || '';
            document.getElementById('eventStartDate').value = event.startStr;
            document.getElementById('eventEndDate').value = event.endStr || event.startStr;
            document.getElementById('eventType').value = props.event_type || 'other';
            document.getElementById('eventAmount').value = props.amount || '';
            document.getElementById('eventCompleted').checked = props.completed || false;
            
            document.getElementById('eventModalTitle').textContent = 'Editar Evento';
            document.getElementById('completedContainer').style.display = 'block';
            document.getElementById('deleteEventBtn').style.display = 'inline-block';
            
            eventModal.show();
        },
        dateClick: function(info) {
            // Nuevo evento en la fecha seleccionada
            resetEventForm();
            document.getElementById('eventStartDate').value = info.dateStr;
            document.getElementById('eventEndDate').value = info.dateStr;
            eventModal.show();
        }
    });
    
    calendar.render();
    
    // Guardar evento
    document.getElementById('saveEventBtn').addEventListener('click', function() {
        const formData = new FormData(eventForm);
        const eventId = document.getElementById('eventId').value;
        const action = eventId ? 'updateEvent' : 'createEvent';
        
        fetch(`index.php?controller=calendar&action=${action}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                calendar.refetchEvents();
                eventModal.hide();
                showAlert('success', data.message);
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            showAlert('danger', 'Error al guardar el evento');
        });
    });
    
    // Eliminar evento
    document.getElementById('deleteEventBtn').addEventListener('click', function() {
        if (confirm('¿Está seguro de que desea eliminar este evento?')) {
            const formData = new FormData();
            formData.append('id', document.getElementById('eventId').value);
            
            fetch('index.php?controller=calendar&action=deleteEvent', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    calendar.refetchEvents();
                    eventModal.hide();
                    showAlert('success', data.message);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                showAlert('danger', 'Error al eliminar el evento');
            });
        }
    });
    
    // Reset modal when closed
    document.getElementById('eventModal').addEventListener('hidden.bs.modal', function() {
        resetEventForm();
    });
    
    function resetEventForm() {
        eventForm.reset();
        document.getElementById('eventId').value = '';
        document.getElementById('eventModalTitle').textContent = 'Nuevo Evento';
        document.getElementById('completedContainer').style.display = 'none';
        document.getElementById('deleteEventBtn').style.display = 'none';
    }
    
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.container').firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
});
</script>