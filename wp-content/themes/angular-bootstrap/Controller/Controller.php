<?php

class AppException extends Exception {
    
}

require_once("Database.php");

class Controller extends Database {

    private function __construct() {
        exit('Init function is not allowed');
    }

    private static function checkCustomerToken($token) {
        return 1;
    }

    private static function xml2array($optionsXML) {
        if (!empty($optionsXML)) {
            $options = '<OPTIONS>' . $optionsXML . '</OPTIONS>';
            return (array) simplexml_load_string(str_replace('&', '&amp;', $options), 'SimpleXMLElement', LIBXML_NOBLANKS);
        } else {
            return array();
        }
    }

    static public function set_creator( &$in_value, $key) {
        $in_value = "`{$in_value}`=:{$in_value}";
    }
    
    static public function values_creator( &$in_value, $key) {
        $in_value = ":{$key}";
    }
    
    /************************************************************************
      CUSTOMER FUNCTIONS
     ************************************************************************/

    static public function auth($request) {
        foreach (array('email', 'password') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }
        $token = bin2hex(openssl_random_pseudo_bytes(16));

        $sqlStr = "UPDATE customer set token = UNHEX(:token), options = updateOptionValue(options, 'LAST_LOGIN', NOW())
            WHERE email = :email AND password = UNHEX(MD5(:password)) ";

        $sth = self::pdo()->prepare($sqlStr);
        $sth->bindValue(':token', $token, PDO::PARAM_STR);
        $sth->bindValue(':email', $email, PDO::PARAM_STR);
        $sth->bindValue(':password', $password, PDO::PARAM_STR);
        $sth->execute();

        if ($sth->rowCount() == 0) {
            $token = null;
        }
        return( array("token" => $token));
    }

