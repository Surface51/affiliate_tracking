<?php
/**
 * @file
 * Contains \Drupal\affiliate_tracking\AffiliateTrackingController.
 */

namespace Drupal\affiliate_tracking;

class AffiliateTrackingController {
	public function content() {
		// Table header
		$header = array(
			'affiliate_id' => t('Affiliate ID'),
			'original_user' => t('Original User'),
			'new_user' => t('New User'),
			'action' => t('Action'),
			'date' => t('Date'),
		);

		$rows = array();

		$db = \Drupal::database();
		$query = $db->select('at_hits', 'n');
		$query->fields('n');
		$result = $query->execute();

		foreach ($result as $row) {
			$output = array();

			$query = $db->select('at_links', 'n');
			$query->fields('n');
			$query->condition('affiliate_id', $row->affiliate_id, "=");
			$links = $query->execute();

			$returned_link = null;
			foreach ($links as $link) {
				$returned_link = $link;
			}

			$output['affiliate_id'] = $returned_link->affiliate_code;
			$output['original_user'] = $returned_link->user_info;
			$output['new_user'] = $row->user_info;
			$output['action'] = $row->action;
			$output['date'] = date("Y-m-d", $row->created);

			array_push($rows, $output);
		}
	 	$table = array(
			'#type' => 'table',
			'#header' => $header,
			'#rows' => $rows,
			'#attributes' => array(
				'id' => 'bd-contact-table',
			),
		);

		return array(
			'#markup' => drupal_render($table),
		);
	}

	static function addTrackingCode() {
		$name = AffiliateTrackingController::generateRandomString(20);
		$user_info = "original user";
		$result = db_insert('at_links')->fields(array(
			'affiliate_code' => $name,
			'user_info' => $user_info,
			'ip_address' => $_SERVER['REMOTE_ADDR'],
			'nid' => "1",
			'created' => time(),
		))->execute();
		return array(
			'#markup' => "New tracking code is ".$name,
		);
	}

	static function findTrackingCode($affiliate_code) {
		$user_info = 1;
		$db = \Drupal::database();
		$query = $db->select('at_links', 'n');
    $query->fields('n');
    $query->condition('affiliate_code', $affiliate_code, "=");
    $result = $query->execute();
    $return = null;
    foreach ($result as $row) {
    	$return = $row;
    }
		return $return;
	}

	static function addTrackingHit($affiliate_id, $action, $hit_user) {
		$result = db_insert('at_hits')->fields(array(
			'affiliate_id' => $affiliate_id,
			'user_info' => $hit_user,
			'ip_address' => "192.168.1.100",
			'nid' => "1",
			'action' => $action,
			'created' => time(),
		))->execute();
		return $result;
	}

	static function generateRandomString($length = 20) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}


}
