<?php
/**
 * Controlador del calendario de actividades
 */

class CalendarController extends BaseController {
    
    /**
     * Mostrar calendario
     */
    public function index() {
        $data = [
            'title' => 'Calendario de Actividades'
        ];
        
        $this->render('calendar/index', $data);
    }
    
    /**
     * Obtener eventos del calendario (AJAX)
     */
    public function getEvents() {
        $userId = $_SESSION['user_id'];
        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        
        $whereConditions = ['user_id = ?'];
        $params = [$userId];
        
        if (!empty($start)) {
            $whereConditions[] = 'start_date >= ?';
            $params[] = $start;
        }
        
        if (!empty($end)) {
            $whereConditions[] = 'end_date <= ?';
            $params[] = $end;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        $stmt = $this->db->prepare("
            SELECT 
                id,
                title,
                description,
                start_date as start,
                end_date as end,
                event_type,
                amount,
                completed,
                CASE 
                    WHEN event_type = 'payment' THEN '#28a745'
                    WHEN event_type = 'due_date' THEN '#dc3545'
                    WHEN event_type = 'reminder' THEN '#ffc107'
                    ELSE '#007bff'
                END as color
            FROM calendar_events 
            WHERE {$whereClause}
            ORDER BY start_date
        ");
        $stmt->execute($params);
        $events = $stmt->fetchAll();
        
        // Formatear eventos para FullCalendar
        $calendarEvents = [];
        foreach ($events as $event) {
            $calendarEvents[] = [
                'id' => $event['id'],
                'title' => $event['title'],
                'start' => $event['start'],
                'end' => $event['end'],
                'color' => $event['color'],
                'extendedProps' => [
                    'description' => $event['description'],
                    'event_type' => $event['event_type'],
                    'amount' => $event['amount'],
                    'completed' => $event['completed']
                ]
            ];
        }
        
        $this->jsonResponse($calendarEvents);
    }
    
    /**
     * Crear nuevo evento
     */
    public function createEvent() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $userId = $_SESSION['user_id'];
        $title = $this->sanitize($_POST['title'] ?? '');
        $description = $this->sanitize($_POST['description'] ?? '');
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? null;
        $eventType = $_POST['event_type'] ?? 'other';
        $amount = floatval($_POST['amount'] ?? 0);
        $categoryId = intval($_POST['category_id'] ?? 0) ?: null;
        
        // Validaciones
        if (empty($title)) {
            $this->jsonResponse(['success' => false, 'message' => 'El título es requerido']);
        }
        
        if (empty($startDate)) {
            $this->jsonResponse(['success' => false, 'message' => 'La fecha de inicio es requerida']);
        }
        
        // Si no se especifica fecha de fin, usar la fecha de inicio
        if (empty($endDate)) {
            $endDate = $startDate;
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO calendar_events (user_id, title, description, start_date, end_date, event_type, amount, category_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$userId, $title, $description, $startDate, $endDate, $eventType, $amount, $categoryId])) {
            $this->jsonResponse(['success' => true, 'message' => 'Evento creado exitosamente']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Error al crear el evento']);
        }
    }
    
    /**
     * Actualizar evento
     */
    public function updateEvent() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $userId = $_SESSION['user_id'];
        $id = intval($_POST['id'] ?? 0);
        $title = $this->sanitize($_POST['title'] ?? '');
        $description = $this->sanitize($_POST['description'] ?? '');
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? null;
        $eventType = $_POST['event_type'] ?? 'other';
        $amount = floatval($_POST['amount'] ?? 0);
        $categoryId = intval($_POST['category_id'] ?? 0) ?: null;
        $completed = isset($_POST['completed']) ? 1 : 0;
        
        // Validaciones
        if ($id <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de evento inválido']);
        }
        
        if (empty($title)) {
            $this->jsonResponse(['success' => false, 'message' => 'El título es requerido']);
        }
        
        if (empty($startDate)) {
            $this->jsonResponse(['success' => false, 'message' => 'La fecha de inicio es requerida']);
        }
        
        // Si no se especifica fecha de fin, usar la fecha de inicio
        if (empty($endDate)) {
            $endDate = $startDate;
        }
        
        $stmt = $this->db->prepare("
            UPDATE calendar_events 
            SET title = ?, description = ?, start_date = ?, end_date = ?, event_type = ?, amount = ?, category_id = ?, completed = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND user_id = ?
        ");
        
        if ($stmt->execute([$title, $description, $startDate, $endDate, $eventType, $amount, $categoryId, $completed, $id, $userId])) {
            $this->jsonResponse(['success' => true, 'message' => 'Evento actualizado exitosamente']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar el evento']);
        }
    }
    
    /**
     * Eliminar evento
     */
    public function deleteEvent() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $userId = $_SESSION['user_id'];
        $id = intval($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de evento inválido']);
        }
        
        $stmt = $this->db->prepare("DELETE FROM calendar_events WHERE id = ? AND user_id = ?");
        
        if ($stmt->execute([$id, $userId])) {
            $this->jsonResponse(['success' => true, 'message' => 'Evento eliminado exitosamente']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar el evento']);
        }
    }
}
?>