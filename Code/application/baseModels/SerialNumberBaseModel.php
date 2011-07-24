<?php
class SerialNumberBaseModel extends Kyomu_AbstractBaseModel
{	
	public $label = null;
	public $serialNumber = null;
	public $update = null;

	public function setLabel($label)
	{
		$this->label = $label;
	}
	
	public function setUpdate($update)
	{
		$this->update = $update;
	}
	
	public function number()
	{
		if (null === $this->serialNumber) {
			$this->serialNumber = $this->configModel->get($this->label);
		}
		$this->serialNumber++;
		return $this->serialNumber;
	}
	
	public function update()
	{
		$this->configModel->set($this->label, $this->serialNumber);
	}

	public function __destruct()
	{
		if ($this->update) {
			$this->update();
		}
	}
}