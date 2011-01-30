<?php 

// db functionality

require("config.php");
class db {
    function connect() {
        $this->db = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die (mysql_error());
            mysql_select_db(DB_NAME, $this->db) or die (mysql_error());
    } 
    function query($query, $unbuffered = false) {
        if ($unbuffered == true) {
            $this->query_id = mysql_unbuffered_query($query, $this->db);
        } else {
            $this->query_id = mysql_query($query, $this->db);
        } 
        if (!$this->query_id) {
            $this->fatal_error("mySQL query error: $query");
        } 
        return $this->query_id;
    } 
    function disconnect() {
        mysql_close($this->db);
    } 
    function this() {
    	return $this->db;
    }
    
    function fatal_error($the_error) {
        $the_error .= "\n\nmySQL error: " . mysql_error() . "\n";
        $the_error .= "mySQL error code: " . mysql_errno() . "\n";
        
        @header("HTTP/1.0 403 Forbidden");
    	print $the_error;
    	print_r($_REQUEST);
        exit;
    } 
	function free($query_id) {
		mysql_free_result($query_id);
	}
} 

?>
