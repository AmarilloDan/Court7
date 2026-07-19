// Simple court management logic

document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('court-table');
    const addBtn = document.getElementById('add-court');
    addBtn.addEventListener('click', function() {
        const name = prompt('Enter Court Name:');
        const type = prompt('Enter Court Type:');
        if (name && type) {
            const row = table.insertRow(-1);
            row.insertCell(0).textContent = name;
            row.insertCell(1).textContent = type;
            const actions = row.insertCell(2);
            actions.innerHTML = '<button class="edit-btn">Edit</button> <button class="delete-btn">Delete</button>';
        }
    });
    table.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-btn')) {
            e.target.closest('tr').remove();
        } else if (e.target.classList.contains('edit-btn')) {
            const row = e.target.closest('tr');
            const name = prompt('Edit Court Name:', row.cells[0].textContent);
            const type = prompt('Edit Court Type:', row.cells[1].textContent);
            if (name && type) {
                row.cells[0].textContent = name;
                row.cells[1].textContent = type;
            }
        }
    });
});
