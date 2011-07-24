<?php
/**
 * Description of SchoolInfoBaseModel
 *
 * @author Yoshihide
 */
class SchoolBaseModel extends Kyomu_AbstractBaseModel{

    public function getPrincipalName(){
		return $this ->configModel ->getConfig('principal_name');
	}

	public function getSchoolName(){
		return $this ->configModel ->getConfig('school_name');
	}
}
