<?php
namespace efi;

require_once(__DIR__."/../../init.php");

use \efi\user_auth;


abstract class user_jobs extends user_auth {

    private $user_token = "";
    protected $user_email = "";
    protected $jobs = array();
    private $is_admin = false;
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function load_jobs($token) {
        $this->user_token = $token;
        $this->user_email = self::get_email_from_token($this->db, $token);
        if (!$this->user_email)
            return;

        $this->is_admin = self::get_user_admin($this->db, $this->user_email);

        $this->load_user_jobs();
    }

    protected abstract function load_user_jobs();

    public function get_jobs() {
        return $this->jobs;
    }

    public function get_email() {
        return $this->user_email;
    }

    public function is_admin() {
        return $this->is_admin;
    }
}



