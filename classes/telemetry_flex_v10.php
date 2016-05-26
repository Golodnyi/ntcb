<?php
/**
 * User: golodnyi
 * Date: 12.05.16
 * Time: 17:03
 */

class telemetry_flex_v10 {
    private $numPage = false;
    private $Code = false;
    private $Time = false;
    private $State = false;
    private $Module1 = false;
    private $Module2 = false;
    private $GSM = false;
    private $StateGauge = false;
    private $LastTime = false;
    private $Lat = false;
    private $Lon = false;
    private $Alt = false;
    private $Speed = false;
    private $Course = false;
    private $Mileage = false;
    private $Way = false;
    private $AllSeconds = false;
    private $SecondLast = false;
    private $Power = false;
    private $Reserv = false;
    private $StateU_Ain1 = false;
    private $StateU_Ain2 = false;
    private $StateU_Ain3 = false;
    private $StateU_Ain4 = false;
    private $StateU_Ain5 = false;
    private $StateU_Ain6 = false;
    private $StateU_Ain7 = false;
    private $StateU_Ain8 = false;
    private $StateIn1 = false;
    private $StateIn2 = false;
    private $stateOut1 = false;
    private $StateOut2 = false;
    private $StateInImp1 = false;
    private $StateInImp2 = false;
    private $Frequency1 = false;
    private $Frequency2 = false;
    private $Motochas = false;
    private $LevelRS485_1 = false;
    private $LevelRS485_2 = false;
    private $LevelRS485_3 = false;
    private $LevelRS485_4 = false;
    private $LevelRS485_5 = false;
    private $LevelRS485_6 = false;
    private $LevelRS232 = false;
    private $Temp1 = false;
    private $Temp2 = false;
    private $Temp3 = false;
    private $Temp4 = false;
    private $Temp5 = false;
    private $Temp6 = false;
    private $Temp7 = false;
    private $Temp8 = false;
    private $CAN_FuelLevel = false;
    private $CAN_FuelConsumption = false;
    private $CAN_EngineTurns = false;
    private $CAN_Temp = false;
    private $CAN_FullRun = false;
    private $CAN_AxleLoad_1 = false;
    private $CAN_AxleLoad_2 = false;
    private $CAN_AxleLoad_3 = false;
    private $CAN_AxleLoad_4 = false;
    private $CAN_AxleLoad_5 = false;
    private $CAN_PedalAccel = false;
    private $CAN_PedalStop = false;
    private $CAN_EngineLoad = false;
    private $CAN_LevelFiltr = false;
    private $CAN_EngineTime = false;
    private $CAN_TimeTO = false;
    private $CAN_Speed = false;

    /**
     * @return mixed
     */
    public function getNumPage()
    {
        return $this->numPage;
    }

    /**
     * @param mixed $numPage
     */
    public function setNumPage($numPage)
    {
        $this->numPage = $numPage;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->Code;
    }

    /**
     * @param mixed $Code
     */
    public function setCode($Code)
    {
        $this->Code = $Code;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->Time;
    }

    /**
     * @param mixed $Time
     */
    public function setTime($Time)
    {
        $this->Time = $Time;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->State;
    }

    /**
     * @param mixed $State
     */
    public function setState($State)
    {
        $this->State = $State;
    }

    /**
     * @return mixed
     */
    public function getModule1()
    {
        return $this->Module1;
    }

    /**
     * @param mixed $Module1
     */
    public function setModule1($Module1)
    {
        $bit = strrev(sprintf( "%08d", decbin($Module1)));
        var_dump($bit);
        $this->Module1 = $bit;
    }

    /**
     * @return mixed
     */
    public function getModule2()
    {
        return $this->Module2;
    }

    /**
     * @param mixed $Module2
     */
    public function setModule2($Module2)
    {
        $this->Module2 = $Module2;
    }

    /**
     * @return mixed
     */
    public function getGSM()
    {
        return $this->GSM;
    }

    /**
     * @param mixed $GSM
     */
    public function setGSM($GSM)
    {
        $this->GSM = $GSM;
    }

    /**
     * @return mixed
     */
    public function getStateGauge()
    {
        return $this->StateGauge;
    }

    /**
     * @param mixed $StateGauge
     */
    public function setStateGauge($StateGauge)
    {
        $this->StateGauge = $StateGauge;
    }

    /**
     * @return mixed
     */
    public function getLastTime()
    {
        return $this->LastTime;
    }

