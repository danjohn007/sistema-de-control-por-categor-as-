<div class="row">
    <!-- Statistics Cards -->
    <div class="col-12 mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Ingresos del Mes</h6>
                                <h4 class="mb-0">$<?php echo number_format($stats['total_income'], 2); ?></h4>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-arrow-up-circle-fill" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Gastos del Mes</h6>
                                <h4 class="mb-0">$<?php echo number_format($stats['total_expense'], 2); ?></h4>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-arrow-down-circle-fill" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-<?php echo $stats['balance'] >= 0 ? 'info' : 'warning'; ?> text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Balance</h6>
                                <h4 class="mb-0">$<?php echo number_format($stats['balance'], 2); ?></h4>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-calculator" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Movimientos</h6>
                                <h4 class="mb-0"><?php echo $stats['total_movements']; ?></h4>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-list-ul" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Charts -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-bar-chart"></i> Tendencia Mensual
                </h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart"></i> Por Categoría
                </h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Recent Movements -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history"></i> Movimientos Recientes
                </h5>
                <a href="index.php?controller=movement&action=index" class="btn btn-primary btn-sm">
                    Ver Todos <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentMovements)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th>Categoría</th>
                                    <th>Subcategoría</th>
                                    <th>Tipo</th>
                                    <th class="text-end">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentMovements as $movement): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($movement['movement_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($movement['description']); ?></td>
                                        <td><?php echo htmlspecialchars($movement['category_name']); ?></td>
                                        <td><?php echo htmlspecialchars($movement['subcategory_name']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $movement['type'] === 'income' ? 'success' : 'danger'; ?>">
                                                <?php echo $movement['type'] === 'income' ? 'Ingreso' : 'Gasto'; ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-<?php echo $movement['type'] === 'income' ? 'success' : 'danger'; ?>">
                                                <?php echo $movement['type'] === 'income' ? '+' : '-'; ?>$<?php echo number_format($movement['amount'], 2); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No hay movimientos registrados aún</p>
                        <a href="index.php?controller=movement&action=create" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Agregar Primer Movimiento
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Scripts for charts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly trend chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($chartData['monthly'], 'month')); ?>,
            datasets: [{
                label: 'Ingresos',
                data: <?php echo json_encode(array_column($chartData['monthly'], 'income')); ?>,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4
            }, {
                label: 'Gastos',
                data: <?php echo json_encode(array_column($chartData['monthly'], 'expense')); ?>,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Category pie chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($chartData['categories'], 'name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($chartData['categories'], 'total')); ?>,
                backgroundColor: <?php echo json_encode(array_column($chartData['categories'], 'color')); ?>,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
});
</script>