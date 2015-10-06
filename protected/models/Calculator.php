<?php
class Calculator {
    /**
     * Преобразовать дату из формата ISO в русский вариант
     * @param string $iso YYYY-MM-DD HH:II:SS
     * @return string без времени DD.MM.YYYY 
     */
    public static function date($iso) {
        $iso = trim($iso);
        if (!$iso) {
            return null;
        }
        list($date_ISO) = explode(' ', $iso);
        return implode('.', array_reverse(explode('-', $date_ISO)));
    }
    /**
     * Преобразовать дату из русского формата в ISO-формат
     * @param string $rusDate DD.MM.YYYY
     * @return string $iso YYYY-MM-DD
     */
    public static function convertDateToISO($rusDate) {
        $rusDate = trim($rusDate);
        if (!$rusDate) {
            return null;
        }
        return implode('-', array_reverse(explode('.', $rusDate)));
    }
    /**
     * Получить полную матрицу данных
     * @param string $sql
     * @return array [<index>][<field>] => <value>
     */
    public static function getAll($sql) {
        return Yii::app()->db->createCommand($sql)->query()->readAll();
    }
    /**
     * Выполнить произвольный запрос
     * @param string $query
     * @return mixed
     */
    public static function runQuery($query) {
        $connection=Yii::app()->db;
        $command = $connection->createCommand($query);
        return $command->query();
    }
    /**
     * Получить строку таблицы
     * @param string $sql
     * @return array [<field>] => <value>
     */
    public static final function getRow($sql) {
        $data = self::getAll($sql);
        return $data[0]; 
    }
    /**
     * Получить значение поля таблицы
     * @param string $sql
     * @return mixed
     */
    public static final function getOne($sql) {
        return current(self::getRow($sql)); 
    }
    /**
     * Получить значения столбца таблицы
     * @param string $sql
     * @return array
     */
    public static final function getCol($sql) {
        $result = array();
        foreach (self::getAll($sql) as $row) {
            $result[] = current($row);
        }
        return $result; 
    }
    /**
     * Получить ассоциативную матрицу таблицы
     * @param string $sql
     * @return array для кол-ва полей > 2 array[<first-field-value>][<field>] => <value> 
     *               для кол-ва полей = 2 array[<first-field-value>] => <second-field-value>
     */
    public static final function getAssoc($sql) {
        $result = array();
        $data = self::getAll($sql); //print_r($sql); die();
        $row = current($data);
        if (!$row) {
            return array();
        }
        $fields = array_keys($row);
        if (count($fields) == 2) {
            foreach ($data as $v) {
                $result[$v[$fields[0]]] = $v[$fields[1]];
            }
        } else {
            $fkey = $fields[0];
            unset($fields[0]);
            foreach ($data as $v) {
                $key = $v[$fkey]; 
                unset($v[$fkey]);
                $result[$key] = $v; 
            }
        }
        return $result; 
    }
    
