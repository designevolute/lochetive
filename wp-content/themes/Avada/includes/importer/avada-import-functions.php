<?php
/**
 * A collection of functions used by the importer.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Importer
 * @since      5.2
 */

/**
 * Don't resize images.
 * Returns an empty array.
 *
 * @since 5.2
 * @param array $sizes We don't really care in this context...
 * @return array
 */
function avada_filter_image_sizes( $sizes ) {
	return array();
}


/**
 * Parsing Widgets Function
 *
 * @since 5.2
 * @see http://wordpress.org/plugins/widget-settings-importexport/
 * @param string $widget_data The widget-data, JSON-formatted.
 */
function fusion_import_widget_data( $widget_data ) {
	$json_data = json_decode( $widget_data, true );

	$sidebar_data = $json_data[0];
	$widget_data = $json_data[1];

	foreach ( $widget_data as $widget_data_title => $widget_data_value ) {
		$widgets[ $widget_data_title ] = array();
		foreach ( $widget_data_value as $widget_data_key => $widget_data_array ) {
			if ( is_int( $widget_data_key ) ) {
				$widgets[ $widget_data_title ][ $widget_data_key ] = 'on';
			}
		}
	}
	unset( $widgets[''] );

	foreach ( $sidebar_data as $title => $sidebar ) {
		$count = count( $sidebar );
		for ( $i = 0; $i < $count; $i++ ) {
			$widget = array();
			$widget['type'] = trim( substr( $sidebar[ $i ], 0, strrpos( $sidebar[ $i ], '-' ) ) );
			$widget['type-index'] = trim( substr( $sidebar[ $i ], strrpos( $sidebar[ $i ], '-' ) + 1 ) );
			if ( ! isset( $widgets[ $widget['type'] ][ $widget['type-index'] ] ) ) {
				unset( $sidebar_data[ $title ][ $i ] );
			}
		}
		$sidebar_data[ $title ] = array_values( $sidebar_data[ $title ] );
	}

	foreach ( $widgets as $widget_title => $widget_value ) {
		foreach ( $widget_value as $widget_key => $widget_value ) {
			$widgets[ $widget_title ][ $widget_key ] = $widget_data[ $widget_title ][ $widget_key ];
		}
	}

	$sidebar_data = array( array_filter( $sidebar_data ), $widgets );

	fusion_parse_import_data( $sidebar_data );
}

/**
 * Import data.
 *
 * @since 5.2
 * @param array $import_array The array of data to be imported.
 */
function fusion_parse_import_data( $import_array ) {
	global $wp_registered_sidebars;
	$sidebars_data = $import_array[0];
	$widget_data = $import_array[1];
	$current_sidebars = get_option( 'sidebars_widgets' );
	$new_widgets = array();

	foreach ( $sidebars_data as $import_sidebar => $import_widgets ) {

		foreach ( $import_widgets as $import_widget ) {
			// If the sidebar exists.
			if ( isset( $wp_registered_sidebars[ $import_sidebar ] ) ) {
				$title = trim( substr( $import_widget, 0, strrpos( $import_widget, '-' ) ) );
				$index = trim( substr( $import_widget, strrpos( $import_widget, '-' ) + 1 ) );
				$current_widget_data = get_option( 'widget_' . $title );
				$new_widget_name = fusion_get_new_widget_name( $title, $index );
				$new_index = trim( substr( $new_widget_name, strrpos( $new_widget_name, '-' ) + 1 ) );

				if ( ! empty( $new_widgets[ $title ] ) && is_array( $new_widgets[ $title ] ) ) {
					while ( array_key_exists( $new_index, $new_widgets[ $title ] ) ) {
						$new_index++;
					}
				}
				$current_sidebars[ $import_sidebar ][] = $title . '-' . $new_index;
				if ( array_key_exists( $title, $new_widgets ) ) {
					if ( 'nav_menu' == $title & ! is_numeric( $index ) ) {
						$menu = wp_get_nav_menu_object( $index );
						$menu_id = $menu->term_id;
						$new_widgets[ $title ][ $new_index ] = $menu_id;
					} else {
						$new_widgets[ $title ][ $new_index ] = $widget_data[ $title ][ $index ];
					}
					$multiwidget = $new_widgets[ $title ]['_multiwidget'];
					unset( $new_widgets[ $title ]['_multiwidget'] );
					$new_widgets[ $title ]['_multiwidget'] = $multiwidget;
				} else {
					if ( 'nav_menu' == $title & ! is_numeric( $index ) ) {
						$menu = wp_get_nav_menu_object( $index );
						$menu_id = $menu->term_id;
						$current_widget_data[ $new_index ] = $menu_id;
					} else {
						$current_widget_data[ $new_index ] = $widget_data[ $title ][ $index ];
					}
					$current_multiwidget = isset( $current_widget_data['_multiwidget'] ) ? $current_widget_data['_multiwidget'] : false;
					$new_multiwidget = isset( $widget_data[ $title ]['_multiwidget'] ) ? $widget_data[ $title ]['_multiwidget'] : false;
					$multiwidget = ( $current_multiwidget != $new_multiwidget) ? $current_multiwidget : 1;
					unset( $current_widget_data['_multiwidget'] );
					$current_widget_data['_multiwidget'] = $multiwidget;
					$new_widgets[ $title ] = $current_widget_data;
				}
			} // End if().
		} // End foreach().
	} // End foreach().

	if ( isset( $new_widgets ) && isset( $current_sidebars ) ) {
		update_option( 'sidebars_widgets', $current_sidebars );

		foreach ( $new_widgets as $title => $content ) {
			update_option( 'widget_' . $title, $content );
		}
		return true;
	}
	return false;
}

