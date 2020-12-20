<?php
if (!defined('Access')) {
	die('Silence is gold');
}

require_once INCLUDES_DIR . '/config.php';
require_once CLASSES_DIR . '/DataManager.php';

class Permission {

	const EDIT_USERS = "bool_edit_users";
	const EDIT_ROLES = "bool_edit_roles";

	const TABLE_NAME = "tbl_roles";
	private $dm;

	/**
	 * Permission constructor.
	 */
	public function __construct() {
		$this->dm = new DataManager();
	}

	public function accessIf(array $roles) {

		if ( !isset($_SESSION["user"]["user_role"]) || count($roles) < 1 ) {
			return false;
		}

		$roleId = $_SESSION["user"]["user_role"];

		$roleData = $this->dm->select( self::TABLE_NAME, $roles )
		                     ->where( "id", "=" )
		                     ->execute( "i", $roleId );

		$index = 0;
		while ( $index < count( $roles ) ) {
			if ( $roleData[0][ $roles[ $index ] ] != 1 ) {
				return false;
			}

			$index ++;
		}

		return true;
	}
}