<?php

class Connection
{
    /*datebase connection setting*/
    public $hostname = "";
    public $database = "";
    public $username = "";
    public $password = "";

    /*they are for connection and getting data from database*/
    private $link;
    private $result;

    public $sql;

    function construct()
    {
        $this->link = mysql_connect($this->hostname,$this->username,$this->password);
        if(!$this->link)
        {
                die("Can't connect to host...".mysql_error());
        }

        mysql_select_db($this->database,$this->link) or die("Can't slect databace...".mysql_error());

        mysql_set_charset("utf8");
    }


    function query($sql)
    {
        if(!empty($sql))
        {
            $this->sql = $sql;

            $this->result = mysql_query($this->sql);
            if(!$this->result)
            {
                echo $sql;
                die("Nothing:".mysql_error());
            }

            return $this->result;
        }
        else
        {
            return false;
        }
    }

    function destruct()
    {
        mysql_close($this->link);
    }
}

?>
