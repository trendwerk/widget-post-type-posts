<?php
/**
 * Plugin Name: Post type posts
 * Description: Widget with posts from a post type.
 *
 * Plugin URI: https://github.com/trendwerk/widget-post-type-posts
 * 
 * Author: Trendwerk
 * Author URI: https://github.com/trendwerk
 * 
 * Version: 1.0.0
 */

class TP_Post_Type_Posts_Plugin {

	function __construct() {
		add_action( 'plugins_loaded', array( $this, 'localization' ) );
	}

	/**
	 * Load localization
	 */
	function localization() {
		load_muplugin_textdomain( 'widget-post-type-posts', dirname( plugin_basename( __FILE__ ) ) . '/assets/lang/' );
	}

} new TP_Post_Type_Posts_Plugin;

class TP_Post_Type_Posts extends WP_Widget {

	function TP_Post_Type_Posts() {
		$this->WP_Widget( 'TP_Post_Type_Posts', __( 'Post type posts', 'widget-post-type-posts' ), array( 
			'description' => __( 'List of posts from a given post type', 'widget-post-type-posts' ),
		) );
	}
	
	function form( $instance ) {
		$defaults = array(
			'title'        => __( 'Latest posts', 'widget-post-type-posts' ),
			'post_type'    => 'post',
			'number'       => 5,
			'archive_link' => true,
		);

		$instance = wp_parse_args( $instance, $defaults );

		$post_types = array_diff( get_post_types( array(
			'public'   => true,
		) ), array( 'page', 'attachment' ) );

		if( 0 < count( $post_types ) ) {
			?>

			<p>
				<label>
					<strong><?php _e( 'Title' ); ?></strong><br />
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
				</label>
			</p>

			<p>
				<label>
					<strong><?php _e( 'Post type', 'widget-post-type-posts' ); ?></strong><br />
					<select class="widefat" name="<?php echo $this->get_field_name( 'post_type' ); ?>">
							<?php 
								foreach( $post_types as $post_type ) { 
									$post_type = get_post_type_object( $post_type ); 
									?>

									<option <?php selected( $post_type->name, $instance['post_type'] ); ?> value="<?php echo $post_type->name; ?>">
										<?php echo $post_type->labels->name; ?>
									</option>

									<?php 
								} 
							?>
						</select>
				</label>
			</p>

			<p>
				<label>
					<?php _e( 'Number of posts to show:' ); ?>
					<input name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $instance['number']; ?>" size="3" />
				</label>
			</p>

			<p>
				<label>
					<input type="checkbox" name="<?php echo $this->get_field_name( 'archive_link' ); ?>" value="true" <?php checked( $instance['archive_link'] ); ?>>
					<?php _e( 'Show link to post type archive', 'widget-post-type-posts' ); ?>
				</label>
			</p>

			<?php 
		}
	}

	function update( $new_instance, $old_instance ) {
		$new_instance['number'] = absint( $new_instance['number'] );
		$new_instance['archive_link'] = isset( $new_instance['archive_link'] );
		
		return $new_instance;
	}

	function widget( $args, $instance ) {
		extract( $args );
		
		$post_type = get_post_type_object( $instance['post_type'] );

		$post_type_posts = new WP_Query( array(
			'post_type'           => $post_type->name, 
			'posts_per_page'      => $instance['number'],
			'ignore_sticky_posts' => true
		) );
	
		if( $post_type_posts->have_posts() ) {
			echo $before_widget;

				if( $instance['title'] )
					echo $before_title . $instance['title'] . $after_title;
				?>
		
				<ul class="post-type-posts">
		
					<?php while( $post_type_posts->have_posts() ) : $post_type_posts->the_post(); ?>
		
						<li class="post-type-post">
							<a href="<?php the_permalink(); ?>">
								<?php the_title(); ?>
							</a>
						</li>
		
					<?php endwhile; ?>
		
				</ul>
				
				<?php 
				if( $instance['archive_link'] ) {
					?>
					
			    	<a class="more-link" href="<?php echo get_post_type_archive_link( $post_type->name ); ?>"><?php echo __( 'View all', 'widget-post-type-posts') . ' ' . strtolower( $post_type->labels->name ); ?></a>

		    		<?php 
		    	} 
			echo $after_widget;
		}

		wp_reset_postdata();
	}
}

add_action( 'widgets_init', create_function( '', 'return register_widget( "TP_Post_Type_Posts" );' ) );