    /**
     * @param mixed $LastTime
     */
    public function setLastTime($LastTime)
    {
        $this->LastTime = $LastTime;
    }

    /**
     * @return mixed
     */
    public function getLat()
    {
        return $this->Lat;
    }

    /**
     * @param mixed $Lat
     */
    public function setLat($Lat)
    {
        $this->Lat = $Lat;
    }

    /**
     * @return mixed
     */
    public function getLon()
    {
        return $this->Lon;
    }

    /**
     * @param mixed $Lon
     */
    public function setLon($Lon)
    {
        $this->Lon = $Lon;
    }

    /**
     * @return mixed
     */
    public function getAlt()
    {
        return $this->Alt;
    }

    /**
     * @param mixed $Alt
     */
    public function setAlt($Alt)
    {
        $this->Alt = $Alt;
    }

    /**
     * @return mixed
     */
    public function getSpeed()
    {
        return $this->Speed;
    }

    /**
     * @param mixed $Speed
     */
    public function setSpeed($Speed)
    {
        $this->Speed = $Speed;
    }

    /**
     * @return mixed
     */
    public function getCourse()
    {
        return $this->Course;
    }

    /**
     * @param mixed $Course
     */
    public function setCourse($Course)
    {
        $this->Course = $Course;
    }

    /**
     * @return mixed
     */
    public function getMileage()
    {
        return $this->Mileage;
    }

    /**
     * @param mixed $Mileage
     */
    public function setMileage($Mileage)
    {
        $this->Mileage = $Mileage;
    }

    /**
     * @return mixed
     */
    public function getWay()
    {
        return $this->Way;
    }

    /**
     * @param mixed $Way
     */
    public function setWay($Way)
    {
        $this->Way = $Way;
    }

    /**
     * @return mixed
     */
    public function getAllSeconds()
    {
        return $this->AllSeconds;
    }

    /**
     * @param mixed $AllSeconds
     */
    public function setAllSeconds($AllSeconds)
    {
        $this->AllSeconds = $AllSeconds;
    }

    /**
     * @return mixed
     */
    public function getSecondLast()
    {
        return $this->SecondLast;
    }

    /**
     * @param mixed $SecondLast
     */
    public function setSecondLast($SecondLast)
    {
        $this->SecondLast = $SecondLast;
    }

    /**
     * @return mixed
     */
    public function getPower()
    {
        return $this->Power;
    }

    /**
     * @param mixed $Power
     */
    public function setPower($Power)
    {
        $this->Power = $Power;
    }

    /**
     * @return mixed
     */
    public function getReserv()
    {
        return $this->Reserv;
    }

    /**
     * @param mixed $Reserv
     */
    public function setReserv($Reserv)
    {
        $this->Reserv = $Reserv;
    }

    /**
     * @return mixed
     */
    public function getStateUAin1()
    {
        return $this->StateU_Ain1;
    }

    /**
     * @param mixed $StateU_Ain1
     */
    public function setStateUAin1($StateU_Ain1)
    {
        $this->StateU_Ain1 = $StateU_Ain1;
    }

    /**
     * @return mixed
     */
    public function getStateUAin2()
    {
        return $this->StateU_Ain2;
    }

    /**
     * @param mixed $StateU_Ain2
     */
    public function setStateUAin2($StateU_Ain2)
    {
        $this->StateU_Ain2 = $StateU_Ain2;
    }

    /**
     * @return mixed
     */
    public function getStateUAin3()
    {
        return $this->StateU_Ain3;
    }

    /**
     * @param mixed $StateU_Ain3
     */
    public function setStateUAin3($StateU_Ain3)
    {
        $this->StateU_Ain3 = $StateU_Ain3;
    }

    /**
     * @return mixed
     */
    public function getStateUAin4()
    {
        return $this->StateU_Ain4;
    }

    /**
     * @param mixed $StateU_Ain4
     */
    public function setStateUAin4($StateU_Ain4)
    {
        $this->StateU_Ain4 = $StateU_Ain4;
    }

    /**
     * @return mixed
     */
    public function getStateUAin5()
    {
        return $this->StateU_Ain5;
    }

    /**
     * @param mixed $StateU_Ain5
     */
    public function setStateUAin5($StateU_Ain5)
    {
        $this->StateU_Ain5 = $StateU_Ain5;
    }

    /**
     * @return mixed
     */
    public function getStateUAin6()
    {
        return $this->StateU_Ain6;
    }

