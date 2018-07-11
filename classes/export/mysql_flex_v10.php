<?php

/**
 * User: golodnyi
 * Date: 31.03.17
 * Time: 9:15
 */
class mysql_flex_v10
{
    public static function export($imei, array $telemetry, $prefix = false)
    {
        $dbhost = "localhost";
        $dbname = "glonass";
        $dbuser = "root";
        $dbpswd = "10ytuhtnzn";
    
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
        /** @var telemetry_flex_v10 $t */
        foreach ($telemetry as $t)
        {
            try
            {
                $stmt = $db->prepare('SELECT 1 FROM telemetry_events WHERE `IMEI` = ? AND `numPage` = ? LIMIT 1');
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
            
            /**
             * Записываем:
             * IMEI - уникальный идентификатор устройства (int 15)
             * reqType - тип запроса (телеметрические данные или тревожное сообщение)  (char 2)
             * numPage - уникальный ID записи (unsigned int 4)
             * Code - код события (unsigned int 2)
             * Time - время события (unsigned int 4 или timestamp)
             * GSM - уровень сигнала (unsigned int 1)
             * LastTime - время последних валидных координат (unsigned int 4 или timestamp)
             * Lat - широта (signed int 4)
             * Lon - долгота (signed int 4)
             * Alt - высота (signed int 4)
             * Course - куср (в градусах) (unsigned int 2)
             * Mileage - текущий пробег в км (float 4 bytes)
             * CAN_EngineTurns - обороты двигателя (unsigned int 2)
             * CAN_Temp - температура охлаждающей жидкости в цельсиях (signed int 1)
             * CAN_EngineLoad - нагрузка на двигатель в процентах (unsigned int 1)
             * CAN_Speed - скорость (unsigned int 1)
             */
            try
            {
                $stmt = $db->prepare('
                    INSERT INTO telemetry_events
                        (`IMEI`, `reqType`, `numPage`, `Code`, `Module1GSM`, `Module1USB`, `Module1Watch`, `Module1SIM`, `Module1Network`, `Module1Roaming`, `Module1Engine`, `Time`, `GSM`, `LastTime`, `Lat`, `Lon`, `Alt`, `Course`, `Mileage`, `CAN_EngineTurns`, `CAN_Temp`, `CAN_EngineLoad`, `CAN_Speed`, `CAN_AxleLoad1`, `CAN_AxleLoad2`, `CAN_AxleLoad3`, `CAN_AxleLoad4`, `CAN_AxleLoad5`, `StateU_Ain1`, `StateU_Ain2`, `StateU_Ain3`, `StateInImp2`, `Temp1`, `Temp2`, `Temp3`, `Speed`, `Frequency1`, `Motochas`, `Power`, `Reserv`)
                    VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
                $stmt->bindValue(35, $t->getTemp3(), PDO::PARAM_INT);
                $stmt->bindValue(36, $t->getSpeed(), PDO::PARAM_INT);
                $stmt->bindValue(37, $t->getFrequency1(), PDO::PARAM_INT);
                $stmt->bindValue(38, $t->getMotochas(), PDO::PARAM_INT);
                $stmt->bindValue(39, $t->getPower(), PDO::PARAM_INT);
                $stmt->bindValue(40, $t->getReserv(), PDO::PARAM_INT);
            
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