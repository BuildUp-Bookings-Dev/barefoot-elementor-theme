<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'default' => [
		'showFilterButton' => false,
		'calendarOptions' => [
			'monthsToShow' => 2,
		]
	],
	'villa-rentals' => [
		'targetUrl' => '/properties/',
		'showLocation' => false,
		'showFilterButton' => false,
		'dateLabel' => 'Dates of Stay',
		'datePlaceholder' => 'Check in — Check out',
		'calendarOptions' => [
			'monthsToShow' => 2,
			'defaultMinDays' => 1,
			'datepickerPlacement' => 'auto',
			'showTooltip' => true,
			'tooltipLabel' => 'Nights',
			'showClearButton' => true,
		],
		'fields' => [
			[
				'label' => 'Bedrooms',
				'type' => 'select',
				'options' => [
					[
						'label' => 'Studio',
						'value' => '0',
					],
					[
						'label' => '1 Bedroom',
						'value' => '1',
					],
					[
						'label' => '2 Bedrooms',
						'value' => '2',
					],
				],
				'position' => 'end',
				'required' => false,
				'key' => 'bedrooms',
				'icon' => 'fa-solid fa-bed',
			],
		],
		'filters' => [],
	],
];
