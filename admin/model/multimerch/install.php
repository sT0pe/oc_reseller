<?php
class ModelMultimerchInstall extends Model {
	public function __construct($registry) {
		parent::__construct($registry);
		$this->load->model('localisation/language');
		$this->load->model('extension/extension');
		$this->load->model('extension/module');
	}
	
	public function createSchema() {
	
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_seller` (
		`seller_id` int(11) NOT NULL AUTO_INCREMENT,
		`nickname` VARCHAR(32) NOT NULL DEFAULT '',
		`company` VARCHAR(32) NOT NULL DEFAULT '',
		`description` TEXT NOT NULL DEFAULT '',
		`avatar` VARCHAR(255) DEFAULT NULL,
		`date_created` DATETIME NOT NULL,
		`seller_status` TINYINT NOT NULL,
		`seller_approved` TINYINT NOT NULL,
		`offer_limit` tinyint(4) NOT NULL DEFAULT '20',
		PRIMARY KEY (`seller_id`)) default CHARSET=utf8");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_seller_description` (
		`seller_description_id` int(11) NOT NULL AUTO_INCREMENT,
		`seller_id` int(11) NOT NULL,
		`language_id` int(11) NOT NULL,
		`description` text DEFAULT '',
		PRIMARY KEY (`seller_description_id`),
		UNIQUE KEY `seller_language_id` (`seller_id`,`language_id`)) DEFAULT CHARSET=utf8");


		/* messaging */
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_conversation` (
		`conversation_id` int(11) NOT NULL AUTO_INCREMENT,
		`conversation_from` int(11) DEFAULT NULL,
		`title` varchar(256) NOT NULL DEFAULT '',
		`date_created` DATETIME NOT NULL,
		PRIMARY KEY (`conversation_id`)) default CHARSET=utf8");

		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_message` (
		`message_id` int(11) NOT NULL AUTO_INCREMENT,
		`conversation_id` int(11) NOT NULL,
		`from` int(11) DEFAULT NULL,
		`from_admin` tinyint(1) NOT NULL DEFAULT 0,
		`message` text NOT NULL DEFAULT '',
		`read` tinyint(1) NOT NULL DEFAULT 0,
		`date_created` DATETIME NOT NULL,
		PRIMARY KEY (`message_id`)) default CHARSET=utf8");

		$this->db->query("
		CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "ms_conversation_participants (
		`conversation_id` int(11) NOT NULL,
		`customer_id` int(11) NOT NULL DEFAULT '0',
		`user_id` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`conversation_id`,`customer_id`,`user_id`))
		default CHARSET=utf8");

		$this->db->query("
		CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "ms_message_upload (
		`message_id` int(11) NOT NULL,
		`upload_id` int(11) NOT NULL,
		PRIMARY KEY (`message_id`, `upload_id`))
		default CHARSET=utf8");


		/* Offers */
		$this->db->query("
		CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "ms_conversation_to_offer (
		`offer_id` int(11) NOT NULL,
		`conversation_id` int(11) NOT NULL,
		PRIMARY KEY (`offer_id`,`conversation_id`))
		default CHARSET=utf8");

