<?php

/**
 * User: golodnyi
 * Date: 31.03.17
 * Time: 9:15
 */
class mysql_flex_v11
{
    public static function export($imei, array $telemetry, $prefix = false)
    {
        $dbhost = "localhost";
        $dbname = "getpart";
        $dbuser = "getpart2";
        $dbpswd = "BiBxzE";
    
        try
        {
        
            $db = new PDO("mysql:host=" . $dbhost . ";dbname=" . $dbname, $dbuser, $dbpswd);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $db->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES UTF8');
        
        } catch (PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode());
        }
        
        $insertNumPages = [];
        /** @var telemetry_flex_v11 $t */
        foreach ($telemetry as $t)
        {
            try
            {
                $stmt = $db->prepare('SELECT 1 FROM ntcb11 WHERE `IMEI` = ? AND `numPage` = ? LIMIT 1');
                $stmt->bindValue(1, $imei, PDO::PARAM_INT);
                $stmt->bindValue(2, $t->getNumPage(), PDO::PARAM_INT);
                $stmt->execute();
                $exist = $stmt->rowCount();
            } catch (PDOException $e)
            {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        
            if ($exist && $t->getNumPage() > 0)
            {
                continue;
            }
    
            $prefix = '~A';
    
            if ($t->getStateUAin1() <= 2300)
            {
                $prefix = '~T';
                $t->setCode('65535');
            }
            
            try
            {
                $stmt = $db->prepare('
                    INSERT INTO ntcb11
                        (`IMEI`, `reqType`, `numPage`, `Code`, `Module1GSM`, `Module1USB`, `Module1Watch`, `Module1SIM`, `Module1Network`, `Module1Roaming`, `Module1Engine`, `Time`, `GSM`, `LastTime`, `Lat`, `Lon`, `Alt`, `Course`, `Mileage`, `CAN_EngineTurns`, `CAN_Temp`, `CAN_EngineLoad`, `CAN_Speed`, `CAN_AxleLoad1`, `CAN_AxleLoad2`, `CAN_AxleLoad3`, `CAN_AxleLoad4`, `CAN_AxleLoad5`, `StateU_Ain1`, `StateU_Ain2`, `StateU_Ain3`, `StateInImp2`, `Temp1`, `Temp2`, `Speed`, `Frequency1`, `Motochas`, `Power`, `Reserv`, `ATemp1`, `ATemp2`, `ATemp3`, `ATemp4`, `ATemp5`, `ATemp6`, `ATemp7`, `ATemp8`, `ATemp9`, `ATemp10`, `ATemp11`, `ATemp12`, `ATemp13`, `ATemp14`, `ATemp15`, `ATemp16`)
                    VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ');
            
                $stmt->bindValue(1, $imei, PDO::PARAM_INT);
                $stmt->bindValue(2, $prefix, PDO::PARAM_STR);
                $stmt->bindValue(3, $t->getNumPage(), PDO::PARAM_INT);
                $stmt->bindValue(4, $t->getCode(), PDO::PARAM_INT);
                $stmt->bindValue(5, intval($t->getModule1()[0]), PDO::PARAM_INT);
                $stmt->bindValue(6, intval($t->getModule1()[1]), PDO::PARAM_INT);
                $stmt->bindValue(7, intval($t->getModule1()[3]), PDO::PARAM_INT);
                $stmt->bindValue(8, intval($t->getModule1()[4]), PDO::PARAM_INT);
                $stmt->bindValue(9, intval($t->getModule1()[5]), PDO::PARAM_INT);
                $stmt->bindValue(10, intval($t->getModule1()[6]), PDO::PARAM_INT);
                $stmt->bindValue(11, intval($t->getModule1()[7]), PDO::PARAM_INT);
                $stmt->bindValue(12, $t->getTime(), PDO::PARAM_INT);
                $stmt->bindValue(13, $t->getGSM(), PDO::PARAM_INT);
                $stmt->bindValue(14, $t->getLastTime(), PDO::PARAM_INT);
                $stmt->bindValue(15, $t->getLat(), PDO::PARAM_INT);
                $stmt->bindValue(16, $t->getLon(), PDO::PARAM_INT);
                $stmt->bindValue(17, $t->getAlt(), PDO::PARAM_INT);
                $stmt->bindValue(18, $t->getCourse(), PDO::PARAM_INT);
                $stmt->bindValue(19, $t->getMileage(), PDO::PARAM_INT);
                $stmt->bindValue(20, $t->getCANEngineTurns(), PDO::PARAM_INT);
                $stmt->bindValue(21, $t->getCANTemp(), PDO::PARAM_INT);
                $stmt->bindValue(22, $t->getCANEngineLoad(), PDO::PARAM_INT);
                $stmt->bindValue(23, $t->getCANSpeed(), PDO::PARAM_INT);
                $stmt->bindValue(24, $t->getCANAxleLoad1(), PDO::PARAM_INT);
                $stmt->bindValue(25, $t->getCANAxleLoad2(), PDO::PARAM_INT);
                $stmt->bindValue(26, $t->getCANAxleLoad3(), PDO::PARAM_INT);
                $stmt->bindValue(27, $t->getCANAxleLoad4(), PDO::PARAM_INT);
                $stmt->bindValue(28, $t->getCANAxleLoad4(), PDO::PARAM_INT);
                $stmt->bindValue(29, $t->getStateUAin1(), PDO::PARAM_INT);
                $stmt->bindValue(30, $t->getStateUAin2(), PDO::PARAM_INT);
                $stmt->bindValue(31, $t->getStateUAin3(), PDO::PARAM_INT);
                $stmt->bindValue(32, $t->getStateInImp1(), PDO::PARAM_INT);
                $stmt->bindValue(33, $t->getTemp1(), PDO::PARAM_INT);
                $stmt->bindValue(34, $t->getTemp2(), PDO::PARAM_INT);
                $stmt->bindValue(35, $t->getSpeed(), PDO::PARAM_INT);
                $stmt->bindValue(36, $t->getFrequency1(), PDO::PARAM_INT);
                $stmt->bindValue(37, $t->getMotochas(), PDO::PARAM_INT);
                $stmt->bindValue(38, $t->getPower(), PDO::PARAM_INT);
                $stmt->bindValue(39, $t->getReserv(), PDO::PARAM_INT);
                $stmt->bindValue(40, $t->getATemp1(), PDO::PARAM_INT);
                $stmt->bindValue(41, $t->getATemp2(), PDO::PARAM_INT);
                $stmt->bindValue(42, $t->getATemp3(), PDO::PARAM_INT);
                $stmt->bindValue(43, $t->getATemp4(), PDO::PARAM_INT);
                $stmt->bindValue(44, $t->getATemp5(), PDO::PARAM_INT);
                $stmt->bindValue(45, $t->getATemp6(), PDO::PARAM_INT);
                $stmt->bindValue(46, $t->getATemp7(), PDO::PARAM_INT);
                $stmt->bindValue(47, $t->getATemp8(), PDO::PARAM_INT);
                $stmt->bindValue(48, $t->getATemp9(), PDO::PARAM_INT);
                $stmt->bindValue(49, $t->getATemp10(), PDO::PARAM_INT);
                $stmt->bindValue(50, $t->getATemp11(), PDO::PARAM_INT);
                $stmt->bindValue(51, $t->getATemp12(), PDO::PARAM_INT);
                $stmt->bindValue(52, $t->getATemp13(), PDO::PARAM_INT);
                $stmt->bindValue(53, $t->getATemp14(), PDO::PARAM_INT);
                $stmt->bindValue(54, $t->getATemp15(), PDO::PARAM_INT);
                $stmt->bindValue(66, $t->getATemp16(), PDO::PARAM_INT);
            
                $insert = $stmt->execute();
            } catch (PDOException $e)
            {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        
            if ($insert === false)
            {
                throw new Exception('Ошибка при insert данных ' . $stmt->queryString, -55);
            }
    
            $insertNumPages[] = $t->getNumPage();
        }
    
        return $insertNumPages;
    }
}