document.addEventListener('DOMContentLoaded', function() {
    // Show the spinner and hide the table while fetching
    document.getElementById('spinner').style.display = 'block';
    document.getElementById('deliveryTable').style.display = 'none';
    // Function to fetch data from the route
    function fetchDeliveries() {
        fetch('deliveries/etp-delivered-dr')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                populateTable(data.deliveries);
            })
            .catch(error => {
                displayErrorMessage('Failed to fetch delivery data. Please try again later.');
                console.error('Error fetching delivery data:', error);
            })
            .finally(() => {
                // Hide the spinner after the fetch completes
                document.getElementById('spinner').style.display = 'none';
            });
    }

    // Function to populate the table with data
    function populateTable(deliveries) {
        const tableBody = document.getElementById('deliveryTableBody');
        tableBody.innerHTML = ''; // Clear any existing content

        if (deliveries.length === 0) {
            displayErrorMessage('No delivery records found.');
            return;
        }

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

        // Show the table after data is loaded
        document.getElementById('deliveryTable').style.display = 'table';
    }

    // Function to display an error message in the table body
    function displayErrorMessage(message) {
        const tableBody = document.getElementById('deliveryTableBody');
        tableBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-danger">${message}</td>
            </tr>
        `;

        // Show the table (even with an error message)
        document.getElementById('deliveryTable').style.display = 'table';
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