		$this->db->query("
		CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "ms_offer (
		`offer_id` int(11) NOT NULL AUTO_INCREMENT,
		`seller_id` int(11) NOT NULL,
		`offer_name` VARCHAR(255) NOT NULL,
		`date_start` DATE NOT NULL,
		`date_end` DATE NOT NULL,
		`offer_by_image` VARCHAR(255) NOT NULL,
		`offer_by_name` VARCHAR(255) NOT NULL,
		`offer_by_company` VARCHAR(255) NOT NULL,
		`offer_by_nip` int(11) NOT NULL DEFAULT '0',
		`offer_by_address` VARCHAR(255) NOT NULL,
		`offer_by_phone` VARCHAR(20) NOT NULL,
		`offer_by_email` VARCHAR(96) NOT NULL,
		`offer_for_image` VARCHAR(255) NOT NULL,
		`offer_for_name` VARCHAR(255) NOT NULL,
		`offer_for_company` VARCHAR(255) NOT NULL,
		`offer_for_nip` int(11) NOT NULL DEFAULT '0',
		`offer_for_address` VARCHAR(255) NOT NULL,
		`offer_for_phone` VARCHAR(20) NOT NULL,
		`offer_for_email` VARCHAR(96) NOT NULL,
		`services` TEXT NOT NULL,
		`date_created` DATETIME NOT NULL,
		PRIMARY KEY (`offer_id`)) default CHARSET=utf8");


		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ms_offer_product` (
		`offer_product_id` int(11) NOT NULL AUTO_INCREMENT,
		`offer_id` int(11) NOT NULL DEFAULT '0',
		`product_id` int(11) NOT NULL DEFAULT '0',
		`retail_price` DECIMAL(10,2) NOT NULL,
		`tax` DECIMAL(10,2) NOT NULL,
		`discount` DECIMAL(10,2) NOT NULL,
		`quantity` INT(11) NOT NULL DEFAULT '1',
		`recurring_id` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`offer_product_id`)) default CHARSET=utf8");

	}
	
	public function createData() {

		// multimerch routes
		// seller
		$this->db->query("INSERT INTO " . DB_PREFIX . "layout SET name = '[MultiMerch] Seller Account Pages'");
		$layout_id = $this->db->getLastId();
		$this->db->query("INSERT INTO " . DB_PREFIX . "layout_module (`layout_id`, `code`, `position`, `sort_order`) VALUES($layout_id, 'account', 'column_right', 1);");

		$this->db->query("INSERT INTO " . DB_PREFIX . "layout_route SET layout_id = '" . (int)$layout_id . "', route = 'seller/account-%'");
		$this->db->query("INSERT INTO " . DB_PREFIX . "layout SET name = '[MultiMerch] Sellers List'");


		// customer
		$this->db->query("INSERT INTO " . DB_PREFIX . "layout SET name = '[MultiMerch] Customer Account Pages'");
		$layout_id = $this->db->getLastId();
		$this->db->query("INSERT INTO " . DB_PREFIX . "layout_module (`layout_id`, `code`, `position`, `sort_order`) VALUES($layout_id, 'customer', 'column_right', 1);");

		$this->db->query("INSERT INTO " . DB_PREFIX . "layout_route SET layout_id = '" . (int)$layout_id . "', route = 'customer/%'");


		$account = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `code`='account' AND `key`='account_status'")->row;
		if(empty($account)) {
			$sql = "INSERT INTO " . DB_PREFIX . "setting SET `store_id` = 0, `code` = 'account', `key` = 'account_status', `value` = 1, `serialized` = 0";
		} else {
			$sql = "UPDATE " . DB_PREFIX . "setting SET `store_id` = 0, `code` = 'account', `key` = 'account_status', `value` = 1, `serialized` = 0 WHERE `setting_id` = " . (int)$account['setting_id'];
		}
		$this->db->query($sql);

	}
	
	public function deleteSchema() {
		$this->db->query("DROP TABLE IF EXISTS
		`" . DB_PREFIX . "ms_seller`,
		`" . DB_PREFIX . "ms_seller_description`,
		`" . DB_PREFIX . "ms_offer`,
		`" . DB_PREFIX . "ms_offer_product`");

		/* messaging */
		$this->db->query("DROP TABLE IF EXISTS
		`" . DB_PREFIX . "ms_message`,
		`" . DB_PREFIX . "ms_message_upload`,
		`" . DB_PREFIX . "ms_conversation_participants`,
		`" . DB_PREFIX . "ms_conversation_to_offer`,
		`" . DB_PREFIX . "ms_conversation`");

	}
	
	public function deleteData() {

		// remove MultiMerch routes
		$this->db->query("DELETE FROM " . DB_PREFIX . "layout WHERE name = '[MultiMerch] Seller Account Pages'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "layout_route WHERE route = 'seller/account-%'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "layout WHERE name = '[MultiMerch] Customer Account Pages'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "layout_route WHERE route = 'customer/%'");

		$mm_layout_modules = array('account', 'customer');
		foreach ($mm_layout_modules as $module_code) {
			$modules = $this->db->query("SELECT * FROM " . DB_PREFIX . "layout_module WHERE code = '" . $this->db->escape($module_code) . "'");
			if($modules->num_rows) {
				foreach ($modules->rows as $module) {
					$layout_exists = $this->db->query("SELECT 1 FROM " . DB_PREFIX . "layout WHERE layout_id = " . (int)$module['layout_id']);
					if(!$layout_exists->num_rows) {
						$this->db->query("DELETE FROM " . DB_PREFIX . "layout_module WHERE code = '" . $this->db->escape($module_code) . "' AND layout_id = " . (int)$module['layout_id']);
					}
				}
			}
		}

		// Uninstall MultiMerch modules
		$extensions = $this->model_extension_extension->getInstalled('module');
		foreach ($extensions as $key => $value) {
			if(strpos($value,'multimerch_') !== FALSE) {
				$this->model_extension_extension->uninstall('module', $value);
				$this->model_extension_module->deleteModulesByCode($value);
			}
		}

	}
}