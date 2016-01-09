<?php

/**
 * Ajax Controller Class
 *
 * @class Class Flexible_Content_Copy_Ajax
 * @package ACF Flexible Content Copy
 */
class Flexible_Content_Copy_Ajax {

	/**
	 * Labels cache
	 * @var array
	 */
	private $post_types = array();

	/**
	 * Return posts by search query
	 * Example: wp-admin/admin-ajax.php?action=flexible-content-copy/load-posts&q=aabb
	 */
	public function posts() {
		$query = ( isset( $_REQUEST['q'] ) ) ? trim( $_REQUEST['q'] ) : '';
		$query = esc_sql( $query );

		if ( empty( $query ) ) {
			wp_send_json( array() );
		}

		$default_types = array(
			'post',
			'page'
		);

		$types = get_post_types( array(
			'public'             => true,
			'publicly_queryable' => true,
			'_builtin'           => false
		) );

		/**
		 * Exclude post types from query
		 *
		 * @since 1.0.0
		 *
		 * @param array $value
		 */
		$exclude_types = apply_filters( 'flexible-content-copy/exclude', array() );
		if ( ! empty( $exclude_types ) ) {
			foreach ( $exclude_types as $type ) {
				if ( array_key_exists( $type, $types ) ) {
					unset( $types[ $type ] );
				}
			}
		}

		$types = array_merge( $default_types, array_keys( $types ) );

		/**
		 * Post types for query
		 *
		 * @since 1.0.0
		 *
		 * @param array $types post types
		 */
		$types = apply_filters( 'flexible-content-copy/types', $types );

		$result = array();

		$query = new WP_Query( array(
			'post_type'   => $types,
			'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private' ),
			's'           => $query
		) );

		foreach ( $query->posts as $post ) {
			$result[] = array(
				'id'     => $post->ID,
				'title'  => ( mb_strlen( $post->post_title ) > 50 ) ? mb_substr( $post->post_title, 0, 50 ) . '...' : $post->post_title,
				'full'   => esc_attr( $post->post_title ),
				'type'   => $this->get_post_type_label( $post->post_type ),
				'status' => $post->post_status,
				'author' => get_userdata( $post->post_author )->display_name,
				'date'   => date( 'd.m.Y', strtotime( $post->post_date ) ),
				'link'   => esc_url( admin_url( 'post.php?post=' . $post->ID . '&action=edit' ) )
			);
		}

		wp_send_json( $result );
	}

	/**
	 * Return flexible content layouts
	 * Example: wp-admin/admin-ajax.php?action=flexible-content-copy/layouts&post=512
	 */
	public function layouts() {
		$post_id = ( isset( $_REQUEST['post'] ) ) ? trim( $_REQUEST['post'] ) : '';

		if ( empty( $post_id ) ) {
			wp_send_json( array() );
		}
		$blocks = get_field( 'content', (int) $post_id ) ?: array();
		$meta   = acf_get_field( 'content' );

		if ( ! $meta ) {
			wp_send_json( array() );
		}

		$result = array();

		for ( $i = 0; $i <= sizeof( $blocks ) - 1; $i ++ ) {
			$block_name = $blocks[ $i ]['acf_fc_layout'];
			$block_meta = $this->search( $meta['layouts'], 'name', $block_name );
			if ( ! isset( $block_meta[0] ) ) {
				continue;
			}
			$block_meta = $block_meta[0];

			$result[] = array(
				'order' => $i,
				'name'  => $block_name,
				'label' => $block_meta['label']
			);
		}

		wp_send_json( array( 'layouts' => $result ) );
	}

	/**
	 * Update post, inserting blocks
	 */
	public function post_save() {
		if ( empty( $_POST ) ) {
			wp_send_json_error( 'empty request' );
		}

		$source_post = ( isset( $_POST['source_post'] ) ) ? (int) $_POST['source_post'] : 0; // from
		$dest_post   = ( isset( $_POST['dest_post'] ) ) ? (int) $_POST['dest_post'] : 0; // to

		if ( $source_post == 0 || $dest_post == 0 ) {
			wp_send_json_error( 'Bad params' );
		}

		$layouts = ( isset( $_POST['flexible'] ) ) ? (array) $_POST['flexible'] : array();

		if ( empty( $layouts ) ) {
			wp_send_json_error( 'No layouts' );
		}

		$blocks      = get_field( 'content', $source_post ) ?: array();
		$copy_blocks = array();
		$result      = array();

		foreach ( $layouts as $layout => $value ) {
			$layout = explode( '-', $layout );
			if ( sizeof( $layout ) < 2 ) {
				continue;
			}
			$copy_blocks[ $layout[1] ] = $layout[0];
		}

		foreach ( $copy_blocks as $block_key => $block_name ) {
			$block_result = $blocks[ $block_key ];
			if ( $block_result['acf_fc_layout'] === $block_name ) {
				$result[] = $block_result;
			}
		}

		$old_blocks = get_field( 'content', $dest_post ) ?: array();
		update_field( 'field_55dad56913c08', array_merge( $old_blocks, $result ), $dest_post );

		wp_redirect( admin_url( 'post.php?post=' . $dest_post . '&action=edit' ) );
	}

	/**
	 * Search in array by key=>value
	 *
	 * @param array $array
	 * @param string $key
	 * @param string $value
	 *
	 * @return array
	 */
	private function search( $array, $key, $value ) {
		$results = array();

		if ( is_array( $array ) ) {
			if ( isset( $array[ $key ] ) && $array[ $key ] == $value ) {
				$results[] = $array;
			}

			foreach ( $array as $subarray ) {
				$results = array_merge( $results, $this->search( $subarray, $key, $value ) );
			}
		}

		return $results;
	}

	/**
	 * @param string $type
	 *
	 * @return string
	 */
	private function get_post_type_label( $type ) {
		if ( array_key_exists( $type, $this->post_types ) ) {
			return $this->post_types[ $type ];
		}

		$post_type = get_post_type_object( $type );
		if ( $post_type === null ) {
			return '';
		}

		return $this->post_types[ $type ] = $post_type->labels->singular_name;
	}

}