/**
 * Get the new widget name.
 *
 * @since 5.2
 * @param string $widget_name  The widget-name.
 * @param int    $widget_index The index of the widget.
 * @return array
 */
function fusion_get_new_widget_name( $widget_name, $widget_index ) {
	$current_sidebars = get_option( 'sidebars_widgets' );
	$all_widget_array = array();
	foreach ( $current_sidebars as $sidebar => $widgets ) {
		if ( ! empty( $widgets ) && is_array( $widgets ) && 'wp_inactive_widgets' != $sidebar ) {
			foreach ( $widgets as $widget ) {
				$all_widget_array[] = $widget;
			}
		}
	}
	while ( in_array( $widget_name . '-' . $widget_index, $all_widget_array ) ) {
		$widget_index++;
	}
	$new_widget_name = $widget_name . '-' . $widget_index;
	return $new_widget_name;
}

if ( function_exists( 'layerslider_import_sample_slider' ) ) {
	/**
	 * Import LayerSlider.
	 *
	 * @since 5.2
	 * @param mixed $layerslider_data The data.
	 */
	function avada_import_sample_slider( $layerslider_data ) {
		// Base64 encoded, serialized slider export code.
		$sample_slider = $layerslider_data;

		// Iterate over the sliders.
		foreach ( $sample_slider as $sliderkey => $slider ) {

			// Iterate over the layers.
			foreach ( $sample_slider[ $sliderkey ]['layers'] as $layerkey => $layer ) {

				// Change background images if any.
				if ( ! empty( $sample_slider[ $sliderkey ]['layers'][ $layerkey ]['properties']['background'] ) ) {
					$sample_slider[ $sliderkey ]['layers'][ $layerkey ]['properties']['background'] = LS_ROOT_URL . 'sampleslider/' . basename( $layer['properties']['background'] );
				}

				// Change thumbnail images if any.
				if ( ! empty( $sample_slider[ $sliderkey ]['layers'][ $layerkey ]['properties']['thumbnail'] ) ) {
					$sample_slider[ $sliderkey ]['layers'][ $layerkey ]['properties']['thumbnail'] = LS_ROOT_URL . 'sampleslider/' . basename( $layer['properties']['thumbnail'] );
				}

				// Iterate over the sublayers.
				if ( isset( $layer['sublayers'] ) && ! empty( $layer['sublayers'] ) ) {
					foreach ( $layer['sublayers'] as $sublayerkey => $sublayer ) {

						// Only IMG sublayers.
						if ( 'img' == $sublayer['type'] ) {
							$sample_slider[ $sliderkey ]['layers'][ $layerkey ]['sublayers'][ $sublayerkey ]['image'] = LS_ROOT_URL . 'sampleslider/' . basename( $sublayer['image'] );
						}
					}
				}
			}
		}

		// Get WPDB Object.
		global $wpdb;

		// Table name.
		$table_name = $wpdb->prefix . 'layerslider';

		// Append duplicate.
		foreach ( $sample_slider as $key => $val ) {

			// Insert the duplicate.
			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO $table_name (name, data, date_c, date_m) VALUES (%s, %s, %d, %d)",
					$val['properties']['title'],
					wp_json_encode( $val ),
					time(),
					time()
				)
			);
		}
	}
} // End if().

