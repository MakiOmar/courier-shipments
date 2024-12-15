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
						// Convert the data object into a table
						const createTableHTML = (data) => {
							let table = '<table style="width:100%; border-collapse:collapse;">';
							table += '<tr><th style="text-align:left; padding:5px; border:1px solid #ddd;">Key</th><th style="text-align:left; padding:5px; border:1px solid #ddd;">Value</th></tr>';

							for (const key in data) {
								if (data.hasOwnProperty(key)) {
									const formattedKey = key.replace(/^cct_/, '').replace(/^./, str => str.toUpperCase());
									table += `<tr>
										<td style="padding:5px; border:1px solid #ddd;">${formattedKey}</td>
										<td style="padding:5px; border:1px solid #ddd;">${data[key]}</td>
									</tr>`;
								}
							}

							table += '</table>';
							return table;
						};

						// Show the SweetAlert2 popup
						swal.fire({
							title: "بيانات الشحنة",
							html: createTableHTML(jsonResponse.data),
							icon: "info",
							width: '600px',
							showCloseButton: true,
							allowOutsideClick: false,
							allowEscapeKey: false,
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
			});
		</script>

		<?php
	}
);