<?php
	$foo = $this->getData('foo', 'baz');
?>
<?php $site->getParts(array('sticky-footer/header_html', 'sticky-footer/header')) ?>

		<section>
			<div class="container">
				<h1>Products</h1>
			</div>
		</section>

<?php $site->getParts(array('sticky-footer/footer', 'sticky-footer/footer_html')) ?>