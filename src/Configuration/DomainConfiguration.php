<?php

namespace LdapRecord\Configuration;

use LdapRecord\Ldap;

class DomainConfiguration
{
    /**
     * The configuration options array.
     *
     * The default values for each key indicate the type of value it requires.
     *
     * @var array
     */
    protected $options = [
        // An array of LDAP hosts.
        'hosts' => [],

        // The global LDAP operation timeout limit in seconds.
        'timeout' => 5,

        // The LDAP version to utilize.
        'version' => 3,

        // The port to use for connecting to your hosts.
        'port' => Ldap::PORT,

        // The base distinguished name of your domain.
        'base_dn' => '',

        // The username to use for binding.
        'username' => '',

        // The password to use for binding.
        'password' => '',

        // Whether or not to use SSL when connecting.
        'use_ssl' => false,

        // Whether or not to use TLS when connecting.
        'use_tls' => false,

        // Whether or not follow referrals is enabled when performing LDAP operations.
        'follow_referrals' => false,

        // Custom LDAP options.
        'options' => [],
    ];

    /**
     * Constructor.
     *
     * @param array $options
     *
     * @throws ConfigurationException When an option value given is an invalid type.
     */
    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Get all configuration options.
     *
     * @return array
     */
    public function all()
    {
        return $this->options;
    }

    /**
     * Sets a configuration option.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws ConfigurationException When an option value given is an invalid type.
     */
    public function set($key, $value)
    {
        if ($this->validate($key, $value)) {
            $this->options[$key] = $value;
        }
    }

    /**
     * Returns the value for the specified configuration options.
     *
     * @param string $key
     *
     * @throws ConfigurationException When the option specified does not exist.
     *
     * @return mixed
     */
    public function get($key)
    {
        if ($this->has($key)) {
            return $this->options[$key];
        }

        throw new ConfigurationException("Option {$key} does not exist.");
    }

    /**
     * Checks if a configuration option exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->options);
    }

    /**
     * Validate the configuration option.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws ConfigurationException When an option value given is an invalid type.
     *
     * @return bool
     */
    protected function validate($key, $value)
    {
        $default = $this->get($key);

        if (is_array($default)) {
            $validator = new Validators\ArrayValidator($key, $value);
        } elseif (is_int($default)) {
            $validator = new Validators\IntegerValidator($key, $value);
        } elseif (is_bool($default)) {
            $validator = new Validators\BooleanValidator($key, $value);
        } else {
            $validator = new Validators\StringOrNullValidator($key, $value);
        }

        return $validator->validate();
    }
}