    /**
     * @param mixed $StateU_Ain6
     */
    public function setStateUAin6($StateU_Ain6)
    {
        $this->StateU_Ain6 = $StateU_Ain6;
    }

    /**
     * @return mixed
     */
    public function getStateUAin7()
    {
        return $this->StateU_Ain7;
    }

    /**
     * @param mixed $StateU_Ain7
     */
    public function setStateUAin7($StateU_Ain7)
    {
        $this->StateU_Ain7 = $StateU_Ain7;
    }

    /**
     * @return mixed
     */
    public function getStateUAin8()
    {
        return $this->StateU_Ain8;
    }

    /**
     * @param mixed $StateU_Ain8
     */
    public function setStateUAin8($StateU_Ain8)
    {
        $this->StateU_Ain8 = $StateU_Ain8;
    }

    /**
     * @return mixed
     */
    public function getStateIn1()
    {
        return $this->StateIn1;
    }

    /**
     * @param mixed $StateIn1
     */
    public function setStateIn1($StateIn1)
    {
        $this->StateIn1 = $StateIn1;
    }

    /**
     * @return mixed
     */
    public function getStateIn2()
    {
        return $this->StateIn2;
    }

    /**
     * @param mixed $StateIn2
     */
    public function setStateIn2($StateIn2)
    {
        $this->StateIn2 = $StateIn2;
    }

    /**
     * @return mixed
     */
    public function getStateOut1()
    {
        return $this->stateOut1;
    }

    /**
     * @param mixed $stateOut1
     */
    public function setStateOut1($stateOut1)
    {
        $this->stateOut1 = $stateOut1;
    }

    /**
     * @return mixed
     */
    public function getStateOut2()
    {
        return $this->StateOut2;
    }

    /**
     * @param mixed $StateOut2
     */
    public function setStateOut2($StateOut2)
    {
        $this->StateOut2 = $StateOut2;
    }

    /**
     * @return mixed
     */
    public function getStateInImp1()
    {
        return $this->StateInImp1;
    }

    /**
     * @param mixed $StateInImp1
     */
    public function setStateInImp1($StateInImp1)
    {
        $this->StateInImp1 = $StateInImp1;
    }

    /**
     * @return mixed
     */
    public function getStateInImp2()
    {
        return $this->StateInImp2;
    }

    /**
     * @param mixed $StateInImp2
     */
    public function setStateInImp2($StateInImp2)
    {
        $this->StateInImp2 = $StateInImp2;
    }

    /**
     * @return mixed
     */
    public function getFrequency1()
    {
        return $this->Frequency1;
    }

    /**
     * @param mixed $Frequency1
     */
    public function setFrequency1($Frequency1)
    {
        $this->Frequency1 = $Frequency1;
    }

    /**
     * @return mixed
     */
    public function getFrequency2()
    {
        return $this->Frequency2;
    }

    /**
     * @param mixed $Frequency2
     */
    public function setFrequency2($Frequency2)
    {
        $this->Frequency2 = $Frequency2;
    }

    /**
     * @return mixed
     */
    public function getMotochas()
    {
        return $this->Motochas;
    }

    /**
     * @param mixed $Motochas
     */
    public function setMotochas($Motochas)
    {
        $this->Motochas = $Motochas;
    }

    /**
     * @return mixed
     */
    public function getLevelRS4851()
    {
        return $this->LevelRS485_1;
    }

    /**
     * @param mixed $LevelRS485_1
     */
    public function setLevelRS4851($LevelRS485_1)
    {
        $this->LevelRS485_1 = $LevelRS485_1;
    }

    /**
     * @return mixed
     */
    public function getLevelRS4852()
    {
        return $this->LevelRS485_2;
    }

    /**
     * @param mixed $LevelRS485_2
     */
    public function setLevelRS4852($LevelRS485_2)
    {
        $this->LevelRS485_2 = $LevelRS485_2;
    }

    /**
     * @return mixed
     */
    public function getLevelRS4853()
    {
        return $this->LevelRS485_3;
    }

    /**
     * @param mixed $LevelRS485_3
     */
    public function setLevelRS4853($LevelRS485_3)
    {
        $this->LevelRS485_3 = $LevelRS485_3;
    }

    /**
     * @return mixed
     */
    public function getLevelRS4854()
    {
        return $this->LevelRS485_4;
    }

    /**
     * @param mixed $LevelRS485_4
     */
    public function setLevelRS4854($LevelRS485_4)
    {
        $this->LevelRS485_4 = $LevelRS485_4;
    }

