<?php
/**
 * User: golodnyi
 * Date: 12.05.16
 * Time: 17:03
 */

class telemetry_flex_v10 {
    private $_numPage;
    private $_Code;
    private $_Time;
    private $_State;
    private $_Module1;
    private $_Module2;
    private $_GSM;
    private $_StateGauge;
    private $_LastTime;
    private $_Lat;
    private $_Lon;
    private $_Alt;
    private $_Speed;
    private $_Course;
    private $_Mileage;
    private $_Way;
    private $_AllSeconds;
    private $_SecondLast;
    private $_Power;
    private $_Reserv;
    private $_StateU_Ain1;
    private $_StateU_Ain2;
    private $_StateU_Ain3;
    private $_StateU_Ain4;
    private $_StateU_Ain5;
    private $_StateU_Ain6;
    private $_StateU_Ain7;
    private $_StateU_Ain8;
    private $_StateIn1;
    private $_StateIn2;
    private $_stateOut1;
    private $_StateOut2;
    private $_StateInImp1;
    private $_StateInImp2;
    private $_Frequency1;
    private $_Frequency2;
    private $_Motochas;
    private $_LevelRS485_1;
    private $_LevelRS485_2;
    private $_LevelRS485_3;
    private $_LevelRS485_4;
    private $_LevelRS485_5;
    private $_LevelRS485_6;
    private $_LevelRS232;
    private $_Temp1;
    private $_Temp2;
    private $_Temp3;
    private $_Temp4;
    private $_Temp5;
    private $_Temp6;
    private $_Temp7;
    private $_Temp8;
    private $_CAN_FuelLevel;
    private $_CAN_FuelConsumption;
    private $_CAN_EngineTurns;
    private $_CAN_Temp;
    private $_CAN_FullRun;
    private $_CAN_AxleLoad_1;
    private $_CAN_AxleLoad_2;
    private $_CAN_AxleLoad_3;
    private $_CAN_AxleLoad_4;
    private $_CAN_AxleLoad_5;
    private $_CAN_PedalAccel;
    private $_CAN_PedalStop;
    private $_CAN_EngineLoad;
    private $_CAN_LevelFiltr;
    private $_CAN_EngineTime;
    private $_CAN_TimeTO;
    private $_CAN_Speed;

    /**
     * @return mixed
     */
    public function getNumPage()
    {
        return $this->_numPage;
    }

    /**
     * @param mixed $numPage
     */
    public function setNumPage($numPage)
    {
        $this->_numPage = $numPage;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->_Code;
    }

    /**
     * @param mixed $Code
     */
    public function setCode($Code)
    {
        $this->_Code = $Code;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->_Time;
    }

    /**
     * @param mixed $Time
     */
    public function setTime($Time)
    {
        $this->_Time = $Time;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->_State;
    }

    /**
     * @param mixed $State
     */
    public function setState($State)
    {
        $this->_State = $State;
    }

    /**
     * @return mixed
     */
    public function getModule1()
    {
        return $this->_Module1;
    }

    /**
     * @param mixed $Module1
     */
    public function setModule1($Module1)
    {
        $this->_Module1 = $Module1;
    }

    /**
     * @return mixed
     */
    public function getModule2()
    {
        return $this->_Module2;
    }

    /**
     * @param mixed $Module2
     */
    public function setModule2($Module2)
    {
        $this->_Module2 = $Module2;
    }

    /**
     * @return mixed
     */
    public function getGSM()
    {
        return $this->_GSM;
    }

    /**
     * @param mixed $GSM
     */
    public function setGSM($GSM)
    {
        $this->_GSM = $GSM;
    }

    /**
     * @return mixed
     */
    public function getStateGauge()
    {
        return $this->_StateGauge;
    }

    /**
     * @param mixed $StateGauge
     */
    public function setStateGauge($StateGauge)
    {
        $this->_StateGauge = $StateGauge;
    }

    /**
     * @return mixed
     */
    public function getLastTime()
    {
        return $this->_LastTime;
    }

    /**
     * @param mixed $LastTime
     */
    public function setLastTime($LastTime)
    {
        $this->_LastTime = $LastTime;
    }

    /**
     * @return mixed
     */
    public function getLat()
    {
        return $this->_Lat;
    }

    /**
     * @param mixed $Lat
     */
    public function setLat($Lat)
    {
        $this->_Lat = $Lat;
    }

