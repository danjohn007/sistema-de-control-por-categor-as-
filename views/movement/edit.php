<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-pencil-square"></i> Editar Movimiento
                </h5>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['errors'])): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['errors']); ?>
                <?php endif; ?>

                <form method="POST" action="index.php?controller=movement&action=update" onsubmit="return validateForm(this)">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="id" value="<?php echo $movement['id']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" id="category_id" required>
                                    <option value="">Seleccionar categoría</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo $movement['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="subcategory_id" class="form-label">Subcategoría <span class="text-danger">*</span></label>
                                <select class="form-select" name="subcategory_id" id="subcategory_id" required>
                                    <option value="">Seleccionar subcategoría</option>
                                    <?php foreach ($subcategories as $subcategory): ?>
                                        <option value="<?php echo $subcategory['id']; ?>" 
                                                <?php echo $movement['subcategory_id'] == $subcategory['id'] ? 'selected' : ''; ?>
                                                data-type="<?php echo $subcategory['type']; ?>">
                                            <?php echo htmlspecialchars($subcategory['name']); ?> 
                                            (<?php echo $subcategory['type'] === 'income' ? 'Ingreso' : 'Gasto'; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Monto <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="amount" id="amount" 
                                           step="0.01" min="0.01" required value="<?php echo $movement['amount']; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="movement_date" class="form-label">Fecha <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="movement_date" id="movement_date" 
                                       required value="<?php echo $movement['movement_date']; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" id="description" rows="3" 
                                  required placeholder="Ingrese una descripción del movimiento..."><?php echo htmlspecialchars($movement['description']); ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php?controller=movement&action=index" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Actualizar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cargar subcategorías cuando cambie la categoría
    document.getElementById('category_id').addEventListener('change', function() {
        const categoryId = this.value;
        if (categoryId) {
            loadSubcategories(categoryId);
        } else {
            const subcategorySelect = document.getElementById('subcategory_id');
            subcategorySelect.innerHTML = '<option value="">Primero seleccione una categoría</option>';
            subcategorySelect.disabled = true;
        }
    });
});
</script>