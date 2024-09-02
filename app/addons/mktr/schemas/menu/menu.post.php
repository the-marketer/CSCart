<?php
/**
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @license     https://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @docs        https://themarketer.com/resources/api
 **/
$schema['central']['marketing']['items']['mktr'] = [
    'attrs' => ['class' => 'is-addon'],
    'href' => 'addons.update&addon=mktr&selected_sub_section=mktr_tracker&selected_section=settings',
    'position' => 1,
    'subitems' => [
        'mktr_tracker' => [
            'href' => 'addons.update&addon=mktr&selected_sub_section=mktr_tracker&selected_section=settings',
            'position' => 1,
        ],
        'mktr_google' => [
            'href' => 'addons.update&addon=mktr&selected_sub_section=mktr_google&selected_section=settings',
            'position' => 1,
        ],
    ],
];

return $schema;
