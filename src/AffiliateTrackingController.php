<?php
/**
 * @file
 * Contains \Drupal\affiliate_tracking\AffiliateTrackingController.
 */

namespace Drupal\affiliate_tracking;

use Drupal\Core\Url;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

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
		$query->orderBy('n.created', 'DESC');
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
			$output['date'] = date("Y-m-d h:i:s", $row->created);

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

	 	$markup = drupal_render($table);
	 // 	$pager = array('#theme' => 'pager');
		// $markup .= drupal_render($pager);

		return array(
			'#markup' => $markup,
		);
	}

	public function contentByAffiliateCode() {
	// Table header
	//
		$header = array(
			'affiliate_id' => t('Affiliate ID'),
			'original_user' => t('Creator of Affiliate'),
			'hit_count' => t('Hit Count'),
			'register_count' => t('Register Count'),
			'date' => t('Date'),
			'operations' => t('Operations'),
		);

		$rows = array();

		$db = \Drupal::database();

		$query = $db->select('at_links', 'n');
		$query->orderBy('n.created', 'DESC');
		$query->fields('n');

		/// REVERSE ORDER

		$result = $query->execute();

		foreach ($result as $row) {
			$output = array();

			$query = $db->select('at_hits', 'n');
			$query->fields('n');
			$query->condition('affiliate_id', $row->affiliate_id, "=");
			$hits = $query->execute();

			$hit_count = 0;
			$register_count = 0;
			foreach ($hits as $hit) {
				$hit_count++;
				if ($hit->action == "register") {
					$register_count++;
				}
			}

			$output['affiliate_id'] = $row->affiliate_code;
			$output['original_user'] = $row->user_info;
			$output['hit_count'] = $hit_count;
			$output['register_count'] = $register_count;
			$output['date'] = date("Y-m-d h:i:s", $row->created);
			$url = Url::fromRoute('affiliate_tracking.affiliate_details', ['affiliate_id' => $row->affiliate_code]);
			$output['operations'] = \Drupal::l(t('Details'),$url);
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

	 	$markup = drupal_render($table);


		return array(
			'#markup' => $markup,
		);
	}

	public function affiliateDetails($affiliate_id) {
		$header = array(
			'affiliate_id' => t('Affiliate ID'),
			'original_user' => t('Creator of Affiliate'),
			'hit_count' => t('Hit Count'),
			'register_count' => t('Register Count'),
			'date' => t('Date')
		);

		$rows = array();

		$db = \Drupal::database();

		$query = $db->select('at_links', 'n');
		$query->fields('n');
		$query->condition('affiliate_code', $affiliate_id, "=");
		$result = $query->execute();

		$affiliate_actual_id;

		foreach ($result as $row) {
			$output = array();

			$query = $db->select('at_hits', 'n');
			$query->fields('n');
			$query->condition('affiliate_id', $row->affiliate_id, "=");
			$hits = $query->execute();

			$hit_count = 0;
			$register_count = 0;
			foreach ($hits as $hit) {
				$hit_count++;
				if ($hit->action == "register") {
					$register_count++;
				}
			}

			$output['affiliate_id'] = $row->affiliate_code;
			$affiliate_actual_id = $row->affiliate_id;
			$output['original_user'] = $row->user_info;
			$output['hit_count'] = $hit_count;
			$output['register_count'] = $register_count;
			$output['date'] = date("Y-m-d h:i:s", $row->created);

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

		$markup = drupal_render($table);

		$header = array(
			'node' => t('Node'),
			'action' => t('Action'),
			'user_info' => t('User Info'),
			'ip_address' => t('IP Address'),
			'referrer' => t('Referrer'),
			'date' => t('Date')
		);

		$db = \Drupal::database();

		$rows = array();

		$query = $db->select('at_hits', 'n');
		$query->fields('n');
		$query->condition('affiliate_id', $affiliate_actual_id, "=");
		$query->orderBy('n.created', 'DESC');
		$result = $query->execute();

		foreach ($result as $row) {
			$output = array();

			if ($row->nid != 0) {
				$node = node_load($row->nid);

				$options = ['absolute' => TRUE];
				$url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $node->nid->value], $options);

				$output['node'] = \Drupal::l(t($node->title->value),$url);
			} else {
				$output['node'] = "none";
			}

			$output['action'] = $row->action;
			$output['user_info'] = $row->user_info;
			$output['ip_address'] = $row->ip_address;
			$output['referrer'] = $row->forwarder;
			$output['date'] = date("Y-m-d h:i:s", $row->created);

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

		$markup .= drupal_render($table);


		return array(
			'#markup' => $markup,
		);
	}

	static function addTrackingCode($requested_code = null, $user_info = "original user") {
		if ($requested_code == null) {
			$name = AffiliateTrackingController::generateRandomString(20);
		} else {
			$name = $requested_code;
			// check to see if code exists.
		}

		// $user_info = "original user";
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


	static function addTrackingCodeForm() {

		return array(
			'#markup' => "Here is your form.",
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
		$db = \Drupal::database();

		$query = $db->select('at_links', 'n');
		$query->fields('n');
		$query->condition('affiliate_code', $affiliate_id, "=");
		$result = $query->execute();
		$affiliate_link = null;
		$count = 0;
		foreach ($result as $row) {
			$affiliate_link = $row;
			$count++;
		}
		if ($count > 0) {

			$path = $_SERVER['HTTP_REFERER'];
			$path = str_replace("https://". $_SERVER['SERVER_NAME'], "", $path);
			$path = str_replace("http://". $_SERVER['SERVER_NAME'], "", $path);

			$path = parse_url($path);
			$path = $path['path'];

			$path = \Drupal::service('path.alias_manager')->getPathByAlias($path);
			// $path = strpos($path, "/node/");
			if (strpos($path, "/node/") === false) {
				$path = 0;
			} else {
				$path = str_replace("/node/", "", $path);
			}
			$output = $path;

			$result = db_insert('at_hits')->fields(array(
				'affiliate_id' => $affiliate_link->affiliate_id,
				'user_info' => $hit_user,
				'ip_address' => $_SERVER['REMOTE_ADDR'],
				'forwarder' => $_SERVER['HTTP_REFERER'],
				'nid' => $path,
				'action' => $action,
				'created' => time(),
			))->execute();
		}

		$response = new JsonResponse();
		$response->setContent(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
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
