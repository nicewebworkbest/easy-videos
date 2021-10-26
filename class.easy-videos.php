<?php
/**
 * Easy Videos class.
 */
class EasyVideos {

	/**
	 * Initialization.
	 */
	public function init() {
		load_plugin_textdomain( 'easy-videos', false, EASY_VIDEOS_PLUGIN_DIR . '/languages/' );

		// Add actions.
		add_action( 'init', array( $this, 'add_custom_post_types' ) );
		add_action( 'init', array( $this, 'add_custom_taxonomies' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_items' ) );
		add_action( 'wp_ajax_import_video', array( $this, 'ajax_import_video' ) );
	}

	/**
	 * Add custom post types for the plugin. Ex: video.
	 */
	public static function add_custom_post_types() {
		$labels = array(
			'name'                => _x( 'Videos', 'Post Type General Name', 'easy-videos' ),
			'singular_name'       => _x( 'Video', 'Post Type Singular Name', 'easy-videos' ),
			'menu_name'           => __( 'Videos', 'easy-videos' ),
			'all_items'           => __( 'All Videos', 'easy-videos' ),
			'view_item'           => __( 'View Video', 'easy-videos' ),
			'add_new_item'        => __( 'Add New Video', 'easy-videos' ),
			'add_new'             => __( 'New Video', 'easy-videos' ),
			'edit_item'           => __( 'Edit Videos', 'easy-videos' ),
			'update_item'         => __( 'Update Videos', 'easy-videos' ),
			'search_items'        => __( 'Search Videos', 'easy-videos' ),
			'not_found'           => __( 'No Videos found', 'easy-videos' ),
			'not_found_in_trash'  => __( 'No Videos found in Trash', 'easy-videos' ),
		);
		$args = array(
			'label'               => __( 'Video', 'easy-videos' ),
			'description'         => __( 'Video information pages', 'easy-videos' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'author' ),
			'taxonomies'          => array( ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		);

		register_post_type( 'video', $args );
	}

	/**
	 * Add custom taxonomies.
	 */
	public static function add_custom_taxonomies() {
		$labels = array(
				'name'							=> _x( 'Video Categories', 'taxonomy general name', 'easy-videos' ),
				'singular_name'					=> _x( 'Video Category', 'taxonomy singular name', 'easy-videos' ),
				'menu_name'						=> __( 'Video Categories', 'easy-videos' ),
				'all_items'						=> __( 'All Video Categories', 'easy-videos' ),
				'parent_item'       			=> null,
				'parent_item_colon'				=> null,
				'new_item_name'					=> __( 'New Video Category', 'easy-videos' ),
				'add_new_item'					=> __( 'Add New Video Category', 'easy-videos' ),
				'edit_item'						=> __( 'Edit Video Category', 'easy-videos' ),
				'update_item'					=> __( 'Update Video Category', 'easy-videos' ),
				'separate_items_with_commas'	=> __( 'Separate video categories with commas', 'easy-videos' ),
				'search_items'					=> __( 'Search Video Categories', 'easy-videos' ),
				'add_or_remove_items'			=> __( 'Add or remove video categories', 'easy-videos' ),
				'choose_from_most_used'			=> __( 'Choose from the most used video categories', 'easy-videos' ),
				'not_found'						=> __( 'No video categories found.', 'easy-videos' ),
			);
		$args = array(
				'labels'            => $labels,
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'meta_box_cb'       => false
			);
		register_taxonomy( 'video-category', array( 'video' ), $args );
	}

	/*
	 * Add video import page to menu.
	 */
	function add_menu_items() {
		$page = add_submenu_page( 'edit.php?post_type=video', esc_html__( 'Import Videos', 'easy-videos' ), esc_html__( 'Import Videos', 'easy-videos' ), 'edit_posts', 'import_videos', array( $this, 'render_import_videos_page' ) );

		add_action( 'admin_print_scripts-' . $page, array( $this, 'admin_import_video_page_enqueue_scripts' ) );
	}

	/*
	 * Render Youtube video import page.
	 */
	function admin_import_video_page_enqueue_scripts() {
		wp_enqueue_script( 'admin-import-video' , EASY_VIDEOS_PLUGIN_URI . 'js/import-video.js', array( 'jquery' ), '1.0', true );
		wp_localize_script( 'admin-import-video', 'js_vars', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}

	/*
	 * Import video via ajax.
	 */
	function ajax_import_video() {
		$google_api_key	= ( ! empty( $_POST['google_api_key'] ) ) ? $_POST['google_api_key'] : '';
		$channel_id		= ( ! empty( $_POST['channel_id'] ) ) ? $_POST['channel_id'] : '';
		$page_token		= ( ! empty( $_POST['page_token'] ) ) ? $_POST['page_token'] : '';

		if ( empty( $google_api_key ) || empty( $channel_id ) ) {
			$error_message = esc_html__( 'Please add Google API Key and Channel ID', 'easy-videos' );
			wp_send_json_error( array( 'message' => esc_html__( 'Please add Google API Key and Channel ID', 'easy-videos' ) ) );
		} else {
			$result = $this->import_videos( $google_api_key, $channel_id, $page_token );
			wp_send_json_success( $result );
		}
	}

	/*
	 * Render Youtube video import page.
	 */
	function render_import_videos_page() {
		?>
		<div class="wrap">
			<h2><?php echo esc_html__( 'Import videos from Youtube channel.'); ?></h2>

			<div class="form-wrapper">
				<form class="import-form" id="import-form" method="post">
					<input type="hidden" name="action" value="import_video">
					<input type="hidden" id="page-token" name="page_token" value="">
					<div class="control-wrapper">
						<label for="google-api-key"><?php echo esc_html__( 'Google API Key', 'easy-videos' ); ?>:</label>
						<input type="text" id="google-api-key" name="google_api_key">
					</div>
					<div class="control-wrapper">
						<label for="channel-id"><?php echo esc_html__( 'Youtube Channel ID', 'easy-videos' ); ?>:</label>
						<input type="text" id="channel-id" name="channel_id">
					</div>
					<div class="control-wrapper">
						<button type="submit"><?php echo esc_html__( 'Import', 'easy-videos' ); ?></button>
					</div>
				</form>
			</div>

			<div class="result"></div>
		</div>
		<?php
	}

	/*
	 * Import Youtube videos.
	 *
	 * @param string $google_api_key	Google API Key.
	 * @param string $channel_id		Youtube Video Channel ID.
	 *
	 * @return bool
	 */
	function import_videos( $google_api_key, $channel_id, $page_token = '' ) {

		$Max_Results = 10;

		// Set initial video channel url.
		$channel_url = 'https://www.googleapis.com/youtube/v3/search?order=date&part=snippet&channelId=' . $channel_id . '&maxResults=' . $Max_Results . '&key=' . $google_api_key;
		$video_url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet&key=' . $google_api_key;
		$video_category_url = 'https://www.googleapis.com/youtube/v3/videoCategories?part=snippet&key=' . $google_api_key;

		if ( ! empty( $page_token ) ) {
			$channel_url = $channel_url . '&pageToken=' . $page_token;
		}
		// Get video from the channel.
		$apiData = @file_get_contents( $channel_url );
		if ( $apiData ) {
			$videoList = json_decode( $apiData );

			if ( ! empty( $videoList->items ) ) {
				foreach( $videoList->items as $item ) {

					if( isset( $item->id->videoId ) ) {

						// Check the video is already imported.
						if ( $this->video_exist( $item->id->videoId ) ) {
							continue;
						}

						$video_categories = array();

						// Get video information.
						$video_infos = json_decode( file_get_contents( $video_url . '&id=' . $item->id->videoId ) );
						if ( isset( $video_infos->items[0] ) ) {
							$video_info = $video_infos->items[0];
							$video_category_id = $video_info->snippet->categoryId;

							// Get video category.
							$category_infos = json_decode( file_get_contents( $video_category_url . '&id=' . $video_category_id ) );
							if ( isset( $category_infos->items ) ) {
								foreach( $category_infos->items as $category ) {
									$video_categories[] = $category->snippet->title;
								}
							}
						}

						$video_embed_code = '<iframe width="280" height="150" src="https://www.youtube.com/embed/' . $item->id->videoId . '" frameborder="0" allowfullscreen></iframe>';
						$video_title = $item->snippet->title;
						$video_id = $item->id->videoId;

						// Save to the post.
						$this->save_video_to_post( $video_id, $video_title, $video_embed_code, $video_categories );
					}
				}

				$next_page_token = '';
				$message = esc_html__( 'Done!', 'easy-videos' );

				if ( ! empty( $videoList->nextPageToken ) ) {
					$next_page_token = $videoList->nextPageToken;
					$message = esc_html__( '10 videos imported!', 'easy-videos' );
				}
				return array( 'success' => true, 'message' => $message, 'next_page_token' => $next_page_token );
			} else {
				return array( 'success' => true, 'message' => esc_html__( 'Done!', 'easy-videos' ), 'next_page_token' => '' );
			}
		} else {
			return array( 'success' => false, 'message' => esc_html__( 'Invalid API key or channel ID.', 'easy-videos' ), 'next_page_token' => '' );
		}
	}

	/*
	 * Save Youtube video.
	 *
	 * @param string $video_id			Youtube Video ID.
	 * @param string $video_title		Youtube Video Title.
	 * @param string $video_embed_code	Youtube Video Embed Code.
	 * @param array  $video_categories	Youtube Video Categories.
	 */
	function save_video_to_post( $video_id, $video_title, $video_embed_code, $video_categories ) {
		$video_post_id = wp_insert_post( array(
			'post_title'	=> $video_title,
			'post_content'	=> $video_embed_code,
			'post_type'		=> 'video',
			'post_status'	=> 'publish',
			'meta_input'	=> array(
				'video_id'		=> $video_id
			)
		) );

		$video_terms = array();
		foreach ( $video_categories as $video_category ) {
			$exist_video_category = get_term_by( 'name', $video_category, 'video-category', ARRAY_A );
			if ( $exist_video_category ) {
				$video_terms[] = $exist_video_category['term_id'];
			} else {
				$exist_video_category = wp_insert_term( $video_category, 'video-category' );
				$video_terms[] = $exist_video_category['term_id'];
			}
		}
		wp_set_post_terms( $video_post_id, $video_terms, 'video-category' );
	}

	/*
	 * Check Youtube video exits in posts.
	 *
	 * @param string $video_id	Youtube Video ID.
	 *
	 * @return bool
	 *
	 */
	function video_exist( $video_id ) {
		$args = array(
			'post_type'		=> 'video',
			'meta_key'		=> 'video_id',
			'meta_value'	=> $video_id,
			'meta_compare'	=> '='
		);
		$videos = get_posts( $args );

		return ! empty( $videos );
	}
}
