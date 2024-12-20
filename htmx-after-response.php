<?php
/**
 * Handels htmx after response actions
 *
 * @package WordPress Maglev
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_action(
	'wp_footer',
	function () {
		?>
		<script>
			// Listen to the htmx:afterRequest event
			document.body.addEventListener('htmx:afterRequest', (event) => {
				// Check if the request came from the specific form
				var form = event.detail.requestConfig.triggeringEvent.target;
				if (form && form.hasAttribute('hx-post') && form.getAttribute('hx-target') === '#client-tracking-details' ) {
					// Get the response
					let response = event.detail.xhr.responseText;

					// Parse the JSON response
					let jsonResponse;
					try {
						jsonResponse = JSON.parse(response);
					} catch (error) {
						console.error('Failed to parse JSON:', error);
						swal.fire({
							title: "<?php esc_html_e( 'Error', 'coursh' ); ?>",
							text: "<?php esc_html_e( 'An rrror occured', 'coursh' ); ?>",
							icon: "error",
							showCloseButton: true,
							allowOutsideClick: false,
							allowEscapeKey: false,
						});
						return;
					}
					// Check if the response contains success and data
					if (jsonResponse.success && jsonResponse.data) {
						let shipmentId = jsonResponse.data['ID'];

						// Convert the data object into a table
						const createTableHTML = (data) => {
							// Check if 'tracking_info' exists and is an array
							if (Array.isArray(data.tracking_info) && data.tracking_info.length > 0) {
								// Get the headers from the keys of the first object
								const headers = Object.keys(data.tracking_info[0]).filter(key => key !== 'id');

								// Start building the table with dynamic headers
								let table = `
									<table style="width:100%; border-collapse:collapse;">
										<tr>
											${headers.map(header => `
												<th style="text-align:left; padding:5px; border:1px solid #ddd;font-size:12px">
													${header.replace('_', ' ').toUpperCase()}
												</th>
											`).join('')}
										</tr>
								`;

								// Iterate over each tracking entry
								data.tracking_info.forEach((item) => {
									table += `
										<tr>
											${headers.map(header => {
												// Apply special styling if the header is 'status'
												const style = header === 'status' ? 'background-color:#f15f22; color:#fff;' : '';
												return `
													<td style="padding:5px; border:1px solid #ddd; ${style}">
														${item[header]}
													</td>
												`;
											}).join('')}
										</tr>
									`;
								});

								// Close the table
								table += '</table>';
								return table;
							} else {
								// Handle cases where 'tracking_info' is not available or empty
								return '<p><?php esc_html_e( 'No tracking information available.', 'coursh' ); ?></p>';
							}
						};


						// Show the SweetAlert2 popup
						swal.fire({
							title: "<?php esc_html_e( 'Tracking details', 'coursh' ); ?>",
							html: createTableHTML(jsonResponse.data),
							icon: "info",
							width: '600px',
							showCloseButton: true,
							allowOutsideClick: false,
							allowEscapeKey: false,
							showCancelButton: true, // Optional: adds Cancel button
							
						});

					} else {
						// Handle unsuccessful response
						swal.fire({
							title: "<?php esc_html_e( 'Error', 'coursh' ); ?>",
							text: jsonResponse.data.message || "<?php esc_html_e( 'Sorry! No available data.', 'coursh' ); ?>",
							icon: "error",
							showCloseButton: true,
							allowOutsideClick: false,
							allowEscapeKey: false,
						});
					}
				}
				
				if (form && form.hasAttribute('hx-post') && form.getAttribute('hx-target') === '#tracking-result' ) {
					// Get the response
					let response = event.detail.xhr.responseText;

					// Parse the JSON response
					let jsonResponse;
					try {
						jsonResponse = JSON.parse(response);
					} catch (error) {
						console.error('Failed to parse JSON:', error);
						swal.fire({
							title: "<?php esc_html_e( 'Error', 'coursh' ); ?>",
							text: "<?php esc_html_e( 'An rrror occured', 'coursh' ); ?>",
							icon: "error",
							showCloseButton: true,
							allowOutsideClick: false,
							allowEscapeKey: false,
						});
						return;
					}

					// Check if the response contains success and data
					if (jsonResponse.success && jsonResponse.data) {
						let shipmentId = jsonResponse.data['ID'];

						// Convert the data object into a table
						const createTableHTML = (data) => {
							let table = '<table style="width:100%; border-collapse:collapse;">';
							table += '<tr><th style="text-align:left; padding:5px; border:1px solid #ddd;">Key</th><th style="text-align:left; padding:5px; border:1px solid #ddd;">Value</th></tr>';

							for (const key in data) {
								if (Object.prototype.hasOwnProperty.call(data, key)) {
									// Build the table row with the formatted key and its value
									var style;
									if ( key === 'Tracking number' ) {
										style= 'background-color:#f15f22;color:#fff';
									} else {
										style = ';'
									}
									if ( key === 'ID' ) {
										continue;
									}
									table += `<tr>
										<td style="padding:5px; border:1px solid #ddd;${style}">${key}</td>
										<td style="padding:5px; border:1px solid #ddd;${style}">${data[key]}</td>
									</tr>`;
								}
							}


							table += '</table>';
							return table;
						};

						// Show the SweetAlert2 popup
						swal.fire({
							title: "<?php esc_html_e( 'Shipment details', 'coursh' ); ?>",
							html: createTableHTML(jsonResponse.data),
							icon: "info",
							width: '600px',
							showCloseButton: true,
							allowOutsideClick: false,
							allowEscapeKey: false,
							showCancelButton: true, // Optional: adds Cancel button
							confirmButtonText: "<?php esc_html_e( 'Insert tracking', 'coursh' ); ?>",
						}).then((result) => {
							// Check if Confirm button was clicked
							if (result.isConfirmed) {
								// Show the #employee-actions-form element
								const formElement = document.querySelector('#employee-actions-form');
								if (formElement) {
									formElement.style.display = 'block'; // Ensure the form is displayed
								}

								// Set the input with name shipment_id to 123
								const shipmentInput = document.querySelector('input[name="shipment_id"]');
								if (shipmentInput) {
									shipmentInput.value = shipmentId; // Set the value
								}
							}
						});

					} else {
						// Handle unsuccessful response
						swal.fire({
							title: "<?php esc_html_e( 'Error', 'coursh' ); ?>",
							text: jsonResponse.message || "<?php esc_html_e( 'Sorry! No available data.', 'coursh' ); ?>",
							icon: "error",
							showCloseButton: true,
							allowOutsideClick: false,
							allowEscapeKey: false,
						});
					}
				}

				if (form && form.hasAttribute('hx-post') && form.getAttribute('hx-target') === '#employee-actions' ) {
					// Get the response
					let response = event.detail.xhr.responseText;
					// Parse the JSON response
					let jsonResponse;
					try {
						jsonResponse = JSON.parse(response);
					} catch (error) {
						console.error('Failed to parse JSON:', error);
						swal.fire({
							title: "<?php esc_html_e( 'Errors', 'coursh' ); ?>",
							text: "<?php esc_html_e( 'An error occured', 'coursh' ); ?>",
							icon: "error",
							showCloseButton: true,
							allowOutsideClick: false,
							allowEscapeKey: false,
						});
						return;
					}

					// Check if the response contains success and data
					if (jsonResponse.success && jsonResponse.data) {
						// Show the SweetAlert2 popup
						swal.fire({
							title: "<?php esc_html_e( 'Success', 'coursh' ); ?>",
							text: "<?php esc_html_e( 'Tracking has been inserted', 'coursh' ); ?>",
							icon: "info",
							width: '600px',
							allowOutsideClick: false,
							allowEscapeKey: false,
							showCancelButton: true, // Optional: adds Cancel button
							confirmButtonText: "<?php esc_html_e( 'Ok', 'coursh' ); ?>",
						})
					}
				}
			});

			document.body.addEventListener('htmx:beforeRequest', (event) => {
				var form = event.detail.requestConfig.triggeringEvent.target;
				if (form && form.hasAttribute('hx-post') && form.getAttribute('hx-target') === '#tracking-result' ) {
					$("#employee-actions-form").hide();
					$("#employee-actions-form")[0].reset();
				}
			});
		</script>

		<?php
	}
);