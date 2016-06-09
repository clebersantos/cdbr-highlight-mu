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
 *
 * Plugin Name: Cultura Digital Highlights
 * Plugin URI: http:culturadigital.br
 * Description: Destaques da rede multisite para um widget, pode ser utilizado para a capa principal do site, conforme o site culturadigital.br. Este plugin é um fork do Headlines do grande Marcelo Mesquita<marcelomesquita.com>
 * Author: Cleber Santos
 * Stable tag: 0.1
 * Author URI: http://culturadigital.br/members/clebersantos
 * 
 * RePost is released under the GNU General Public License (GPL)
 *  http://www.gnu.org/licenses/gpl.txt
 * 
 * Este plugin foi desenvolvido baseado no plugin Minc Headlines do desenvolvedor Marcelo Mesquita
*/ 

class HighlightsMU
{	
	// ATRIBUTOS ////////////////////////////////////////////////////////////////////////////////////
	var $path = "";
	var $capability = "manage_network";

	// METODOS //////////////////////////////////////////////////////////////////////////////////////
	/*
	 *	Cria os valores padrão para a configuração do plugin.
	 *
	 *	@name    install
	 *	@author  Cleber Santos <oclebersantos@gmailcom>
	 *	@since   2015-01-08
	 *	@updated 2015-01-08
	 */
	function install()
	{
		// ativar somente se for multisite
		if( !is_multisite() )
			return false;

		// images sizes
		add_image_size( 'highlight', 200, 200, true );
	}

	/**
	 * Remove as configurações do plugin 
	 *
	 * @name    uninstall
	 * @author  Cleber Santos <oclebersantos@gmail.com>
	 * @since   2015-01-08
	 * @updated 2015-01-08
	 * @return  void
	 */
	function uninstall()
	{

	}

	/**
	 * Carrega os scripts
	 *
	 * @name    admin_scripts
	 * @author  Cleber Santos <oclebersantos@gmail.com>
	 * @since   2015-04-01
	 * @updated 2015-04-01
	 * @return  void
	 */
	function admin_scripts()
	{
		$blog_url = get_bloginfo( 'url' );

		$plugin_url = str_replace( ABSPATH, $blog_url . '/', $this->path );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'highlights', plugins_url ('/js/highlights.js', __file__) , array( 'jquery-ui-draggable' ), true );

