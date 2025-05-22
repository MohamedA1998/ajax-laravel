<!-- filepath: resources/views/products/index.blade.php -->
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
            <div>
                <button type="button" class="btn btn-danger me-2" id="deleteSelectedBtn" onclick="deleteProductHandler(selectedIds)" disabled>حذف المحدد</button>
                <button type="button" class="btn btn-primary" onclick="showProductModal()">إضافة منتج</button>
            </div>
            <h2>قائمة المنتجات</h2>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <input type="text" id="searchInput" class="form-control" placeholder="ابحث عن منتج...">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="parent_id" id="parent_id">
                    <option value="">كل الأقسام</option>
                    @foreach ($productsParent as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-9">
                <table class="table" id="products">
                    <thead>
                        <tr>
                            <th scope="col"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                            <th scope="col">#</th>
                            <th scope="col">name</th>
                            <th scope="col">price</th>
                            <th scope="col">description</th>
                            <th scope="col">action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- سيتم تعبئته عبر جافاسكريبت --}}
                    </tbody>
                </table>
                <nav>
                    <ul class="pagination" id="pagination"></ul>
                </nav>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="productModalLabel">إضافة منتج</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <div id="modalErrors"></div>
                        <form id="productForm">
                            <input type="hidden" id="product_id">
                            <div class="mb-3">
                                <label for="name" class="col-form-label">اسم المنتج:</label>
                                <input type="text" class="form-control" id="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="col-form-label">السعر:</label>
                                <input type="number" class="form-control" id="price" required>
                            </div>
                            <div class="mb-3">
                                <label for="modal_parent_id" class="col-form-label">القسم الرئيسي:</label>
                                <select class="form-select" id="modal_parent_id" required>
                                    <option value="">اختر القسم الرئيسي</option>
                                    @foreach ($productsParent as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="col-form-label">الوصف:</label>
                                <textarea class="form-control" id="description"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="button" class="btn btn-primary" id="saveBtn" onclick="saveProduct()">حفظ</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    let selectedIds = [];

    function fetchProducts(page = 1, search = '', parent_id = '') {
        $.get('/products', { page: page, search: search, parent_id: parent_id }, function(response) {
            let html = '';
            response.products.forEach((product, index) => {
                let checked = selectedIds.includes(product.id) ? 'checked' : '';
                html += `
                    <tr>
                        <td>
                            <input type="checkbox" class="row-checkbox" value="${product.id}" ${checked} onchange="toggleSelect(${product.id}, this)">
                        </td>
                        <th scope="row">${(response.pagination.per_page * (response.pagination.current_page - 1)) + index + 1}</th>
                        <td>${product.name}</td>
                        <td>${product.price}</td>
                        <td>${product.description ?? ''}</td>
                        <td>
                            <button class="btn btn-warning btn-sm me-2"
                                onclick="showProductModal(${product.id}, '${product.name.replace(/'/g,"\\'")}', '${product.price}', \`${product.description ? product.description.replace(/`/g,"\\`") : ''}\`, '${product.parent_id}')">
                                تعديل
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteProductHandler([${product.id}])">حذف</button>
                        </td>
                    </tr>
                `;
            });
            $('#products tbody').html(html);

            // بناء الباجيناشن مع Previous و Next
            let pagHtml = '';
            let prevPage = response.pagination.current_page - 1;
            let nextPage = response.pagination.current_page + 1;

            pagHtml += `<li class="page-item ${response.pagination.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="fetchProducts(${prevPage}, $('#searchInput').val(), $('#parent_id').val());return false;">Previous</a>
            </li>`;

            for(let i = 1; i <= response.pagination.last_page; i++) {
                pagHtml += `<li class="page-item ${i === response.pagination.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="fetchProducts(${i}, $('#searchInput').val(), $('#parent_id').val());return false;">${i}</a>
                </li>`;
            }

            pagHtml += `<li class="page-item ${response.pagination.current_page === response.pagination.last_page ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="fetchProducts(${nextPage}, $('#searchInput').val(), $('#parent_id').val());return false;">Next</a>
            </li>`;

            $('#pagination').html(pagHtml);

            updateDeleteBtn();
            updateSelectAll();
        });
    }

    $('#searchInput').on('input', function() {
        fetchProducts(1, $(this).val(), $('#parent_id').val());
    });

    $('#parent_id').on('change', function() {
        fetchProducts(1, $('#searchInput').val(), $(this).val());
    });

    function toggleSelect(id, checkbox) {
        if (checkbox.checked) {
            if (!selectedIds.includes(id)) selectedIds.push(id);
        } else {
            selectedIds = selectedIds.filter(item => item !== id);
        }
        updateDeleteBtn();
        updateSelectAll();
    }

    function toggleSelectAll(master) {
        $('.row-checkbox').each(function() {
            this.checked = master.checked;
            let id = parseInt(this.value);
            if (master.checked) {
                if (!selectedIds.includes(id)) selectedIds.push(id);
            } else {
                selectedIds = selectedIds.filter(item => item !== id);
            }
        });
        updateDeleteBtn();
    }

    function updateDeleteBtn() {
        $('#deleteSelectedBtn').prop('disabled', selectedIds.length === 0);
    }

    function updateSelectAll() {
        let allChecked = $('.row-checkbox').length > 0 && $('.row-checkbox:checked').length === $('.row-checkbox').length;
        $('#selectAll').prop('checked', allChecked);
    }

    function showProductModal(id = '', name = '', price = '', description = '', parent_id = '') {
        $('#modalErrors').html('');
        $('#product_id').val(id);
        $('#name').val(name);
        $('#price').val(price);
        $('#description').val(description);
        $('#modal_parent_id').val(parent_id);
        $('#productModalLabel').text(id ? 'تعديل منتج' : 'إضافة منتج');
        $('#saveBtn').text(id ? 'تعديل' : 'إضافة');
        var modal = new bootstrap.Modal(document.getElementById('productModal'));
        modal.show();
    }

    function saveProduct() {
        $('#modalErrors').html('');
        let id = $('#product_id').val();
        let name = $('#name').val();
        let price = $('#price').val();
        let description = $('#description').val();
        let parent_id = $('#modal_parent_id').val();

        let url = id ? '/products/' + id : '/products';
        let type = id ? 'PUT' : 'POST';
        let data = {name, price, description, parent_id};

        $.ajax({
            url: url,
            type: type,
            data: data,
            success: function() {
                fetchProducts($('.pagination .active a').text() || 1, $('#searchInput').val(), $('#parent_id').val());
                var modal = bootstrap.Modal.getInstance(document.getElementById('productModal'));
                modal.hide();
                $('#productForm')[0].reset();
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorHtml = '<div class="alert alert-danger"><ul>';
                    Object.values(errors).forEach(errArr => {
                        errArr.forEach(err => errorHtml += `<li>${err}</li>`);
                    });
                    errorHtml += '</ul></div>';
                    $('#modalErrors').html(errorHtml);
                }
            }
        });
    }

    function deleteProductHandler(ids) {
        if (!Array.isArray(ids)) ids = [ids];
        if(ids.length === 0) return;
        $.ajax({
            url: '/products/bulk-delete',
            type: 'POST',
            data: {ids: ids},
            success: function() {
                selectedIds = selectedIds.filter(id => !ids.includes(id));
                fetchProducts($('.pagination .active a').text() || 1, $('#searchInput').val(), $('#parent_id').val());
            }
        });
    }

    $(document).ready(function() {
        fetchProducts(1, '', $('#parent_id').val());
    });
</script>
</body>
</html>