    /**
     * @return mixed
     */
    public function getLon()
    {
        return $this->_Lon;
    }

    /**
     * @param mixed $Lon
     */
    public function setLon($Lon)
    {
        $this->_Lon = $Lon;
    }

    /**
     * @return mixed
     */
    public function getAlt()
    {
        return $this->_Alt;
    }

    /**
     * @param mixed $Alt
     */
    public function setAlt($Alt)
    {
        $this->_Alt = $Alt;
    }

    /**
     * @return mixed
     */
    public function getSpeed()
    {
        return $this->_Speed;
    }

    /**
     * @param mixed $Speed
     */
    public function setSpeed($Speed)
    {
        $this->_Speed = $Speed;
    }

    /**
     * @return mixed
     */
    public function getCourse()
    {
        return $this->_Course;
    }

    /**
     * @param mixed $Course
     */
    public function setCourse($Course)
    {
        $this->_Course = $Course;
    }

    /**
     * @return mixed
     */
    public function getMileage()
    {
        return $this->_Mileage;
    }

    /**
     * @param mixed $Mileage
     */
    public function setMileage($Mileage)
    {
        $this->_Mileage = $Mileage;
    }

    /**
     * @return mixed
     */
    public function getWay()
    {
        return $this->_Way;
    }

    /**
     * @param mixed $Way
     */
    public function setWay($Way)
    {
        $this->_Way = $Way;
    }

    /**
     * @return mixed
     */
    public function getAllSeconds()
    {
        return $this->_AllSeconds;
    }

    /**
     * @param mixed $AllSeconds
     */
    public function setAllSeconds($AllSeconds)
    {
        $this->_AllSeconds = $AllSeconds;
    }

    /**
     * @return mixed
     */
    public function getSecondLast()
    {
        return $this->_SecondLast;
    }

    /**
     * @param mixed $SecondLast
     */
    public function setSecondLast($SecondLast)
    {
        $this->_SecondLast = $SecondLast;
    }

    /**
     * @return mixed
     */
    public function getPower()
    {
        return $this->_Power;
    }

    /**
     * @param mixed $Power
     */
    public function setPower($Power)
    {
        $this->_Power = $Power;
    }

    /**
     * @return mixed
     */
    public function getReserv()
    {
        return $this->_Reserv;
    }

    /**
     * @param mixed $Reserv
     */
    public function setReserv($Reserv)
    {
        $this->_Reserv = $Reserv;
    }

    /**
     * @return mixed
     */
    public function getStateUAin1()
    {
        return $this->_StateU_Ain1;
    }

    /**
     * @param mixed $StateU_Ain1
     */
    public function setStateUAin1($StateU_Ain1)
    {
        $this->_StateU_Ain1 = $StateU_Ain1;
    }

    /**
     * @return mixed
     */
    public function getStateUAin2()
    {
        return $this->_StateU_Ain2;
    }

    /**
     * @param mixed $StateU_Ain2
     */
    public function setStateUAin2($StateU_Ain2)
    {
        $this->_StateU_Ain2 = $StateU_Ain2;
    }

    /**
     * @return mixed
     */
    public function getStateUAin3()
    {
        return $this->_StateU_Ain3;
    }

    /**
     * @param mixed $StateU_Ain3
     */
    public function setStateUAin3($StateU_Ain3)
    {
        $this->_StateU_Ain3 = $StateU_Ain3;
    }

    /**
     * @return mixed
     */
    public function getStateUAin4()
    {
        return $this->_StateU_Ain4;
    }

    /**
     * @param mixed $StateU_Ain4
     */
    public function setStateUAin4($StateU_Ain4)
    {
        $this->_StateU_Ain4 = $StateU_Ain4;
    }

    /**
     * @return mixed
     */
    public function getStateUAin5()
    {
        return $this->_StateU_Ain5;
    }

    /**
     * @param mixed $StateU_Ain5
     */
    public function setStateUAin5($StateU_Ain5)
    {
        $this->_StateU_Ain5 = $StateU_Ain5;
    }

    /**
     * @return mixed
     */
    public function getStateUAin6()
    {
        return $this->_StateU_Ain6;
    }

    /**
     * @param mixed $StateU_Ain6
     */
    public function setStateUAin6($StateU_Ain6)
    {
        $this->_StateU_Ain6 = $StateU_Ain6;
    }

    /**
     * @return mixed
     */
    public function getStateUAin7()
    {
        return $this->_StateU_Ain7;
    }

