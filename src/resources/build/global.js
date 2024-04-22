// Fetch our API endpoint and then inject it as a table
fetch('/wp-json/challenge/v1/1')
	.then(function (response) {
		if (response.ok) {
			return response.json();
		} else {
			return Promise.reject(response);
		}
	})
	.then(function (data) {
		const table = document.querySelector('#applicant-challenge-table');

		// Builder headers
		var holder = '<thead><tr>';
		data.data.headers.forEach((header) => {
			holder += '<td>' + header + '</td>';
		});

		holder += '</tr></thead>';

		// Build data
		holder += '<tbody>';
		Object.keys(data.data.rows).forEach(function (row) {
			holder += '<tr>';

			Object.keys(data.data.rows[row]).forEach(function (item) {
				holder += '<td>' + data.data.rows[row][item] + '</td>';
			});

			holder += '</tr>';
		});

		holder += '</tbody>';

		console.log(holder);

		// Inject it
		table.innerHTML = holder;
	})
	.catch(function (error) {
		console.warn(error);
	});
