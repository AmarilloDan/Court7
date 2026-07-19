// supplies.js

document.addEventListener('DOMContentLoaded', function() {
    const supplyForm = document.getElementById('supplyForm');
    const supplyMessage = document.getElementById('supplyMessage');
    const suppliesTable = document.getElementById('supplies-table').querySelector('tbody');

    function loadSupplies() {
        fetch('supplies.php?action=list')
            .then(res => res.json())
            .then(data => {
                suppliesTable.innerHTML = '';
                data.forEach(supply => {
                    const row = suppliesTable.insertRow();
                    row.insertCell(0).textContent = supply.name;
                    row.insertCell(1).textContent = supply.quantity;
                    row.insertCell(2).textContent = supply.category;
                    row.insertCell(3).textContent = supply.date_added;
                    const actions = row.insertCell(4);
                    const delBtn = document.createElement('button');
                    delBtn.textContent = 'Delete';
                    delBtn.onclick = function() {
                        if (confirm('Delete this supply?')) {
                            fetch('supplies.php?action=delete', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: 'id=' + supply.id
                            })
                            .then(res => res.json())
                            .then(() => loadSupplies());
                        }
                    };
                    actions.appendChild(delBtn);
                });
            });
    }

    supplyForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(supplyForm);
        fetch('supplies.php?action=add', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                supplyMessage.textContent = 'Supply added!';
                supplyMessage.style.color = 'green';
                supplyForm.reset();
                loadSupplies();
            } else {
                supplyMessage.textContent = 'Error adding supply.';
                supplyMessage.style.color = 'red';
            }
        });
    });

    loadSupplies();
});
