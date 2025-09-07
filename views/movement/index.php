<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-arrow-left-right"></i> Movimientos
    </h2>
    <a href="index.php?controller=movement&action=create" class="btn btn-primary">
        <i class="bi bi-plus"></i> Nuevo Movimiento
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="index.php" id="filterForm">
            <input type="hidden" name="controller" value="movement">
            <input type="hidden" name="action" value="index">
            
            <div class="row g-3">
                <div class="col-md-2">
                    <label for="type" class="form-label">Tipo</label>
                    <select class="form-select" name="type" id="type">
                        <option value="">Todos</option>
                        <option value="income" <?php echo $filters['type'] === 'income' ? 'selected' : ''; ?>>Ingresos</option>
                        <option value="expense" <?php echo $filters['type'] === 'expense' ? 'selected' : ''; ?>>Gastos</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="category_id" class="form-label">Categoría</label>
                    <select class="form-select" name="category_id" id="category_id">
                        <option value="">Todas</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $filters['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Desde</label>
                    <input type="date" class="form-control" name="date_from" id="date_from" value="<?php echo $filters['date_from']; ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Hasta</label>
                    <input type="date" class="form-control" name="date_to" id="date_to" value="<?php echo $filters['date_to']; ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control" name="search" id="search" placeholder="Descripción..." value="<?php echo htmlspecialchars($filters['search']); ?>">
                </div>
                
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-search"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resumen -->
<?php if (!empty($totals)): ?>
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h6>Total Ingresos</h6>
                <h4>$<?php echo number_format($totals['total_income'], 2); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h6>Total Gastos</h6>
                <h4>$<?php echo number_format($totals['total_expense'], 2); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-<?php echo ($totals['total_income'] - $totals['total_expense']) >= 0 ? 'info' : 'warning'; ?> text-white">
            <div class="card-body text-center">
                <h6>Balance</h6>
                <h4>$<?php echo number_format($totals['total_income'] - $totals['total_expense'], 2); ?></h4>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Lista de movimientos -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Lista de Movimientos</h6>
    </div>
    <div class="card-body">
        <?php if (!empty($movements)): ?>
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
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movements as $movement): ?>
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
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?controller=movement&action=edit&id=<?php echo $movement['id']; ?>" 
                                           class="btn btn-outline-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="index.php?controller=movement&action=delete&id=<?php echo $movement['id']; ?>" 
                                           class="btn btn-outline-danger btn-delete" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <nav aria-label="Paginación">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="?controller=movement&action=index&page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($filters)); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">No se encontraron movimientos</h5>
                <p class="text-muted">Agrega tu primer movimiento para comenzar</p>
                <a href="index.php?controller=movement&action=create" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Agregar Movimiento
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>