<?php
namespace li3b_users\models;

use li3b_core\util\Util;
use lithium\util\Validator;
use lithium\storage\Cache;
use lithium\util\Inflector;
use lithium\security\Auth;
use lithium\security\Password;
use lithium\data\entity\Document;
use \MongoId;
use \Exception;

class User extends \li3b_core\models\BaseModel {

	protected $_meta = array(
		'locked' => true,
		'connection' => 'li3b_users'
		//'source' => 'users'
	);
	
	protected $_schema = array(
		'_id' => array('type' => 'id'),
		'firstName' => array('type' => 'string'),
		'lastName' => array('type' => 'string'),
		'url' => array('type' => 'string'),
		'email' => array('type' => 'string'),
		'password' => array('type' => 'string'),
		'role' => array('type' => 'string'),
		'active' => array('type' => 'boolean'),
		'lastLoginIp' => array('type' => 'string'),
		'lastLoginTime' => array('type' => 'date'),
		'created' => array('type' => 'date')
	);
	
	public $url_field = array('firstName', 'lastName');
	
	public $url_separator = '-';
	
	public $search_schema = array(
		'first_name' => array(
			'weight' => 1  
		),
		'last_name' => array(
			'weight' => 1
		),
		'email' => array(
			'weight' => 1
		)
	);
	
	// These are user roles for the entire system.
	protected $_user_roles = array(
		'administrator' => 'Administrator',
		'content_editor' => 'Content Editor',
		'registered_user' => 'Registered User'
	);
	
	public $validates = array(
		'firstName' => array(
			array('notEmpty', 'message' => 'First name cannot be empty.')
		),
		'email' => array(
			array('notEmpty', 'message' => 'E-mail cannot be empty.'),
			array('email', 'message' => 'E-mail is not valid.'),
			// array('uniqueEmail', 'message' => 'Sorry, this e-mail address is already registered.'),
		),
		'password' => array(
			array('notEmpty', 'message' => 'Password cannot be empty.'),
			array('notEmptyHash', 'message' => 'Password cannot be empty.'),
			array('moreThanFive', 'message' => 'Password must be at least 6 characters long.')
		)
	);
	
	public static function __init() {
		/*
		 * Some special validation rules
		*/
		Validator::add('uniqueEmail', function($value) {
			$current_user = Auth::check('li3b_user');
			if(!empty($current_user)) {
				$user = User::find('first', array('fields' => array('_id'), 'conditions' => array('email' => $value, '_id' => array('$ne' => new MongoId($current_user['_id'])))));
			} else {
				$user = User::find('first', array('fields' => array('_id'), 'conditions' => array('email' => $value)));
			}
			if(!empty($user)) {
			    return false;
			}
			return true;
		});
		
		Validator::add('notEmptyHash', function($value) {    
			if($value == Password::hash('')) {	
			    return false;
			}
			return true;
		});
		
		Validator::add('moreThanFive', function($value) {
			if(strlen($value) < 5) {	
				return false;
			}
			return true;
		});
		
		parent::__init();
	}
	
	/**
	 * Get the user roles.
	 * 
	 * @return Array
	*/
	public static function userRoles() {
		$class =  __CLASS__;
		return $class::_object()->_user_roles;
	}
	
}
?>