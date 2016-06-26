<?php
return  array(
	'NAME_CONTROLLER' => 'page',
	'NAME_MODEL' => 'index',
	'404' => array('404', 'task=page&action=404'),          
	'default' => array(
		'[task]/[action]/[params]',
		'task=[task]&action=[action]',
		'params' => '(.*)',
		'_params' => array(
			'[name]/[value]/', 
			'[name]=[value]'
			)
        ),       
	);
?>