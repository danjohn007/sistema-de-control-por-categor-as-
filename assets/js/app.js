/**
 * JavaScript principal para Sistema de Control de Gastos
 */

// Esperar a que el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Inicializar popovers de Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-dismiss alerts después de 5 segundos
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Formatear números en campos de monto
    var amountInputs = document.querySelectorAll('input[name="amount"]');
    amountInputs.forEach(function(input) {
        input.addEventListener('input', function(e) {
            // Remover caracteres no numéricos excepto punto decimal
            let value = e.target.value.replace(/[^0-9.]/g, '');
            
            // Asegurar solo un punto decimal
            let parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            
            // Limitar a 2 decimales
            if (parts[1] && parts[1].length > 2) {
                value = parts[0] + '.' + parts[1].substring(0, 2);
            }
            
            e.target.value = value;
        });
    });

    // Confirmación antes de eliminar
    var deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Está seguro de que desea eliminar este elemento?')) {
                e.preventDefault();
            }
        });
    });

    // Funcionalidad para cargar subcategorías dinámicamente
    var categorySelects = document.querySelectorAll('select[name="category_id"]');
    categorySelects.forEach(function(select) {
        select.addEventListener('change', function() {
            loadSubcategories(this.value);
        });
    });
});

/**
 * Cargar subcategorías basadas en la categoría seleccionada
 */
function loadSubcategories(categoryId) {
    var subcategorySelect = document.querySelector('select[name="subcategory_id"]');
    if (!subcategorySelect) return;
    
    // Limpiar opciones existentes
    subcategorySelect.innerHTML = '<option value="">Cargando...</option>';
    subcategorySelect.disabled = true;
    
    // Realizar petición AJAX
    fetch('index.php?controller=category&action=getSubcategories&category_id=' + categoryId)
        .then(response => response.json())
        .then(data => {
            subcategorySelect.innerHTML = '<option value="">Seleccionar subcategoría</option>';
            
            if (data.success && data.subcategories.length > 0) {
                data.subcategories.forEach(function(subcategory) {
                    var option = document.createElement('option');
                    option.value = subcategory.id;
                    option.textContent = subcategory.name + ' (' + (subcategory.type === 'income' ? 'Ingreso' : 'Gasto') + ')';
                    option.dataset.type = subcategory.type;
                    subcategorySelect.appendChild(option);
                });
            } else {
                subcategorySelect.innerHTML = '<option value="">No hay subcategorías disponibles</option>';
            }
            
            subcategorySelect.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            subcategorySelect.innerHTML = '<option value="">Error al cargar subcategorías</option>';
            subcategorySelect.disabled = false;
        });
}

/**
 * Actualizar tipo de movimiento basado en subcategoría
 */
function updateMovementType() {
    var subcategorySelect = document.querySelector('select[name="subcategory_id"]');
    var typeSelect = document.querySelector('select[name="type"]');
    
    if (!subcategorySelect || !typeSelect) return;
    
    var selectedOption = subcategorySelect.options[subcategorySelect.selectedIndex];
    if (selectedOption && selectedOption.dataset.type) {
        typeSelect.value = selectedOption.dataset.type;
    }
}

/**
 * Filtrar tabla de movimientos
 */
function filterMovements() {
    var filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.submit();
    }
}

/**
 * Limpiar filtros
 */
function clearFilters() {
    var filterForm = document.getElementById('filterForm');
    if (filterForm) {
        var inputs = filterForm.querySelectorAll('input, select');
        inputs.forEach(function(input) {
            if (input.type === 'text' || input.type === 'date') {
                input.value = '';
            } else if (input.tagName === 'SELECT') {
                input.selectedIndex = 0;
            }
        });
        filterForm.submit();
    }
}

/**
 * Formatear número como moneda
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(amount);
}

/**
 * Validar formulario antes del envío
 */
function validateForm(form) {
    var isValid = true;
    var requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(function(field) {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    // Validar monto si existe
    var amountField = form.querySelector('input[name="amount"]');
    if (amountField && amountField.value) {
        var amount = parseFloat(amountField.value);
        if (isNaN(amount) || amount <= 0) {
            amountField.classList.add('is-invalid');
            isValid = false;
        } else {
            amountField.classList.remove('is-invalid');
        }
    }
    
    return isValid;
}

/**
 * Mostrar loading en botón
 */
function showButtonLoading(button) {
    button.disabled = true;
    var originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
    button.dataset.originalText = originalText;
}

/**
 * Ocultar loading en botón
 */
function hideButtonLoading(button) {
    button.disabled = false;
    if (button.dataset.originalText) {
        button.innerHTML = button.dataset.originalText;
    }
}

/**
 * Exportar datos a CSV
 */
function exportToCSV(data, filename) {
    var csv = '';
    var headers = Object.keys(data[0]);
    csv += headers.join(',') + '\n';
    
    data.forEach(function(row) {
        var values = headers.map(function(header) {
            var escaped = ('' + row[header]).replace(/"/g, '\\"');
            return '"' + escaped + '"';
        });
        csv += values.join(',') + '\n';
    });
    
    var blob = new Blob([csv], { type: 'text/csv' });
    var url = window.URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

/**
 * Generar colores para gráficas
 */
function generateColors(count) {
    var colors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF',
        '#4BC0C0', '#FF6384', '#36A2EB', '#FFCE56'
    ];
    
    var result = [];
    for (var i = 0; i < count; i++) {
        result.push(colors[i % colors.length]);
    }
    
    return result;
}