<?php
/**
 * Controlador de movimientos (ingresos y gastos)
 */

class MovementController extends BaseController {
    
    /**
     * Listar movimientos
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $page = intval($_GET['page'] ?? 1);
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        // Filtros
        $filters = [
            'type' => $_GET['type'] ?? '',
            'category_id' => intval($_GET['category_id'] ?? 0),
            'subcategory_id' => intval($_GET['subcategory_id'] ?? 0),
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        // Construir consulta con filtros
        $whereConditions = ['m.user_id = ?'];
        $params = [$userId];
        
        if (!empty($filters['type'])) {
            $whereConditions[] = 'm.type = ?';
            $params[] = $filters['type'];
        }
        
        if ($filters['category_id'] > 0) {
            $whereConditions[] = 'm.category_id = ?';
            $params[] = $filters['category_id'];
        }
        
        if ($filters['subcategory_id'] > 0) {
            $whereConditions[] = 'm.subcategory_id = ?';
            $params[] = $filters['subcategory_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = 'm.movement_date >= ?';
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = 'm.movement_date <= ?';
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = 'm.description LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        // Obtener total de registros
        $countSql = "
            SELECT COUNT(*) 
            FROM movements m 
            WHERE {$whereClause}
        ";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $totalRecords = $stmt->fetchColumn();
        
        // Obtener movimientos paginados
        $sql = "
            SELECT m.*, c.name as category_name, s.name as subcategory_name
            FROM movements m
            JOIN categories c ON m.category_id = c.id
            JOIN subcategories s ON m.subcategory_id = s.id
            WHERE {$whereClause}
            ORDER BY m.movement_date DESC, m.created_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $movements = $stmt->fetchAll();
        
        // Obtener categorías para filtros
        $categories = $this->getActiveCategories();
        
        // Calcular totales
        $totalsSql = "
            SELECT 
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
            FROM movements m
            WHERE {$whereClause}
        ";
        $stmt = $this->db->prepare($totalsSql);
        $stmt->execute($params);
        $totals = $stmt->fetch();
        
        $data = [
            'title' => 'Movimientos',
            'movements' => $movements,
            'categories' => $categories,
            'filters' => $filters,
            'totals' => $totals,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalRecords / $limit),
                'total_records' => $totalRecords
            ]
        ];
        
        $this->render('movement/index', $data);
    }
    
    /**
     * Mostrar formulario para crear movimiento
     */
    public function create() {
        $categories = $this->getActiveCategories();
        
        $data = [
            'title' => 'Nuevo Movimiento',
            'categories' => $categories,
            'csrf_token' => $this->generateCSRF()
        ];
        
        $this->render('movement/create', $data);
    }
    
    /**
     * Guardar nuevo movimiento
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=movement&action=index');
        }
        
        // Validar CSRF
        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            $this->redirect('index.php?controller=movement&action=create');
        }
        
        $userId = $_SESSION['user_id'];
        $categoryId = intval($_POST['category_id'] ?? 0);
        $subcategoryId = intval($_POST['subcategory_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $description = $this->sanitize($_POST['description'] ?? '');
        $movementDate = $_POST['movement_date'] ?? date('Y-m-d');
        
        // Validaciones
        $errors = [];
        
        if ($categoryId <= 0) {
            $errors[] = 'Debe seleccionar una categoría';
        }
        
        if ($subcategoryId <= 0) {
            $errors[] = 'Debe seleccionar una subcategoría';
        }
        
        if ($amount <= 0) {
            $errors[] = 'El monto debe ser mayor a cero';
        }
        
        if (empty($description)) {
            $errors[] = 'La descripción es requerida';
        }
        
        // Obtener tipo de la subcategoría
        $stmt = $this->db->prepare("SELECT type FROM subcategories WHERE id = ?");
        $stmt->execute([$subcategoryId]);
        $subcategory = $stmt->fetch();
        
        if (!$subcategory) {
            $errors[] = 'Subcategoría no válida';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            $this->redirect('index.php?controller=movement&action=create');
        }
        
        // Insertar movimiento
        $stmt = $this->db->prepare("
            INSERT INTO movements (user_id, category_id, subcategory_id, type, amount, description, movement_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$userId, $categoryId, $subcategoryId, $subcategory['type'], $amount, $description, $movementDate])) {
            $_SESSION['success'] = 'Movimiento registrado exitosamente';
        } else {
            $_SESSION['error'] = 'Error al registrar el movimiento';
        }
        
        $this->redirect('index.php?controller=movement&action=index');
    }
    
    /**
     * Mostrar formulario para editar movimiento
     */
    public function edit() {
        $id = intval($_GET['id'] ?? 0);
        $userId = $_SESSION['user_id'];
        
        if ($id <= 0) {
            $_SESSION['error'] = 'ID de movimiento inválido';
            $this->redirect('index.php?controller=movement&action=index');
        }
        
        // Obtener movimiento
        $stmt = $this->db->prepare("
            SELECT * FROM movements 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$id, $userId]);
        $movement = $stmt->fetch();
        