    /**
     * Расчитать размер пенсии
     * @param int $caseID Идентификатор карточки
     * @return array Параметры расчета
     */
    public static function calcPension($caseID) {
        return self::_calcPension($caseID, self::getOne("select now()::date"));
    }
    /**
     * Пересчитать пенсию
     * @param unknown $caseID
     * @param unknown $params array(date)
     */
    public static function recalcPension($caseID, $params) { //print_r($params); die();
        //print_r($caseID); die();
        $result = self::_calcPension($caseID, $params['date']); //print_r($result);
        $value = $result['RESULT'];

        self::runQuery("UPDATE cases SET saved_summa = '$value' WHERE id = {$caseID}");
        self::runQuery("INSERT INTO calcs (
            \"case\", basis_doc, time, value, calc_group) 
            VALUES (
            {$caseID}, 
            {$params['basicDocID']}, 
            '{$params['date']}', 
            '$value',
            {$params['calcGroupID']}
            )");
    }
    /**
     * @ignore
     */
    private static function _calcPension($caseID, $date, $recalc = false) {
        // Данные карточки
        $case_Row = self::getRow("SELECT * FROM cases WHERE id = {$caseID}"); //print_r($case_Row); echo "<hr>";
        // Данные человека
        $person_Row = self::getRow("SELECT * FROM persons WHERE id = {$case_Row['person']}"); // print_r($person_Row); //echo "<hr>"; die();
               
        // Выслуги (лет)
        $seniorities_y = self::getAssoc("SELECT class, value FROM seniorities WHERE type = 'y' AND person = {$case_Row['person']}");
        // Определить SQL-дату перерасчета
        $dateSQL = $date ? ("'" . $date . "'") : ("'" . $case_Row['calc_date'] . "'");
        $R['calc_date'] = $case_Row['calc_date'];
        
        // Считать актуальные параметры перерасчета
        // Ограничение окладов денежного содержания (на текущее время)
        $R['OODS'] = self::getOne("SELECT get_actual_param_value('OODS', $dateSQL)");
        $R['OODS'] = $R['OODS'] ? $R['OODS'] : 1; //$OODS = 0.56;
        
        // Базовая часть трудовой пенсии по старости
        $R['TPS'] = self::getOne("SELECT get_actual_param_value('U_TPS', $dateSQL)");
        
        // Для удобства понимания алгоритма действующей программы имена переменых названы в соответствии с названиями в программе
        // PENS->...
        $PENS = array(
            'VP' => $case_Row['type'], // Вид карточки
            'TRS' => $case_Row['include_seniority'] ? true : false, // Признак учета трудового стажа в выслуге лет
            'BISLOB' => $seniorities_y['common'], // Выслуга общая
        );
        // Дата перерасчета
        $R['DPER'] = $case_Row['calc_date'] ? $case_Row['calc_date'] : date('Y-m-d');
        // Дата рождения
        $R['DROD'] = $person_Row['birth_date'];
        // Оклад по должности
        $R['OKLD'] = $case_Row['salary_post']; //echo($OKLD); die();
        // Оклад по званию
        $R['OKLR'] = $case_Row['salary_rank']; //echo($OKLR); die();
        // Процент надбавки за выслугу лет        
        $R['NADBPR'] = $case_Row['year_inc_percent'];
        // Размер надбавки за выслугу лет
        $R['NADB'] = ($R['OKLD'] + $R['OKLR']) * $R['NADBPR'] / 100; //echo($NADB);  //"IF(VP$'СПВЗ',0.001,(OKLD+OKLR)*nachNADB/100)"
        // Всего
        $R['COMMON'] = $R['OKLD'] + $R['OKLR'] + $R['NADB'];
        // оклады из которых исчислена пенсия
        $R['nachODS'] = $R['COMMON'] * $R['OODS'];
        
        // Процент от размера пенсии ($OKLD + $OKLR + $NADB)
        $R['RAZPEN'] = null;
        if (!$PENS['TRS'] && $PENS['BISLOB'] >= 20) { // "!PENS->TRS.and.PENS->BISLOB>=20"   "Min(85,50+3*(BISLOB-20))"
            $R['RAZPEN'] = 50 + 3 * ($PENS['BISLOB'] - 20);
        }
        if ($PENS['TRS'] && $PENS['BISLOB'] >= 20) { // "PENS->TRS.and.PENS->BISLOB>=20"    "Min(85,50+BISLOB-25)"
            $R['RAZPEN'] = 50 + $PENS['BISLOB'] - 20;
        }
        if ($R['RAZPEN'] && $R['RAZPEN'] > 85) {
            $R['RAZPEN'] = 85;
        }
        // Основной размер пенсии //"Many(RAZPEN*nachODS/100)"
        $R['OSNPR'] = $R['RAZPEN'] * $R['nachODS'] / 100; //print_r($R['OSNPR']);
        
        // Право на пенсию иждивенца //IF(PENS->VP='ПК',Pravo_Igd(),IF(PENS->VP='ВЛ'.and.BISLOB<20,.F.,.t.))        
        $pravopen = false;
        if ($PENS['VP'] == 'ПК') {
            $pravopen = self::Pravo_Igd($case_Row);
        } 
        if ($PENS['VP'] == 'ВЛ') {
            $pravopen = $PENS['BISLOB'] >= 20;
        }

        // Надбавки
        $markups = array();
        
        // Надбавка на уход достигшему 80 лет //"pravopen.and.SrYear(DPER,DROD)>=80"    "Many(TPS)"
        if ($pravopen && (self::SrYear($R['DPER'], $R['DROD']) >= 80)) {
            if ($R['TPS'] > $markups['r_uhod']) {
                $markups['r_uhod'] = $R['TPS'];
            }
        } 
        
        // "pravopen.and.('6'$nachWAR.or.FindChr('1а1б1г1д1е1ж2в',nachWAR,2)).and.(!Bit(APP,12).or.Bit(APP,8))" "Many(TPS*0.32)"
        
        $R['RESULT'] = $R['OSNPR'];
        
        // Прибавление надбавок
        $R['markups_SUMMA'] = 0;
        foreach ($markups as $k => $v) {
            $R['markups_SUMMA'] += $v;
        }
        $R['RESULT'] += $R['markups_SUMMA'];
        
        $R['markups'] = $markups;
        $R['PENS'] = $PENS;
        
        return $R;
    }
    
    /**
     * Получить список пенсионеров для которых в следующем месяце заканчиваются те или иные выплаты
     * @return array[$caseID][<field>] = <value>
     */
    public static function getCases_WithOverduePaymentsInNextMonth() {
        // Последние актуальные платежи (актуальный график платежей)
        $actualPayments_SQL = "
        SELECT p.* FROM payments AS p, payments_type AS t WHERE t.id = p.type
        AND p.id = (SELECT p2.id FROM payments AS p2 WHERE p2.case = p.case AND p2.type = p.type AND p.end_date IS NOT NULL AND t.period = 12 AND summa > 0 ORDER BY begin_date DESC LIMIT 1)
        AND ((p.end_date >= date_trunc('month', now()) + interval '1 month') AND (p.end_date < date_trunc('month', now()) + interval '2 month'))
        ";
        
        $query = "SELECT DISTINCT \"case\" FROM ({$actualPayments_SQL}) AS q";
        
        $data = array();
        foreach (self::getCol($query) as $caseID) {
            $row = self::getRow("SELECT persons.*, cases.type, cases.number FROM persons, cases WHERE cases.person = persons.id AND cases.id = {$caseID}");
            $data[$caseID] = $row;
        }

        return $data;
    }
    
    
    private static function Pravo_Igd($case_Row) {
    
    }
    /**
     * Получить количество лет разницы между 2-мя датами
     * @param string $date1 ISO
     * @param string $date2 ISO
     * @return int
     */
    private static function SrYear($date1, $date2) {
        return 90;
    }
    
}