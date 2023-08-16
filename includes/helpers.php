<?php
/**
 * Frontend helper functions used throught the theme.
 *
 * @package Sinatra
 * @author  Gekik, LLC <hello@gekik.co>
 * @since   1.0.0
 */

/**
 * Do not allow direct script access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ds8_current_url' ) ) {
  /**
   * Returns current page URL.
   *
   * @since 1.0.0
   * @return string, current page URL.
   */
  function ds8_current_url() {
          global $wp;
          return home_url( add_query_arg( array(), $wp->request ) );
  }
}

if ( ! function_exists( 'ds8_get_site_url' ) ) {
  /**
   * Returns site URL.
   *
   * @since 1.0.0
   * @return string, current page URL.
   */
  function ds8_get_site_url() {
          return apply_filters( 'ds8_site_url', home_url( '/' ) );
  }
}

if ( ! function_exists( 'ds8_get_site_title' ) ) {
  /**
   * Returns site title.
   *
   * @since 1.0.0
   * @return string, current page URL.
   */
  function ds8_get_site_title() {
          return apply_filters( 'ds8_site_title', get_bloginfo( 'name' ) );
  }
}

if ( ! function_exists( 'ds8_get_site_description' ) ) {
  /**
   * Returns site description.
   *
   * @since 1.0.0
   * @return string, current page URL.
   */
  function ds8_get_site_description() {
          return apply_filters( 'ds8_site_description', get_bloginfo( 'description' ) );
  }
}

if ( ! function_exists( 'ds8_the_title' ) ) {

	/**
	 * Wrapper function for ds8_get_the_title().
	 *
	 * @since 1.0.0
	 * @param string $before  Optional. Content to prepend to the title.
	 * @param string $after   Optional. Content to append to the title.
	 * @param int    $post_id Optional, default to 0. Post id.
	 * @param bool   $echo    Optional, default to true. Whether to display or return.
	 * @return string|void    String if $echo parameter is false.
	 */
	function ds8_the_title( $before = '', $after = '', $post_id = 0, $echo = true ) {

		$title  = ds8_get_the_title( $post_id );
		$before = apply_filters( 'ds8_the_title_before', $before );
		$after  = apply_filters( 'ds8_the_title_after', $after );

		$title = $before . $title . $after;

		if ( $echo ) {
			echo wp_kses( $title, ds8_get_allowed_html_tags() );
		} else {
			return $title;
		}
	}
}

if ( ! function_exists( 'ds8_get_the_title' ) ) {

	/**
	 * Get page title. Adds support for non-singular pages.
	 *
	 * @since 1.0.0
	 * @param int  $post_id Optional, default to 0. Post id.
	 * @param bool $echo    Optional, default to false. Whether to display or return.
	 * @return string|void  String if $echo parameter is false.
	 */
	function ds8_get_the_title( $post_id = 0, $echo = false ) {

		$title = '';

		if ( $post_id || is_singular() ) {
			$title = get_the_title( $post_id );

			if ( function_exists( 'yith_wcwl_is_wishlist_page' ) && yith_wcwl_is_wishlist_page() ) {

				// Retireve wishlist title.
				$wishlist_title = get_option( 'yith_wcwl_wishlist_title' ) ? get_option( 'yith_wcwl_wishlist_title' ) : __( 'Wishlist', 'ds8' );

				// Yith wishlist title.
				$title = apply_filters( 'ds8_yith_wishlist_title', esc_html( $wishlist_title ) );
			}
		} else {
			if ( is_front_page() && is_home() ) {
				// Homepage.
				$title = apply_filters( 'ds8_home_page_title', esc_html__( 'Home', 'ds8' ) );
			} elseif ( is_home() ) {
				// Blog page.
				$title = apply_filters( 'ds8_blog_page_title', get_the_title( get_option( 'page_for_posts', true ) ) );
			} elseif ( is_404() ) {
				// 404 page - title always display.
				$title = apply_filters( 'ds8_404_page_title', esc_html__( 'This page doesn&rsquo;t seem to exist.', 'ds8' ) );
			} elseif ( is_search() ) {
				// Search page - title always display.
				/* translators: 1: search string */
				$title = apply_filters( 'ds8_search_page_title', sprintf( __( 'Search results for: %s', 'ds8' ), get_search_query() ) );
			} elseif ( class_exists( 'WooCommerce' ) && is_shop() ) {
				// Woocommerce.
				$title = woocommerce_page_title( false );
			} elseif ( is_author() ) {
				// Author post archive.
				$title = apply_filters( 'ds8_author_page_title', esc_html__( 'Posts by', 'ds8' ) . ' ' . esc_html( get_the_author() ) );
			} elseif ( is_category() || is_tag() || is_tax() ) {
				// Category, tag and custom taxonomy archive.
				$title = single_term_title( '', false );
			} elseif ( is_archive() ) {
				// Archive.
				$title = get_the_archive_title();
			}
		}
		if ( $echo ) {
			echo wp_kses( $title, ds8_get_allowed_html_tags() );
		} else {
			return $title;
		}
	}
}

