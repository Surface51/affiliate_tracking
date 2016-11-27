<?php
/**
 * @file
 * Contains \Drupal\affiliate_tracking\AffiliateTrackingForm.
 */
namespace Drupal\affiliate_tracking;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AffiliateTrackingForm extends FormBase {
	/**
	 * {@inheritdoc}
	 */
	public function getFormId() {
		return 'resume_form';
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state) {
		$form['affiliate_code'] = array(
			'#type' => 'textfield',
			'#title' => t('Affilate Code:'),
			'#required' => TRUE,
		);

		$form['actions']['#type'] = 'actions';
		$form['actions']['submit'] = array(
			'#type' => 'submit',
			'#value' => $this->t('Create Affiliate Tracking Code'),
			'#button_type' => 'primary',
		);
		return $form;
	}

	/**
	 * {@inheritdoc}
	 */
		public function validateForm(array &$form, FormStateInterface $form_state) {
			$affiliate_code = $form_state->getValue('affiliate_code');

			$user_info = 1;
			$db = \Drupal::database();
			$query = $db->select('at_links', 'n');
			$query->fields('n');
			$query->condition('affiliate_code', $affiliate_code, "=");
			$result = $query->execute();
			$count = 0;
			foreach ($result as $row) {
				$count++;
			}
			if ($count != 0) {
				$form_state->setErrorByName('affiliate_code', $this->t('Affiliate Code Already is Use'));
			}
		}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {

		$affiliate_code = $form_state->getValue('affiliate_code', 'admin');

		AffiliateTrackingController::addTrackingCode($affiliate_code);

		$basename = $_SERVER['HTTP_HOST'];

		drupal_set_message($this->t('The new affiliate code is: @aff_code. Any traffic with the affiilate code appeneded to the URL will be tracked. The format of the URL should be as <a href="http://@website_url?affiliate=@aff_code">http://@website_url?affiliate=@aff_code</a>', array('@aff_code' => $form_state->getValue('affiliate_code'), '@website_url' => $basename)));
	 }
}
