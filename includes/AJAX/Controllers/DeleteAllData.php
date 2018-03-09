<?php if ( ! defined( 'ABSPATH' ) ) exit;

class NF_AJAX_Controllers_DeleteAllData extends NF_Abstracts_Controller
{
	public function __construct()
	{
		add_action( 'wp_ajax_nf_delete_all_data', array( $this, 'delete_all_data' ) );
	}

	public function delete_all_data()
	{
		check_ajax_referer( 'ninja_forms_settings_nonce', 'security' );

		global $wpdb;
		$total_subs_deleted = 0;
		$post_result = 0;
		$max_cnt = 250;
		$form_id = $_POST[ 'form' ];
		$sub_sql = "SELECT id FROM `" . $wpdb->prefix . "posts` AS p
			LEFT JOIN `" . $wpdb->prefix . "postmeta` AS m ON p.id = m.post_id
			WHERE p.post_type = 'nf_sub' AND m.meta_key = '_form_id'
			AND m.meta_value = %s LIMIT " . $max_cnt;

		while ($post_result <= $max_cnt ) {
			$subs = $wpdb->get_col( $wpdb->prepare( $sub_sql, $form_id ),0 );
			if( 0 === count( $subs ) ) break;
			$delete_meta_query = "DELETE FROM `" . $wpdb->prefix . "postmeta` WHERE post_id IN ( [IN] )";
			$delete_meta_query = $this->prepare_in( $delete_meta_query, $subs );
			$meta_result       = $wpdb->query( $delete_meta_query );
			if ( $meta_result > 0 ) {
				$delete_post_query = "DELETE FROM `" . $wpdb->prefix . "posts` WHERE id IN ( [IN] )";
				$delete_post_query = $this->prepare_in( $delete_post_query, $subs );
				$post_result       = $wpdb->query( $delete_post_query );
				$total_subs_deleted = $total_subs_deleted + $post_result;

			}
		}

		$this->_data[ 'form_id' ] = $_POST[ 'form' ];
		$this->_data[ 'delete_count' ] = $total_subs_deleted;
		$this->_data[ 'success' ] = true;

		$this->_respond();
	}

	 private function prepare_in( $sql, $vals ) {
		global $wpdb;
		$not_in_count = substr_count( $sql, '[IN]' );
		if ( $not_in_count > 0 ) {
			$args = array( str_replace( '[IN]', implode( ', ', array_fill( 0, count( $vals ), '%d' ) ), str_replace( '%', '%%', $sql ) ) );
			// This will populate ALL the [IN]'s with the $vals, assuming you have more than one [IN] in the sql
			for ( $i=0; $i < substr_count( $sql, '[IN]' ); $i++ ) {
				$args = array_merge( $args, $vals );
			}
			$sql = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( $args ) );
		}
		return $sql;
	}


}
