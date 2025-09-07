<?php
/**
 * Controlador del dashboard principal
 */

class DashboardController extends BaseController {
    
    /**
     * Mostrar dashboard principal
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        
        // Obtener estadísticas generales
        $stats = $this->getGeneralStats($userId);
        
        // Obtener movimientos recientes
        $recentMovements = $this->getRecentMovements($userId);
        
        // Obtener datos para gráficas
        $chartData = $this->getChartData($userId);
        
        $data = [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recentMovements' => $recentMovements,
            'chartData' => $chartData
        ];
        
        $this->render('dashboard/index', $data);
    }
    
    /**
     * Obtener estadísticas generales
     */
    private function getGeneralStats($userId) {
        // Total de ingresos del mes actual
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total_income
            FROM movements 
            WHERE user_id = ? AND type = 'income' 
            AND MONTH(movement_date) = MONTH(CURRENT_DATE()) 
            AND YEAR(movement_date) = YEAR(CURRENT_DATE())
        ");
        $stmt->execute([$userId]);
        $totalIncome = $stmt->fetchColumn();
        
        // Total de gastos del mes actual
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total_expense
            FROM movements 
            WHERE user_id = ? AND type = 'expense' 
            AND MONTH(movement_date) = MONTH(CURRENT_DATE()) 
            AND YEAR(movement_date) = YEAR(CURRENT_DATE())
        ");
        $stmt->execute([$userId]);
        $totalExpense = $stmt->fetchColumn();
        
        // Balance
        $balance = $totalIncome - $totalExpense;
        
        // Total de movimientos del mes
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_movements
            FROM movements 
            WHERE user_id = ? 
            AND MONTH(movement_date) = MONTH(CURRENT_DATE()) 
            AND YEAR(movement_date) = YEAR(CURRENT_DATE())
        ");
        $stmt->execute([$userId]);
        $totalMovements = $stmt->fetchColumn();
        
        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $balance,
            'total_movements' => $totalMovements
        ];
    }
    
    /**
     * Obtener movimientos recientes
     */
    private function getRecentMovements($userId) {
        $stmt = $this->db->prepare("
            SELECT m.*, c.name as category_name, s.name as subcategory_name
            FROM movements m
            JOIN categories c ON m.category_id = c.id
            JOIN subcategories s ON m.subcategory_id = s.id
            WHERE m.user_id = ?
            ORDER BY m.movement_date DESC, m.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener datos para gráficas
     */
    private function getChartData($userId) {
        // Datos por categoría
        $stmt = $this->db->prepare("
            SELECT c.name, c.color, SUM(m.amount) as total
            FROM movements m
            JOIN categories c ON m.category_id = c.id
            WHERE m.user_id = ? 
            AND MONTH(m.movement_date) = MONTH(CURRENT_DATE()) 
            AND YEAR(m.movement_date) = YEAR(CURRENT_DATE())
            GROUP BY c.id, c.name, c.color
            ORDER BY total DESC
        ");
        $stmt->execute([$userId]);
        $categoryData = $stmt->fetchAll();
        
        // Datos por mes (últimos 6 meses)
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(movement_date, '%Y-%m') as month,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
            FROM movements
            WHERE user_id = ? 
            AND movement_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(movement_date, '%Y-%m')
            ORDER BY month
        ");
        $stmt->execute([$userId]);
        $monthlyData = $stmt->fetchAll();
        
        return [
            'categories' => $categoryData,
            'monthly' => $monthlyData
        ];
    }
}
?>