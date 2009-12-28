<?php

abstract class Properties {
	protected $_target;
	protected $_child;

	protected function construct($target) {
		$this->_target = $target;
		$this->_child = null;
        }

        public function __get($name) {
                if(method_exists($this->_child, "_" . $name))
                        return call_user_func(array($this->_child, "_" . $name));
                return null; // TODO: Change to exception.
        }

	protected function setChild($child) {
		$this->_child = $child;
	}

	protected function getTarget() {
		return $this->_target;
	}
}


?>
