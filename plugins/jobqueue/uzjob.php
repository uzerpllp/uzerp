<?php
/**
 *  uzERP Delayed jobs
 * 
 *  A framework for queueing jobs for later execution by workers
 * 
 *  @author Steve Blamey <blameys@blueloop.net>
 *  @license GPLv3 or later
 *  @copyright (c) 2019 uzERP LLP (support#uzerp.com). All rights reserved.
 **/

class uzJobException extends Exception
{
}

abstract class uzJob
{

    private $user;

    private $egs_co;

    /**
     *
     * @param string $user
     *            uzERP user that started the job
     * @param integer $egs_co
     *            uzERP user company id that started the job
     */
    public function __construct($user, $egs_co)
    {
        $this->user = $user;
        $this->egs_co = $egs_co;
    }

    /**
     *
     * @return integer Job ID
     *
     * @throws uzJobException
     */
    public function push()
    {
        $config = Config::Instance();

        DJJob::configure([
            'driver' => $config->get('DB_TYPE'),
            'host' => $config->get('DB_HOST'),
            'dbname' => $config->get('DB_NAME'),
            'user' => $config->get('DB_USER'),
            'password' => $config->get('DB_PASSWORD')
        ]);

        $job_id = DJJob::enqueue($this);
        if ($job_id === false) {
            throw new uzJobException('Failed to queue job');
        }

        return $job_id;
    }

    abstract public function perform();
}

abstract class uzExclusiveJob
{

    private $exclusive_with;

    /**
     *
     * @param string $user
     *            uzERP user that started the job
     * @param integer $egs_co
     *            uzERP user company id that started the job
     * @param string $exclusive_with
     *            Job will fail if a job with this class is already queued
     * @param string $queue
     *            Job queue name
     *
     * @return integer Job ID
     *
     * @throws uzJobException
     */
    public function __construct($user, $egs_co, $exclusive_with = '', $queue = 'exclusive')
    {
        $this->user = $user;
        $this->egs_co = $egs_co;
        $this->exclusive_with = $exclusive_with;
        $this->queue = $queue;
    }

    /**
     *
     * @return boolean
     */
    public function isQueued()
    {
        $db = DB::Instance();
        $stmt = 'select handler from jobs where queue = ? and failed_at is null and attempts = 0';
        $q = $db->getAll($stmt, [
            $this->queue
        ]);
        foreach ($q as $r) {
            $obj = unserialize(base64_decode($r['handler']));
            if (get_class($obj) == get_class($this)) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function canQueue()
    {
        $db = DB::Instance();
        $stmt = 'select handler from jobs where queue = ? and failed_at is null and attempts = 0';
        $q = $db->getAll($stmt, [
            $this->queue
        ]);
        foreach ($q as $r) {
            $obj = unserialize(base64_decode($r['handler']));
            echo get_class($obj);
            if (get_class($obj) == $this->exclusive_with) {
                return false;
            }
        }
        return true;
    }

    /**
     *
     * @return integer Job ID
     *
     * @throws uzJobException
     */
    public function push()
    {
        $config = Config::Instance();

        DJJob::configure([
            'driver' => $config->get('DB_TYPE'),
            'host' => $config->get('DB_HOST'),
            'dbname' => $config->get('DB_NAME'),
            'user' => $config->get('DB_USER'),
            'password' => $config->get('DB_PASSWORD')
        ]);

        if ($this->isQueued()) {
            throw new uzJobException("Job is already queued");
        }

        if ($this->exclusive_with !== '' && ! $this->canQueue()) {
            throw new uzJobException("Failed to queue job, a dependent job is already running");
        }

        $job_id = DJJob::enqueue($this, $this->queue);
        if ($job_id === false) {
            throw new uzJobException('Failed to queue job');
        }

        return $job_id;
    }

    abstract public function perform();
}

/**
 * Store messages from jobs and display them in the uzERP web UI
 *
 * @author steve
 *
 */
class uzJobMessages
{

    private $message_store;

    private $storage;

    private $user;

    private $egs_co;

    private $key_part;

    /**
     *
     * @param Flash $message_store
     * @param Cache $storage
     * @param string $user
     * @param integer $egs_co
     */
    public function __construct(Flash $message_store, Cache $storage, $user, $egs_co)
    {
        $this->message_store = $message_store;
        $this->storage = $storage;
        $this->user = $user;
        $this->egs_co = $egs_co;
        $this->key_part = $user . "_" . $egs_co;
    }

    /**
     * Inject dependencies and Return an instance
     *
     * @param string $user
     * @param integer $egs_co
     * @return uzJobMessages
     */
    public static function Factory($user, $egs_co)
    {
        $flash = Flash::Instance();
        $cache = Cache::Instance();

        $inst = new self($flash, $cache, $user, $egs_co);
        return $inst;
    }

    /**
     * Send a message
     *
     * @param unknown $token
     * @param unknown $message
     * @param string $type Flash type: 'error, 'warning' or 'success'
     */
    public function send($token, $message, $type = 'success')
    {
        $message_json = json_encode([
            'type' => $type,
            'message' => $message
        ]);
        $this->storage->add("{$token}_job_message", $message_json, 86400);
    }

    /**
     *
     * @return mixed
     */
    public function getMessageTokens()
    {
        $tokens = json_decode($this->storage->get("{$this->key_part}_jobs"));
        return $tokens;
    }

    /**
     *
     * @param string $token
     */
    public function storeMessageToken($token)
    {
        $tokens = $this->getMessageTokens();
        if ($tokens) {
            $tokens[] = $token;
        } else {
            $tokens = [
                $token
            ];
        }
        $this->storage->add("{$this->key_part}_jobs", json_encode($tokens));
    }

    public function displayJobMessages()
    {
        $tokens = $this->getMessageTokens();
        $new_tokens = [];
        if ($tokens) {
            foreach ($tokens as $token) {
                $job_message = json_decode($this->storage->get("{$token}_job_message"), true);
                if ($job_message) {
                    switch ($job_message['type']) {
                        case 'warning':
                            $this->message_store->addWarning($job_message['message']);
                            break;
                        case 'error':
                            $this->message_store->addError($job_message['message']);
                            break;
                        case 'success':
                            $this->message_store->addMessage($job_message['message']);
                            break;
                    }
                    $this->storage->delete("{$token}_job_message");
                } else {
                    $new_tokens[] = $token;
                }
            }
        }
        $this->storage->add("{$this->key_part}_jobs", json_encode($new_tokens));
    }
}
