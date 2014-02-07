<?php
	$product = $this->getData('product', null);
?>
<?php $site->getParts(array('sticky-footer/header_html', 'sticky-footer/header')) ?>

		<section>
			<div class="container">
				<h1>Datasheets</h1>
				<?php if ($product): ?>
				<p><code>Datasheets for product '<?php echo $product; ?>'</code></p>
				<?php endif; ?>
			</div>
		</section>

<?php $site->getParts(array('sticky-footer/footer', 'sticky-footer/footer_html')) ?>