        if (!$movement) {
            $_SESSION['error'] = 'Movimiento no encontrado';
            $this->redirect('index.php?controller=movement&action=index');
        }
        
        $categories = $this->getActiveCategories();
        
        // Obtener subcategorías de la categoría actual
        $stmt = $this->db->prepare("
            SELECT * FROM subcategories 
            WHERE category_id = ? AND active = 1 
            ORDER BY name
        ");
        $stmt->execute([$movement['category_id']]);
        $subcategories = $stmt->fetchAll();
        
        $data = [
            'title' => 'Editar Movimiento',
            'movement' => $movement,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'csrf_token' => $this->generateCSRF()
        ];
        
        $this->render('movement/edit', $data);
    }
    
    /**
     * Actualizar movimiento
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=movement&action=index');
        }
        
        $id = intval($_POST['id'] ?? 0);
        $userId = $_SESSION['user_id'];
        
        // Validar CSRF
        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            $this->redirect('index.php?controller=movement&action=edit&id=' . $id);
        }
        
        // Verificar que el movimiento pertenece al usuario
        $stmt = $this->db->prepare("SELECT id FROM movements WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = 'Movimiento no encontrado';
            $this->redirect('index.php?controller=movement&action=index');
        }
        
        $categoryId = intval($_POST['category_id'] ?? 0);
        $subcategoryId = intval($_POST['subcategory_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $description = $this->sanitize($_POST['description'] ?? '');
        $movementDate = $_POST['movement_date'] ?? date('Y-m-d');
        
        // Validaciones (similar a store)
        $errors = [];
        
        if ($categoryId <= 0) {
            $errors[] = 'Debe seleccionar una categoría';
        }
        
        if ($subcategoryId <= 0) {
            $errors[] = 'Debe seleccionar una subcategoría';
        }
        
        if ($amount <= 0) {
            $errors[] = 'El monto debe ser mayor a cero';
        }
        
        if (empty($description)) {
            $errors[] = 'La descripción es requerida';
        }
        
        // Obtener tipo de la subcategoría
        $stmt = $this->db->prepare("SELECT type FROM subcategories WHERE id = ?");
        $stmt->execute([$subcategoryId]);
        $subcategory = $stmt->fetch();
        
        if (!$subcategory) {
            $errors[] = 'Subcategoría no válida';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            $this->redirect('index.php?controller=movement&action=edit&id=' . $id);
        }
        
        // Actualizar movimiento
        $stmt = $this->db->prepare("
            UPDATE movements 
            SET category_id = ?, subcategory_id = ?, type = ?, amount = ?, description = ?, movement_date = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND user_id = ?
        ");
        
        if ($stmt->execute([$categoryId, $subcategoryId, $subcategory['type'], $amount, $description, $movementDate, $id, $userId])) {
            $_SESSION['success'] = 'Movimiento actualizado exitosamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar el movimiento';
        }
        
        $this->redirect('index.php?controller=movement&action=index');
    }
    
    /**
     * Eliminar movimiento
     */
    public function delete() {
        $id = intval($_GET['id'] ?? 0);
        $userId = $_SESSION['user_id'];
        
        if ($id <= 0) {
            $_SESSION['error'] = 'ID de movimiento inválido';
            $this->redirect('index.php?controller=movement&action=index');
        }
        
        // Eliminar movimiento
        $stmt = $this->db->prepare("DELETE FROM movements WHERE id = ? AND user_id = ?");
        
        if ($stmt->execute([$id, $userId])) {
            $_SESSION['success'] = 'Movimiento eliminado exitosamente';
        } else {
            $_SESSION['error'] = 'Error al eliminar el movimiento';
        }
        
        $this->redirect('index.php?controller=movement&action=index');
    }
    
    /**
     * Obtener categorías activas
     */
    private function getActiveCategories() {
        $stmt = $this->db->query("
            SELECT * FROM categories 
            WHERE active = 1 
            ORDER BY name
        ");
        return $stmt->fetchAll();
    }
}
?>