    /**
     * @param mixed $StateU_Ain7
     */
    public function setStateUAin7($StateU_Ain7)
    {
        $this->_StateU_Ain7 = $StateU_Ain7;
    }

    /**
     * @return mixed
     */
    public function getStateUAin8()
    {
        return $this->_StateU_Ain8;
    }

    /**
     * @param mixed $StateU_Ain8
     */
    public function setStateUAin8($StateU_Ain8)
    {
        $this->_StateU_Ain8 = $StateU_Ain8;
    }

    /**
     * @return mixed
     */
    public function getStateIn1()
    {
        return $this->_StateIn1;
    }

    /**
     * @param mixed $StateIn1
     */
    public function setStateIn1($StateIn1)
    {
        $this->_StateIn1 = $StateIn1;
    }

    /**
     * @return mixed
     */
    public function getStateIn2()
    {
        return $this->_StateIn2;
    }

    /**
     * @param mixed $StateIn2
     */
    public function setStateIn2($StateIn2)
    {
        $this->_StateIn2 = $StateIn2;
    }

    /**
     * @return mixed
     */
    public function getStateOut1()
    {
        return $this->_stateOut1;
    }

    /**
     * @param mixed $stateOut1
     */
    public function setStateOut1($stateOut1)
    {
        $this->_stateOut1 = $stateOut1;
    }

    /**
     * @return mixed
     */
    public function getStateOut2()
    {
        return $this->_StateOut2;
    }

    /**
     * @param mixed $StateOut2
     */
    public function setStateOut2($StateOut2)
    {
        $this->_StateOut2 = $StateOut2;
    }

    /**
     * @return mixed
     */
    public function getStateInImp1()
    {
        return $this->_StateInImp1;
    }

    /**
     * @param mixed $StateInImp1
     */
    public function setStateInImp1($StateInImp1)
    {
        $this->_StateInImp1 = $StateInImp1;
    }

    /**
     * @return mixed
     */
    public function getStateInImp2()
    {
        return $this->_StateInImp2;
    }

    /**
     * @param mixed $StateInImp2
     */
    public function setStateInImp2($StateInImp2)
    {
        $this->_StateInImp2 = $StateInImp2;
    }

    /**
     * @return mixed
     */
    public function getFrequency1()
    {
        return $this->_Frequency1;
    }

    /**
     * @param mixed $Frequency1
     */
    public function setFrequency1($Frequency1)
    {
        $this->_Frequency1 = $Frequency1;
    }

    /**
     * @return mixed
     */
    public function getFrequency2()
    {
        return $this->_Frequency2;
    }

    /**
     * @param mixed $Frequency2
     */
    public function setFrequency2($Frequency2)
    {
        $this->_Frequency2 = $Frequency2;
    }

    /**
     * @return mixed
     */
    public function getMotochas()
    {
        return $this->_Motochas;
    }

    /**
     * @param mixed $Motochas
     */
    public function setMotochas($Motochas)
    {
        $this->_Motochas = $Motochas;
    }

    /**
     * @return mixed
     */
    public function getLevelRS4851()
    {
        return $this->_LevelRS485_1;
    }

    /**
     * @param mixed $LevelRS485_1
     */
    public function setLevelRS4851($LevelRS485_1)
    {
        $this->_LevelRS485_1 = $LevelRS485_1;
    }

    /**
     * @return mixed
     */
    public function getLevelRS4852()
    {
        return $this->_LevelRS485_2;
    }

    /**
     * @param mixed $LevelRS485_2
     */
    public function setLevelRS4852($LevelRS485_2)
    {
        $this->_LevelRS485_2 = $LevelRS485_2;
    }

    /**
     * @return mixed
     */
    public function getLevelRS4853()
    {
        return $this->_LevelRS485_3;
    }

    /**
     * @param mixed $LevelRS485_3
     */
    public function setLevelRS4853($LevelRS485_3)
    {
        $this->_LevelRS485_3 = $LevelRS485_3;
    }

    /**
     * @return mixed
     */
    public function getLevelRS4854()
    {
        return $this->_LevelRS485_4;
    }

    /**
     * @param mixed $LevelRS485_4
     */
    public function setLevelRS4854($LevelRS485_4)
    {
        $this->_LevelRS485_4 = $LevelRS485_4;
    }

    /**
     * @return mixed
     */
    public function getLevelRS4855()
    {
        return $this->_LevelRS485_5;
    }

