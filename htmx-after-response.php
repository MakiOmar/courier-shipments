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
				const form = event.detail.requestConfig.triggeringEvent.target;
				if (form && form.hasAttribute('hx-post') && form.getAttribute('hx-target') === '#tracking-result' ) {
					// Get the response
					const response = event.detail.xhr.responseText;

					// Parse the JSON response
					let jsonResponse;
					try {
						jsonResponse = JSON.parse(response);
					} catch (error) {
						console.error('Failed to parse JSON:', error);
						swal.fire({
							title: "خطأ",
							text: "حدث خطأ ما",
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
							title: "خطأ",
							text: jsonResponse.message || "عفواً لا توجد بيانات.",
							icon: "error",
							showCloseButton: true,
							allowOutsideClick: false,
							allowEscapeKey: false,
						});
					}
				}

				if (form && form.hasAttribute('hx-post') && form.getAttribute('hx-target') === '#employee-actions' ) {
					// Get the response
					const response = event.detail.xhr.responseText;
					console.log(response);
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
							showCloseButton: true,
							allowOutsideClick: false,
							allowEscapeKey: false,
							showCancelButton: true, // Optional: adds Cancel button
							confirmButtonText: "<?php esc_html_e( 'Ok', 'coursh' ); ?>",
						})
					}
				}
			});
		</script>

		<?php
	}
);