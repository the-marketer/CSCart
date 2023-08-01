<?php
/**
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @license     https://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @docs        https://themarketer.com/resources/api
 **/

namespace Mktr\Model;

use Mktr\Helper\DataBase;

class Subscription extends DataBase
{
    protected $attributes = [
        'email_address' => null,
        'firstname' => null,
        'lastname' => null,
        'phone' => null,
        'subscribed' => null,
    ];
    protected $ref = [
        'email_address' => 'email',
        'firstname' => 'getFirstName',
        'lastname' => 'getLastName',
        'phone' => 'getPhone',
        'subscribed' => 'getSubscribed',
    ];

    protected $functions = [
        'getPhone',
        'getSubscribed',
        'getFirstName',
        'getLastName',
    ];

    protected $vars = [];

    protected $cast = [
        'subscribed' => 'bool',
    ];

    protected $is = null; /* newsletter | customer */
    protected $name = null;
    protected $adressData = null;
    protected $tmp = null;

    protected $direction = 'ASC';
    protected $dateFormat = 'Y-m-d H:i';

    private static $i = null;
    private static $d = [];
    private static $curent = null;

    public static function i()
    {
        if (self::$i === null) {
            self::$i = new static();
        }

        return self::$i;
    }

    public static function c()
    {
        return self::$curent;
    }

    public static function getByEmail($email, $new = false)
    {
        if ($new || !array_key_exists($email, self::$d)) {
            self::$d[$email] = new static();
            self::$d[$email]->tmp = $email;
            $retrun = fn_get_users(['email' => $email, 'items_per_page' => 1], \Tygh\Tygh::$app['session']['auth']);
            if (!empty($retrun[0])) {
                self::$d[$email]->data = $retrun[0][0];
            } else {
                self::$d[$email]->data = [
                    'email' => $email,
                ];
            }
        }

        self::$curent = self::$d[$email];

        return self::$curent;
    }

    protected function getName($w = null)
    {
        if ($this->name === null) {
            // $split = explode('@', $this->email);
            // if (!array_key_exists(0, $split)) { $split[0] = null; }
            $this->name = [
                // 'firstname' => $split[0],
                // 'lastname' => $split[0],
                'firstname' => '',
                'lastname' => '',
            ];
        }

        return $w === null ? $this->name : $this->name[$w];
    }

    protected function getFirstName()
    {
        return isset($this->data['firstname']) ? $this->data['firstname'] : $this->getName('firstname');
    }

    protected function getLastName()
    {
        return isset($this->data['lastname']) ? $this->data['lastname'] : $this->getName('lastname');
    }

    protected function getPhone()
    {
        return array_key_exists('phone', $this->data) ? \Mktr\Helper\Valid::validateTelephone($this->data['phone']) : null;
    }

    protected function getSubscribed()
    {
        return !empty(db_get_field('SELECT subscriber_id FROM ?:subscribers WHERE email = ?s', $this->email));
    }
}