if ( ! function_exists( 'ds8_get_the_id' ) ) {

	/**
	 * Get post ID.
	 *
	 * @since  1.0.0
	 * @return string Current post/page ID.
	 */
	function ds8_get_the_id() {

		$post_id = 0;

		if ( is_home() && 'page' === get_option( 'show_on_front' ) ) {
			$post_id = get_option( 'page_for_posts' );
		} elseif ( is_front_page() && 'page' === get_option( 'show_on_front' ) ) {
			$post_id = get_option( 'page_on_front' );
		} elseif ( is_singular() ) {
			$post_id = get_the_ID();
		}

		return apply_filters( 'ds8_get_the_id', $post_id );
	}
}

if ( ! function_exists( 'ds8_get_the_description' ) ) {

	/**
	 * Get page description. Adds support for non-singular pages.
	 *
	 * @since 1.0.0
	 * @param int  $post_id Optional, default to 0. Post id.
	 * @param bool $echo    Optional, default to false. Whether to display or return.
	 * @return string|void  String if $echo parameter is false.
	 */
	function ds8_get_the_description( $post_id = 0, $echo = false ) {

		$description = '';

		if ( $post_id ) {
			// @todo: take from meta..
			$description = get_the_excerpt( $post_id );
		} elseif ( is_search() ) {
			global $wp_query;
			$found_posts = $wp_query->found_posts;

			if ( $found_posts > 0 ) {
				// Translators: $s number of found results.
				$description = sprintf( _n( '%s result found', '%s results found', $found_posts, 'ds8' ), number_format_i18n( $found_posts ) );
			} else {
				$description = esc_html__( 'No results found', 'ds8' );
			}
		} elseif ( is_author() ) {
			$description = '';
		} else {
			$description = get_the_archive_description();
		}

		if ( $echo ) {
			echo esc_html( $description );
		} else {
			return $description;
		}
	}
}

if ( ! function_exists( 'ds8_show_post_thumbnail' ) ) {
  /**
   * Determines if post thumbnail can be displayed.
   *
   * @since 1.0.0
   * @param int $post_id Optional. The post ID to check. If not supplied, defaults to the current post if used in the loop.
   * @return boolean, Thumbnail displayed.
   */
  function ds8_show_post_thumbnail( $post_id = null ) {

          $post_id = is_null( $post_id ) ? ds8_get_the_id() : $post_id;

          $display = ! post_password_required( $post_id ) && ! is_attachment( $post_id ) && has_post_thumbnail( $post_id );

          if ( get_post_meta( $post_id, 'ds8_disable_thumbnail', true ) ) {
                  $display = false;
          }

          return apply_filters( 'ds8_show_post_thumbnail', $display );
  }
}