    static public function getCustomerInfo($request) {
        foreach (array('i_customer', 'token') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }

        $iCustomer = self::checkCustomerToken($token);

        $i_customer = intval($i_customer);

        $sqlStr = "SELECT c.i_customer, c.email, c.first_name, c.options, issue_date, 
            co.title 'co_name', co.description AS 'co_desc'
            FROM customer c
            LEFT JOIN company co ON c.i_company = co.i_company
            WHERE c.i_customer = :i_customer";

        $sth = self::pdo()->prepare($sqlStr);
        $sth->execute(array(":i_customer" => $i_customer));

        $result = array();
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['options'])) {
                $row['options'] = self::xml2array($row['options']);
            }
            $result[] = $row;
        }
        return($result);
    }

    /***********************************************************************
      EVENT FUNCTIONS
     ***********************************************************************/
    static public function getEventList($request) {
        $limit = empty($request->limit) ? 10 : intval($request->limit);
        $offset = empty($request->offset) ? 0 : intval($request->offset);

        $sqlStr = "SELECT i_event, title, HEX(duration) duration, issue_date, short_desc"
                . " FROM event";

        $sth = self::pdo()->query($sqlStr);

        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        foreach($result as $key => $row) {
            if (!empty($row['duration'])) {
                $binStr = "";
                foreach (str_split($row['duration']) as $hexDig) {
                    $binStr .= str_pad(base_convert($hexDig, 16, 2), 4, '0', STR_PAD_LEFT);
                }
                $result[$key]['duration'] = str_split($binStr, 24);
            }
        }
        return $result;
    }

    static public function setEventInfo($request) {
        /*
          foreach (array('token') as $fnParamName) {
          if (empty($request->$fnParamName)) {
          throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
          }
          $$fnParamName = $request->$fnParamName;
          }
          $i_customer = self::checkCustomerToken($token);
         */
        $columns = array('title', 'short_desc', 'full_desc', 'options', 'duration', 'i_group');
        if (isset($request->i_event)) {
            $columns[] = 'i_event';
        }
        foreach ($columns as $value) {
            $$value = empty($request->$value) ? NULL : $request->$value;
        }
        if (!empty($duration)) {
            $hexStr = '';
            foreach (str_split($duration, 4) as $binStr) {
                $hexStr .= base_convert($binStr, 2, 16);
            }
            $duration = $hexStr;
        }

        $set = self::setBuilder($columns, array('duration' => 'UNHEX'));

        $sqlStr = "INSERT INTO event SET {$set} ON DUPLICATE KEY UPDATE {$set},i_event=LAST_INSERT_ID(i_event)";

        $sth = self::pdo()->prepare($sqlStr);
        foreach ($columns as $value) {
            $sth->bindValue(':' . $value, $$value);
        }
        $sth->execute();

        $i_event = self::pdo()->lastInsertId();

        return(array('i_event' => $i_event));
    }

    static public function getEventInfo($request) {
        foreach (array('i_event') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }
        $fields = 'i_event,title,short_desc,full_desc,options,HEX(duration) AS duration,i_group';

        $sqlStr = "SELECT {$fields} FROM event e WHERE e.i_event = :i_event";
        $sth = self::pdo()->prepare($sqlStr);
        $sth->execute(array(":i_event" => $i_event));

        $row = $sth->fetch(PDO::FETCH_ASSOC);
        if (!empty($row['options'])) {
            $row['options'] = self::xml2array($row['options']);
        }

        if (!empty($row['duration'])) {
            $binStr = "";
            foreach (str_split($row['duration']) as $hexDig) {
                $binStr .= str_pad(base_convert($hexDig, 16, 2), 4, '0', STR_PAD_LEFT);
            }
            $row['duration'] = str_split($binStr, 24);
        }
        if(!empty($row['i_project'])) {
            $row['i_project'] = self::getProjectInfo(array('i_project' => $row['i_project']));
        }
        if(!empty($row['i_group'])) {
            $row['i_group'] = self::getProjectInfo(array('i_group' => $row['i_group']));
        }
        return($row);
    }

    static public function delEventInfo($request) {
        foreach (array('i_event', 'token') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }

        //$i_customer = self::checkCustomerToken($token);

        $sqlStr = "DELETE FROM event WHERE i_event = :i_event";
        $sth = self::pdo()->prepare($sqlStr);
        $sth->execute(array(":i_event" => $i_event));

        $result = ($sth->rowCount());
        return( array('count' => $result));
    }

    /*********************************************************************
                             SCHEDULE FUNCTIONS 
     *********************************************************************/
    static public function getScheduleList($request) {
        $fields = "s.i_schedule, DATE(s.start_date) start_date, e.title, HEX(e.duration) duration, e.i_event";
        $from = "FROM schedule s INNER JOIN event e ON s.i_event = e.i_event";
        $order = "ORDER BY s.start_date";
        $where = $limit = "";

        if( !empty($request->i_event)) {
            $i_event = intval($request->i_event);
            $where = "WHERE e.i_event = $i_event";
        }
        if(!empty($request->limit)) {
            $_limit = intval($request->limit);
            $offset = empty($request->offset) ? 0 : intval($request->offset);
            $limit = "LIMIT {$offset}, {$limit}";
        }
        $sqlStr = "SELECT $fields $from $where $order $limit";

        $sth = self::pdo()->query($sqlStr);
        $result = $sth->fetchAll( PDO::FETCH_ASSOC );

        foreach($result as $key => $row) {
            if (!empty($row['duration'])) {
                $binStr = "";
                foreach (str_split($row['duration']) as $hexDig) {
                    $binStr .= str_pad(base_convert($hexDig, 16, 2), 4, '0', STR_PAD_LEFT);
                }
                $result[$key]['duration'] = str_split($binStr, 24);
            }
        }
        return $result;
    }

    static public function setScheduleInfo($request) {
        console.log("setScheduleInfo");
        console.log($request);
        foreach (array('i_event', 'start_date', 'token') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }

        $i_customer = self::checkCustomerToken($token);
        $i_schedule = empty($request->i_schedule) ? null : $request->i_schedule;

        $i_event = intval($i_event);

        if (!empty($i_schedule)) {
            $sqlStr = "UPDATE schedule SET start_date = :start_date  WHERE i_schedule = :i_schedule";
            $sth = self::pdo()->prepare($sqlStr);
            $sth->bindValue(':start_date', $start_date, PDO::PARAM_STR);
            $sth->bindValue(':i_schedule', $i_schedule, PDO::PARAM_INT);
            $sth->execute();
        } else {
            $sqlStr = "INSERT INTO `schedule` (start_date, i_event) VALUES (:start_date, :i_event)";
            $sth = self::pdo()->prepare($sqlStr);
            $sth->bindValue(':start_date', $start_date, PDO::PARAM_STR);
            $sth->bindValue(':i_event', $i_event, PDO::PARAM_INT);
            $sth->execute();
            $i_schedule = self::pdo()->lastInsertId();
        }
        return(array('i_schedule' => $i_schedule));
    }

    static public function getScheduleInfo($request) {
        foreach (array('i_schedule') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }
        $sqlStr = "SELECT s.i_schedule, s.start_date, s.options, e.i_event, e.title, HEX(e.duration) as duration "
                . "FROM schedule s INNER JOIN event e ON s.i_event = e.i_event "
                . "WHERE s.i_schedule = :i_schedule";

        $sth = self::pdo()->prepare($sqlStr);
        $sth->execute(array(":i_schedule" => $i_schedule));

        $row = $sth->fetch(PDO::FETCH_ASSOC);
        if (!empty($row['options'])) {
            $row['options'] = self::xml2array($row['options']);
        }
        if (!empty($row['duration'])) {
            $binStr = "";
            foreach (str_split($row['duration']) as $hexDig) {
                $binStr .= str_pad(base_convert($hexDig, 16, 2), 4, '0', STR_PAD_LEFT);
            }
            $row['duration'] = str_split($binStr, 24);
        }

        return($row);
    }

    static public function delScheduleInfo($request) {
        foreach (array('i_schedule', 'token') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }

        $i_customer = self::checkCustomerToken($token);

        $sqlStr = "DELETE FROM schedule WHERE i_schedule = :i_schedule";
        $sth = self::pdo()->prepare($sqlStr);
        $sth->execute(array(":i_schedule" => $i_schedule));

        $result = ($sth->rowCount());
        return( array('count' => $result));
    }

    /**************** TICKETS  FUNCTIONS **************************/
    static public function getTicketList($request) {
        foreach (array('i_schedule') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }
        /* @var $i_schedule integer */
        $i_schedule = intval($i_schedule);
        if (empty($i_schedule)) {
            throw new AppException(__METHOD__ . " Argument i_schedule sould be integer");
        }
        $sqlStr = "SELECT t.i_ticket, t.title, t.description, t.price, t.quantity, t.options, t.issue_date, t.i_schedule,
            (SELECT COUNT(s.i_subscription) FROM subscription s WHERE s.i_ticket =  t.i_ticket) AS ordered
            FROM ticket t WHERE t.i_schedule = " . $i_schedule;

        return( self::pdo()->query($sqlStr)->fetchAll(PDO::FETCH_ASSOC) );
    }

    /**
     * 
     * @param array $request
     * @return mixed
     */
    static public function getTicketInfo($request) {
        foreach (array('i_ticket') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }
        /* @var $i_ticket integer */
        $i_ticket = intval($i_ticket);
        if (empty($i_ticket)) {
            throw new AppException(__METHOD__ . " Argument i_ticket sould be integer");
        }
        $sqlStr = "SELECT t.i_ticket, t.title, t.description, t.price, t.quantity, t.options, t.issue_date, t.i_schedule,
            (SELECT COUNT(s.i_subscription) FROM subscription s WHERE s.i_ticket =  t.i_ticket) AS ordered
            FROM ticket t WHERE t.i_ticket = " . $i_ticket;
        
        $row = self::pdo()->query($sqlStr)->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $row['price'] = floatval($row['price']);
            $row['quantity'] = intval($row['quantity']);
            $row['ordered'] = intval($row['ordered']);
        }

        return($row);
    }

    /* ************************* TICKET FUNCTIONS ************************* */
    static private function setBuilder($columns, $functions = NULL) {
        $setExpr = array();
        foreach ($columns as $key => $value) {
            $param = isset($functions[$value]) ? "{$functions[$value]}(:{$value})" : ":{$value}";
            $setExpr[$key] = "{$value}={$param}";
        }
        return implode(",", $setExpr);
    }

    /*
cd /var/www/html/eventmanager/wp-content/themes/angular-bootstrap/Controller
curl -i -X POST -d @test/setTicketInfo.txt http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
     */
    static public function setTicketInfo($request) {
        $columns = array('title', 'description', 'price', 'quantity', 'i_schedule');
        foreach ($columns as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }
        //$i_ticket = empty($request->i_ticket) ? 0 : intval($request->i_ticket);
        if (!empty($request->i_ticket)) {
            $columns[] = 'i_ticket';
            $i_ticket = intval($request->i_ticket);
        }
        $set = self::setBuilder($columns);

        $sqlStr = "INSERT INTO ticket SET {$set} ON DUPLICATE KEY UPDATE {$set},i_ticket=LAST_INSERT_ID(i_ticket)";

        $sth = self::pdo()->prepare($sqlStr);
        foreach ($columns as $key => $value) {
            $sth->bindValue(':' . $value, $$value);
        }
        $sth->execute();

        $i_ticket = self::pdo()->lastInsertId();
        return( array('i_ticket' => $i_ticket) );
    }

    static public function delTicketInfo($request) {
        foreach (array('i_ticket') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = intval($request->$fnParamName);
        }

        $sqlStr = "SELECT i_schedule FROM ticket WHERE i_ticket = {$i_ticket}";
        $i_schedule = self::pdo()->query($sqlStr)->fetchColumn();
        
        $sqlStr = "DELETE FROM ticket WHERE i_ticket = :i_ticket";
        $sth = self::pdo()->prepare($sqlStr);
        $sth->execute(array(":i_ticket" => $i_ticket));

        $result = self::getTicketList((object)['i_schedule' => $i_schedule]);
        return( $result );
    }

    /************************ PROJECT FUNCTIONS ************************************/
