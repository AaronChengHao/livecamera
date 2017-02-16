<?php
/**
 * livecamera class
 * @Author: Aaron
 * @Date:   2017-02-12 23:13:39
 * @Last Modified by:   Aaron
 * @Last Modified time: 2017-02-15 16:24:46
 */

require './assist.php';

class livecamera
{
	/**
	 * user maera
	 */
	const ACTION_DRAW = 'draw';

	/**
	 * user quit
	 */
	const ACTION_QUIT = 'quit';

	/**
	 * online user number
	 */
	const ACTION_ONLINENUM = 'onlineNum';

	/**
	 * listen server addr
	 *
	 * @var null
	 */
	private $_listenAddr = null;

	/**
	 * listen server port
	 *
	 * @var null
	 */
	private $_listenPort = null;

	/**
	 * swoole server object
	 *
	 * @var null
	 */
	private $_swoole = null;

	/**
	 * client set
	 *
	 * @var array
	 */
	private $_fds = [];


	public function __construct( array $config = [] )
	{
		$this->_listenAddr = $config['addr'];
		$this->_listenPort = $config['port'];
		$swoole = new swoole_websocket_server($this->_listenAddr,$this->_listenPort);
		$swoole->on('open',[$this,'open']);
		$swoole->on('message',[$this,'receive']);
		$swoole->on('close',[$this,'close']);
		$this->_swoole = $swoole;
	}

	/**
	 * connect callback
	 *
	 * @param  swoole_websocket_server $server  swoole_websocket_server_obj
	 * @param  object                  $request user_request_object
	 * @return void
	 */
	public function open(swoole_websocket_server $server, $request)
	{
		print $request->fd.PHP_EOL;
		$nowfd = $request->fd;
		$this->_fds[$nowfd] = $nowfd;
		foreach ($this->_fds as $fd) {
			$this->_swoole->push($fd,json_encode(assist::formatData(self::ACTION_ONLINENUM,$nowfd,count($this->_fds))));
		}
	}

	/**
	 * receive callback
	 *
	 * @param  swoole_websocket_server $server  swoole_websocket_server_obj
	 * @param  [type]                  $frame  [description]
	 * @return void
	 */
	public function receive(swoole_websocket_server $server, $frame)
	{
		print 'send message is :'. $frame->data.PHP_EOL;
		$nowfd = $frame->fd;
		$fds   = $this->_fds;
		foreach ($fds as $fd) {
			if ($fd === $nowfd) continue;
			$this->_swoole->push($fd,json_encode(assist::formatData(self::ACTION_DRAW,$nowfd,$frame->data)));
		}
	}

	/**
	 * close callback
	 *
	 * @param  swoole_websocket_server $server  swoole_websocket_server_obj
	 * @param  [int]                   $quitfd  user_socket_descriptor
	 * @return void
	 */
	public function close(swoole_websocket_server $server, $quitfd)
	{
		print $quitfd . PHP_EOL;
		unset($this->_fds[$quitfd]);
		$fds = $this->_fds;
		foreach ($fds as  $fd) {
			$this->_swoole->push($fd,json_encode(assist::formatData(self::ACTION_QUIT,$quitfd,'')));
			$this->_swoole->push($fd,json_encode(assist::formatData(self::ACTION_ONLINENUM,$quitfd,count($this->_fds))));
		}
	}

	/**
	 * websocket server start
	 *
	 * @return void
	 */
	public function start()
	{
		$this->_swoole->start();
	}

}

$serverConfig = [
	'addr' => '0.0.0.0',
	'port' => 10001
];
$liveServer = new livecamera($serverConfig);
$liveServer->start();
