<div class="history" style="max-width: 400px;margin:auto">
	<div class="row d-flex justify-content-center">    
		<div class="main-card card">
			<div class="card-header">حالة الشحنة <?php echo $tracking_number; ?></div>
			<div class="card-body">
				<div class="vertical-timeline vertical-timeline--animate vertical-timeline--one-column">
					<?php
					foreach ( $trackings as $tracking ) {

						switch ( $tracking['status'] ) {
							case 'collected':
								$color       = 'primary'; // Blue
								$description = ! empty( $tracking['description'] ) ? $tracking['description'] : esc_html__( 'Collected', 'coursh' );
								break;
							case 'packaged':
								$color       = 'info'; // Light Blue
								$description = ! empty( $tracking['description'] ) ? $tracking['description'] : esc_html__( 'Packaged', 'coursh' );
								break;
							case 'shipped':
								$color       = 'warning'; // Yellow
								$description = ! empty( $tracking['description'] ) ? $tracking['description'] : esc_html__( 'Shipped', 'coursh' );
								break;
							case 'delivered':
								$color       = 'success'; // Green
								$description = ! empty( $tracking['description'] ) ? $tracking['description'] : esc_html__( 'Delivered', 'coursh' );
								break;
							case '':
								$color       = 'secondary'; // Gray for undefined status
								$description = ! empty( $tracking['description'] ) ? $tracking['description'] : esc_html__( 'Processing', 'coursh' );
								break;
							default:
								$color       = 'danger'; // Red for unexpected status
								$description = ! empty( $tracking['description'] ) ? $tracking['description'] : esc_html__( 'Cancelled', 'coursh' );
						}
						?>
						<div class="vertical-timeline-item vertical-timeline-element">
							<div>
								<span class="vertical-timeline-element-icon bounce-in">
									<i class="badge bg-<?php echo $color; ?> badge-dot-xl">
										&nbsp;
									</i>
								</span>
								<div class="vertical-timeline-element-content bounce-in">
									<h4 class="timeline-title"><?php echo gmdate( 'M d Y', strtotime( $tracking['created_at'] ) ); ?></h4>
									<p><?php echo $description; ?></p>
									<span class="vertical-timeline-element-date">
									<?php echo gmdate( 'h:i A', strtotime( $tracking['created_at'] ) ); ?></p>
									</span>
								</div>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
