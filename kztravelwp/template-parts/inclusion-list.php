<?php
defined( 'ABSPATH' ) || exit;

$items   = $args['items'] ?? array();
$variant = $args['variant'] ?? 'included';
if ( empty( $items ) ) {
	return;
}
?>
<div class="inclusion-table inclusion-table--<?php echo esc_attr( $variant ); ?>">
	<table>
		<thead>
			<tr>
				<th><?php echo esc_html( kztravel_ui( 'item' ) ); ?></th>
				<th><?php echo esc_html( kztravel_ui( 'price' ) ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $items as $item ) : ?>
				<tr>
					<td><?php echo esc_html( $item['title'] ?? $item['name'] ?? '' ); ?></td>
					<td>
						<?php if ( 'included' === $variant ) : ?>
							<?php echo esc_html( kztravel_ui( 'includedPrice' ) ); ?>
						<?php else : ?>
							<?php if ( isset( $item['price'] ) && null !== $item['price'] ) : ?>
								<?php if ( isset( $item['priceBgn'] ) && null !== $item['priceBgn'] ) : ?>
									<?php echo esc_html( kztravel_format_dual_price( (float) $item['price'], (float) $item['priceBgn'] ) ); ?>
								<?php else : ?>
									<?php echo esc_html( kztravel_format_price( (float) $item['price'] ) ); ?>
								<?php endif; ?>
							<?php else : ?>
								<?php echo esc_html( kztravel_ui( 'onRequest' ) ); ?>
							<?php endif; ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
