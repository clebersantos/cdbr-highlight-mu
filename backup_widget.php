<?php

/**
 * Copyright (c) 2015 Ministério da Cultura do Brasil
 *
 * Written by Cleber Santos <oclebersantos@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the
 * Free Software Foundation, Inc.,
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *
 * Public License can be found at http://www.gnu.org/copyleft/gpl.html
 */

class WidgetHighlightsMU extends WP_Widget
{
	// ATRIBUTES /////////////////////////////////////////////////////////////////////////////////////
	var $path = '';

	// METHODS ///////////////////////////////////////////////////////////////////////////////////////
	/**
	 * load widget
	 *
	 * @name    widget
	 * @author  Cleber Santos <oclebersantos@gmail.com>
	 * @since   2015-04-06
	 * @updated 2010-01-27
	 * @param   array $args - widget structure
	 * @param   array $instance - widget data
	 * @return  void
	 */
	function widget( $args, $instance )
	{
		global $post;

		$title = !empty( $instance[ 'title' ] ) ? $args[ 'before_title' ] . $instance[ 'title' ] . $args[ 'after_title' ] : "";
		$show_posts     = empty($instance['showposts']) ? 5 : $instance['showposts'];
		$limit_title 	= empty($instance['limit_title']) ? 100 : $instance['limit_title'];
		$limit_excerpt  = empty($instance['limit_excerpt']) ? 120 : $instance['limit_excerpt'];
		$i = 0;

		// carrega destaques
		$highlights = stripslashes_deep( get_site_option( 'cdbr_highlights_mu' ) );
		
		// show posts
		if( $highlights ) {

			print $args[ 'before_widget' ];

			print '<div id="highlights">';

				// print $title;

			 	if( is_array( $highlights ) ) {

					$highlights = array_slice( $highlights, 0, $show_posts ); 

					foreach( $highlights as $highlight ) { 

						$i++; ?>

						<?php if( function_exists( 'switch_to_blog' ) ) switch_to_blog( $highlight[ 'blog_id' ] ); ?>
									
								<?php $cycle = new WP_Query( "p={$highlight[ 'post_id' ]}" ); ?>
								
								<?php if( $cycle->have_posts() ) : $cycle->the_post(); ?>

									<article id="post-<?php print get_the_ID(); ?>" class="<?php print strlen( $highlight[ 'highlight_excerpt' ]) <= 1  ? 'not-excerpt' : 'true-excerpt'; echo 'item-'.$i; ?>">
										<a href="<?php print get_permalink(); ?>" title="<?php print $highlight[ 'highlight_title' ]; ?>">
											<div class="entry-thumb">
												<?php the_post_thumbnail( "highlight-small", "class=''" ); ?>
											</div>

											<div class="headline">

												<?php $new_categories = null; ?>

												<?php $new_categories = get_the_term_list( get_the_ID(), 'category', '', ', '); ?>

												<?php if( $highlight[ 'blog_id' ] != 1 ) 
												$new_categories = preg_replace("/\/blog\//", "/", get_the_category_list( ', ' ) ); ?>
												
												<p class="post-meta"><?php echo $new_categories; ?></p>
												<div class="post-title"><a href="<?php print get_permalink(); ?>" title="<?php print get_the_title(); ?>"><?php print cdbr_limit_chars( $highlight[ 'highlight_title' ], $limit_title ); ?></a></div>
												<div class="post-excerpt"><?php print cdbr_limit_chars( $highlight[ 'highlight_excerpt' ], $limit_excerpt ); ?></div>
											</div>

										</a>
									</article>
									

								<?php endif; ?>

						<?php if( function_exists( 'restore_current_blog' ) ) restore_current_blog(); 

						// print "</div>";
						// print ( $i == 2 ) ? "</div><!-- close column-2 -->" : "";
						// print ( $i == 5 ) ? "</div></div>" : ""; 

			
					}

				}
				print "</div><!-- close #highlights -->";
			print $args[ 'after_widget' ];
		}
	}

