<?php 
$uid = 'c4d-woo-gp-'.uniqid();
?>
<script>
	(function($){
		$(document).ready(function(){
			c4dwgp['<?php echo $uid; ?>'] = <?php echo json_encode($params); ?>;
		});	
	})(jQuery);
</script>
<div class="c4d-woo-gp">
	<div class="c4d-woo-gp__categories">
		<span class="active" data-category="<?php echo esc_attr($params['category']); ?>"><?php esc_html_e('All', 'c4d-woo-gp'); ?></span>
		<?php 
			$categories = explode(',', $params['category']);
			if (is_array($categories)) {
				foreach ($categories as $key => $value) {
					$cate = get_cat_name($value);
					if ($cate) {
						echo '<span data-category="'.esc_attr((int)$value).'">'.$cate.'</span>';	
					}
				}
			}
		?>
	</div>
	<div class="c4d-woo-gp__grid" data-cols="<?php esc_html_e($params['cols']); ?>">
		<div id="<?php echo esc_attr($uid); ?>">
			<?php while ( $q->have_posts() ) : ?>
				<?php $p = $q->the_post(); ?>
				<?php require dirname(__FILE__). '/__item.php'; ?>
			<?php endwhile; // end of the loop. ?>
		</div>
	</div>
	<div class="c4d-woo-gp__loading">
		<span class="button"></span>
	</div>
	<?php if ($params['loadmore'] == 1) : ?>
		<div class="c4d-woo-gp__loadmore">
			<a data-count="<?php echo esc_attr($params['count']); ?>" data-page="2" href="#" class="c4d-woo-gp__loadmore_button">
				<span><?php esc_html_e($params['loadmore_text'], 'c4d-woo-gp'); ?></span>
			</a>
		</div>
	<?php endif; ?>
</div>