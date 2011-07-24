<?php
/**
 * Description of SubjectECI
 *
 * @author Yoshihide
 */
class SubjectECI extends AbstractECI {
	public function getSubjectSchema(){
		return $this ->getSchema('subjects');
	}
}