/*
curl -X POST -H "AUTHORIZATION: Bearer D755F3CA53BE5FB3EEAE8DCC337BACEB"  -d "functionName=getProjectList" http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
 */
    static public function getProjectList($request) {
        
        $prime_key = "i_project";
        
        $fields = "p.*";
        $from = "FROM project p";
        $order = "ORDER BY start_date";
        $where = $limit = "";

        if( !empty($request->$prime_key)) {
            $$prime_key = intval($request->$prime_key);
            $where = "WHERE {$prime_key} = {$$prime_key}";
        }
        if(!empty($request->limit)) {
            $_limit = intval($request->limit);
            $offset = empty($request->offset) ? 0 : intval($request->offset);
            $limit = "LIMIT {$offset}, {$limit}";
        }
        $sqlStr = "SELECT {$fields} {$from} {$where} {$order} {$limit}";
        return( self::pdo()->query($sqlStr)->fetchAll(PDO::FETCH_ASSOC) );
    }
/*
curl -X POST  -d @setProjectInfo.txt http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
 */
    static public function setProjectInfo($request) {

        $primaryKey = "i_project";
        $fields = [
            'title',
            'short_desc',
            'full_desc',
            'options',
            'logo_url',
            'start_date',
            'end_date',
        ];
        $i_customer = self::checkCustomerToken($request->token);

        $params = [];
        $setArr = [];

        if(!empty($request->$primaryKey)) {
            $params[$primaryKey] = intval($request->$primaryKey);
            if(empty($params[$primaryKey])) {
                throw new AppException(__METHOD__ . " Empty Primary key");
            }
        }
        foreach ($fields as $value) {
            $params[$value] = empty($request->$value) ? null : $request->$value;
            $setArr[] = "{$value}=:{$value}";
        }
        $setStr = implode(",", $setArr);
        
        $params['start_date'] = empty($request->start_date) ? null : date("Y-m-d", intval($request->start_date) / 1000);
        $params['end_date'] = empty($request->end_date) ? null : date("Y-m-d", intval($request->end_date) / 1000);

        if (!empty($params[$primaryKey])) {
            $sqlStr = "UPDATE `project` SET $setStr  WHERE {$primaryKey} = :{$primaryKey}";
        } else {
            $sqlStr = "INSERT INTO `project` SET {$setStr}";
        }
        $sth = self::pdo()->prepare($sqlStr);
        $sth->execute($params);
        $last_id = self::pdo()->lastInsertId();
        
        if(empty ($last_id)) {
            $last_id = $request->$primaryKey;
        }
        return(array( $primaryKey => $last_id));
    }
