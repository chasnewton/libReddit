<?
class Test {
	public function __construct() {
		$r = new Run();
		$r->call(array(&$this, '_hidden'));
	}

	public function visible() {
		print "Called public function.\n";
	}
	private function hidden() {
		print "Called private function!\n";
	}
} 

class Run {
	public function call($f) {
		call_user_func($f);
	}
}


print_r(gettype(create_function('','print "hi";')));
?>
