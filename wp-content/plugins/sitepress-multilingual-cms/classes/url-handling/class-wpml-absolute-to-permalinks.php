<?php

use WPML\Utils\AutoAdjustIds;

class WPML_Absolute_To_Permalinks {

	private $taxonomies_query;
	private $lang;

	/** @var SitePress $sitepress */
	private $sitepress;

	/** @var AutoAdjustIds $auto_adjust_ids */
	private $auto_adjust_ids;

	public function __construct( SitePress $sitepress, AutoAdjustIds $auto_adjust_ids = null ) {
		$this->sitepress       = $sitepress;
		$this->auto_adjust_ids = $auto_adjust_ids ?: new AutoAdjustIds( $sitepress );
	}

	public function convert_text( $text ) {

		$this->lang = $this->sitepress->get_current_language();

		$active_langs_reg_ex = implode( '|', array_keys( $this->sitepress->get_active_languages() ) );

		if ( ! $this->taxonomies_query ) {
			$this->taxonomies_query = new WPML_WP_Taxonomy_Query( $this->sitepress->get_wp_api() );
		}

		$home    = rtrim( $this->sitepress->get_wp_api()->get_option( 'home' ), '/' );
		$parts   = parse_url( $home );
		$abshome = $parts['scheme'] . '://' . $parts['host'];
		$path    = isset( $parts['path'] ) ? ltrim( $parts['path'], '/' ) : '';
		$tx_qvs  = join( '|', $this->taxonomies_query->get_query_vars() );
		$reg_ex  = '@<a([^>]+)?href="((' . $abshome . ')?/' . $path . '/?(' . $active_langs_reg_ex . ')?\?(p|page_id|cat_ID|' . $tx_qvs . ')=([0-9a-z-]+))(#?[^"]*)"([^>]+)?>@i';
		$text    = preg_replace_callback( $reg_ex, [ $this, 'show_permalinks_cb' ], $text );

		return $text;
	}

	function show_permalinks_cb( $matches ) {

		$parts = $this->get_found_parts( $matches );

		$url = $this->auto_adjust_ids->runWith(
			function() use ( $parts ) {
				return $this->get_url( $parts );
			}
		);

		if ( $this->sitepress->get_wp_api()->is_wp_error( $url ) || empty( $url ) ) {
			return $parts->whole;
		}

		$fragment = $this->get_fragment( $url, $parts );

		if ( 'widget_text' == $this->sitepress->get_wp_api()->current_filter() ) {
			$url = $this->sitepress->convert_url( $url );
		}

		return '<a' . $parts->pre_href . 'href="' . $url . $fragment . '"' . $parts->trail . '>';
	}

	private function get_found_parts( $matches ) {
		return (object) array(
			'whole'        => $matches[0],
			'pre_href'     => $matches[1],
			'content_type' => $matches[5],
			'id'           => $matches[6],
			'fragment'     => $matches[7],
			'trail'        => isset( $matches[8] ) ? $matches[8] : '',
		);
	}

	private function get_url( $parts ) {
		$tax = $this->taxonomies_query->find( $parts->content_type );

		if ( $parts->content_type == 'cat_ID' ) {
			$url = $this->sitepress->get_wp_api()->get_category_link( $parts->id );
		} elseif ( $tax ) {
			$url = $this->sitepress->get_wp_api()->get_term_link( $parts->id, $tax );
		} else {
			$url = $this->sitepress->get_wp_api()->get_permalink( $parts->id );
		}

		return $url;
	}

	private function get_fragment( $url, $parts ) {
		$fragment = $parts->fragment;
		$fragment = $this->remove_query_in_wrong_lang( $fragment );
		if ( is_string( $fragment ) && $fragment != '' ) {
			$fragment = str_replace( '&#038;', '&', $fragment );
			$fragment = str_replace( '&amp;', '&', $fragment );
			if ( $fragment[0] == '&' ) {
				if ( strpos( $fragment, '?' ) === false && strpos( $url, '?' ) === false ) {
					$fragment[0] = '?';
				}
			}

			if ( strpos( $url, '?' ) ) {
				$fragment = $this->check_for_duplicate_lang_query( $fragment, $url );
			}
		}

		return $fragment;
	}

	private function remove_query_in_wrong_lang( $fragment ) {
		if ( is_string( $fragment ) && $fragment != '' ) {
			$fragment = str_replace( '&#038;', '&', $fragment );
			$fragment = str_replace( '&amp;', '&', $fragment );
			$start    = $fragment[0];
			parse_str( substr( $fragment, 1 ), $fragment_query );
			if ( isset( $fragment_query['lang'] ) ) {
				if ( $fragment_query['lang'] != $this->lang ) {
					unset( $fragment_query['lang'] );

					$fragment = build_query( $fragment_query );
					if ( strlen( $fragment ) ) {
						$fragment = $start . $fragment;
					}
				}
			}
		}
		return $fragment;
	}

	private function check_for_duplicate_lang_query( $fragment, $url ) {
		$url_parts = explode( '?', $url );
		parse_str( $url_parts[1], $url_query );

		if ( isset( $url_query['lang'] ) ) {
			parse_str( substr( $fragment, 1 ), $fragment_query );
			if ( isset( $fragment_query['lang'] ) ) {
				unset( $fragment_query['lang'] );
				$fragment = build_query( $fragment_query );
				if ( strlen( $fragment ) ) {
					$fragment = '&' . $fragment;
				}
			}
		}
		return $fragment;
	}
}
