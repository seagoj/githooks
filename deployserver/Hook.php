<?php
    require_once 'lib/Devtools/Log.php';

    class Hook {
        private $log;
        private $testCount;
        private $repository;
        private $docRoot;
        private $payload;
        private $config;
        public $output;

        public function __construct($options)
        {
            $this->_config($options);

            $this->log = new \Devtools\Log($this->config['file']);

            if($this->checkIP() && $this->getPayload())
            {
                $this->repository = $this->payload->repository->name;
                $this->docRoot = $this->config['docroot'].escapeshellcmd($this->repository);

                $pathExists = is_dir($this->docRoot);
                $this->log->file('is_dir('.$this->docRoot.')', $pathExists);

                if($pathExists) {
                    $this->updateRepo();
                }
            }
        }

        private function _config($options)
        {
            $defaults = [
                'docroot'=>'/var/www/',
                'file'=>'hook.log'
            ];

            foreach($options as $option=>$value) {
                if(array_key_exists($option, $defaults))
                    $defaults[$option]=$value;
                else
                    $this->log->file($option." is not a valid option for ".__CLASS__);
            }

            $this->config = $defaults;
   
        }

        private function checkIP()
        {
            if ( isset($_SERVER["REMOTE_ADDR"]) ) { 
                $requestIP = $_SERVER["REMOTE_ADDR"];
            } else if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ) { 
                $requestIP = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if ( isset($_SERVER["HTTP_CLIENT_IP"]) ) {
                $requestIP = $_SERVER["HTTP_CLIENT_IP"]; 
            } else $requestIP = '0.0.0.0';
            
            $validIPs = array(
                '72.1.161.68',
                '68.188.83.74',
                '204.232.175.75',
                '207.97.227.253',
                '50.57.128.197',
                '180.171.174.178'.
                '50.57.231.61',
                '54.235.183.49',
                '54.235.183.23',
                '54.235.118.251',
                '54.235.120.57',
                '54.235.120.61',
                '54.235.120.62',
                '127.0.0.1'
            );          
            
            $this->log->file(__METHOD__.'('.$requestIP.')', $result = in_array($requestIP, $validIPs));

            return in_array($requestIP, $validIPs);
        }

        private function getPayload($var='payload')
        {
            $this->payload = json_decode($_REQUEST[$var]);

            file_put_contents('tests/payload.json', $_REQUEST[$var]);

            $this->log->file(__METHOD__.'('.$var.')',$setPayload=isset($this->payload));

            return isset($this->payload);
        }

        private function updateRepo()
        {
            $actions = array(
                'cd'=>'cd '.$this->docRoot,
                'gitPull'=>'git pull -u git://github.com/seagoj/'.$this->repository.'.git master',
            );

            $command = implode($actions, ' && ');
            $this->log->file($command, $setCommand = isset($command));

            if($setCommand = isset($command))
                $output = shell_exec("$command 2>&1");

            $this->log->file($output, $output!=null);
            $this->output = $output;
        }

        public function __destruct()
        {
        }
    }
