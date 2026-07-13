<?php
// This file is generated. Do not modify it manually.
return array(
	'build' => array(
		'schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'tsjippy-news-gallery/show',
		'version' => '0.1.0',
		'title' => 'News gallery',
		'category' => 'widgets',
		'description' => 'A showcast of recent content',
		'textdomain' => 'tsjippy',
		'editorScript' => 'file:./index.js',
		'style' => 'file:./style-index.css',
		'usesContext' => array(
			'postType'
		),
		'attributes' => array(
			'postTypes' => array(
				'type' => 'array',
				'default' => array(
					
				)
			),
			'amount' => array(
				'type' => 'integer',
				'default' => 10
			),
			'categories' => array(
				'type' => 'array',
				'default' => array(
					
				)
			),
			'title' => array(
				'type' => 'string',
				'default' => ''
			),
			'color' => array(
				'type' => 'string',
				'default' => ''
			),
			'gradient' => array(
				'type' => 'boolean',
				'default' => false
			),
			'age' => array(
				'type' => 'integer',
				'default' => 60
			)
		)
	)
);