if ( ! function_exists( 'ds8_get_video_from_post' ) ) :

	/**
	 * Get video HTML markup from post content.
	 *
	 * @since 1.0.0
	 * @param  number $post_id Post id.
	 * @return mixed
	 */
	function ds8_get_video_from_post( $post_id = null ) {

		$post    = get_post( $post_id );
		$content = do_shortcode( apply_filters( 'the_content', $post->post_content ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$embeds  = apply_filters( 'ds8_get_post_video', get_media_embedded_in_content( $content ) );

		if ( empty( $embeds ) ) {
			return '';
		}

		// Return first embedded item that is a video format.
		foreach ( $embeds as $embed ) {
			if ( strpos( $embed, 'video' ) || strpos( $embed, 'youtube' ) || strpos( $embed, 'vimeo' ) ) {
				return $embed;
			}
		}
	}
endif;

if ( ! function_exists( 'ds8_get_audio_from_post' ) ) :

	/**
	 * Get video HTML markup from post content.
	 *
	 * @since 1.0.0
	 * @param  number $post_id Post id.
	 * @return mixed
	 */
	function ds8_get_audio_from_post( $post_id = null ) {

		$post    = get_post( $post_id );
		$content = do_shortcode( apply_filters( 'the_content', $post->post_content ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$embeds  = apply_filters( 'ds8_get_post_audio', get_media_embedded_in_content( $content ) );

		if ( empty( $embeds ) ) {
			return '';
		}

		// check what is the first embed containg video tag, youtube or vimeo.
		foreach ( $embeds as $embed ) {
			if ( strpos( $embed, 'audio' ) || strpos( $embed, 'soundcloud' ) ) {
				return '<span class="ds8-post-audio-wrapper">' . $embed . '</span>';
			}
		}
	}
endif;

if ( ! function_exists( 'ds8_get_post_gallery' ) ) :
	/**
	 * A get_post_gallery() polyfill for Gutenberg.
	 *
	 * @since 1.0.0
	 * @param object|int|null $post Optional. The post to check. If not supplied, defaults to the current post if used in the loop.
	 * @param boolean         $html Return gallery HTML or array of gallery items.
	 * @return string|array   The gallery html or array of gallery items.
	 */
	function ds8_get_post_gallery( $post = 0, $html = false ) {

		// Get gallery shortcode.
		$gallery = get_post_gallery( $post, $html );

		// Already found a gallery so lets quit.
		if ( $gallery ) {
			return $gallery;
		}

		// Check the post exists.
		$post = get_post( $post );
		if ( ! $post ) {
			return;
		}

		// Not using Gutenberg so let's quit.
		if ( ! function_exists( 'has_blocks' ) ) {
			return;
		}

		// Not using blocks so let's quit.
		if ( ! has_blocks( $post->post_content ) ) {
			return;
		}

		/**
		 * Search for gallery blocks and then, if found, return the
		 * first gallery block.
		 */
		$pattern = '/<!--\ wp:gallery.*-->([\s\S]*?)<!--\ \/wp:gallery -->/i';
		preg_match_all( $pattern, $post->post_content, $the_galleries );

		// Check a gallery was found and if so change the gallery html.
		if ( ! empty( $the_galleries[1] ) ) {
			$gallery_html = reset( $the_galleries[1] );

			if ( $html ) {
				$gallery = $gallery_html;
			} else {
				$srcs = array();
				$ids  = array();

				preg_match_all( '#src=([\'"])(.+?)\1#is', $gallery_html, $src, PREG_SET_ORDER );
				if ( ! empty( $src ) ) {
					foreach ( $src as $s ) {
						$srcs[] = $s[2];
					}
				}

				preg_match_all( '#data-id=([\'"])(.+?)\1#is', $gallery_html, $id, PREG_SET_ORDER );
				if ( ! empty( $id ) ) {
					foreach ( $id as $i ) {
						$ids[] = $i[2];
					}
				}

				$gallery = array(
					'ids' => implode( ',', $ids ),
					'src' => $srcs,
				);
			}
		}

		return $gallery;
	}
endif;

if ( ! function_exists( 'ds8_get_image_from_post' ) ) :

	/**
	 * Get image HTML markup from post content.
	 *
	 * @since 1.0.0
	 * @param object|int|null $post Optional. The post to check. If not supplied, defaults to the current post if used in the loop.
	 * @param boolean         $html Return image HTML or array of image items.
	 * @return mixed
	 */
	function ds8_get_image_from_post( $post = null, $html = true ) {

		// Check the post exists.
		$post = get_post( $post );
		if ( ! $post ) {
			return;
		}

		$attachment_id = null;

		// Using Blocks, check if wp:image exists.
		if ( function_exists( 'has_blocks' ) && has_blocks( $post->post_content ) ) {

			/**
			 * Search for image blocks.
			 */
			$pattern = '/<!--\ wp:image.*"id"\s*:\s*([0-9]+).*-->/i';
			preg_match( $pattern, $post->post_content, $the_images );

			// Check if an image was found.
			if ( ! empty( $the_images[1] ) ) {
				$attachment_id = absint( $the_images[1] );
			}
		}

		// Nothing found, check if images added through Add Media.
		if ( ! $attachment_id ) {

			/**
			 * Search for img tags in the content.
			 */
			$pattern = '/<img.*wp-image-([0-9]+).*>/';
			preg_match( $pattern, $post->post_content, $the_images );

			// Check if an image was found.
			if ( ! empty( $the_images[0] ) ) {
				$attachment_id = absint( $the_images[0] );
			}
		}

		// Still nothing was found, check for attached images.
		if ( ! $attachment_id ) {

			$the_images = get_attached_media( 'image', $post->ID );

			if ( ! empty( $the_images ) ) {
				$image         = reset( $the_images );
				$attachment_id = $image->ID;
			}
		}

		// Check if an image was found.
		if ( $attachment_id ) {

			if ( $html ) {
				$atts = array(
					'alt' => get_the_title( $post->ID ),
				);

				if ( ds8_get_schema_markup( 'image' ) ) {
					$atts['itemprop'] = 'image';
				}

				return wp_get_attachment_image( $attachment_id, 'full', false, $atts );
			} else {
				return wp_get_attachment_url( $attachment_id );
			}
		}

		return false;
	}
endif;

if ( ! function_exists( 'ds8_get_post_thumbnail' ) ) :

	/**
	 * Get post thumbnail markup.
	 *
	 * @since 1.0.0
	 * @param int|WP_Post  $post Optional. Post ID or WP_Post object.  Default is global `$post`.
	 * @param string|array $size Optional. Image size to use. Accepts any valid image size, or
	 *                           an array of width and height values in pixels (in that order).
	 *                           Default 'post-thumbnail'.
	 * @param boolean      $caption Optional. Display image caption.
	 * @return string The post thumbnail image tag.
	 */
	function ds8_get_post_thumbnail( $post = null, $size = 'post-thumbnail', $caption = false ) {

		$attachment_id  = get_post_thumbnail_id( $post );
		$attachment_alt = trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ); // phpcs:ignore
		$attachment_alt = empty( $attachment_alt ) ? get_the_title( $post ) : $attachment_alt;

		$atts = array(
			'alt' => $attachment_alt,
		);

		if ( ds8_get_schema_markup( 'image' ) ) {
			$atts['itemprop'] = 'image';
		}

		$size = apply_filters( 'ds8_post_thumbnail_default_size', $size );
		$atts = apply_filters( 'ds8_post_thumbnail_default_size', $atts );

		$html = get_the_post_thumbnail( $post, $size, $atts );

		if ( $caption ) {

			$caption = wp_get_attachment_caption( $attachment_id );

			if ( ! empty( $caption ) ) {
				$caption = '<div class="post-thumb-caption">' . wp_kses( $caption, ds8_get_allowed_html_tags( 'button' ) ) . '</div>';
			}

			$html .= $caption;
		}

		return apply_filters( 'ds8_post_thumbnail_html', $html, $post, $attachment_id, $size, $atts );
	}
endif;

if ( ! function_exists( 'ds8_entry_get_permalink' ) ) :
	/**
	 * Get permalink for one post entry.
	 *
	 * @since 1.0.0
	 * @param int|WP_Post $post Optional. Post ID or WP_Post object.  Default is global `$post`.
	 * @return string
	 */
	function ds8_entry_get_permalink( $post = null ) {

		$permalink = '';

		if ( 'link' === get_post_format( $post ) ) {
			$permalink = get_url_in_content( get_the_content( $post ) );
		} else {
			$permalink = get_permalink( $post );
		}

		return apply_filters( 'ds8_entry_permalink', $permalink );
	}
endif;

if ( ! function_exists( 'ds8_modify_read_more_link' ) ) {
  /**
   * Modifies the default Read More link. Do not show if "Read More" button (from Customizer) is enabled.
   *
   * @since  1.0.0
   * @return Modified read more HTML.
   */
  function ds8_modify_read_more_link() {

          $has_read_more = in_array( 'summary-footer', ds8_get_blog_entry_elements(), true );
          $class         = $has_read_more ? ' ds8-hide' : '';

          return '<footer class="entry-footer' . esc_attr( $class ) . '"><a class="si-btn btn-text-1" href="' . esc_url( get_the_permalink() ) . '" role="button"><span>' . esc_html__( 'Continue Reading', 'ds8' ) . '</span></a></footer>';
  }
}
add_filter( 'the_content_more_link', 'ds8_modify_read_more_link' );

if ( ! function_exists( 'ds8_dynamic_strings' ) ) {
  /**
   * Insert dynamic text into content.
   *
   * @since 1.0.0
   * @param string $content Text to be modified.
   * @return string Modified text.
   */
  function ds8_dynamic_strings( $content ) {

          $content = str_replace( '{{the_year}}', date_i18n( 'Y' ), $content );
          $content = str_replace( '{{the_date}}', date_i18n( get_option( 'date_format' ) ), $content );
          $content = str_replace( '{{site_title}}', get_bloginfo( 'name' ), $content );
          $content = str_replace( '{{theme_link}}', '<a href="https://wordpress.org/themes/ds8/" class="imprint" target="_blank" rel="noopener noreferrer">Sinatra WordPress Theme</a>', $content );

          if ( false !== strpos( $content, '{{current_user}}' ) ) {
                  $current_user = wp_get_current_user();
                  $content      = str_replace( '{{current_user}}', apply_filters( 'ds8_logged_out_user_name', $current_user->display_name ), $content );
          }

          return apply_filters( 'ds8_parse_dynamic_strings', $content );
  }
}
add_filter( 'ds8_dynamic_strings', 'ds8_dynamic_strings' );

if ( ! function_exists( 'ds8_x_ua_compatible_headers' ) ) {
  /**
   * Add headers for IE to override IE's Compatibility View Settings
   *
   * @since 1.0.0
   * @param array $headers The list of headers to be sent.
   */
  function ds8_x_ua_compatible_headers( $headers ) {
          $headers['X-UA-Compatible'] = 'IE=edge';
          return $headers;
  }
}
add_filter( 'wp_headers', 'ds8_x_ua_compatible_headers' );

if ( ! function_exists( 'ds8_cat_count_filter' ) ) {
  /**
   * Removes parentheses from widget category count.
   *
   * @since 1.0.0
   * @param array $variable The filtered variable.
   */
  function ds8_cat_count_filter( $variable ) {
          $variable = str_replace( '(', '<span> ', $variable );
          $variable = str_replace( ')', ' </span>', $variable );

          return $variable;
  }
}
add_filter( 'wp_list_categories', 'ds8_cat_count_filter' );

if ( ! function_exists( 'ds8_arc_count_filter' ) ) {
  /**
   * Removes parentheses from widget archive count.
   *
   * @since 1.0.0
   * @param array $variable The filtered variable.
   */
  function ds8_arc_count_filter( $variable ) {
          $variable = str_replace( '(', '<span>', $variable );
          $variable = str_replace( ')', '</span>', $variable );

          return $variable;
  }
}
add_filter( 'get_archives_link', 'ds8_arc_count_filter' );

if ( ! function_exists( 'ds8_header_menu_desc' ) ) {
  /**
   * Add descriptions on menu dropdowns.
   *
   * @since 1.0.0
   * @param string $item_output HTML output for the menu item.
   * @param object $item menu item object.
   * @param int    $depth depth in menu structure.
   * @param object $args arguments passed to wp_nav_menu().
   * @return string $item_output
   */
  function ds8_header_menu_desc( $item_output, $item, $depth, $args ) {

          if ( $depth > 0 && $item->description ) {
                  $item_output = str_replace( '</a>', '<span class="description">' . $item->description . '</span></a>', $item_output );
          }

          return $item_output;
  }
}
add_filter( 'walker_nav_menu_start_el', 'ds8_header_menu_desc', 10, 4 );


if ( ! function_exists( 'ds8_get_post_media' ) ) :

	/**
	 * Post format featured media: image / gallery / audio / video etc.
	 *
	 * @since  1.0
	 * @return mixed
	 * @param  string $post_format Post Format.
	 * @param  mixed  $post        Post object.
	 */
	function ds8_get_post_media( $post_format = false, $post = null ) {

		if ( false === $post_format ) {
			$post_format = get_post_format( $post );
		}

		$return = '';

		switch ( $post_format ) {
			case 'image':
			default:
				$size    = 'full';
				$caption = false;

				if ( is_single( $post ) || is_page( $post ) ) {

					$caption = true;

					//if ( 'no-sidebar' === ds8_get_sidebar_position( $post ) ) {
						$size = 'full';
					//}
				}

				if ( has_post_thumbnail( $post ) ) {
					$return = ds8_get_post_thumbnail( $post, $size, $caption );
				} elseif ( 'image' === $post_format ) {
					$return = ds8_get_image_from_post( $post );
				}

				break;
		}

		return apply_filters( 'ds8_get_post_media', $return, $post_format, $post );
	}
endif;


if ( ! function_exists( 'ds8_get_image_from_post' ) ) :

	/**
	 * Get image HTML markup from post content.
	 *
	 * @since 1.0.0
	 * @param object|int|null $post Optional. The post to check. If not supplied, defaults to the current post if used in the loop.
	 * @param boolean         $html Return image HTML or array of image items.
	 * @return mixed
	 */
	function ds8_get_image_from_post( $post = null, $html = true ) {

		// Check the post exists.
		$post = get_post( $post );
		if ( ! $post ) {
			return;
		}

		$attachment_id = null;

		// Using Blocks, check if wp:image exists.
		if ( function_exists( 'has_blocks' ) && has_blocks( $post->post_content ) ) {

			/**
			 * Search for image blocks.
			 */
			$pattern = '/<!--\ wp:image.*"id"\s*:\s*([0-9]+).*-->/i';
			preg_match( $pattern, $post->post_content, $the_images );

			// Check if an image was found.
			if ( ! empty( $the_images[1] ) ) {
				$attachment_id = absint( $the_images[1] );
			}
		}

		// Nothing found, check if images added through Add Media.
		if ( ! $attachment_id ) {

			/**
			 * Search for img tags in the content.
			 */
			$pattern = '/<img.*wp-image-([0-9]+).*>/';
			preg_match( $pattern, $post->post_content, $the_images );

			// Check if an image was found.
			if ( ! empty( $the_images[0] ) ) {
				$attachment_id = absint( $the_images[0] );
			}
		}

		// Still nothing was found, check for attached images.
		if ( ! $attachment_id ) {

			$the_images = get_attached_media( 'image', $post->ID );

			if ( ! empty( $the_images ) ) {
				$image         = reset( $the_images );
				$attachment_id = $image->ID;
			}
		}

		// Check if an image was found.
		if ( $attachment_id ) {

			if ( $html ) {
				$atts = array(
					'alt' => get_the_title( $post->ID ),
				);

				if ( ds8_get_schema_markup( 'image' ) ) {
					$atts['itemprop'] = 'image';
				}

				return wp_get_attachment_image( $attachment_id, 'full', false, $atts );
			} else {
				return wp_get_attachment_url( $attachment_id );
			}
		}

		return false;
	}
endif;


if ( ! function_exists( 'ds8_get_schema_markup' ) ) :
	/**
	 * Return correct schema markup.
	 *
	 * @since 1.0.0
	 * @param string $location Location for schema parameters.
	 */
	function ds8_get_schema_markup( $location = '' ) {


		// Return if no location parameter is passed.
		if ( ! $location ) {
			return;
		}

		$schema = '';

		if ( 'url' === $location ) {
			$schema = 'itemprop="url"';
		} elseif ( 'name' === $location ) {
			$schema = 'itemprop="name"';
		} elseif ( 'text' === $location ) {
			$schema = 'itemprop="text"';
		} elseif ( 'headline' === $location ) {
			$schema = 'itemprop="headline"';
		} elseif ( 'image' === $location ) {
			$schema = 'itemprop="image"';
		} elseif ( 'header' === $location ) {
			$schema = 'itemtype="https://schema.org/WPHeader" itemscope="itemscope"';
		} elseif ( 'site_navigation' === $location ) {
			$schema = 'itemtype="https://schema.org/SiteNavigationElement" itemscope="itemscope"';
		} elseif ( 'logo' === $location ) {
			$schema = 'itemprop="logo"';
		} elseif ( 'description' === $location ) {
			$schema = 'itemprop="description"';
		} elseif ( 'organization' === $location ) {
			$schema = 'itemtype="https://schema.org/Organization" itemscope="itemscope" ';
		} elseif ( 'footer' === $location ) {
			$schema = 'itemtype="http://schema.org/WPFooter" itemscope="itemscope"';
		} elseif ( 'sidebar' === $location ) {
			$schema = 'itemtype="http://schema.org/WPSideBar" itemscope="itemscope"';
		} elseif ( 'main' === $location ) {
			$schema = 'itemtype="http://schema.org/WebPageElement" itemprop="mainContentOfPage"';

			if ( is_singular( 'post' ) ) {
				$schema = 'itemscope itemtype="http://schema.org/Blog"';
			}
		} elseif ( 'author' === $location ) {
			$schema = 'itemprop="author" itemscope="itemscope" itemtype="http://schema.org/Person"';
		} elseif ( 'name' === $location ) {
			$schema = 'itemprop="name"';
		} elseif ( 'datePublished' === $location ) {
			$schema = 'itemprop="datePublished"';
		} elseif ( 'dateModified' === $location ) {
			$schema = 'itemprop="dateModified"';
		} elseif ( 'article' === $location ) {
			$schema = 'itemscope="" itemtype="https://schema.org/CreativeWork"';
		} elseif ( 'comment' === $location ) {
			$schema = 'itemprop="comment" itemscope="" itemtype="https://schema.org/Comment"';
		} elseif ( 'html' === $location ) {
			if ( is_singular() ) {
				$schema = 'itemscope itemtype="http://schema.org/WebPage"';
			} else {
				$schema = 'itemscope itemtype="http://schema.org/Article"';
			}
		}

		$schema = ' ' . trim( apply_filters( 'ds8_schema_markup', $schema, $location ) );

		return $schema;
	}
endif;


if ( ! function_exists( 'render_image' ) ) :
      /**
       * Return image post.
       *
       * 
       */
      function render_image( ) {
          global $post;
          $size    = 'full';
          $caption = false;
          $return = "";
          
          $post_format = get_post_format( $post );

          if ( is_single( $post ) || is_page( $post ) ) {

                  $caption = true;
                  $size = 'full';
          }

          if ( has_post_thumbnail( $post ) ) {
                  $return = ds8_get_post_thumbnail( $post, $size, $caption );
          } elseif ( 'image' === $post_format ) {
                  $return = ds8_get_image_from_post( $post );
          }
          
          return $return;
      }

endif;

if ( ! function_exists( 'ds8_get_allowed_html_tags' ) ) {
	/**
	 * Array of allowed HTML Tags.
	 *
	 * @since 1.0.0
	 * @param string $type predefined HTML tags group name.
	 * @return array, allowed HTML tags.
	 */
	function ds8_get_allowed_html_tags( $type = 'post' ) {

		$tags = array();

		switch ( $type ) {

			case 'basic':
				$tags = array(
					'strong' => array(),
					'em'     => array(),
					'b'      => array(),
					'br'     => array(),
					'i'      => array(
						'class' => array(),
					),
					'img'    => array(
						'src'    => array(),
						'alt'    => array(),
						'width'  => array(),
						'height' => array(),
						'class'  => array(),
						'id'     => array(),
					),
					'span'   => array(
						'class' => array(),
					),
					'a'      => array(
						'href'   => array(),
						'rel'    => array(),
						'target' => array(),
						'class'  => array(),
						'role'   => array(),
						'id'     => array(),
					),
				);
				break;

			case 'button':
				$tags = array(
					'strong' => array(),
					'em'     => array(),
					'span'   => array(
						'class' => array(),
					),
					'i'      => array(
						'class' => array(),
					),
				);
				break;

			case 'span':
				$tags = array(
					'span' => array(
						'class' => array(),
					),
				);
				break;

			case 'icon':
				$tags = array(
					'i'    => array(),
					'span' => array(),
					'img'  => array(),
				);
				break;

			case 'post':
				$tags = wp_kses_allowed_html( 'post' );

				$tags = array_merge(
					$tags,
					array(
						'svg'     => array(
							'class'       => true,
							'xmlns'       => true,
							'width'       => true,
							'height'      => true,
							'viewbox'     => true,
							'aria-hidden' => true,
							'role'        => true,
							'focusable'   => true,
						),
						'path'    => array(
							'fill'      => true,
							'fill-rule' => true,
							'd'         => true,
							'transform' => true,
						),
						'polygon' => array(
							'fill'      => true,
							'fill-rule' => true,
							'points'    => true,
							'transform' => true,
							'focusable' => true,
						),
						'title'   => array(),
					)
				);

				break;

			case 'svg':
				$tags = array(
					'svg'     => array(
						'class'       => true,
						'xmlns'       => true,
						'width'       => true,
						'height'      => true,
						'viewbox'     => true,
						'aria-hidden' => true,
						'role'        => true,
						'focusable'   => true,
					),
					'path'    => array(
						'fill'      => true,
						'fill-rule' => true,
						'd'         => true,
						'transform' => true,
					),
					'polygon' => array(
						'fill'      => true,
						'fill-rule' => true,
						'points'    => true,
						'transform' => true,
						'focusable' => true,
					),
					'title'   => array(),
				);
				break;

			default:
				$tags = array(
					'strong' => array(),
					'em'     => array(),
					'b'      => array(),
					'i'      => array(),
					'img'    => array(
						'src'    => array(),
						'alt'    => array(),
						'width'  => array(),
						'height' => array(),
						'class'  => array(),
						'id'     => array(),
					),
					'span'   => array(),
					'a'      => array(
						'href'   => array(),
						'rel'    => array(),
						'target' => array(),
						'class'  => array(),
						'role'   => array(),
						'id'     => array(),
					),
				);
				break;
		}

		return apply_filters( 'ds8_allowed_html_tags', $tags, $type );
	}
}

if ( ! function_exists( 'ds8_pagination' ) ) :
	/**
	 * Output the pagination navigation.
	 *
	 * @since 1.0.0
	 */
	function ds8_pagination() {

		// Don't print empty markup if there's only one page.
		if ( $GLOBALS['wp_query']->max_num_pages <= 1 ) {
			return;
		}

		?>
		<div class="ds8-pagination">
			<?php

			the_posts_pagination(
				array(
					'mid_size'  => 2,
					'prev_text' => ds8_animated_arrow( 'left', 'button', false ) . '<span class="screen-reader-text">' . __( 'Previous page', 'ds8' ) . '</span>',
					'next_text' => '<span class="screen-reader-text">' . __( 'Next page', 'ds8' ) . '</span>' . ds8_animated_arrow( 'right', 'button', false ),
				)
			);
			?>
		</div>
		<?php
	}
endif;

if ( ! function_exists( 'ds8_schema_markup' ) ) :
	/**
	 * Outputs correct schema markup
	 *
	 * @since 1.0.0
	 * @param string $location Location for schema parameters.
	 */
	function ds8_schema_markup( $location ) {
		echo ds8_get_schema_markup( $location ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
endif;