document.addEventListener('DOMContentLoaded', function() {
    // Function to fetch data from the route
    function fetchDeliveries() {
        fetch('deliveries/etp-delivered-dr')  // Replace with your actual route
            .then(response => response.json())
            .then(data => {
                populateTable(data.deliveries);
            })
            .catch(error => console.error('Error fetching delivery data:', error));
    }

    // Function to populate the table with data
    function populateTable(deliveries) {
        const tableBody = document.getElementById('deliveryTableBody');
        tableBody.innerHTML = ''; // Clear any existing content

        deliveries.forEach(delivery => {
            const row = `
                <tr>
                    <td>${delivery.from_wh.store_name}</td>
                    <td>${delivery.to_wh.store_name}</td>
                    <td>${delivery.OrderNumber}</td>
                    <td>${delivery.status.order_status}</td>
                </tr>
            `;
            tableBody.innerHTML += row;
        });
    }

    // Function to filter the table based on search input
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#deliveryTable tbody tr');

        rows.forEach(row => {
            const columns = row.querySelectorAll('td');
            let match = false;

            // Check if any column in the row contains the search term
            columns.forEach(column => {
                if (column.textContent.toLowerCase().indexOf(filter) > -1) {
                    match = true;
                }
            });

            row.style.display = match ? '' : 'none';
        });
    });

    // Call fetchDeliveries when modal is shown to load data dynamically
    $('#deliveryModal').on('shown.bs.modal', function() {
        fetchDeliveries();
    });

});

function pullDeliveries() {
    $('#deliveryModal').modal('show');
}
