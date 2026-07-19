    const sortNamesBtn = document.getElementById('sort-names');
    if (sortNamesBtn) {
        sortNamesBtn.addEventListener('click', function() {
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            rows.sort((a, b) => {
                const nameA = a.cells[0].textContent.trim().toLowerCase();
                const nameB = b.cells[0].textContent.trim().toLowerCase();
                return nameA.localeCompare(nameB);
            });
            rows.forEach(row => tbody.appendChild(row));
        });
    }
// Simple reservation management logic

document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('reservation-table');
    const addBtn = document.getElementById('add-reservation');
    const printBtn = document.getElementById('print-reservations');
    const revenueDate = document.getElementById('revenue-date');
    const showRevenueBtn = document.getElementById('show-revenue');
    const revenueResult = document.getElementById('revenue-result');
    const categoryFilter = document.getElementById('category-filter');

    printBtn.addEventListener('click', function() {
        const printContents = table.outerHTML;
        const win = window.open('', '', 'height=700,width=900');
        win.document.write('<html><head><title>Print Bookings</title>');
        win.document.write('<style>table{width:100%;border-collapse:collapse;}th,td{border:1px solid #333;padding:8px;text-align:left;}th{background:#bdbdbd;}</style>');
        win.document.write('</head><body >');
        win.document.write('<h2>Bookings</h2>');
        win.document.write(printContents);
        win.document.write('</body></html>');
        win.document.close();
        win.print();
    });

    addBtn.addEventListener('click', function() {
        const name = prompt('Enter Name:');
        const date = prompt('Enter Date (YYYY-MM-DD):');
        const category = prompt('Enter Category (Tennis/Badminton/Basketball):', 'Tennis');
        const revenue = prompt('Enter Revenue (₱):', '0.00');
        if (name && date && category && revenue !== null) {
            const row = table.insertRow(-1);
            row.insertCell(0).textContent = name;
            row.insertCell(1).textContent = date;
            row.insertCell(2).textContent = category;
            row.insertCell(3).textContent = parseFloat(revenue).toFixed(2);
            const actions = row.insertCell(4);
            actions.innerHTML = '<button class="edit-btn">Edit</button> <button class="delete-btn">Delete</button>';
        }
    });

    table.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-btn')) {
            e.target.closest('tr').remove();
        } else if (e.target.classList.contains('edit-btn')) {
            const row = e.target.closest('tr');
            const name = prompt('Edit Name:', row.cells[0].textContent);
            const date = prompt('Edit Date:', row.cells[1].textContent);
            const category = prompt('Edit Category:', row.cells[2].textContent);
            const revenue = prompt('Edit Revenue (₱):', row.cells[3].textContent);
            if (name && date && category && revenue !== null) {
                row.cells[0].textContent = name;
                row.cells[1].textContent = date;
                row.cells[2].textContent = category;
                row.cells[3].textContent = parseFloat(revenue).toFixed(2);
            }
        }
    });

    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            const value = categoryFilter.value;
            const tbody = table.querySelector('tbody');
            Array.from(tbody.querySelectorAll('tr')).forEach(row => {
                if (!value || row.cells[2].textContent === value) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    if (showRevenueBtn) {
        showRevenueBtn.addEventListener('click', function() {
            const date = revenueDate.value;
            if (!date) {
                revenueResult.textContent = 'Please select a date.';
                revenueResult.style.color = 'red';
                return;
            }
            fetch(`reservations.php?action=revenue_by_date&date=${date}`)
                .then(res => res.json())
                .then(data => {
                    if (data.total_revenue !== undefined) {
                        revenueResult.textContent = `Total Revenue: ₱${parseFloat(data.total_revenue).toFixed(2)}`;
                        revenueResult.style.color = 'green';
                    } else {
                        revenueResult.textContent = 'No data.';
                        revenueResult.style.color = 'orange';
                    }
                })
                .catch(() => {
                    revenueResult.textContent = 'Error fetching revenue.';
                    revenueResult.style.color = 'red';
                });
        });
    }
});
