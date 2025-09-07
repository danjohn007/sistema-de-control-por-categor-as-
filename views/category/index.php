<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-tags"></i> Gestión de Categorías
    </h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
        <i class="bi bi-plus"></i> Nueva Categoría
    </button>
</div>

<!-- Lista de categorías -->
<?php foreach ($categories as $category): ?>
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <span class="badge me-2" style="background-color: <?php echo $category['color']; ?>; width: 20px; height: 20px;"></span>
            <h5 class="mb-0"><?php echo htmlspecialchars($category['name']); ?></h5>
            <span class="badge bg-secondary ms-2"><?php echo $category['subcategories_count']; ?> subcategorías</span>
        </div>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-primary" onclick="editCategory(<?php echo $category['id']; ?>)">
                <i class="bi bi-pencil"></i>
            </button>
            <a href="index.php?controller=category&action=delete&id=<?php echo $category['id']; ?>" 
               class="btn btn-outline-danger btn-delete">
                <i class="bi bi-trash"></i>
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($category['description'])): ?>
            <p class="text-muted mb-3"><?php echo htmlspecialchars($category['description']); ?></p>
        <?php endif; ?>
        
        <?php if (!empty($category['subcategories'])): ?>
            <h6>Subcategorías:</h6>
            <div class="row">
                <?php foreach ($category['subcategories'] as $subcategory): ?>
                    <div class="col-md-6 mb-2">
                        <span class="badge bg-<?php echo $subcategory['type'] === 'income' ? 'success' : 'danger'; ?>">
                            <?php echo htmlspecialchars($subcategory['name']); ?>
                            (<?php echo $subcategory['type'] === 'income' ? 'Ingreso' : 'Gasto'; ?>)
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">No hay subcategorías definidas</p>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

<?php if (empty($categories)): ?>
<div class="text-center py-5">
    <i class="bi bi-tags text-muted" style="font-size: 4rem;"></i>
    <h5 class="text-muted mt-3">No hay categorías registradas</h5>
    <p class="text-muted">Crea tu primera categoría para comenzar</p>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
        <i class="bi bi-plus"></i> Crear Primera Categoría
    </button>
</div>
<?php endif; ?>

<!-- Modal para categoría -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm" method="POST" action="index.php?controller=category&action=store">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">>
                    
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="categoryName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">Descripción</label>
                        <textarea class="form-control" name="description" id="categoryDescription" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categoryColor" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" name="color" id="categoryColor" value="#007bff">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="categoryForm" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function editCategory(id) {
    // Implementar edición de categoría
    alert('Función de edición en desarrollo');
}
</script>