// debts.js

document.addEventListener('DOMContentLoaded', function() {
    const debtForm = document.getElementById('debtForm');
    const debtMessage = document.getElementById('debtMessage');
    const debtsTable = document.getElementById('debts-table').querySelector('tbody');

    function loadDebts() {
        fetch('debts.php?action=list')
            .then(res => res.json())
            .then(data => {
                debtsTable.innerHTML = '';
                data.forEach(debt => {
                    const row = debtsTable.insertRow();
                    row.insertCell(0).textContent = debt.customer_name;
                    row.insertCell(1).textContent = parseFloat(debt.amount).toFixed(2);
                    row.insertCell(2).textContent = debt.debt_date;
                    row.insertCell(3).textContent = debt.description;
                    const actions = row.insertCell(4);
                    const delBtn = document.createElement('button');
                    delBtn.textContent = 'Delete';
                    delBtn.onclick = function() {
                        if (confirm('Delete this debt?')) {
                            fetch('debts.php?action=delete', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: 'id=' + debt.id
                            })
                            .then(res => res.json())
                            .then(() => loadDebts());
                        }
                    };
                    actions.appendChild(delBtn);
                });
            });
    }

    debtForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(debtForm);
        fetch('debts.php?action=add', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                debtMessage.textContent = 'Debt added!';
                debtMessage.style.color = 'green';
                debtForm.reset();
                loadDebts();
            } else {
                debtMessage.textContent = 'Error adding debt.';
                debtMessage.style.color = 'red';
            }
        });
    });

    loadDebts();
});
