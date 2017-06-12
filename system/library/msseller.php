<?php
final class MsSeller extends Model {
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	const STATUS_DISABLED = 3;
	const STATUS_DELETED = 4;
	const STATUS_UNPAID = 5;
	const STATUS_INCOMPLETE = 6;
		
	const MS_SELLER_VALIDATION_NONE = 1;
	const MS_SELLER_VALIDATION_ACTIVATION = 2;
	const MS_SELLER_VALIDATION_APPROVAL = 3;

	private $isSeller = FALSE; 
	private $nickname;
	private $description;
	private $company;
	private $country_id;
	private $avatar;
	private $seller_status;

  	public function __construct($registry) {
  		parent::__construct($registry);

		if (isset($this->session->data['customer_id'])) {

			$seller_query = $this->db->query("
				SELECT s.*, md.description as md_description FROM " . DB_PREFIX . "ms_seller s
				LEFT JOIN `" . DB_PREFIX . "ms_seller_description` md
					ON (s.seller_id = md.seller_id AND md.language_id = '" . (int)$this->config->get('config_language_id') . "')
				WHERE s.seller_id = '" . (int)$this->session->data['customer_id'] . "'
				");
			
			if ($seller_query->num_rows) {
				$this->isSeller = TRUE;
				$this->nickname = $seller_query->row['nickname'];
				$this->description = $seller_query->row['md_description'];
				$this->avatar = $seller_query->row['avatar'];
				$this->seller_status = $seller_query->row['seller_status'];
			}
  		}
	}

	private function _dupeSlug($slug) {
		$similarity_query = $this->db->query("SELECT * FROM ". DB_PREFIX . "url_alias WHERE keyword LIKE '" . $this->db->escape($slug) . "%'");
		return ($similarity_query->num_rows > 0 AND $slug) ? $slug . $similarity_query->num_rows : $slug;
	}

  	public function isCustomerSeller($customer_id) {
		$sql = "SELECT COUNT(*) as 'total'
				FROM `" . DB_PREFIX . "ms_seller`
				WHERE seller_id = " . (int)$customer_id;
		
		$res = $this->db->query($sql);
		
		if ($res->row['total'] == 0)
			return FALSE;
		else
			return TRUE;	  		
  	}
  	
	public function getSellerName($seller_id) {
		$sql = "SELECT firstname as 'firstname'
				FROM `" . DB_PREFIX . "customer`
				WHERE customer_id = " . (int)$seller_id;
		
		$res = $this->db->query($sql);
		
		return $res->row['firstname'];
	}

	public function getSellerDescriptions($seller_id) {
		$seller_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ms_seller_description WHERE seller_id = '" . (int)$seller_id . "'");

		foreach ($query->rows as $result) {
			$seller_description_data[$result['language_id']] = array(
				'description'      => $result['description']
			);
		}

		return $seller_description_data;
	}

	public function getSellerNickname($seller_id) {
		$sql = "SELECT nickname
				FROM `" . DB_PREFIX . "ms_seller`
				WHERE seller_id = " . (int)$seller_id;

		$res = $this->db->query($sql);

		return ($res->rows && isset($res->row['nickname'])) ? $res->row['nickname'] : '';
	}
	
	public function getSellerEmail($seller_id) {
		$sql = "SELECT email as 'email' 
				FROM `" . DB_PREFIX . "customer`
				WHERE customer_id = " . (int)$seller_id;
		
		$res = $this->db->query($sql);
		
		return $res->row['email'];
	}
		
	public function createSeller($data) {
		$avatar = isset($data['avatar_name']) ? $this->MsLoader->MsFile->moveImage($data['avatar_name']) : '';

		$sql = "INSERT INTO " . DB_PREFIX . "ms_seller
				SET seller_id = " . (int)$data['seller_id'] . ",
					seller_status = " . (isset($data['status']) ? (int)$data['status'] : self::STATUS_INACTIVE) . ",
					seller_approved = " . (isset($data['approved']) ? (int)$data['approved'] : 0) . ",
					nickname = '" . $this->db->escape($data['nickname']) . "',
					avatar = '" . $this->db->escape($avatar) . "',
					offer_limit = '" . (isset($data['offer_limit']) ? (int)$data['offer_limit'] : 0) . "',
					date_created = NOW()";

		$this->db->query($sql);
		$seller_id = $this->db->getLastId();

		if (isset($data['description'])){
			foreach ($data['description'] as $language_id => $value){
				$this->db->query("INSERT INTO " . DB_PREFIX . "ms_seller_description SET
				seller_id = '" . (int)$seller_id . "',
				language_id = '" . (int)$language_id . "',
				description = '" . $this->db->escape($value['description']) . "'");
			}
		}

		//settings block
		if(!empty($data['settings'])) {
			if (!empty($data['settings']['slr_website'])) {
				$data['settings']['slr_website'] = $this->MsLoader->MsHelper->addHttp($data['settings']['slr_website']);
			}

			$this->MsLoader->MsSetting->createSellerSetting($data);
		}
		//end settings block

		if (isset($data['keyword'])) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'seller_id=" . (int)$seller_id . "', keyword = '" . $this->db->escape($this->_dupeSlug($data['keyword'])) . "'");
		}
	}
	
	public function nicknameTaken($nickname) {
		$sql = "SELECT nickname
				FROM `" . DB_PREFIX . "ms_seller` p
				WHERE p.nickname = '" . $this->db->escape($nickname) . "'";
		
		$res = $this->db->query($sql);

		return $res->num_rows;
	}
	
	public function editSeller($data) {
		$seller_id = (int)$data['seller_id'];

		$old_avatar = $this->getSellerAvatar($seller_id);
		
		if (!isset($data['avatar_name']) || ($old_avatar['avatar'] != $data['avatar_name'])) {
			$this->MsLoader->MsFile->deleteImage($old_avatar['avatar']);
		}
		
		if (isset($data['avatar_name'])) {
			if ($old_avatar['avatar'] != $data['avatar_name']) {			
				$avatar = $this->MsLoader->MsFile->moveImage($data['avatar_name']);
			} else {
				$avatar = $old_avatar['avatar'];
			}
		} else {
			$avatar = '';
		}


		$sql = "UPDATE " . DB_PREFIX . "ms_seller SET
					nickname = '" . $this->db->escape($data['nickname'])  . "',"
					. (isset($data['status']) ? "seller_status=  " .  (int)$data['status'] . "," : '')
					. (isset($data['approved']) ? "seller_approved=  " .  (int)$data['approved'] . "," : '')
					. " avatar = '" . $this->db->escape($avatar) . "'
				WHERE seller_id = " . (int)$seller_id;
		
		$this->db->query($sql);

		if (isset($data['description'])){
			foreach ($data['description'] as $language_id => $value){
				foreach ($data['description'] as $language_id => $value) {
					$sql = "INSERT INTO " . DB_PREFIX . "ms_seller_description SET
						seller_id = " . (int)$seller_id . ",
						description = '" . $this->db->escape($value['description']) . "',
						language_id = '" . (int)$language_id . "'
					ON DUPLICATE KEY UPDATE
						description = '" . $this->db->escape($value['description']) . "'";

					$this->db->query($sql);
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'seller_id=" . (int)$seller_id. "'");
		if (isset($data['keyword'])) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'seller_id=" . (int)$seller_id . "', keyword = '" . $this->db->escape($this->_dupeSlug($data['keyword'])) . "'");
		}
	}
		
	public function getSellerAvatar($seller_id) {
		$query = $this->db->query("SELECT avatar as avatar FROM " . DB_PREFIX . "ms_seller WHERE seller_id = '" . (int)$seller_id . "'");
		
		return $query->row;
	}
		
  	public function getNickname() {
  		return $this->nickname;
  	}

  	public function getCompany() {
  		return $this->company;
  	}
  	
  	public function getCountryId() {
  		return $this->country_id;
  	}

  	public function getDescription() {
  		return $this->description;
  	}
  	
  	public function getStatus() {
  		return $this->seller_status;
  	}
  	
  	public function isSeller() {
  		return $this->isSeller;
  	}
	
	public function getSalt($seller_id) {
		$sql = "SELECT salt
				FROM `" . DB_PREFIX . "customer`
				WHERE customer_id = " . (int)$seller_id;
		
		$res = $this->db->query($sql);
		
		return $res->row['salt'];		
	}
	

	public function adminEditSeller($data) {
		$seller_id = (int)$data['seller_id'];


        //settings block
        if(!empty($data['settings'])) {
			if (!empty($data['settings']['slr_website'])) {
				$data['settings']['slr_website'] = $this->MsLoader->MsHelper->addHttp($data['settings']['slr_website']);
			}

            $this->MsLoader->MsSetting->createSellerSetting($data);
        }
        //end settings block

		
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'seller_id=" . (int)$seller_id. "'");
		
		if (isset($data['keyword'])) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'seller_id=" . (int)$seller_id . "', keyword = '" . $this->db->escape($this->_dupeSlug($data['keyword'])) . "'");
		}

		$sql = "UPDATE " . DB_PREFIX . "ms_seller SET
					seller_status = '" .  (int)$data['status'] .  "',
					seller_approved = '" .  (int)$data['approved'] .  "',
					offer_limit  = '" . (int)$data['offer_limit'] . "'
				WHERE seller_id = " . (int)$seller_id;
		
		$this->db->query($sql);

		if (isset($data['description'])){
			foreach ($data['description'] as $language_id => $value){
				foreach ($data['description'] as $language_id => $value) {
					$sql = "INSERT INTO " . DB_PREFIX . "ms_seller_description SET
						seller_id = " . (int)$seller_id . ",
						description = '" . $this->db->escape($value['description']) . "',
						language_id = '" . (int)$language_id . "'
					ON DUPLICATE KEY UPDATE
						description = '" . $this->db->escape($value['description']) . "'";

					$this->db->query($sql);
				}
			}
		}
	}
	
	/********************************************************/
	
	
	public function getTotalSellers($data = array()) {
		$sql = "
			SELECT COUNT(*) as total
			FROM " . DB_PREFIX . "ms_seller ms
			WHERE 1 = 1 "
			. (isset($data['seller_status']) ? " AND seller_status IN  (" .  $this->db->escape(implode(',', $data['seller_status'])) . ")" : '');

		$res = $this->db->query($sql);

		return $res->row['total'];
	}
	
	public function getSeller($seller_id, $data = array()) {
		$sql = "SELECT	CONCAT(c.firstname, ' ', c.lastname) as name,
						c.email as 'c.email',
						ms.seller_id as 'seller_id',
						ms.nickname as 'ms.nickname',
						ms.seller_status as 'ms.seller_status',
						ms.seller_approved as 'ms.seller_approved',
						ms.date_created as 'ms.date_created',
						ms.avatar as 'ms.avatar',
						ms.offer_limit as 'ms.offer_limit',
						md.description as 'ms.description',
						ms.offer_limit as 'ms.offer_limit',
						(SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE `query` = 'seller_id=" . (int)$seller_id . "' LIMIT 1) AS keyword
				FROM `" . DB_PREFIX . "customer` c
				INNER JOIN `" . DB_PREFIX . "ms_seller` ms
					ON (c.customer_id = ms.seller_id)
				LEFT JOIN `" . DB_PREFIX . "ms_seller_description` md
					ON (c.customer_id = md.seller_id AND md.language_id = '" . (int)$this->config->get('config_language_id') . "')
				WHERE ms.seller_id = " .  (int)$seller_id
				. (isset($data['product_id']) ? " AND mp.product_id =  " .  (int)$data['product_id'] : '')
				. (isset($data['seller_status']) ? " AND seller_status IN  (" .  $this->db->escape(implode(',', $data['seller_status'])) . ")" : '')
				. " GROUP BY ms.seller_id
				LIMIT 1";
				
		$res = $this->db->query($sql);

		if (!isset($res->row['seller_id']) || !$res->row['seller_id'])
			return FALSE;
		else{
			$res->row['descriptions'] = $this->getSellerDescriptions($res->row['seller_id']);
			return $res->row;
		}
	}
	
	public function getSellers($data = array(), $sort = array(), $cols = array()) {
		$hFilters = $wFilters = '';
		if(isset($sort['filters'])) {
			$cols = array_merge($cols, array("`c.name`" => 1, "`ms.date_created`" => 1));
			foreach($sort['filters'] as $k => $v) {
				if (!isset($cols[$k])) {
					$wFilters .= " AND {$k} LIKE '%" . $this->db->escape($v) . "%'";
				} else {
					$hFilters .= " AND {$k} LIKE '%" . $this->db->escape($v) . "%'";
				}
			}
		}
		
		$sql = "SELECT
					SQL_CALC_FOUND_ROWS"
					// additional columns
					. (isset($cols['total_products']) ? "
						(SELECT COUNT(*) FROM " . DB_PREFIX . "product p
						LEFT JOIN " . DB_PREFIX . "ms_seller USING (seller_id)
						WHERE seller_id = ms.seller_id) as total_products,
					" : "")

					// default columns
					." CONCAT(c.firstname, ' ', c.lastname) as 'c.name',
					c.email as 'c.email',
					ms.seller_id as 'seller_id',
					ms.nickname as 'ms.nickname',
					ms.seller_status as 'ms.seller_status',
					ms.seller_approved as 'ms.seller_approved',
					ms.date_created as 'ms.date_created',
					ms.avatar as 'ms.avatar',
					md.description as 'ms.description'
				FROM `" . DB_PREFIX . "customer` c
				INNER JOIN `" . DB_PREFIX . "ms_seller` ms
					ON (c.customer_id = ms.seller_id)
				LEFT JOIN `" . DB_PREFIX . "ms_seller_description` md
					ON (c.customer_id = md.seller_id AND md.language_id = '" . (int)$this->config->get('config_language_id') . "')
				WHERE 1 = 1 "
				. (isset($data['seller_id']) ? " AND ms.seller_id =  " .  (int)$data['seller_id'] : '')
				. (isset($data['seller_status']) ? " AND seller_status IN  (" .  $this->db->escape(implode(',', $data['seller_status'])) . ")" : '')
				
				. $wFilters
				
				. " GROUP BY ms.seller_id HAVING 1 = 1 "
				
				. $hFilters
				
				. (isset($sort['order_by']) ? " ORDER BY {$sort['order_by']} {$sort['order_way']}" : '')
				. (isset($sort['limit']) ? " LIMIT ".(int)$sort['offset'].', '.(int)($sort['limit']) : '');

		$res = $this->db->query($sql);
		$total = $this->db->query("SELECT FOUND_ROWS() as total");
		if ($res->rows) $res->rows[0]['total_rows'] = $total->row['total'];
		
		return $res->rows;
	}
	
	public function getCustomers($sort = array()) {
		$sql = "SELECT  CONCAT(c.firstname, ' ', c.lastname) as 'c.name',
						c.email as 'c.email',
						c.customer_id as 'c.customer_id',
						ms.seller_id as 'seller_id'
				FROM `" . DB_PREFIX . "customer` c
				LEFT JOIN `" . DB_PREFIX . "ms_seller` ms
					ON (c.customer_id = ms.seller_id)
				WHERE ms.seller_id IS NULL"
				. (isset($sort['order_by']) ? " ORDER BY {$sort['order_by']} {$sort['order_way']}" : '')
    			. (isset($sort['limit']) ? " LIMIT ".(int)$sort['offset'].', '.(int)($sort['limit']) : '');

		$res = $this->db->query($sql);
		
		return $res->rows;
	}

	
	public function changeStatus($seller_id, $seller_status) {
		$sql = "UPDATE " . DB_PREFIX . "ms_seller
				SET	seller_status =  " .  (int)$seller_status . "
				WHERE seller_id = " . (int)$seller_id;
		
		$res = $this->db->query($sql);
	}
	
	public function changeApproval($seller_id, $approved) {
		$sql = "UPDATE " . DB_PREFIX . "ms_seller
				SET	approved =  " .  (int)$approved . "
				WHERE seller_id = " . (int)$seller_id;
		
		$res = $this->db->query($sql);
	}

	public function getTotalOffers($seller_id) {
		$sql = "SELECT offer_id FROM " . DB_PREFIX . "ms_offer WHERE seller_id = " . $seller_id;

		$res = $this->db->query($sql);
		return count($res->rows);
	}
	
	public function deleteSeller($seller_id) {
		// Delete all seller's settings
		$this->db->query("DELETE FROM " . DB_PREFIX . "ms_seller_setting WHERE seller_id = '" . (int)$seller_id . "'");

		// Delete seller's description
		$this->db->query("DELETE FROM " . DB_PREFIX . "ms_seller_description WHERE seller_id = '" . (int)$seller_id . "'");

		// Delete seller's SEO url
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE `query` = 'seller_id=".(int)$seller_id."'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "ms_seller WHERE seller_id = '" . (int)$seller_id . "'");
	}

	
}

?>
