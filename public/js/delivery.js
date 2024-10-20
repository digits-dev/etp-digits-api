$(document).ready(function() {
    // Show the spinner and hide the table while fetching
    $('#spinner').css('display', 'block');
    $('#deliveryTable').css('display', 'none');
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
                $('#spinner').css('display', 'none');
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
            const dateTimeString = `${delivery.ReceivingDate} ${delivery.ReceivingTime}`;
            const year = dateTimeString.slice(0, 4);
            const month = dateTimeString.slice(4, 6);
            const day = dateTimeString.slice(6, 8);
            const hours = dateTimeString.slice(9, 11);
            const minutes = dateTimeString.slice(11, 13);
            const seconds = dateTimeString.slice(13, 15);

            // Create a JavaScript Date object
            const date = new Date(`${year}-${month}-${day}T${hours}:${minutes}:${seconds}`);
            const row = `
                <tr>
                    <td>${delivery.from_wh.store_name}</td>
                    <td>${delivery.to_wh.store_name}</td>
                    <td>${delivery.OrderNumber}</td>
                    <td>${date.toLocaleDateString('en-US')} ${date.toLocaleTimeString('en-US')}</td>
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
    $('#searchInput').on('keyup', function() {
        const filter = this.value.toLowerCase();
        $('#deliveryTable tbody tr').each(function() {
            const columns = $(this).find('td');
            let match = false;

            columns.each(function() {
                if ($(this).text().toLowerCase().indexOf(filter) > -1) {
                    match = true;
                }
            });

            $(this).css('display', match ? '' : 'none');
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
