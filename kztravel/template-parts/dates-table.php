<?php
defined( 'ABSPATH' ) || exit;

$dates = $args['dates'] ?? array();
if ( empty( $dates ) ) {
	return;
}

usort(
	$dates,
	function ( $a, $b ) {
		return strcmp( $a['date'] ?? '', $b['date'] ?? '' );
	}
);

$status_config = array(
	'available' => array(
		'class' => 'status-chip--available',
		'label' => kztravel_ui( 'available' ),
	),
	'lastSpots' => array(
		'class' => 'status-chip--lastSpots',
		'label' => kztravel_ui( 'lastSpots' ),
	),
	'soldout'   => array(
		'class' => 'status-chip--soldout',
		'label' => kztravel_ui( 'soldOut' ),
	),
);
?>
<div class="dates-table">
	<table class="dates-table__desktop">
		<thead>
			<tr>
				<th><?php echo esc_html( kztravel_ui( 'date' ) ); ?></th>
				<th><?php echo esc_html( kztravel_ui( 'price' ) ); ?></th>
				<th><?php echo esc_html( kztravel_ui( 'status' ) ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $dates as $i => $entry ) : ?>
				<?php
				$row_class = '';
				if ( kztravel_is_date_past( $entry['date'] ?? '' ) ) {
					$row_class = 'dates-table__past';
				} elseif ( 'soldout' === ( $entry['status'] ?? '' ) ) {
					$row_class = 'dates-table__unavailable';
				}
				$status = $entry['status'] ?? 'available';
				?>
				<tr class="<?php echo esc_attr( $row_class ); ?>">
					<td><?php echo esc_html( kztravel_format_date( $entry['date'] ) ); ?></td>
					<td>
						<?php
						get_template_part(
							'template-parts/price',
							'display',
							array(
								'price'              => $entry['price'],
								'priceBgn'           => $entry['priceBgn'],
								'discountedPrice'    => $entry['discountedPrice'] ?? null,
								'discountedPriceBgn' => $entry['discountedPriceBgn'] ?? null,
							)
						);
						?>
					</td>
					<td>
						<?php if ( kztravel_is_date_past( $entry['date'] ?? '' ) ) : ?>
							<span class="dates-table__completed"><?php echo esc_html( kztravel_ui( 'completed' ) ); ?></span>
						<?php else : ?>
							<span class="status-chip <?php echo esc_attr( $status_config[ $status ]['class'] ?? '' ); ?>">
								<?php echo esc_html( $status_config[ $status ]['label'] ?? $status ); ?>
							</span>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div class="dates-table__mobile">
		<?php foreach ( $dates as $i => $entry ) : ?>
			<?php
			$row_class = '';
			if ( kztravel_is_date_past( $entry['date'] ?? '' ) ) {
				$row_class = 'dates-table__past';
			} elseif ( 'soldout' === ( $entry['status'] ?? '' ) ) {
				$row_class = 'dates-table__unavailable';
			}
			$status = $entry['status'] ?? 'available';
			?>
			<div class="dates-table__card <?php echo esc_attr( $row_class ); ?>">
				<div class="dates-table__card-row">
					<div class="dates-table__card-date"><?php echo esc_html( kztravel_format_date( $entry['date'] ) ); ?></div>
					<div class="dates-table__card-price">
						<?php
						get_template_part(
							'template-parts/price',
							'display',
							array(
								'price'              => $entry['price'],
								'priceBgn'           => $entry['priceBgn'],
								'discountedPrice'    => $entry['discountedPrice'] ?? null,
								'discountedPriceBgn' => $entry['discountedPriceBgn'] ?? null,
							)
						);
						?>
					</div>
				</div>
				<div class="dates-table__card-status">
					<?php if ( kztravel_is_date_past( $entry['date'] ?? '' ) ) : ?>
						<span class="dates-table__completed"><?php echo esc_html( kztravel_ui( 'completed' ) ); ?></span>
					<?php else : ?>
						<span class="status-chip <?php echo esc_attr( $status_config[ $status ]['class'] ?? '' ); ?>">
							<?php echo esc_html( $status_config[ $status ]['label'] ?? $status ); ?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