/*    
curl -X POST -d "functionName=getProjectList" http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
*/      
    static public function delProject($request) {
        foreach (array('i_project') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }
        $i_project = intval($i_project);
        
        $sqlStr = "DELETE FROM project WHERE i_project = :i_project";
        $sth = self::pdo()->prepare($sqlStr);
        $sth->execute(array(":i_project" => $i_project));

        $result = ($sth->rowCount());
        return( array('count' => $result));
    }



    /*********************************************************************
     *                        GROUP FUNCTIONS
     *********************************************************************/

    static public function getGroupList($request) {

        $limit = empty($request->limit) ? 0 : intval($request->limit);
        $offset = empty($request->offset) ? 0 : intval($request->offset);

        $sqlStr = "SELECT i_group, title, description, issue_date FROM group";

        if ($limit != 0) {
            $sqlStr .= " LIMIT {$offset}, {$limit}";
        }

        return( self::pdo()->query($sqlStr)->fetchAll(PDO::FETCH_ASSOC) );
    }

    static public function getGroupInfo($request) {
        foreach (array('i_group') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }
        /* @var $i_ticket integer */
        $i_group = intval($i_group);
        if (empty($i_group)) {
            throw new AppException(__METHOD__ . " Argument i_group sould be integer");
        }
        $sqlStr = "SELECT i_group, title, description, issue_date, options "
                . "FROM group WHERE i_group ={$i_group}";

        return( self::pdo()->query($sqlStr)->fetchAll(PDO::FETCH_ASSOC) );
    }

    static public function setGroupInfo($request) {
        $columns = array('title', 'description', 'options');
        foreach ($columns as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }
        if (!empty($request->i_group)) {
            $columns[] = 'i_group';
            $i_group = intval($request->i_group);
        }
        $set = self::setBuilder($columns);

        $sqlStr = "INSERT INTO group SET {$set} ON DUPLICATE KEY UPDATE {$set},i_group=LAST_INSERT_ID(i_group)";

        $sth = self::pdo()->prepare($sqlStr);
        foreach ($columns as $key => $value) {
            $sth->bindValue(':' . $value, $$value);
        }
        $sth->execute();

        $i_group = self::pdo()->lastInsertId();
        return( array('i_group' => $i_group) );
    }

    static public function delGroupInfo($request) {
        foreach (array('i_group') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }
        $sqlStr = "DELETE FROM group WHERE i_group = :i_group";
        $sth = self::pdo()->prepare($sqlStr);
        $sth->execute(array(":i_group" => $i_group));

        $result = ($sth->rowCount());
        return( array('count' => $result));
    }
    
    /************************ TRAINER FUNCTIONS ************************/

    static protected function getTrainerFields() {
        return array(
            "i_trainer" => 0,
            "name" => 1,
            "second_name" => 1,
            "last_name" => 1,
            "email" => 1,
            "short_desc" => 1,
            "full_desc" => 1,
            //"photo_url" => 1,
        );
    }
    
    /**
curl -X POST -H "AUTHORIZATION: Bearer D755F3CA53BE5FB3EEAE8DCC337BACEB"  -d "functionName=getTrainerList" http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
     */
    static public function getTrainerList($request = []) {
        try {
            $fields = implode(",", array_keys (self::getTrainerFields()));
            $from = "FROM trainer";
            $order = "ORDER BY name";
            $where = $limit = "";

            if( !empty($request->i_trainer)) {
                $i_trainer= intval($request->i_trainer);
                $where = "WHERE i_trainer = $i_trainer";
            }
            if(!empty($request->limit)) {
                $_limit = intval($request->limit);
                $offset = empty($request->offset) ? 0 : intval($request->offset);
                $limit = "LIMIT {$offset}, {$limit}";
            }
            $sqlStr = "SELECT $fields $from $where $order $limit";
            $sth = self::pdo()->query($sqlStr);
            $result = $sth->fetchAll( PDO::FETCH_ASSOC );
        } catch (Exception $e) {
            $result = array( "error" => $e->getMessage() );
        } finally {
            return $result;
        }
    }