/**
 * Rename sidebar.
 *
 * @since 5.2
 * @param string $name The name.
 * @return string
 */
function avada_name_to_class( $name ) {
	$class = str_replace( array( ' ', ',', '.', '"', "'", '/', '\\', '+', '=', ')', '(', '*', '&', '^', '%', '$', '#', '@', '!', '~', '`', '<', '>', '?', '[', ']', '{', '}', '|', ':' ), '', $name );
	return $class;
}

/**
 * Import Fusion Sliders.
 *
 * @since 5.2
 * @param string $zip_file  The path to the zip file.
 * @param string $demo_type The demo name.
 */
function avada_import_fsliders( $zip_file, $demo_type = '' ) {
	$upload_dir = wp_upload_dir();
	$base_dir   = trailingslashit( $upload_dir['basedir'] );
	$fs_dir     = $base_dir . 'fusion_slider_exports/';
	$home_url   = untrailingslashit( get_home_url() );

	// In 'classic' demo case 'avada-xml' should be used for replacements.
	if ( 'classic' === $demo_type ) {
		$demo_type = 'avada-xml';
	}
	$demo_type = str_replace( '_', '-', $demo_type );

	// Init the filesystem.
	$filesystem = Fusion_Helper::init_filesystem();

	// Delete existing folder.
	$filesystem->delete( $fs_dir, true, 'd' );

	// Unzip file to folder.
	unzip_file( $zip_file, $fs_dir );

	// Replace remote URLs with local ones.
	$sliders_xml = $filesystem->get_contents( $fs_dir . 'sliders.xml' );

	// Replace URLs.
	$sliders_xml = str_replace(
		array(
			'http://avada.theme-fusion.com/' . $demo_type,
			'https://avada.theme-fusion.com/' . $demo_type,
		),
		$home_url,
		$sliders_xml
	);

	// Make sure assets are still from the remote server.
	// We can use http instead of https here for performance reasons
	// since static assets don't require https anyway.
	$sliders_xml = str_replace(
		$home_url . '/wp-content/',
		'http://avada.theme-fusion.com/' . $demo_type . '/wp-content/',
		$sliders_xml
	);

	$sliders_xml = preg_replace_callback( '/(?<=<wp:meta_value><!\[CDATA\[)(https?:\/\/avada.theme-fusion.com)+(.*?)(?=]]><)/', 'fusion_fs_importer_replace_url', $sliders_xml );
	$filesystem->put_contents( $fs_dir . 'sliders.xml', $sliders_xml );

	$loop = new WP_Query( array(
		'post_type'      => 'slide',
		'posts_per_page' => -1,
		'meta_key'       => '_thumbnail_id',
	) );

	while ( $loop->have_posts() ) { $loop->the_post();
		$thumbnail_ids[ get_post_meta( get_the_ID(), '_thumbnail_id', true ) ] = get_the_ID();
	}

	if ( is_dir( $fs_dir ) ) {
		foreach ( new DirectoryIterator( $fs_dir ) as $file ) {
			if ( $file->isDot() || $file->getFilename() == '.DS_Store' ) {
				continue;
			}

			$image_path = pathinfo( $fs_dir . $file->getFilename() );
			if ( 'xml' != $image_path['extension'] && 'json' != $image_path['extension'] ) {
				$filename = $image_path['filename'];
				$new_image_path = $upload_dir['path'] . '/' . $image_path['basename'];
				$new_image_url = $upload_dir['url'] . '/' . $image_path['basename'];
				@copy( $fs_dir . $file->getFilename(), $new_image_path );

				// Check the type of tile. We'll use this as the 'post_mime_type'.
				$filetype = wp_check_filetype( basename( $new_image_path ), null );

				// Prepare an array of post data for the attachment.
				$attachment = array(
					'guid'		   => $new_image_url,
					'post_mime_type' => $filetype['type'],
					'post_title'	 => preg_replace( '/\.[^.]+$/', '', basename( $new_image_path ) ),
					'post_content'   => '',
					'post_status'	=> 'inherit',
				);

				// Insert the attachment.
				$attach_id = wp_insert_attachment( $attachment, $new_image_path, $thumbnail_ids[ $filename ] );

				// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
				require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/image.php' );

				// Generate the metadata for the attachment, and update the database record.
				$attach_data = wp_generate_attachment_metadata( $attach_id, $new_image_path );
				wp_update_attachment_metadata( $attach_id, $attach_data );

				set_post_thumbnail( $thumbnail_ids[ $filename ], $attach_id );
			}
		} // End foreach().
	} // End if().

	$url = wp_nonce_url( 'edit.php?post_type=slide&page=fs_export_import' );
	$creds = request_filesystem_credentials( $url, '', false, false, null );
	if ( false === $creds ) {
		return; // Stop processing here.
	}

	if ( WP_Filesystem( $creds ) ) {
		global $wp_filesystem;

		$settings = $wp_filesystem->get_contents( $fs_dir . 'settings.json' );

		$decode = json_decode( $settings, true );

		foreach ( $decode as $slug => $settings ) {
			$get_term = get_term_by( 'slug', $slug, 'slide-page' );

			if ( $get_term ) {
				update_option( 'taxonomy_' . $get_term->term_id, $settings );
			}
		}
	}
}

