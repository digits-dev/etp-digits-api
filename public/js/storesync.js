$(document).ready(function() {
    // Show the spinner and hide the table while fetching
    $('#spinner').css('display', 'block');
    $('#storeSyncTable').css('display', 'none');
    // Function to fetch data from the route
    function fetchStoreSync() {
        fetch('deliveries/etp-store-sync')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                populateTable(data.sync);
            })
            .catch(error => {
                displayErrorMessage('Failed to fetch storeSync data. Please try again later.');
                console.error('Error fetching storeSync data:', error);
            })
            .finally(() => {
                // Hide the spinner after the fetch completes
                $('#spinner').css('display', 'none');
            });
    }

    // Function to populate the table with data
    function populateTable(sync) {
        const tableBody = document.getElementById('storeSyncTableBody');
        tableBody.innerHTML = ''; // Clear any existing content

        if (sync.length === 0) {
            displayErrorMessage('No store sync records found.');
            return;
        }


        sync.forEach(storeSync => {
            const dateTimeString = `${storeSync.Date} ${storeSync.Time}`;
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
                    <td>${storeSync.wh.store_name}</td>
                    <td>${date.toLocaleDateString('en-US')} ${date.toLocaleTimeString('en-US')}</td>
                </tr>
            `;
            tableBody.innerHTML += row;
        });

        // Show the table after data is loaded
        document.getElementById('storeSyncTable').style.display = 'table';
    }

    // Function to display an error message in the table body
    function displayErrorMessage(message) {
        const tableBody = document.getElementById('storeSyncTableBody');
        tableBody.innerHTML = `
            <tr>
                <td colspan="2" class="text-center text-danger">${message}</td>
            </tr>
        `;

        // Show the table (even with an error message)
        document.getElementById('storeSyncTable').style.display = 'table';
    }

    // Function to filter the table based on search input
    $('#searchInput').on('keyup', function() {
        const filter = this.value.toLowerCase();
        $('#storeSyncTable tbody tr').each(function() {
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

    // Call fetchStoreSync when modal is shown to load data dynamically
    $('#storeSyncModal').on('shown.bs.modal', function() {
        fetchStoreSync();
    });

});

function storeSync() {
    $('#storeSyncModal').modal('show');
}