    /**
     * @param mixed $LevelRS485_5
     */
    public function setLevelRS4855($LevelRS485_5)
    {
        $this->_LevelRS485_5 = $LevelRS485_5;
    }

    /**
     * @return mixed
     */
    public function getLevelRS4856()
    {
        return $this->_LevelRS485_6;
    }

    /**
     * @param mixed $LevelRS485_6
     */
    public function setLevelRS4856($LevelRS485_6)
    {
        $this->_LevelRS485_6 = $LevelRS485_6;
    }

    /**
     * @return mixed
     */
    public function getLevelRS232()
    {
        return $this->_LevelRS232;
    }

    /**
     * @param mixed $LevelRS232
     */
    public function setLevelRS232($LevelRS232)
    {
        $this->_LevelRS232 = $LevelRS232;
    }

    /**
     * @return mixed
     */
    public function getTemp1()
    {
        return $this->_Temp1;
    }

    /**
     * @param mixed $Temp1
     */
    public function setTemp1($Temp1)
    {
        $this->_Temp1 = $Temp1;
    }

    /**
     * @return mixed
     */
    public function getTemp2()
    {
        return $this->_Temp2;
    }

    /**
     * @param mixed $Temp2
     */
    public function setTemp2($Temp2)
    {
        $this->_Temp2 = $Temp2;
    }

    /**
     * @return mixed
     */
    public function getTemp3()
    {
        return $this->_Temp3;
    }

    /**
     * @param mixed $Temp3
     */
    public function setTemp3($Temp3)
    {
        $this->_Temp3 = $Temp3;
    }

    /**
     * @return mixed
     */
    public function getTemp4()
    {
        return $this->_Temp4;
    }

    /**
     * @param mixed $Temp4
     */
    public function setTemp4($Temp4)
    {
        $this->_Temp4 = $Temp4;
    }

    /**
     * @return mixed
     */
    public function getTemp5()
    {
        return $this->_Temp5;
    }

    /**
     * @param mixed $Temp5
     */
    public function setTemp5($Temp5)
    {
        $this->_Temp5 = $Temp5;
    }

    /**
     * @return mixed
     */
    public function getTemp6()
    {
        return $this->_Temp6;
    }

    /**
     * @param mixed $Temp6
     */
    public function setTemp6($Temp6)
    {
        $this->_Temp6 = $Temp6;
    }

    /**
     * @return mixed
     */
    public function getTemp7()
    {
        return $this->_Temp7;
    }

    /**
     * @param mixed $Temp7
     */
    public function setTemp7($Temp7)
    {
        $this->_Temp7 = $Temp7;
    }

    /**
     * @return mixed
     */
    public function getTemp8()
    {
        return $this->_Temp8;
    }

    /**
     * @param mixed $Temp8
     */
    public function setTemp8($Temp8)
    {
        $this->_Temp8 = $Temp8;
    }

    /**
     * @return mixed
     */
    public function getCANFuelLevel()
    {
        return $this->_CAN_FuelLevel;
    }

    /**
     * @param mixed $CAN_FuelLevel
     */
    public function setCANFuelLevel($CAN_FuelLevel)
    {
        $this->_CAN_FuelLevel = $CAN_FuelLevel;
    }

    /**
     * @return mixed
     */
    public function getCANFuelConsumption()
    {
        return $this->_CAN_FuelConsumption;
    }

    /**
     * @param mixed $CAN_FuelConsumption
     */
    public function setCANFuelConsumption($CAN_FuelConsumption)
    {
        $this->_CAN_FuelConsumption = $CAN_FuelConsumption;
    }

    /**
     * @return mixed
     */
    public function getCANEngineTurns()
    {
        return $this->_CAN_EngineTurns;
    }

    /**
     * @param mixed $CAN_EngineTurns
     */
    public function setCANEngineTurns($CAN_EngineTurns)
    {
        $this->_CAN_EngineTurns = $CAN_EngineTurns;
    }

    /**
     * @return mixed
     */
    public function getCANTemp()
    {
        return $this->_CAN_Temp;
    }

    /**
     * @param mixed $CAN_Temp
     */
    public function setCANTemp($CAN_Temp)
    {
        $this->_CAN_Temp = $CAN_Temp;
    }

    /**
     * @return mixed
     */
    public function getCANFullRun()
    {
        return $this->_CAN_FullRun;
    }

