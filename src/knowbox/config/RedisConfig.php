<?php
return [
	#默认配置	
	"default" => [
        "HOST" => '127.0.0.1',
	    "PORT" => '6379',
	    "AUTH" => '',
	    "DB" => '0',
	], 
	#Redis实例 可继承default
	"instance" => [
	    [
	        "HOST" => '127.0.0.1',
	    ],
        [
	        "HOST" => '127.0.0.1',
	    ],
        [
	        "HOST" => '127.0.0.1',
	    ],
        [
	        "HOST" => '127.0.0.1',
	    ],
	    
	]
];

