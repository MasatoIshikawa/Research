<?php
/**
 * Description of LoginBaseModel
 *
 * @author Yoshihide
 */
class LoginBaseModel extends AbstractBaseModel{
	/**
	 * 問題なし
	 */
	const OK = 0;
	const MULTIPLE_LOGIN = 1;
	const TIMEOUT = 2;
	const NOT_LOGGED_IN = 3;
	const IP_CHANGE = 4;

	public $last_error;


	public function login($user_id){
		if(ENABLE_SECURITY_CHECK == FALSE){
			return self::OK;
		}

		$sql = "SELECT `login_id` ".
			   "FROM `kyomu_extension`.`logins` ".
			   "WHERE `user_id` = :user_id ".
				 "AND `login_timestamp` < (SYSDATE() - INTERVAL :timeout SECOND)";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindValue(':user_id', $user_id);
		$stmt ->bindValue(':timeout', LOGIN_TIMEOUT);
		$stmt ->execute();

		if($stmt ->columnCount() > 0 && ALLOW_MUTIPLE_LOGIN == false){
			return self::MULTIPLE_LOGIN;
		}

		$sql = "DELETE FROM `kyomu_extension`.`logins` WHERE `user_id` = :user_id";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindValue(':user_id', $user_id);
		$stmt ->execute();

		$sql = "INSERT INTO `kyomu_extension`.`logins` (`user_id`, `login_ip`) ".
			   "VALUES (:user_id, :ip)";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindValue(':user_id', $user_id);
		$stmt ->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
		$stmt ->execute();

		return self::OK;
	}

	public function update($user_id){
		if(ENABLE_SECURITY_CHECK == FALSE){
			return self::OK;
		}
		
		$sql = "SELECT `login_id`, `login_ip`, `login_timestamp` ".
			   "FROM `kyomu_extension`.`logins` ".
			   "WHERE `user_id` = :user_id";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindValue(':user_id', $user_id);
		$stmt ->execute();

		if($stmt ->columnCount() == 0){
			return self::NOT_LOGGED_IM;
		}

		$result = $stmt ->fetch();

		if(ALLOW_IP_CHANGE == FALSE && $result['login_ip'] != $_SERVER['REMOTE_ADDR']){
			return self::IP_CHANGE;
		}

		if(LOGIN_TIMEOUT != 0 && strtotime($result['login_timestamp']) > (time() - mktime(0, 0, LOGIN_TIMEOUT))){
			return self::TIMEOUT;
		}
		
		$sql = "UPDATE `kyomu_extension`.`logins` WHERE `login_id` = :login_id";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindValue(':login_id', $result['login_id']);
		$stmt ->execute();

		return self::OK;
	}

	public function logout($user_id){
		if(ENABLE_SECURITY_CHECK == FALSE){
			return self::OK;
		}

		$response = $this ->update($user_id);

		if($response != self::OK){
			return $response;
		}

		$sql = "DELETE FROM `kyomu_extension`.`logins` WHERE `user_id` = :user_id";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindValue(':user_id', $user_id);
		$stmt ->execute();

		return self::OK;
	}

	public function getLastErrorMessage(){
		switch ($this ->last_error) {
			case self::IP_CHANGE:
				return '接続IPアドレスが変更されました。接続を拒否します。';;
				break;

			case self::MULTIPLE_LOGIN;
				return '多重ログインもしくは正しくログアウトされていませんでした。数分後に再度ログインし直して見てください。';
				break;

			case self::NOT_LOGGED_IN;
				return 'ログインしていません。';
				break;

			case self::TIMEOUT;
				return '接続がタイムアウトしました';
				break;

			case self::OK;
				return '';
				break;

			default:
				return '不明なエラーが発生しました。';
				break;
		}
	}
}