	/**
	 * update data
	 *
	 * @name    update
	 * @author  Cleber Santos <oclebersantos@gmail.com>
	 * @since   2015-03-05
	 * @updated 2015-03-05
	 * @param   array $new_instance - new values
	 * @param   array $old_instance - old values
	 * @return  array
	 */
	function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		
		if( $instance != $new_instance )
		{
			$options = array(
				'shows_posts' => $new_instance['show_posts'],
				'limit_title' => $new_instance['limit_title'],
				'limit_excerpt' => $new_instance['limit_excerpt']
			);

			update_site_option( 'cdbr_highlights_mu_options', $options );

			$instance = $new_instance;
		}
		return $instance;
	}

	/**
	 * widget options form
	 *
	 * @name    form
	 * @author  Cleber Santos <oclebersantos@gmail.com>
	 * @since   2015-04-06
	 * @updated 2009-12-15
	 * @param   array $instance - widget data
	 * @return  void
	 */
	function form($instance)
	{
		global $wpdb;

	    $title =  empty( $instance['title'] ) ? '' : esc_attr( $instance['title'] );

		$showposts      =  empty( $instance['showposts']) ? 5 : esc_attr( $instance['showposts'] );
		$limit_title 	=  empty( $instance['limit_title']) ? 100 : absint( $instance['limit_title'] );
		$limit_excerpt  =  empty( $instance['limit_excerpt']) ? 120 : absint( $instance['limit_excerpt'] );
	?>
			<p>
				<label for="<?php print $this->get_field_id( 'title' ); ?>"><?php _e( 'Title' ); ?>:</label>
				<input type="text" id="<?php print $this->get_field_id( 'title' ); ?>" name="<?php print $this->get_field_name( 'title' ); ?>" maxlength="26" value="<?php print $title; ?>" class="widefat" />
			</p>

			<p>
				<label for="<?php print $this->get_field_id( 'showposts' ); ?>"><?php _e( 'Showposts' ); ?>:</label><br />
				<input type="text" id="<?php print $this->get_field_id( 'showposts' ); ?>" name="<?php print $this->get_field_name( 'showposts' ); ?>" size="2" maxlength="2" value="<?php print $showposts; ?>" />
			</p>

			<p>
				<label for="<?php print $this->get_field_id( 'limit_title' ); ?>"><?php _e( 'Tamanho max. do Título' ); ?>:</label><br />
				<input type="text" id="<?php print $this->get_field_id( 'limit_title' ); ?>" name="<?php print $this->get_field_name( 'limit_title' ); ?>" size="3" maxlength="3" value="<?php print $limit_title; ?>" />
			</p>

			<p>
				<label for="<?php print $this->get_field_id( 'limit_excerpt' ); ?>"><?php _e( 'Tamanho max. do sutiã' ); ?>:</label><br />
				<input type="text" id="<?php print $this->get_field_id( 'limit_excerpt' ); ?>" name="<?php print $this->get_field_name( 'limit_excerpt' ); ?>" size="3" maxlength="3" value="<?php print $limit_excerpt; ?>" />
			</p>

			
        <?php
	}

	// CONSTRUCTOR ///////////////////////////////////////////////////////////////////////////////////
	/**
	 * @name    WidgetHighlightsMU
	 * @author  Cleber Santos <oclebersantos@gmail.com>
	 * @since   2015-04-06
	 * @updated 2015-04-06
	 * @return  void
	 */
	function __construct()
	{
		// define plugin path
		$this->path = dirname( __FILE__ ) . '/';

		// register widget
		// $this->WP_Widget( 'Highlights', 'Highlights', array( 'classname' => 'widget_highlights_mu', 'description' => __( 'Cria uma widget de destaques dos posts selecionados na rede.', 'widget-highlights' ) ), array( 'width' => 400 ) );

		$widget_args = array( 'classname' => 'widget_highlights_mu', 'description' => __( 'Cria uma widget de destaques dos posts selecionados na rede.', 'widget-highlights' ) );
		parent::__construct('Highlights', __('Highlights'), $widget_args);
		
		if( !function_exists( 'cdbr_limit_chars' ) )
			include( $this->path . '/inc/limit-chars.php' );
	}

	// DESTRUCTOR ////////////////////////////////////////////////////////////////////////////////////

}

// register widget
add_action( 'widgets_init', create_function( '', 'return register_widget( "WidgetHighlightsMU" );' ) );

?>