    /**
     * @return mixed
     */
    public function getLevelRS4855()
    {
        return $this->LevelRS485_5;
    }

    /**
     * @param mixed $LevelRS485_5
     */
    public function setLevelRS4855($LevelRS485_5)
    {
        $this->LevelRS485_5 = $LevelRS485_5;
    }

    /**
     * @return mixed
     */
    public function getLevelRS4856()
    {
        return $this->LevelRS485_6;
    }

    /**
     * @param mixed $LevelRS485_6
     */
    public function setLevelRS4856($LevelRS485_6)
    {
        $this->LevelRS485_6 = $LevelRS485_6;
    }

    /**
     * @return mixed
     */
    public function getLevelRS232()
    {
        return $this->LevelRS232;
    }

    /**
     * @param mixed $LevelRS232
     */
    public function setLevelRS232($LevelRS232)
    {
        $this->LevelRS232 = $LevelRS232;
    }

    /**
     * @return mixed
     */
    public function getTemp1()
    {
        return $this->Temp1;
    }

    /**
     * @param mixed $Temp1
     */
    public function setTemp1($Temp1)
    {
        $this->Temp1 = $Temp1;
    }

    /**
     * @return mixed
     */
    public function getTemp2()
    {
        return $this->Temp2;
    }

    /**
     * @param mixed $Temp2
     */
    public function setTemp2($Temp2)
    {
        $this->Temp2 = $Temp2;
    }

    /**
     * @return mixed
     */
    public function getTemp3()
    {
        return $this->Temp3;
    }

    /**
     * @param mixed $Temp3
     */
    public function setTemp3($Temp3)
    {
        $this->Temp3 = $Temp3;
    }

    /**
     * @return mixed
     */
    public function getTemp4()
    {
        return $this->Temp4;
    }

    /**
     * @param mixed $Temp4
     */
    public function setTemp4($Temp4)
    {
        $this->Temp4 = $Temp4;
    }

    /**
     * @return mixed
     */
    public function getTemp5()
    {
        return $this->Temp5;
    }

    /**
     * @param mixed $Temp5
     */
    public function setTemp5($Temp5)
    {
        $this->Temp5 = $Temp5;
    }

    /**
     * @return mixed
     */
    public function getTemp6()
    {
        return $this->Temp6;
    }

    /**
     * @param mixed $Temp6
     */
    public function setTemp6($Temp6)
    {
        $this->Temp6 = $Temp6;
    }

    /**
     * @return mixed
     */
    public function getTemp7()
    {
        return $this->Temp7;
    }

    /**
     * @param mixed $Temp7
     */
    public function setTemp7($Temp7)
    {
        $this->Temp7 = $Temp7;
    }

    /**
     * @return mixed
     */
    public function getTemp8()
    {
        return $this->Temp8;
    }

    /**
     * @param mixed $Temp8
     */
    public function setTemp8($Temp8)
    {
        $this->Temp8 = $Temp8;
    }

    /**
     * @return mixed
     */
    public function getCANFuelLevel()
    {
        return $this->CAN_FuelLevel;
    }

    /**
     * @param mixed $CAN_FuelLevel
     */
    public function setCANFuelLevel($CAN_FuelLevel)
    {
        $this->CAN_FuelLevel = $CAN_FuelLevel;
    }

    /**
     * @return mixed
     */
    public function getCANFuelConsumption()
    {
        return $this->CAN_FuelConsumption;
    }

    /**
     * @param mixed $CAN_FuelConsumption
     */
    public function setCANFuelConsumption($CAN_FuelConsumption)
    {
        $this->CAN_FuelConsumption = $CAN_FuelConsumption;
    }

    /**
     * @return mixed
     */
    public function getCANEngineTurns()
    {
        return $this->CAN_EngineTurns;
    }

    /**
     * @param mixed $CAN_EngineTurns
     */
    public function setCANEngineTurns($CAN_EngineTurns)
    {
        $this->CAN_EngineTurns = $CAN_EngineTurns;
    }

    /**
     * @return mixed
     */
    public function getCANTemp()
    {
        return $this->CAN_Temp;
    }

    /**
     * @param mixed $CAN_Temp
     */
    public function setCANTemp($CAN_Temp)
    {
        $this->CAN_Temp = $CAN_Temp;
    }

    /**
     * @return mixed
     */
    public function getCANFullRun()
    {
        return $this->CAN_FullRun;
    }

