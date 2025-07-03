$('.noresult').hide();

// Initialize DataTable
var table = $('#data-table').DataTable({
    ordering: true,
    searching: true,
    info: true,
    pageLength: parseInt($('#pageLengthSelector').val()), // Set initial page length from the selector
    lengthChange: false, // Disable default length change
    dom: "til" // Disable the default DataTable controls
   
});

// Handle page length change
$('#pageLengthSelector').on('change', function () {
    var newLength = parseInt($(this).val());
    table.page.len(newLength).draw(); // Update page length and redraw table
    updatePagination(); // Update pagination after page length change
});

// Hide the "No matching records found" message
$('#data-table').on('draw.dt', function () {
    if (table.rows({search: 'applied'}).count() === 0) {
        $('.noresult').show();
        $('.dt-empty').hide();
    } else {
        $('.noresult').hide();
    }
});

// Custom search box functionality
$('#customSearchBox').on('keyup', function () {
    table.search(this.value).draw();
});

// Function to update custom pagination
function updatePagination() {
    var info = table.page.info();
    var currentPage = info.page + 1;
    var totalPages = info.pages;
    var displayRange = 1;
    var startPage = Math.max(1, currentPage - displayRange);
    var endPage = Math.min(totalPages, currentPage + displayRange);

    var paginationHTML = '';
    paginationHTML += '<li class="page-item pagination-first ' + (currentPage === 1 ? 'disabled' : '') + '"><a href="#">First</a></li>';
    paginationHTML += '<li class="page-item pagination-prev ' + (currentPage === 1 ? 'disabled' : '') + '"><a href="#">Previous</a></li>';

    for (var i = startPage; i <= endPage; i++) {
        paginationHTML += '<li class="page-item ' + (i === currentPage ? 'active' : '') + '"><a class="page" href="#" data-page="' + (i - 1) + '">' + i + '</a></li>';
    }

    paginationHTML += '<li class="page-item pagination-next ' + (currentPage === totalPages ? 'disabled' : '') + '"><a href="#">Next</a></li>';
    paginationHTML += '<li class="page-item pagination-last ' + (currentPage === totalPages ? 'disabled' : '') + '"><a href="#">Last</a></li>';

    $('.pagination.listjs-pagination').html(paginationHTML);
}

// Handle custom pagination link clicks
$(document).on('click', '.pagination a.page', function (e) {
    e.preventDefault();
    var page = $(this).data('page');
    table.page(page).draw('page');
    updatePagination();
});

$(document).on('click', '.pagination-prev', function (e) {
    e.preventDefault();
    if (!$(this).hasClass('disabled')) {
        table.page('previous').draw('page');
        updatePagination();
    }
});

$(document).on('click', '.pagination-next', function (e) {
    e.preventDefault();
    if (!$(this).hasClass('disabled')) {
        table.page('next').draw('page');
        updatePagination();
    }
});

$(document).on('click', '.pagination-first', function (e) {
    e.preventDefault();
    if (!$(this).hasClass('disabled')) {
        table.page(0).draw('page');
        updatePagination();
    }
});

$(document).on('click', '.pagination-last', function (e) {
    e.preventDefault();
    if (!$(this).hasClass('disabled')) {
        table.page(table.page.info().pages - 1).draw('page');
        updatePagination();
    }
});

updatePagination(); // Initial call to update pagination on load
