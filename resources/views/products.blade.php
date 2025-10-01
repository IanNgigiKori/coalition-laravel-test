<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .form-container { max-width: 600px; margin: 0 auto 20px; }
        .table-container { max-width: 1000px; margin: 0 auto; }
        .edit-btn { cursor: pointer; }
    </style>
</head>
<body>
    <div class="container form-container">
        <h2 class="mb-4">Add Product</h2>
        <form id="productForm">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity in Stock</label>
                <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price per Item</label>
                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

    <div class="container table-container">
        <h2 class="mb-4">Product Data</h2>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity in Stock</th>
                    <th>Price per Item</th>
                    <th>Datetime Submitted</th>
                    <th>Total Value</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="productTableBody"></tbody>
            <tfoot>
                <tr>
                    <td colspan="4"><strong>Total Sum</strong></td>
                    <td id="totalSum"></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editId">
                        <div class="mb-3">
                            <label for="editName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="editName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editQuantity" class="form-label">Quantity in Stock</label>
                            <input type="number" class="form-control" id="editQuantity" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPrice" class="form-label">Price per Item</label>
                            <input type="number" class="form-control" id="editPrice" min="0" step="0.01" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const form = document.getElementById('productForm');
        const tableBody = document.getElementById('productTableBody');
        const totalSum = document.getElementById('totalSum');
        const editForm = document.getElementById('editForm');
        const editModal = new bootstrap.Modal(document.getElementById('editModal'));

        function loadProducts() {
            fetch('/products')
                .then(response => response.json())
                .then(data => {
                    tableBody.innerHTML = '';
                    data.products.forEach(product => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${product.name}</td>
                            <td>${product.quantity}</td>
                            <td>${product.price}</td>
                            <td>${product.datetime}</td>
                            <td>${product.total}</td>
                            <td><button class="btn btn-sm btn-secondary edit-btn" data-id="${product.id}">Edit</button></td>
                        `;
                        tableBody.appendChild(row);
                    });
                    totalSum.textContent = data.totalSum;
                })
                .catch(error => console.error('Error loading products:', error));
        }

        form.addEventListener('submit', e => {
    e.preventDefault();
    console.log('Form submitted:', {
        name: document.getElementById('name').value,
        quantity: document.getElementById('quantity').value,
        price: document.getElementById('price').value
    });
    const formData = new FormData(form);
    const csrfToken = document.querySelector('input[name="_token"]').value;
    fetch('/products', {
        method: 'POST',
        body: new URLSearchParams(formData),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': csrfToken // Explicitly send CSRF token
        }
    })
        .then(response => {
            console.log('Raw response status:', response.status);
            if (!response.ok) {
                if (response.status === 419) {
                    console.error('Session expired, refreshing page');
                    location.reload(); // Refresh to get a new token
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Server response:', data);
            if (data.success) {
                form.reset();
                loadProducts();
            } else {
                alert('Validation failed: ' + JSON.stringify(data.errors));
            }
        })
        .catch(error => console.error('Fetch error:', error));
});

        tableBody.addEventListener('click', e => {
            if (e.target.classList.contains('edit-btn')) {
                const id = e.target.dataset.id;
                const row = e.target.closest('tr');
                document.getElementById('editId').value = id;
                document.getElementById('editName').value = row.cells[0].textContent;
                document.getElementById('editQuantity').value = row.cells[1].textContent;
                document.getElementById('editPrice').value = row.cells[2].textContent;
                editModal.show();
            }
        });

        editForm.addEventListener('submit', e => {
    e.preventDefault();
    const id = document.getElementById('editId').value;
    const name = document.getElementById('editName').value.trim();
    const quantity = document.getElementById('editQuantity').value.trim();
    const price = document.getElementById('editPrice').value.trim();
    const data = {
        name: name,
        quantity: quantity,
        price: price,
        _token: document.querySelector('input[name="_token"]').value
    };
    const params = new URLSearchParams(data).toString();
    console.log('Raw request data:', params); // Log the exact string being sent
    fetch(`/products/${id}`, {
        method: 'PUT',
        body: params,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        }
    })
        .then(response => {
            console.log('Edit response status:', response.status);
            if (!response.ok) {
                if (response.status === 419) {
                    console.error('Session expired, refreshing page');
                    location.reload();
                }
                return response.json().then(errors => {
                    throw errors;
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Edit server response:', data);
            if (data.success) {
                editModal.hide();
                loadProducts();
            } else {
                alert('Validation failed: ' + JSON.stringify(data.errors));
            }
        })
        .catch(error => {
            console.error('Edit fetch error:', error);
            if (error.errors) {
                let errorMessage = 'Validation errors:\n';
                for (let field in error.errors) {
                    errorMessage += `${field}: ${error.errors[field][0]}\n`;
                }
                alert(errorMessage);
            } else {
                alert('An unexpected error occurred');
            }
        });
});

        loadProducts(); // Initial load
    </script>
</body>
</html>