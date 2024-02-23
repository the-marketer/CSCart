<?php
/**
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @license     https://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @docs        https://themarketer.com/resources/api
 **/

namespace Mktr\Helper;

abstract class DataBase
{
    protected $cast = [];
    protected $columns = [];
    protected $attributes = [];
    protected $junk = [];
    protected $ref = [];
    protected $functions = [];
    protected $vars = [];
    protected $orderBy = null;
    protected $direction = 'ASC';
    protected $limit = 250;
    protected $hide = [];
    protected $dateFormat = 'Y-m-d H:i';
    protected $data = null;
    protected $list = null;

    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        } else {
            if (MKTR_DEV) {
                throw new \Exception("Method {$name} does not exist.");
            }

            return null;
        }
    }

    public static function __callStatic($name, $arguments)
    {
        $i = new static();
        if (method_exists($i, $name)) {
            return call_user_func_array([$i, $name], $arguments);
        } else {
            if (MKTR_DEV) {
                throw new \Exception("Static method {$name} does not exist.");
            }

            return null;
        }
    }

    protected function getTime()
    {
        return new \DateTime('@' . $this->timestamp);
    }

    private function toArray($if = null)
    {
        $list = [];
        if ($this->attributes) {
            foreach ($this->attributes as $key => $value) {
                if (!in_array($key, $this->hide)) {
                    $value = $this->{$key};
                    if ($value !== null && array_key_exists($key, $this->cast)) {
                        if (in_array($this->cast[$key], ['date', 'datetime'])) {
                            $list[$key] = $value->format($this->dateFormat);
                        } else {
                            $list[$key] = $value;
                        }
                    } else {
                        if ($if !== null && in_array($key, $if)) {
                            if (!empty($value)) {
                                $list[$key] = $value;
                            }
                        } else {
                            $list[$key] = $value;
                        }
                    }
                }
            }
        }

        return $list;
    }

    public function __get($key)
    {
        if ($this->data !== null) {
            if (array_key_exists($key, $this->attributes) && $this->attributes[$key] !== null) {
                return $this->attributes[$key];
            } elseif (array_key_exists($key, $this->ref)) {
                if (in_array($this->ref[$key], $this->functions)) {
                    $this->attributes[$key] = call_user_func_array([$this, $this->ref[$key]], []);
                } elseif (in_array($this->ref[$key], $this->vars)) {
                    $v = $this->ref[$key];
                    $this->attributes[$key] = $this->{$v};
                } else {
                    if (array_key_exists($key, $this->cast)) {
                        $this->attributes[$key] = $this->cast($key, $this->data[$this->ref[$key]]);
                    } elseif ($this->data !== null) {
                        $this->attributes[$key] = $this->data[$this->ref[$key]];
                    } else {
                        $this->attributes[$key] = null;
                    }
                }
            } elseif (array_key_exists($key, $this->data)) {
                if (!array_key_exists($key, $this->junk)) {
                    if (array_key_exists($key, $this->cast)) {
                        $this->junk[$key] = $this->cast($key, $this->data[$key]);
                    } elseif ($this->data !== null) {
                        $this->junk[$key] = $this->data[$key];
                    } else {
                        $this->junk[$key] = null;
                    }
                }

                return $this->junk[$key];
            } else {
                $this->attributes[$key] = null;
            }
        } else {
            return null;
        }

        return $this->attributes[$key];
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    protected function cast($key, $value)
    {
        switch ($this->cast[$key]) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
                return (float) $value;
            case 'double':
                return (float) $this->toDigit($value);
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'object':
            case 'array':
                return unserialize($value);
            case 'json':
                return json_decode($value, true);
            case 'date':
            case 'datetime':
                return new \DateTime($value);
            case 'timestamp':
                return date('U', $value);
            default:
                return $value;
        }
    }

    protected function unCast($key, $value)
    {
        switch ($this->cast[$key]) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (int) $value;
            case 'object':
            case 'array':
                return serialize($value);
            case 'json':
                return json_encode($value, true);
            case 'date':
            case 'datetime':
                return $value->format('c');
            case 'timestamp':
                return (new \DateTime($value))->format('c');
            default:
                return $value;
        }
    }

    protected function toDigit($num = null, $digit = 2)
    {
        if ($num !== null) {
            $num = str_replace(',', '.', $num);
            $num = preg_replace('/\.(?=.*\.)/', '', $num);

            return number_format((float) $num, $digit, '.', '');
        }

        return null;
    }

    protected function toJson($data = null)
    {
        return Valid::toJson($data);
    }
}