		wp_localize_script( 'highlights', 'highlightsAjax', array(
                'ajaxurl'   => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( "highlights_nonce" ),
            )
        );
	}


	/**
	 * Carrega os estilos necessários
	 *
	 * @name    admin_styles
	 * @author  Cleber Santos <oclebersantos@gmail.com>
	 * @since   2015-04-01
	 * @updated 2015-04-01
	 * @return  void
	 */
	function admin_styles()
	{
		$blog_url = get_bloginfo( 'url' );

		$plugin_url = str_replace( ABSPATH, $blog_url . '/', $this->path );

		wp_enqueue_style( 'highlights', plugins_url ('/css/highlights.css', __file__) );
	}

	/*
	 *	Criar os Menus na área administrativa.
	 *
	 *	@name    menus
	 *	@author  Cleber Santos <oclebersantos@gmailcom>
	 *	@since   2015-01-08
	 *	@updated 2015-01-08
	*/
	function menus()
	{
		// Menus
		$menu = add_menu_page( __('Destaques da Rede', 'highlights_mu'), __('Destaques', 'highlights_mu'), $this->capability, 'highlights_mu', array( &$this, 'cdbr_show_manage' ), 'dashicons-welcome-widgets-menus',4);

		// load scripts
		add_action( "admin_print_scripts-{$menu}", array( &$this, 'admin_scripts' ) );

		// load styles
		add_action( "admin_print_styles-{$menu}", array( &$this, 'admin_styles' ) );
	}


	/*
	 *	
	 *
	 *	@name    cdbr_post_exists
	 *	@author  Cleber Santos <oclebersantos@gmailcom>
	 *	@since   2015-01-08
	 *	@updated 2015-01-08
	*/
	function cdbr_post_exists( $post_id = null, $blog_id = null ) {

		$highlights = get_site_option("cdbr_highlights_mu");

		foreach ($highlights as $key => $highlight) {
			if( $post_id == $highlight['post_id'] && $blog_id == $highlight['blog_id'] ) {
				return true;
			}
		}

		return false;
	}	

	/*
	 *	
	 *
	 *	@name    cdbr_get_post_by_blog
	 *	@author  Cleber Santos <oclebersantos@gmailcom>
	 *	@since   2015-01-08
	 *	@updated 2015-01-08
	*/
	function cdbr_get_post_by_blog( $post_id = null, $blog_id = null ) {
		
		if( empty( $post_id ) or empty( $blog_id ) )
			return;

		if ( !is_multisite() )
			return;

		// recupera os dados de configuração da widget
		$highlights_options = get_site_option( 'cdbr_highlights_mu_options' );

		if( function_exists( 'switch_to_blog' ) ) switch_to_blog( $blog_id );

			global $post;

			$post = get_post( $post_id );
			
			$temp = $post;

			setup_postdata( $post );
			
			$post_title = cdbr_limit_chars( get_the_title(), $highlights_options['limit_title'] );
			$post_excerpt = cdbr_limit_chars( get_the_excerpt(), $highlights_options['limit_excerpt'] );

			wp_reset_postdata();

			$post = $temp;

			// troca o resumo
			$post->post_title = $post_title;
			$post->post_excerpt = $post_excerpt;

		if( function_exists( 'switch_to_blog' ) ) restore_current_blog();

		return $post;
	}

	/*
	 *	
	 *
	 *	@name    cdbr_show_manage
	 *	@author  Cleber Santos <oclebersantos@gmailcom>
	 *	@since   2015-01-08
	 *	@updated 2015-01-08
	*/
	function cdbr_show_manage() {

		// check permissions
		if( !current_user_can( $this->capability ) )
		 	return false;

		echo '<div id="highlights" class="wrap nosubsub">';
			echo '<h2></h2>';
			$this->show_manage_order_highlights();
			$this->show_highlights_last_activities();
		echo '</div>';
	}

	/**
	 * show highlights
	 *
	 * @name    show_manage_order_highlights
	 * @author  Marcelo Mesquita <marcelo.costa@cultura.gov.br>
	 * @since   2009-12-08
	 * @updated 2015-04-08
	 * @return  void
	 */
	function show_manage_order_highlights()
	{	
		// check permissions
		if( !current_user_can( $this->capability ) )
		 	return false;
	
		global $wpdb;

		$blog_id = get_current_blog_id();

		// recuperar os dados do banco
		$highlights = stripslashes_deep( get_site_option( 'cdbr_highlights_mu' ) );

		if( !is_array( $highlights ) )
			$highlights = array();
	
		?>
			<div class="widget-liquid-left">

				<h2><?php _e( 'Destaques da Rede', 'highlights_mu' ); ?></h2>

				<div id="highlights-loading" style="display:none;"></div>
				<p><?php _e( 'Arraste um post para reodernar ou clique em adicionar para destacar um novo post', 'highlights_mu' ); ?></p>
				
				<ul id="highlights-sortable" class="highlights-sortable">
					<?php for( $a = 1; $a < 10; $a++ ) : ?>
						<li>
							<input type="hidden" id="order" name="order" value="<?php print $a; ?>" size="4" />
							<input type="hidden" id="post_id" name="post_id" value="<?php print ( empty( $highlights[ $a ][ 'post_id' ] ) ) ? '0' : $highlights[ $a ][ 'post_id' ]; ?>" size="4" />
							<input type="hidden" id="blog_id" name="blog_id" value="<?php print ( empty( $highlights[ $a ][ 'blog_id' ] ) ) ? '0' : $highlights[ $a ][ 'blog_id' ]; ?>" size="4" />
							<input type="hidden" id="highlight_title" name="highlight_title" value="<?php print ( empty( $highlights[ $a ][ 'highlight_title' ] ) ) ? '0' : $highlights[ $a ][ 'highlight_title' ];  ?>" size="4"/>
							<input type="hidden" id="highlight_excerpt" name="highlight_excerpt" value="<?php print ( empty( $highlights[ $a ][ 'highlight_excerpt' ] ) ) ? '0' : $highlights[ $a ][ 'highlight_excerpt' ];  ?>" size="4"/>
							
							<span class="title">
								<?php 
								if ( empty( $highlights[ $a ][ 'post_id' ] ) )
									_e( 'No posts' );
								else 
									echo '<a href=" ' . get_site_url( $highlights[ $a ][ "blog_id"] ) . '/wp-admin/post.php?action=edit&post=' . $highlights[ $a ][ "post_id" ] . '" class="highlights-edit">' . $highlights[ $a ][ "highlight_title" ] . '</a>';
								?>
							</span>							

							<span class="excerpt">
								<br><?php print ( empty( $highlights[ $a ][ 'highlight_excerpt' ] ) ) ? "" : $highlights[ $a ][ 'highlight_excerpt' ]; ?>
							</span>

							<div class="highlights-row-actions">
								<?php if( empty( $highlights[ $a ][ 'post_id' ] ) ) : ?>
									<a href="post-new.php" class="highlights-add"><?php _e( 'Add' ); ?></a>
								<?php else : ?>
									<a href="<?php echo get_site_url( $highlights[ $a ][ 'blog_id'] ); ?>/wp-admin/post.php?action=edit&post=<?php print $highlights[ $a ][ 'post_id' ]; ?>" class="highlights-edit"><?php _e( 'Edit' ); ?></a> |
									<a href="#" class="highlights-delete"><?php _e( 'Remove' ); ?></a>
								<?php endif; ?>
							</div>
						</li>
					<?php endfor; ?>
				</ul>
			</div>

		<?php
		
	}

	/**
	 * Últimas atividades da rede
	 * plugin buddypress tem que estar ativo
	 *
	 * @name    highlights_last_activities
	 * @author  Cleber Santos <oclebersantos@gmail.com>
	 * @since   2015-04-08
	 * @updated 2015-04-08
	 * @return  bool
	 */
	function show_highlights_last_activities() {

		// check permissions
		if( !current_user_can( $this->capability ) )
		 	return false;

		// se o buddypress não estiver ativo  
		if( !function_exists('bp_is_active') )
			return false;

		// mostrar apenas novos posts
		$query_string  = '&action=new_blog_post';

	    // máximo de atividades
		$query_string .= '&max=18';

		// para mostrar apenas 9 posts
		$max = (int) 9;
		$i = 1;

		?>
		<div class="widget-liquid-right">

			<h2><?php _e( 'Últimos posts da rede', 'highlights_mu' ); ?></h2>
			<p><?php _e( 'Para destacar arraste para a lista da esquerda', 'highlights_mu' ); ?></p>

			<?php  if ( bp_has_activities( bp_ajax_querystring( 'activity' ) . $query_string  ) ) :  ?>

				<ul id="highlights-activities-sortable" class="highlights-sortable">

					<?php while ( bp_activities() ) : bp_the_activity(); ?>
						
						<?php 

						// verifica se esse post já está destacado
						if( $this->cdbr_post_exists( bp_get_activity_secondary_item_id(), bp_get_activity_item_id() ) ) continue;  ?>
						
						<?php 
						// detalhes do blog
						$blog_details = get_blog_details( bp_get_activity_item_id() );

						// carrega o post para mostrar o título e resumo
						$post = $this->cdbr_get_post_by_blog( bp_get_activity_secondary_item_id(), bp_get_activity_item_id() ); ?>

						<li>	
							<input type="hidden" id="order" name="order" value="" size="4" />
							<input type="hidden" id="post_id" name="post_id" value="<?php print $post->ID; ?>" size="4" />
							<input type="hidden" id="blog_id" name="blog_id" value="<?php print bp_get_activity_item_id(); ?>" size="4" />
							<input type="hidden" id="highlight_title" name="highlight_title" value="<?php print $post->post_title; ?>" size="4"/>
							<input type="hidden" id="highlight_excerpt" name="highlight_excerpt" value="<?php print $post->post_excerpt; ?>" size="4"/>

							<span class="title">
								<a href="<?php print $post->guid; ?>" title="<?php print $post->post_title; ?>"><?php print $post->post_title; ?></a><span clas="no-blog"> no blog <strong><?php  echo $blog_details->blogname; ?></strong></span>
							</span>

							<span class="excerpt">
								<br><?php print $post->post_excerpt;  ?>
							</span>

							<div class="highlights-row-actions">
								<a href="<?php echo get_site_url( bp_get_activity_item_id() ); ?>/wp-admin/post.php?action=edit&post=<?php print $post->ID; ?>" class="highlights-edit"><?php _e( 'Edit' ); ?></a>
								<span class="separator">|</span> 
								<a href="#" class="highlights-delete"><?php _e( 'Remove' ); ?></a>
							</div>
						</li>
						<?php if( $i == $max ) break; else $i++; ?>

					<?php endwhile; ?>
				</ul>

			<?php else : ?>

				<div id="message" class="info">
					<p><?php _e( 'Sorry, there was no activity found. Please try a different filter.', 'buddypress' ); ?></p>
				</div>

			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * order headlines
	 *
	 * @name    order_highlights
	 * @author  Cleber Santos <oclebersantos@gmail.com>
	 * @since   2015-04-05
	 * @updated 2015-04-05
	 * @return  void
	 */
	function order_highlights()
	{				
		// check permissions
		if( !current_user_can( $this->capability ) )
		 	return false;

		check_ajax_referer( 'highlights_nonce', 'nonce' );

		global $wpdb;

		$new_highlights = array();

		$ordered = $_POST[ 'order' ];
	
		// recuperar os dados do banco
		$highlights = (array) get_site_option( 'cdbr_highlights_mu' );

		foreach( $ordered as $key => $order ) {
			
			if( !$order['post_id'] == 0 or !$order['blog_id'] == 0 ) {
				if( strlen($order['highlight_excerpt']) <= 1 )
					$order['highlight_excerpt'] = "";

				$new_highlights[ $key + 1 ] = $order;
			} 
				

		}

		ksort( $new_highlights );

		// salvar os dados no banco
		update_site_option( 'cdbr_highlights_mu', $new_highlights );

		if( true )
            wp_send_json_success( 'true' );
        else
            wp_send_json_error( array( 'error' => $custom_error ) );
		
		return false;

		die(); // this is required to terminate immediately and return a proper response
	}


	/**
	 * Registrar metaboxes
	 *
	 * @name    highlights_metaboxes
	 * @author  Cleber Santos <oclebersantos@gmail.com>
	 * @since   2015-04-01
	 * @updated 2015-04-01
	 * @return  void
	 */
	function highlights_metaboxes()
	{
		// check permissions
		if( !current_user_can( $this->capability ) )
		 	return false;

		add_meta_box( 'highlights', __( 'Destaques da Rede', 'highlights_mu' ), array( &$this, 'highlights_metabox' ), 'post', 'side', 'high' );
	}

	/**
	 * Highlights metabox
	 *
	 * @name    highlights_metabox
	 * @author  Cleber Santos <oclebersantos@gmail.com>
	 * @since   2015-04-01
	 * @updated 2015-04-01
	 * @param   Object $post post data
	 * @return  void
	 */
	function highlights_metabox( $post ) 
	{

		// check permissions
		if( !current_user_can( $this->capability ) )
		 	return false;

		global $wpdb;

		$blog_id = get_current_blog_id();

		wp_nonce_field( 'cdbr_highlights_meta_box', 'cdbr_highlights_meta_box_nonce' );

		$highlights = array();
		$highlight_title = "";
		$highlight_excerpt = "";

		// recuperar os dados do banco
		$highlights = stripslashes_deep( get_site_option( 'cdbr_highlights_mu' ) );

		// if( !is_array( $highlights ) )
		// 	$highlights = array();

		?>
		<input type="hidden" name="cdbr_current_blog" id="cdbr_current_blog" value="<?php print get_current_blog_id(); ?>" />
		<input type="hidden" name="cdbr_highlights-nonce" id="cdbr_highlights-nonce" value="<?php print wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />

		<?php for( $a = 1; $a <= 10; $a++ ) : ?>
			<?php if( isset($highlights[ $a ]) && $post->ID == $highlights[ $a ][ 'post_id' ] && $blog_id == $highlights[ $a ][ 'blog_id' ] ): ?>
				<?php $highlight_title = $highlights[ $a ][ 'highlight_title' ]; ?>
				<?php $highlight_excerpt = $highlights[ $a ][ 'highlight_excerpt' ]; ?>
			<?php endif; ?>
		<?php endfor; ?>

		<p>
			<label for="highlight-title"><?php print _e( 'Título do destaque', 'highlights_mu' ); ?></label>
			<input type="text" name="highlight_title" id="highlight_title" value="<?php print $highlight_title; ?>" class="widefat" />
		</p>

		<p>
			<label for="highlight-excerpt"><?php print _e( 'Resumo do destaque', 'highlights_mu' ); ?></label>
			<input type="text" name="highlight_excerpt" id="highlight_excerpt" value="<?php print $highlight_excerpt; ?>" class="widefat" />
		</p>

		<p>
			<label for="highlights_order"><?php _e( 'Ordem', 'highlights_mu' ); ?></label>
			<select name="highlights_order" id="highlights_order">
				<option value="0"><?php _e( 'selecione', 'highlights_mu' ); ?></option>
				<?php for( $a = 1; $a <= 10; $a++ ) : ?>
					<option value="<?php print $a; ?>" <?php if( isset($highlights[ $a ]) && $post->ID == $highlights[ $a ][ 'post_id' ] && $blog_id == $highlights[ $a ][ 'blog_id' ] ) print 'selected="selected"'; ?>>
						<?php print $a; ?> <?php if( !empty( $highlights[ $a ][ 'highlight_title' ] ) ) print " - {$highlights[ $a ][ 'highlight_title' ]}"; ?>
					</option>
				<?php endfor; ?>
			</select>
		</p>

		<?php
	}

	/**
	 * save metabox options
	 *
	 * @name    highlights_metabox_save
	 * @author  Cleber Santos <oclebersantos@gmail.com>
	 * @since   2015-04-01
	 * @updated 2015-04-01
	 * @return  bool
	 */
	function highlights_metabox_save( $post_id, $post, $updated )
	{
		// check permissions
		if( !current_user_can( $this->capability ) )
		 	return false;

		// Check if our nonce is set.
		if ( ! isset( $_POST['cdbr_highlights_meta_box_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['cdbr_highlights_meta_box_nonce'], 'cdbr_highlights_meta_box' ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		$cdbr_current_blog =  ( int ) $_POST[ 'cdbr_current_blog' ];

		// evita que o plugin site wide tags sobreponha o post, aqui passa o id do blog do post original
		if( $cdbr_current_blog != get_current_blog_id() )
			return;

		// validar dados
		$order      	= ( int ) $_POST[ 'highlights_order' ];
		$highlights     = array();
		$new_highlights = array();

		$current_blog 	= get_current_blog_id();
		$post_id    	= $post_id;
		$temp 			= $post;
		$post   = get_post( $post_id );

		setup_postdata( $post );

		$highlights_options = get_site_option( 'cdbr_highlights_mu_options' );
		
		$post_title = cdbr_limit_chars( get_the_title(), $highlights_options['limit_title'] );
		// $post_excerpt = cdbr_limit_chars( get_the_excerpt(), $highlights_options['limit_excerpt'] );

		$post_title 	= !empty( $_POST[ 'highlight_title' ] ) ? $_POST[ 'highlight_title' ] : $post_title ;
		$post_excerpt  	= !empty( $_POST[ 'highlight_excerpt' ] ) ? $_POST[ 'highlight_excerpt' ] : "";

		wp_reset_postdata();
		$post = $temp;

		if( !$real_post_id = wp_is_post_revision( $post_id ) )
			$real_post_id = $post_id;

		// recuperar os dados do banco
		$highlights = get_site_option( 'cdbr_highlights_mu', $highlights );

		
		foreach( $highlights as $key => $highlight )
		{
			if( $real_post_id != $highlight[ 'post_id' ] or $current_blog != $highlight[ 'blog_id' ] )
				$new_highlights[ $key ] = $highlight;
		}

		if( $order )
			$new_highlights[ $order ] = array( 'blog_id' => $current_blog, 'post_id' => $real_post_id, 'highlight_title' => $post_title, 'highlight_excerpt' => $post_excerpt  );

	
		ksort( $new_highlights );

		// salvar os dados no banco
		update_site_option( 'cdbr_highlights_mu', $new_highlights );

		return false;
	}

	// CONSTRUTOR ///////////////////////////////////////////////////////////////////////////////////
	/*
	 *	@name    HighlightsMU
	 *	@author  Cleber Santos <oclebersantos@gmailcom>
	 *	@since   2015-01-08
	 *	@updated 2015-01-08
	 *	@return  void
	*/
	function __construct()
	{	

		// install o plugin
		register_activation_hook( __FILE__, array( &$this, 'install' ) );

		// uninstall plugin
		register_deactivation_hook( __FILE__, array( &$this, 'uninstall' ) );

		// load languages
		load_plugin_textdomain( 'highlights_mu', false, $this->path . 'lang/' );

		// thumbnails
		add_theme_support( 'post-thumbnails' );

		// padroniza imagens de todosos blogs
		add_image_size( 'highlight', 400, 400, true );
		add_image_size( 'highlight-small', 350, 220, true );
			
		// adicionando o menu
		add_action( 'admin_menu', array(&$this, 'menus'));

		// adicionando o formulário na tela de edição de posts
		add_action( 'do_meta_boxes', array( &$this, 'highlights_metaboxes' ) );

		// salvar os dados do formulário quando os posts forem salvos
		add_action( 'save_post', array( &$this, 'highlights_metabox_save' ), 8, 3 );

		add_action( 'wp_ajax_order_highlights', array( $this, 'order_highlights' ) );
		add_action( 'wp_ajax_nopriv_order_highlights', array( $this, 'order_highlights' ) );

		add_action( 'wp_ajax_delete_highlights', array( $this, 'delete_highlights' ) );
		add_action( 'wp_ajax_nopriv_delete_highlights', array( $this, 'delete_highlights' ) );

		// widgets
		require( $this->path . 'highlights-mu-widget.php' );

		if( !function_exists( 'cdbr_limit_chars' ) )
			include( dirname( __FILE__ ) . '/inc/limit-chars.php' );
	}

}

$HighlightsMU = new HighlightsMU();


?>