/*
cd /var/www/html/eventmanager/wp-content/themes/angular-bootstrap/Controller
curl -X POST -H "AUTHORIZATION: Bearer D755F3CA53BE5FB3EEAE8DCC337BACEB"  -d @setTrainerInfo.json http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
 */
    static public function setTrainerInfo($request) {

        $requredFields = array_keys(self::getTrainerFields(), 1);
        $allFields = array_keys(self::getTrainerFields());
        $setExpression = $requredFields;
        array_walk( $setExpression, 'self::set_creator');
        $setExpression = implode(",", $setExpression);
        $valuesExpression = implode(",", $requredFields);

        foreach( $requredFields as $fnParamName ) {
            if (!isset($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }
        $i_customer = self::checkCustomerToken($token);

        $params = array(
            "name" => $name,
            "second_name" => $second_name,
            "last_name" => $last_name,
            "email" => $email,
            "short_desc" => $short_desc,
            "full_desc" => $full_desc,
            //"photo_url" => $photo_url,
        );
        if (!empty($request->i_trainer)) {
            $sqlStr = "UPDATE trainer SET $setExpression  WHERE i_trainer = :i_trainer";
            $params["i_trainer"] = $request->i_trainer;
        } else {
            $sqlStr = "INSERT INTO `trainer` SET {$setExpression}";
        }

        $sth = self::pdo()->prepare($sqlStr);
        $sth->execute($params);
        $i_trainer = self::pdo()->lastInsertId();
        $i_trainer = intval(empty($i_trainer) ? $request->i_trainer : $i_trainer);
        
        return(self::getTrainerList());
    }

    static public function getTrainerInfo($request) {
        foreach (array('i_schedule') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }
        $sqlStr = "SELECT s.i_schedule, s.start_date, s.options, e.i_event, e.title, HEX(e.duration) as duration "
                . "FROM schedule s INNER JOIN event e ON s.i_event = e.i_event "
                . "WHERE s.i_schedule = :i_schedule";

        $sth = self::pdo()->prepare($sqlStr);
        $sth->execute(array(":i_schedule" => $i_schedule));

        $row = $sth->fetch(PDO::FETCH_ASSOC);
        if (!empty($row['options'])) {
            $row['options'] = self::xml2array($row['options']);
        }
        if (!empty($row['duration'])) {
            $binStr = "";
            foreach (str_split($row['duration']) as $hexDig) {
                $binStr .= str_pad(base_convert($hexDig, 16, 2), 4, '0', STR_PAD_LEFT);
            }
            $row['duration'] = str_split($binStr, 24);
        }

        return($row);
    }

    static public function delTrainerInfo($request) {
        foreach (array('i_trainer', 'token') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }

        $i_customer = self::checkCustomerToken($token);

        $sqlStr = "DELETE FROM trainer WHERE i_trainer = :i_trainer";
        $sth = self::pdo()->prepare($sqlStr);
        $sth->execute(array(":i_trainer" => $i_trainer));

        $result = ($sth->rowCount());
        return( array('count' => $result));
    }

    /************************ DONOR FUNCTIONS ************************/

    static protected function getDonorFields() {
        return array(
            "i_donor" => 0,
            "title" => 1,
            "options" => 0,
            "short_desc" => 0,
            "full_desc" => 0,
            "log_url" => 0,
            "country" => 0,
            "tagline" => 0,);
    }
    
    /**
curl -X POST -H "AUTHORIZATION: Bearer D755F3CA53BE5FB3EEAE8DCC337BACEB"  -d "functionName=getDonorList" http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
     */
    static public function getDonorList($request) {
        try {
            $fields = implode(",", array_keys (self::getDonorFields()));
            $from = "FROM donor";
            $order = "ORDER BY title";
            $where = $limit = "";

            if( !empty($request->i_donor)) {
                $i_donor= intval($request->i_donor);
                $where = "WHERE i_donor = $i_donor";
            }
            if(!empty($request->limit)) {
                $_limit = intval($request->limit);
                $offset = empty($request->offset) ? 0 : intval($request->offset);
                $limit = "LIMIT {$offset}, {$limit}";
            }
            $sqlStr = "SELECT {$fields} {$from} {$where} {$order} {$limit}";
            $sth = self::pdo()->query($sqlStr);
            $result = $sth->fetchAll( PDO::FETCH_ASSOC );
        } catch (Exception $e) {
            $result = array( "error" => $e->getMessage() );
        } finally {
            return $result;
        }
    }
    
    /**
curl -X POST -d "functionName=getDonorListByProject&i_project=1" http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
     */
    static public function getDonorListByProject($request) {
        
        $i_project = intval($request->i_project);
        if( empty($i_project) ) {
            throw new AppException(__METHOD__ . " Argument i_project is empty");
        }

        $fields = "pd.i_donor, d.title, d.log_url, d.country, d.tagline, pd.issue_date";
        $from = "FROM project_donor pd INNER JOIN donor d ON pd.i_donor = d.i_donor";
        $order = "ORDER BY pd.issue_date";
        $where = "WHERE pd.i_project = {$i_project}";
        $limit = "";

        $sqlStr = "SELECT {$fields} {$from} {$where} {$order} {$limit}";
        $sth = self::pdo()->query($sqlStr);
        $assigned = $sth->fetchAll( PDO::FETCH_ASSOC );
        
        $sqlStr = "SELECT * FROM donor WHERE i_donor NOT IN "
                . "(SELECT pd.i_donor FROM project_donor pd WHERE pd.i_project = {$i_project}) "
                . "ORDER BY title";
                
        $sth = self::pdo()->query($sqlStr);
        
        $unassigned = $sth->fetchAll( PDO::FETCH_ASSOC );

        $result = [
            'assigned' => $assigned,
            'unassigned' => $unassigned,
        ];
        return $result;
    }
    /**
curl -X POST -d "functionName=addDonor2Project&i_project=1&i_donor=14" http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
     */
    static public function addDonor2Project ($request) {
        $i_project = intval($request->i_project);
        $i_donor = intval($request->i_donor);
        if( empty($i_project) or empty($i_donor) ) {
            throw new AppException(__METHOD__ . " Argument i_project or i_donor is empty");
        }
        $sqlStr = "INSERT INTO project_donor SET i_project = {$i_project}, i_donor = {$i_donor}";
        $result = self::pdo()->exec($sqlStr);

        return $result;
    }
    /**
curl -X POST -d "functionName=delDonorFromProject&i_project=1&i_donor=14" http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
     */
    static public function delDonorFromProject ($request) {
        $i_project = intval($request->i_project);
        $i_donor = intval($request->i_donor);
        if( empty($i_project) or empty($i_donor) ) {
            throw new AppException(__METHOD__ . " Argument i_project or i_donor is empty");
        }
        $sqlStr = "DELETE FROM project_donor WHERE i_project = {$i_project} AND i_donor = {$i_donor}";
        $result = self::pdo()->exec($sqlStr);

        return $result;
    }
/*
cd /var/www/html/eventmanager/wp-content/themes/angular-bootstrap/Controller
curl -i -X POST -H "AUTHORIZATION: Bearer D755F3CA53BE5FB3EEAE8DCC337BACEB"  -d @setDonorInfo.txt http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
*/
    static public function setDonorInfo($request) {
        $primaryKey = "i_donor";
        $i_customer = self::checkCustomerToken($request->token);
        
        $$primaryKey = intval($request->$primaryKey);
        
        $requestArr = (array)$request;
        unset($requestArr['functionName']);
        unset($requestArr["$primaryKey"]);
        unset($requestArr['token']);

        $setExpression = array_keys($requestArr);
        array_walk( $setExpression, 'self::set_creator');
        $setExpression = implode(",", $setExpression);

        if (!empty($request->$primaryKey)) {
            $sqlStr = "UPDATE `donor` SET $setExpression  WHERE {$primaryKey} = :{$primaryKey}";
            $requestArr[$primaryKey] = $$primaryKey;
        } else {
            $sqlStr = "INSERT INTO `donor` SET {$setExpression}";
        }

        $sth = self::pdo()->prepare($sqlStr);
        $sth->execute($requestArr);
        $last_id = self::pdo()->lastInsertId();
        
        if(empty ($last_id)) {
            $last_id = $request->$primaryKey;
        }

        return(array( $primaryKey => $last_id));
    }
/*
cd /var/www/html/eventmanager/wp-content/themes/angular-bootstrap/Controller
curl -i -X POST -H "AUTHORIZATION: Bearer D755F3CA53BE5FB3EEAE8DCC337BACEB"  -d @delDonorInfo.txt http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
*/
    static public function delDonor($request) {
        $primaryKey = "i_donor";

        foreach (array($primaryKey, 'token') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }
        $$primaryKey = intval($$primaryKey);
        if(empty($$primaryKey)) {
             throw new AppException(__METHOD__ . " Argument '$primaryKey' is empty");
        }
        $i_customer = self::checkCustomerToken($token);

        $sqlStr = "DELETE FROM donor WHERE {$primaryKey} = {$$primaryKey}";
        $result = self::pdo()->exec($sqlStr);
        return( $result );
    }


    /************************ COMPANY FUNCTIONS ************************/

    static protected function getCompanyFields() {
        return [
            "i_address",
            "title",
            "options",
            "description",
        ];
    }
    
    /**
    curl -X POST -H "AUTHORIZATION: Bearer D755F3CA53BE5FB3EEAE8DCC337BACEB"  -d "functionName=getCompanyList" http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
     */
    static public function getCompanyList($request) {
        //$fields = implode(",", array_keys (self::getCompanyFields()));
        $from = "FROM company c LEFT JOIN address a ON c.i_address = a.i_address";
        $order = "ORDER BY title";
        $where = $limit = "";

        if( !empty($request->i_company)) {
            $i_company= intval($request->i_company);
            $where = "WHERE i_company = {$i_company}";
        }
        if(!empty($request->limit)) {
            $_limit = intval($request->limit);
            $offset = empty($request->offset) ? 0 : intval($request->offset);
            $limit = "LIMIT {$offset}, {$limit}";
        }
        //$sqlStr = "SELECT {$fields} {$from} {$where} {$order} {$limit}";
        $sqlStr = "SELECT * {$from} {$where} {$order} {$limit}";
        $sth = self::pdo()->query($sqlStr);
        $result = $sth->fetchAll( PDO::FETCH_ASSOC );
        return $result;
    }
    
    /**
curl -X POST -d "functionName=getCompanyListByProject&i_project=1" http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
     */
    static public function getCompanyListByProject($request) {
        
        $i_project = intval($request->i_project);
        if( empty($i_project) ) {
            throw new AppException(__METHOD__ . " Argument i_project is empty");
        }

        $fields = "pd.i_company, d.title, d.log_url, d.country, d.tagline, pd.issue_date";
        $from = "FROM project_company pd INNER JOIN company d ON pd.i_company = d.i_company";
        $order = "ORDER BY pd.issue_date";
        $where = "WHERE pd.i_project = {$i_project}";
        $limit = "";

        $sqlStr = "SELECT {$fields} {$from} {$where} {$order} {$limit}";
        $sth = self::pdo()->query($sqlStr);
        $assigned = $sth->fetchAll( PDO::FETCH_ASSOC );
        
        $sqlStr = "SELECT * FROM company WHERE i_company NOT IN "
                . "(SELECT pd.i_company FROM project_company pd WHERE pd.i_project = {$i_project}) "
                . "ORDER BY title";
                
        $sth = self::pdo()->query($sqlStr);
        
        $unassigned = $sth->fetchAll( PDO::FETCH_ASSOC );

        $result = [
            'assigned' => $assigned,
            'unassigned' => $unassigned,
        ];
        return $result;
    }
    /**
curl -X POST -d "functionName=addCompany2Project&i_project=1&i_company=14" http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
     */
    static public function addCompany2Project ($request) {
        $i_project = intval($request->i_project);
        $i_company = intval($request->i_company);
        if( empty($i_project) or empty($i_company) ) {
            throw new AppException(__METHOD__ . " Argument i_project or i_company is empty");
        }
        $sqlStr = "INSERT INTO project_company SET i_project = {$i_project}, i_company = {$i_company}";
        $result = self::pdo()->exec($sqlStr);

        return $result;
    }
    /**
curl -X POST -d "functionName=delCompanyFromProject&i_project=1&i_company=14" http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
     */
    static public function delCompanyFromProject ($request) {
        $i_project = intval($request->i_project);
        $i_company = intval($request->i_company);
        if( empty($i_project) or empty($i_company) ) {
            throw new AppException(__METHOD__ . " Argument i_project or i_company is empty");
        }
        $sqlStr = "DELETE FROM project_company WHERE i_project = {$i_project} AND i_company = {$i_company}";
        $result = self::pdo()->exec($sqlStr);

        return $result;
    }
/*
cd /var/www/html/eventmanager/wp-content/themes/angular-bootstrap/Controller
curl -i -X POST -d @setCompanyInfo.txt http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
*/
    static public function setCompanyInfo($request) {

        $table = "company";
        $primaryKey = "i_{$table}";
        $where = "";
        
        $i_customer = self::checkCustomerToken($request->token);
        
        //Set primary key
        $$primaryKey = isset($request->$primaryKey) ? intval($request->$primaryKey) : null;
        //var_dump($$primar);
        //Check primary key value
        if(!empty($$primaryKey)) {
            $where = "WHERE {$primaryKey}={$$primaryKey}";
        }
        $request->i_address = self::setAddress($request);


        $setArr = [];
        $paramsArr = [];
        foreach (self::getCompanyFields() as $field) {
            $setArr[] = "{$field}=:{$field}";
            $paramsArr[$field] = $request->$field;
        }
        $setStr = implode(",", $setArr);

        if(empty($$primaryKey)) {
            $sqlStr = "INSERT INTO {$table} SET {$setStr} ";
        } else {
            $sqlStr = "UPDATE {$table} SET {$setStr} {$where}";
        }
        
        $sth = self::pdo()->prepare($sqlStr);
        try {
            $sth->execute($paramsArr);
        } catch  (PDOException $e) {
            print_r($e);
            throw new Exception("Компанія вже існує");
        } 
        $last_id = intval(self::pdo()->lastInsertId());
        
        $last_id = empty ($last_id) ? $request->$primaryKey : $last_id;

        return(array( $primaryKey => $last_id));
    }
/*
cd /var/www/html/eventmanager/wp-content/themes/angular-bootstrap/Controller
curl -i -X POST -d @delCompanyInfo.txt http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
*/
    static public function delCompany($request) {
        $primaryKey = "i_company";

        foreach (array($primaryKey, 'token') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }
        $$primaryKey = intval($$primaryKey);
        if(empty($$primaryKey)) {
             throw new AppException(__METHOD__ . " Argument '$primaryKey' is empty");
        }
        $i_customer = self::checkCustomerToken($token);

        $sqlStr = "DELETE FROM company WHERE {$primaryKey} = {$$primaryKey}";
        $result = self::pdo()->exec($sqlStr);
        return( $result );
    }

/*
cd /var/www/html/eventmanager/wp-content/themes/angular-bootstrap/Controller
curl -i -X POST -d 'functionName=getAddress&token=qwerty&i_address=1' http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
*/
    static public function setAddress($request) {
        $table = "address";
        $primaryKey = "i_{$table}";
        $where = "";

        $fieldsArr = [
            'zip',
            'country',
            'region',
            'locality',
            'street',
            'office',
            'email'
        ];
        //Check for requred params
        foreach (array('token') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__." LINE:". __LINE__ . " Argument: '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }
        //Set primary key
        $$primaryKey = isset($request->$primaryKey) ? intval($request->$primaryKey) : null;
        //$$primaryKey = 11;
        //Check primary key value
        //Auth check
        $i_customer = self::checkCustomerToken($token);
        //prepare SQL request
        $setArr = [];
        $paramsArr = [];
        foreach ($fieldsArr as $field) {
            $setArr[] = "{$field}=:{$field}";
            $value = empty($request->$field) ? null : $request->$field;
            $paramsArr[$field] = $value;
        }
        $setStr = implode(", ", $setArr);

        if(!empty($$primaryKey) ) {
            $where = "WHERE {$primaryKey}={$$primaryKey}";
            $sqlStr = "UPDATE `{$table}` SET {$setStr} {$where}";
        } else {
            $sqlStr = "INSERT INTO `{$table}` SET {$setStr}";
        }

        /*
        $addressFields = [
            "i_address" => "i_address",
            "ofice" => "Офіс",
            "country" => "Країна",
            "locality" => "Населений пункт",
            "region" => "Область",
            "street" => "Вулиця",
            "zip" => "Індекс"
        ];
         */
        $sth = self::pdo()->prepare($sqlStr);
        $sth->execute($paramsArr);
        $last_id = intval(self::pdo()->lastInsertId());
        $last_id = empty ($last_id) ? $$primaryKey : $last_id;

        return( $last_id );
    }

    /************************ CUSTOMER FUNCTIONS ************************/
    static protected function getCustomerFields() {
        return [
            'email',
            'password',
            'first_name',
            'second_name',
            'last_name',
            'sex',
            'options'
        ];
    }
    /**
curl -X POST -d "functionName=getCompanyListByProject&i_project=1" http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
     */
    static public function getCustomerList($request) {
        
        $fields = "cs.*, co.title";
        $from = "FROM customer cs LEFT JOIN company co ON cs.i_company = co.i_company";
        $order = "ORDER BY cs.last_name";
        if(!empty($request->i_customer)) {
            $where = "WHERE cs.i_customer = {$request->i_customer}";
        } else {
            $where = "";
        }
        $limit = empty($request->limit) ? 10 : intval($request->limit);
        $offset = empty($request->offset) ? 0 : intval($request->offset);
        $limit = "LIMIT {$limit} OFFSET {$offset}";

        $sqlStr = "SELECT {$fields} {$from} {$where} {$order} {$limit}";
        $result = self::pdo()->query($sqlStr)->fetchAll( PDO::FETCH_ASSOC );

        return $result;
    }
    /**
cd /var/www/html/eventmanager/wp-content/themes/angular-bootstrap/Controller
curl -i -X POST -d @test/submitCustomerInfo.txt http://em.atelecom.biz/wp-content/themes/angular-bootstrap/Controller/ajax.php
     */
    static public function submitCustomerInfo($request) {

        $table = "customer";
        $primaryKey = "i_{$table}";
        $where = "";

        $customerModerator = self::checkCustomerToken($request->token);
        //Set primary key
        $$primaryKey = empty($request->$primaryKey) ? NULL : intval($request->$primaryKey);
        //Check primary key value
        if(!empty($$primaryKey)) {
            $where = "WHERE {$primaryKey}={$$primaryKey}";
        }
        $setArr = [];
        $paramsArr = [];
        foreach (self::getCustomerFields() as $field) {
            if(isset($request->$field)) {
                $setArr[] = "{$field}=:{$field}";
                $paramsArr[$field] = $request->$field;
            }
        }
        //i_company
        $setArr[] = "i_company=:i_company";
        $paramsArr["i_company"] = empty($request->i_company) ? NULL : $request->i_company;
        
        $setStr = implode(",", $setArr);

        if(empty($$primaryKey)) {
            $sqlStr = "INSERT INTO {$table} SET {$setStr} ";
        } else {
            $sqlStr = "UPDATE {$table} SET {$setStr} {$where}";
        }
        $sth = self::pdo()->prepare($sqlStr);
        try {
            $sth->execute($paramsArr);
        } catch  (PDOException $e) {
            print_r($e);
            throw new Exception("Учасники вже існує");
        } 
        $last_id = self::pdo()->lastInsertId();
        
        $last_id = intval(empty ($last_id) ? $request->$primaryKey : $last_id);

        return(array( $primaryKey => $last_id));
    }
    
    static public function delCustomer($request) {
        $table = "customer";
        $primaryKey = "i_{$table}";

        foreach (array($primaryKey, 'token') as $fnParamName) {
            if (empty($request->$fnParamName)) {
                throw new AppException(__METHOD__ . " Argument '$fnParamName' is empty");
            }
            $$fnParamName = $request->$fnParamName;
        }
        $$primaryKey = intval($$primaryKey);
        if(empty($$primaryKey)) {
            throw new AppException(__METHOD__ . " Argument '$primaryKey' is empty");
        }
        $customer_moderator = self::checkCustomerToken($token);

        $sqlStr = "DELETE FROM {$table} WHERE {$primaryKey} = {$$primaryKey}";

        self::pdo()->exec($sqlStr);
        return( self::getCustomerList([]) );
    }


}


