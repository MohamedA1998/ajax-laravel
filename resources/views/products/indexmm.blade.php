<!DOCTYPE html>
<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>قائمة المنتجات</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProduct">Create product</button>
    </div>

    <div id="products" class="row g-3"></div>


    <div class="modal fade" id="createProduct" tabindex="-1" aria-labelledby="createProductLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="createProductLabel">Create Product</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createProductForm">
                        <div class="mb-3">
                            <label for="name" class="col-form-label">Name:</label>
                            <input type="text" class="form-control" id="name">
                        </div>
                        <div class="mb-3">
                            <label for="price" class="col-form-label">Price:</label>
                            <input type="text" class="form-control" id="price">
                        </div>
                        <div class="mb-3">
                            <label for="message-text" class="col-form-label">Description:</label>
                            <textarea class="form-control" id="description"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="createProduct()">Create Product</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    function fetchProducts() {
        $.get('/products', function(data) {
            $('#products').html('');
            data.forEach(product => {
                $('#products').append(`
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">${product.name}</h5>
                                <h6 class="card-subtitle mb-2 text-muted">السعر: ${product.price} جنيه</h6>
                                <p class="card-text">${product.description ?? ''}</p>
                                <button class="btn btn-warning btn-sm me-2" onclick="#createProduct">تعديل</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteProduct(${product.id})">حذف</button>
                            </div>
                        </div>
                    </div>
                `);
            });
        });
    }

    function createProduct() {
        let name = $('#createProductForm #name').val();
        let price = $('#createProductForm #price').val();
        let description = $('#createProductForm #description').val();
        $.post('/products', {name, price, description}, fetchProducts);
        $('#createProduct').modal('hide');
        $('#createProductForm')[0].reset();
    }

    function editProduct(id) {
        $.ajax({
            url: '/products/' + id,
            type: 'PUT',
            data: {name: 'منتج معدل', price: 200, description: 'وصف معدل'},
            success: fetchProducts
        });
    }

    function deleteProduct(id) {
        $.ajax({
            url: '/products/' + id,
            type: 'DELETE',
            success: fetchProducts
        });
    }

fetchProducts();
</script>
</body>
</html>