<?php
/*
Plugin Name: MiniMini Favourite Posts
Author:      Barbara
Description: Simple widget for adding list of favourite posts.
Version:     1.0
*/

class Favourite_Posts extends WP_Widget {
	public function __construct() {
		$widget_options = array(
			'classname'    => 'favourite_posts',
			'description' => 'Wyświetla wybrane wpisy.',
		);
		parent::__construct( 'favourite_posts', 'Polecane wpisy', $widget_options );
		add_action('admin_enqueue_scripts', function(){
			wp_enqueue_style( 'style',  plugin_dir_url( __FILE__ ) . 'assets/css/style.css' );
		});
	}
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) :
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		endif;
		if ( ! empty( $instance['selected_posts'] ) && is_array( $instance['selected_posts'] ) ) :
			?>
			<ul>
			<?php foreach ( $instance['selected_posts'] as $post_id ) : ?>
				<li>
					<a href="<?php echo get_permalink( $post_id ); ?>">
						<?php echo get_the_title( $post_id ); ?>
					</a>
				</li>
			<?php endforeach; ?>
			</ul>
		<?php
		else :
			echo 'Na razie nie ma tu nic. But stay tuned!';
		endif;
		echo $args['after_widget'];
	}
	public function form( $instance ) {
		$title = ! empty($instance['title'])? $instance['title'] : 'Polecane wpisy'; 
		$posts = get_posts( array('posts_per_page' => -1, 'post_status' => 'publish', ) );
		$selected_posts = !empty( $instance['selected_posts'] ) ? $instance['selected_posts'] : array();
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title').':'; ?></label>
			<input id="<?php echo $this->get_field_id('title'); ?>" class="widefat" type="text" 
				   name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>">
		</p>
		<p>
			<label>Wybierz wpisy:</label>
			<?php foreach( $selected_posts as $selected_post ): ?>
			<p class="fav_posts_select">
				<select name="<?php echo esc_attr( $this->get_field_name( 'selected_posts' ) ); ?>[]">
					<?php foreach( $posts as $post ): ?>
						<option value="<?php echo $post->ID; ?>" 
							<?php selected( $selected_post, $post->ID ); ?>>
							<?php echo $post->post_title; ?></option>
					<?php endforeach ?>
				</select>
				<button class="button fav_posts_del" type="button">Usuń</button>
			</p>
			<?php endforeach; ?>
			<button class="button fav_posts_add" type="button">Dodaj wpis</button>
		</p>
		<script>
			<?php
				$select_post = '<p class="fav_posts_select"><select name="' . esc_attr( $this->get_field_name( 'selected_posts' ) ) . '[]">';
				foreach( $posts as $post ):
					$select_post .= '<option value="' . $post->ID . '">' . $post->post_title . '</option>';
				endforeach;
				$select_post .= '</select><button class="button fav_posts_del" type="button">Usuń</button></p>';
			?>
			var select_post = '<?php echo $select_post; ?>';
			$(document).on('click', '.fav_posts_del', function(e){
				$(this).trigger('change');
				$(this).parent().remove();
			});
			$('.fav_posts_add').click( function(e){
				$(this).trigger('change');
				$(this).before(select_post);
			});
		</script>
		<?php
	}
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$selected_posts = ( ! empty ( $new_instance['selected_posts'] ) ) ? (array) $new_instance['selected_posts'] : array();
		$instance['selected_posts'] = array_unique( array_map( 'sanitize_text_field', $selected_posts ));
		return $instance;
	}
}
