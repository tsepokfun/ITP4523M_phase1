document.addEventListener('DOMContentLoaded', () => {
    
    // ---  SEARCH FUNCTIONALITY (For both Product Gallery and Order Table) ---
    const tableSearch = document.getElementById('tableSearch');
    const productSearch = document.getElementById('productSearch');

    // Table Search (Order ID, Customer, Status, etc.)
    if (tableSearch) {
        tableSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            // Target rows specifically in the tbody to avoid hiding the header
            const rows = document.querySelectorAll('#tableBody tr');
            
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(searchTerm) ? "" : "none";
            });
        });
    }

    // Product Gallery Search
    if (productSearch) {
        productSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const links = document.querySelectorAll('.image-link');
            
            links.forEach(link => {
                const name = link.querySelector('.FurnitureName').textContent.toLowerCase();
                link.style.display = name.includes(searchTerm) ? "" : "none";
            });
        });
    }

    // ---  SORTING FUNCTIONALITY (Order Table) ---
    const applySortBtn = document.getElementById('applySort') || document.querySelector('.button-primary');
    const tableBody = document.querySelector('.order-table tbody');

    if (applySortBtn && tableBody) {
        const COLUMN_MAP = {
            'orderId': 0,
            'orderDate': 1,
            'totalAmount': 4,
            'status': 7
        };

        applySortBtn.addEventListener('click', () => {
            const sortBy = document.getElementById('sortFirst').value;
            const order = document.getElementById('orderFirst').value;
            const index = COLUMN_MAP[sortBy];
            const rows = Array.from(tableBody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                let valA = a.cells[index].innerText.trim();
                let valB = b.cells[index].innerText.trim();

                if (sortBy === 'totalAmount') {
                    valA = parseFloat(valA.replace(/[$,]/g, ''));
                    valB = parseFloat(valB.replace(/[$,]/g, ''));
                } else if (sortBy === 'orderDate') {
                    valA = new Date(valA);
                    valB = new Date(valB);
                } else {
                    valA = valA.toLowerCase();
                    valB = valB.toLowerCase();
                }

                if (valA < valB) return order === 'asc' ? -1 : 1;
                if (valA > valB) return order === 'asc' ? 1 : -1;
                return 0;
            });

            rows.forEach(row => tableBody.appendChild(row));
        });
    }
});

// ---  DELETE FUNCTIONALITY (Global Function) ---
function handleDelete(button, orderId, deliveryDate, quantity, furnitureId) {
    const message = `Are you sure you want to delete this order?\n\n` +
                    `Order ID: ${orderId}\n` +
                    `Furniture ID: ${furnitureId}\n` +
                    `Quantity: ${quantity}\n` +
                    `Delivery Date: ${deliveryDate}`;

    if (confirm(message)) {
        const row = button.closest('tr');
        if (row) {
            row.style.transition = "opacity 0.3s ease";
            row.style.opacity = "0";
            setTimeout(() => row.remove(), 300);
        }
        console.log(`Order ${orderId} deleted.`);
    }
}