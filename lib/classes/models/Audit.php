<?php

class Audit extends DataObject
{
    public function __construct($tablename = 'audit_header')
    {
        parent::__construct($tablename);
        $this->idField = 'id';
    }

    public static function &Instance($type = null)
    {
        static $audit = null;

        if (! $audit) {
            $audit = new Audit();

            $result = false;

            $result = $audit->loadBy('sessionid', session_id());

            if ($result === false) {
                $data = [
                    'sessionid' => session_id(),
                    'username' => '',
                    'customer_id' => '',
                ];

                if (isset($_SESSION['username'])) {
                    $data['username'] = $_SESSION['username'];
                }

                if (isset($_SESSION['customer_id'])) {
                    $data['customer_id'] = $_SESSION['customer_id'];
                }

                $errors = [];

                $audit = Audit::Factory($data, $errors, 'Audit');

                $audit->save();
            }
        }

        if (isset($_SESSION['username']) && $audit->username != $_SESSION['username']) {
            $audit->username = $_SESSION['username'];

            $audit->save();
        }

        return $audit;
    }

    public function update($id, $fields, $values)
    {
        if (isLoggedIn()) {
            if (isset($_SESSION['customer_id'])) {
                $this->customer_id = $_SESSION['customer_id'];
            }

            if (isset($_SESSION['username'])) {
                $this->username = $_SESSION['username'];
            }
        }

        $this->save();
    }

    public function write($msg, $newline = true, $elapsed_time = 0)
    {
        $data = [
            'audit_id' => $this->id,
            'username' => $this->username,
            'line' => $msg,
            'remote_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'referer' => (empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER']),
            'elapsed_time' => $elapsed_time,
            'memory_used' => memory_get_usage(),
        ];

        $errors = [];

        $auditline = Auditlines::Factory($data, $errors, 'Auditlines');

        if ($auditline) {
            $auditline->save();
        }
    }
}
