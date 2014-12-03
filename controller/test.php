<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }
Database::initRoot();


Benchmark::get();
Benchmark::get();

for($a = 0;$a <= 10000;$a++)
{
	$value = Sanitize::variable("hello, this is a test. #$%^&*().,/.,/.,?>@#<?>@#<?> ---  I want to see how fast this is.");
}

Benchmark::get();

// 1.775399, 1.530529, 1.596456, 1.603668 -- with hiphop
// 10.190400, 10.110084, 9.718607

exit;
