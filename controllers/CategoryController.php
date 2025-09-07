<?php
/**
 * Controlador de categorías y subcategorías
 */

class CategoryController extends BaseController {
    
    /**
     * Listar categorías y subcategorías
     */
    public function index() {
        // Solo administradores pueden gestionar categorías
        $this->requireAdmin();
        
        // Obtener categorías con subcategorías
        $stmt = $this->db->query("
            SELECT c.*, 
                   COUNT(s.id) as subcategories_count
            FROM categories c
            LEFT JOIN subcategories s ON c.id = s.category_id AND s.active = 1
            WHERE c.active = 1
            GROUP BY c.id
            ORDER BY c.name
        ");
        $categories = $stmt->fetchAll();
        
        // Obtener subcategorías para cada categoría
        foreach ($categories as &$category) {
            $stmt = $this->db->prepare("
                SELECT * FROM subcategories 
                WHERE category_id = ? AND active = 1 
                ORDER BY name
            ");
            $stmt->execute([$category['id']]);
            $category['subcategories'] = $stmt->fetchAll();
        }
        
        $data = [
            'title' => 'Gestión de Categorías',
            'categories' => $categories
        ];
        
        $this->render('category/index', $data);
    }
    
    /**
     * Mostrar formulario para crear categoría
     */
    public function create() {
        $this->requireAdmin();
        
        $data = [
            'title' => 'Nueva Categoría',
            'csrf_token' => $this->generateCSRF()
        ];
        
        $this->render('category/create', $data);
    }
    
    /**
     * Guardar nueva categoría
     */
    public function store() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=category&action=index');
        }
        
        // Validar CSRF
        if (!$this->validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            $this->redirect('index.php?controller=category&action=create');
        }
        
        $name = $this->sanitize($_POST['name'] ?? '');
        $description = $this->sanitize($_POST['description'] ?? '');
        $color = $this->sanitize($_POST['color'] ?? '#007bff');
        
        // Validaciones
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'El nombre es requerido';
        }
        
        // Verificar si el nombre ya existe
        $stmt = $this->db->prepare("SELECT id FROM categories WHERE name = ? AND active = 1");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            $errors[] = 'Ya existe una categoría con ese nombre';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            $this->redirect('index.php?controller=category&action=create');
        }
        
        // Insertar categoría
        $stmt = $this->db->prepare("
            INSERT INTO categories (name, description, color) 
            VALUES (?, ?, ?)
        ");
        
        if ($stmt->execute([$name, $description, $color])) {
            $_SESSION['success'] = 'Categoría creada exitosamente';
        } else {
            $_SESSION['error'] = 'Error al crear la categoría';
        }
        
        $this->redirect('index.php?controller=category&action=index');
    }
    
    /**
     * Obtener subcategorías por AJAX
     */
    public function getSubcategories() {
        $categoryId = intval($_GET['category_id'] ?? 0);
        
        if ($categoryId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de categoría inválido']);
        }
        
        $stmt = $this->db->prepare("
            SELECT id, name, type 
            FROM subcategories 
            WHERE category_id = ? AND active = 1 
            ORDER BY name
        ");
        $stmt->execute([$categoryId]);
        $subcategories = $stmt->fetchAll();
        
        $this->jsonResponse([
            'success' => true,
            'subcategories' => $subcategories
        ]);
    }
    
    /**
     * Eliminar categoría
     */
    public function delete() {
        $this->requireAdmin();
        
        $id = intval($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['error'] = 'ID de categoría inválido';
            $this->redirect('index.php?controller=category&action=index');
        }
        
        // Verificar si tiene movimientos asociados
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM movements WHERE category_id = ?");
        $stmt->execute([$id]);
        $movementCount = $stmt->fetchColumn();
        
        if ($movementCount > 0) {
            $_SESSION['error'] = 'No se puede eliminar la categoría porque tiene movimientos asociados';
            $this->redirect('index.php?controller=category&action=index');
        }
        
        // Desactivar categoría y subcategorías
        $this->db->beginTransaction();
        
        try {
            // Desactivar subcategorías
            $stmt = $this->db->prepare("UPDATE subcategories SET active = 0 WHERE category_id = ?");
            $stmt->execute([$id]);
            
            // Desactivar categoría
            $stmt = $this->db->prepare("UPDATE categories SET active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            $this->db->commit();
            $_SESSION['success'] = 'Categoría eliminada exitosamente';
        } catch (Exception $e) {
            $this->db->rollback();
            $_SESSION['error'] = 'Error al eliminar la categoría';
        }
        
        $this->redirect('index.php?controller=category&action=index');
    }
}
?>