    /**
     * @param mixed $CAN_FullRun
     */
    public function setCANFullRun($CAN_FullRun)
    {
        $this->_CAN_FullRun = $CAN_FullRun;
    }

    /**
     * @return mixed
     */
    public function getCANAxleLoad1()
    {
        return $this->_CAN_AxleLoad_1;
    }

    /**
     * @param mixed $CAN_AxleLoad_1
     */
    public function setCANAxleLoad1($CAN_AxleLoad_1)
    {
        $this->_CAN_AxleLoad_1 = $CAN_AxleLoad_1;
    }

    /**
     * @return mixed
     */
    public function getCANAxleLoad2()
    {
        return $this->_CAN_AxleLoad_2;
    }

    /**
     * @param mixed $CAN_AxleLoad_2
     */
    public function setCANAxleLoad2($CAN_AxleLoad_2)
    {
        $this->_CAN_AxleLoad_2 = $CAN_AxleLoad_2;
    }

    /**
     * @return mixed
     */
    public function getCANAxleLoad3()
    {
        return $this->_CAN_AxleLoad_3;
    }

    /**
     * @param mixed $CAN_AxleLoad_3
     */
    public function setCANAxleLoad3($CAN_AxleLoad_3)
    {
        $this->_CAN_AxleLoad_3 = $CAN_AxleLoad_3;
    }

    /**
     * @return mixed
     */
    public function getCANAxleLoad4()
    {
        return $this->_CAN_AxleLoad_4;
    }

    /**
     * @param mixed $CAN_AxleLoad_4
     */
    public function setCANAxleLoad4($CAN_AxleLoad_4)
    {
        $this->_CAN_AxleLoad_4 = $CAN_AxleLoad_4;
    }

    /**
     * @return mixed
     */
    public function getCANAxleLoad5()
    {
        return $this->_CAN_AxleLoad_5;
    }

    /**
     * @param mixed $CAN_AxleLoad_5
     */
    public function setCANAxleLoad5($CAN_AxleLoad_5)
    {
        $this->_CAN_AxleLoad_5 = $CAN_AxleLoad_5;
    }

    /**
     * @return mixed
     */
    public function getCANPedalAccel()
    {
        return $this->_CAN_PedalAccel;
    }

    /**
     * @param mixed $CAN_PedalAccel
     */
    public function setCANPedalAccel($CAN_PedalAccel)
    {
        $this->_CAN_PedalAccel = $CAN_PedalAccel;
    }

    /**
     * @return mixed
     */
    public function getCANPedalStop()
    {
        return $this->_CAN_PedalStop;
    }

    /**
     * @param mixed $CAN_PedalStop
     */
    public function setCANPedalStop($CAN_PedalStop)
    {
        $this->_CAN_PedalStop = $CAN_PedalStop;
    }

    /**
     * @return mixed
     */
    public function getCANEngineLoad()
    {
        return $this->_CAN_EngineLoad;
    }

    /**
     * @param mixed $CAN_EngineLoad
     */
    public function setCANEngineLoad($CAN_EngineLoad)
    {
        $this->_CAN_EngineLoad = $CAN_EngineLoad;
    }

    /**
     * @return mixed
     */
    public function getCANLevelFiltr()
    {
        return $this->_CAN_LevelFiltr;
    }

    /**
     * @param mixed $CAN_LevelFiltr
     */
    public function setCANLevelFiltr($CAN_LevelFiltr)
    {
        $this->_CAN_LevelFiltr = $CAN_LevelFiltr;
    }

    /**
     * @return mixed
     */
    public function getCANEngineTime()
    {
        return $this->_CAN_EngineTime;
    }

    /**
     * @param mixed $CAN_EngineTime
     */
    public function setCANEngineTime($CAN_EngineTime)
    {
        $this->_CAN_EngineTime = $CAN_EngineTime;
    }

    /**
     * @return mixed
     */
    public function getCANTimeTO()
    {
        return $this->_CAN_TimeTO;
    }

    /**
     * @param mixed $CAN_TimeTO
     */
    public function setCANTimeTO($CAN_TimeTO)
    {
        $this->_CAN_TimeTO = $CAN_TimeTO;
    }

    /**
     * @return mixed
     */
    public function getCANSpeed()
    {
        return $this->_CAN_Speed;
    }

    /**
     * @param mixed $CAN_Speed
     */
    public function setCANSpeed($CAN_Speed)
    {
        $this->_CAN_Speed = $CAN_Speed;
    }

}