/**
 * Delete a slider directory.
 *
 * @since 5.2
 * @param string $dir_path The absolute path to the directory.
 * @throws InvalidArgumentException The exception.
 */
function fusion_slider_delete_dir( $dir_path ) {
	if ( ! is_dir( $dir_path ) ) {
		$message = sprintf( esc_html__( '%s must be a directory', 'Avada' ), $dir_path );
		throw new InvalidArgumentException( $message );
	}
	if ( '/' != substr( $dir_path, strlen( $dir_path ) - 1, 1 ) ) {
		$dir_path .= '/';
	}
	$files = fusion_get_import_files( $dir_path, '*' );

	foreach ( $files as $file ) {
		if ( is_dir( $file ) ) {
			$this->deleteDir( $file );
		} else {
			// @codingStandardsIgnoreLine
			@unlink( $file );
		}
	}
	// @codingStandardsIgnoreLine
	@rmdir( $dir_path );
}

/**
 * Returns all files in directory with the given filetype. Uses glob() for older
 * php versions and recursive directory iterator otherwise.
 *
 * @since 5.2
 * @param string $directory Directory that should be parsed.
 * @param string $filetype  The file type.
 * @return array $files     File names that match the $filetype.
 */
function fusion_get_import_files( $directory, $filetype ) {
	$phpversion = phpversion();
	$files = array();

	// Check if the php version allows for recursive iterators.
	if ( version_compare( $phpversion, '5.2.11', '>' ) ) {
		if ( '*' !== $filetype ) {
			$filetype = '/^.*\.' . $filetype . '$/';
		} else {
			$filetype = '/.+\.[^.]+$/';
		}
		$directory_iterator = new RecursiveDirectoryIterator( $directory );
		$recusive_iterator = new RecursiveIteratorIterator( $directory_iterator );
		$regex_iterator = new RegexIterator( $recusive_iterator, $filetype );

		foreach ( $regex_iterator as $file ) {
			$files[] = $file->getPathname();
		}
	} else {
		if ( '*' !== $filetype ) {
			$filetype = '*.' . $filetype;
		}

		foreach ( glob( $directory . $filetype ) as $filename ) {
			$filename = basename( $filename );
			$files[] = $directory . $filename;
		}
	}

	return $files;
}

/**
 * Replaces URLs.
 *
 * @since 5.2
 * @param array $matches The matches.
 * @return string
 */
function fusion_fs_importer_replace_url( $matches ) {
	// Get the uploads folder.
	$wp_upload_dir = wp_upload_dir();
	if ( is_array( $matches ) ) {
		foreach ( $matches as $key => $match ) {
			if ( false !== strpos( $match, 'wp-content/uploads/sites/' ) ) {
				$parts = explode( 'wp-content/uploads/sites/', $match );
				if ( isset( $parts[1] ) ) {
					$sub_parts = explode( '/', $parts[1] );
					unset( $sub_parts[0] );
					$parts[1] = implode( '/', $sub_parts );

					// append the url to the uploads url.
					$parts[0] = $wp_upload_dir['baseurl'];
					return implode( '/', $parts );
				}
			}
		}
	}
	return $matches;
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