    /**
     * @param mixed $CAN_FullRun
     */
    public function setCANFullRun($CAN_FullRun)
    {
        $this->CAN_FullRun = $CAN_FullRun;
    }

    /**
     * @return mixed
     */
    public function getCANAxleLoad1()
    {
        return $this->CAN_AxleLoad_1;
    }

    /**
     * @param mixed $CAN_AxleLoad_1
     */
    public function setCANAxleLoad1($CAN_AxleLoad_1)
    {
        $this->CAN_AxleLoad_1 = $CAN_AxleLoad_1;
    }

    /**
     * @return mixed
     */
    public function getCANAxleLoad2()
    {
        return $this->CAN_AxleLoad_2;
    }

    /**
     * @param mixed $CAN_AxleLoad_2
     */
    public function setCANAxleLoad2($CAN_AxleLoad_2)
    {
        $this->CAN_AxleLoad_2 = $CAN_AxleLoad_2;
    }

    /**
     * @return mixed
     */
    public function getCANAxleLoad3()
    {
        return $this->CAN_AxleLoad_3;
    }

    /**
     * @param mixed $CAN_AxleLoad_3
     */
    public function setCANAxleLoad3($CAN_AxleLoad_3)
    {
        $this->CAN_AxleLoad_3 = $CAN_AxleLoad_3;
    }

    /**
     * @return mixed
     */
    public function getCANAxleLoad4()
    {
        return $this->CAN_AxleLoad_4;
    }

    /**
     * @param mixed $CAN_AxleLoad_4
     */
    public function setCANAxleLoad4($CAN_AxleLoad_4)
    {
        $this->CAN_AxleLoad_4 = $CAN_AxleLoad_4;
    }

    /**
     * @return mixed
     */
    public function getCANAxleLoad5()
    {
        return $this->CAN_AxleLoad_5;
    }

    /**
     * @param mixed $CAN_AxleLoad_5
     */
    public function setCANAxleLoad5($CAN_AxleLoad_5)
    {
        $this->CAN_AxleLoad_5 = $CAN_AxleLoad_5;
    }

    /**
     * @return mixed
     */
    public function getCANPedalAccel()
    {
        return $this->CAN_PedalAccel;
    }

    /**
     * @param mixed $CAN_PedalAccel
     */
    public function setCANPedalAccel($CAN_PedalAccel)
    {
        $this->CAN_PedalAccel = $CAN_PedalAccel;
    }

    /**
     * @return mixed
     */
    public function getCANPedalStop()
    {
        return $this->CAN_PedalStop;
    }

    /**
     * @param mixed $CAN_PedalStop
     */
    public function setCANPedalStop($CAN_PedalStop)
    {
        $this->CAN_PedalStop = $CAN_PedalStop;
    }

    /**
     * @return mixed
     */
    public function getCANEngineLoad()
    {
        return $this->CAN_EngineLoad;
    }

    /**
     * @param mixed $CAN_EngineLoad
     */
    public function setCANEngineLoad($CAN_EngineLoad)
    {
        $this->CAN_EngineLoad = $CAN_EngineLoad;
    }

    /**
     * @return mixed
     */
    public function getCANLevelFiltr()
    {
        return $this->CAN_LevelFiltr;
    }

    /**
     * @param mixed $CAN_LevelFiltr
     */
    public function setCANLevelFiltr($CAN_LevelFiltr)
    {
        $this->CAN_LevelFiltr = $CAN_LevelFiltr;
    }

    /**
     * @return mixed
     */
    public function getCANEngineTime()
    {
        return $this->CAN_EngineTime;
    }

    /**
     * @param mixed $CAN_EngineTime
     */
    public function setCANEngineTime($CAN_EngineTime)
    {
        $this->CAN_EngineTime = $CAN_EngineTime;
    }

    /**
     * @return mixed
     */
    public function getCANTimeTO()
    {
        return $this->CAN_TimeTO;
    }

    /**
     * @param mixed $CAN_TimeTO
     */
    public function setCANTimeTO($CAN_TimeTO)
    {
        $this->CAN_TimeTO = $CAN_TimeTO;
    }

    /**
     * @return mixed
     */
    public function getCANSpeed()
    {
        return $this->CAN_Speed;
    }

    /**
     * @param mixed $CAN_Speed
     */
    public function setCANSpeed($CAN_Speed)
    {
        $this->CAN_Speed = $CAN_Speed;
    }

}