<?php

/**
 * APIProducerV2DriverRedis
 * @author Kimo Rosenbaum <kimor79@yahoo.com>
 * @version $Id$
 * @package APIProducerV2DriverRedis
 */

include 'api_producer/v2/classes/driver.php';

class APIProducerV2DriverRedis extends APIProducerV2Driver{

	protected $redis;

	public function __construct($slave_okay = false, $config = array()) {
		parent::__construct($slave_okay, $config);

		$host = $this->getConfig('host', '127.0.0.1');
		$password = $this->getConfig('password', false);
		$port = $this->getConfig('port', 6379);
		$prefix = $this->getConfig('prefix', false);
		$timeout = $this->getConfig('timeout', 30);

		$this->redis = new Redis();

		try {
			if(!$this->redis->connect($host, $port, $timeout)) {
				throw new Exception('Unable to connect');
			}
		} catch (RedisException $e) {
			throw new Exception($e->getMessage());
		}

		if($password) {
			if(!$this->redis->auth($password)) {
				throw new Exception('Unable to authenticate');
			}
		}

		if(!$this->redis->setOption(Redis::OPT_PREFIX, $prefix)) {
			throw new Exception('Unable to set prefix');
		}
	}

	public function __deconstruct() {
		parent::__deconstruct();
		$this->redis->close();
	}

	/**
	 * Get a config value
	 * @param string $key
	 * @param string $default
	 * @return string
	 */
	protected function getConfig($key = '', $default = '') {
		$type = 'rw_' . $key;
		if($this->slave_okay) {
			$type = 'ro_' . $key;
		}

		if(array_key_exists($type, $this->config)) {
			return $this->config[$type];
		}

		if(array_key_exists($key, $this->config)) {
			return $this->config[$key];
		}

		return $default;
	}

	/**
	 * Reset error string
	 */
	protected function resetError() {
		$this->error = '';
		//$this->redis->clearLastError();
	}
}

?>
