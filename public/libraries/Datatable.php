<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * The MIT License (MIT)

Copyright (c) 2015 Paul Zepernick

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
 *
 */

/**
 * Codeigniter Datatable library
 *
 *
 * @author Paul Zepernick
 */
class Datatable
{

    public $model;

    public $CI;

    public $rowIdCol;

    public $conds;

    public $RC;

    public $matchedrefs;

    private $current_model;
    /**
     * @params
     *         Associative array.  Expecting key "model" and the value name of the model to load
     */
    public function __construct($params)
    {
        $CI = &get_instance();
        $RC = &get_instance();

        if (isset($params['model']) === false) {
            throw new Exception('Expected a parameter named "model".');
        }

        $model = $params['model'];
        $this->current_model = $params['model'];

        $this->rowIdCol = isset($params['rowIdCol']) ? $params['rowIdCol'] : null;

        $this->conds = isset($params['conds']) ? $params['conds'] : array();

        $this->matchedrefs = isset($params['matchedrefs']) ? $params['matchedrefs'] : '';

        $CI->load->model($model);
       // $RC->load->driver('cache', array('adapter' => 'redis', 'backup' => 'file'));
        if (($CI->$model instanceof DatatableModel) === false) {
            throw new Exception('Model must implement the DatatableModel Interface');
        }

        //even though $model is a String php looks at the String value and finds the property
        //by that name.  Hence the $ when that would not normally be there for a property
        $this->model = $CI->$model;
        $this->CI = $CI;
        $this->RC = $RC;
    }

    /**
     * @param formats
     *             Associative array.
     *                 Key is column name
     *                 Value format: percent, currency, date, boolean
     */
    public function datatableJson($formats = array())
    {
        $f = $this->CI->input;

        $start = (int) $f->post('start');
        $limit = (int) $f->post('length');
        $min_price = (int) $f->post('fmnprice');
        $max_price = (int) $f->post('fmxprice');

        $jsonArry = array();
        $jsonArry['start'] = $start;
        $jsonArry['limit'] = $limit;
        $jsonArry['draw'] = (int) $f->post('draw');
        $jsonArry['recordsTotal'] = 0;
        $jsonArry['recordsFiltered'] = 0;
        $jsonArry['data'] = array();

        //query the data for the records being returned
        $selectArray = array();
        $customCols = array();
        $columnIdxArray = array();

        if ($f->post('columns')) {
            foreach ($f->post('columns') as $c) {
                $columnIdxArray[] = $c['data'];
                if (substr($c['data'], 0, 1) === '$') {
                    //indicates a column specified in the appendToSelectStr()
                    $customCols[] = $c['data'];
                    continue;
                }
                $selectArray[] = $c['data'];
            }
        }

        if ($this->rowIdCol !== null && in_array($this->rowIdCol, $selectArray) === false) {
            $selectArray[] = $this->rowIdCol;
        }
        //put the select string together
        //pull security deposit out
        $newSelectArray = $selectArray;
        //remove lp.security_deposit
        if (($key = array_search('lp.security_deposit', $newSelectArray)) !== false) {
            unset($newSelectArray[$key]);
        }
        $sqlSelectStr = implode(', ', $newSelectArray);
        $appendStr = $this->model->appendToSelectStr();

        if (is_null($appendStr) === false) {
            foreach ($appendStr as $alias => $sqlExp) {
                $sqlSelectStr .= ', ' . $sqlExp . ' ' . $alias;
            }

        }

        //setup group by
        $appendGrpbyStr = $this->model->appendToGroupBy();

        if (is_null($appendGrpbyStr) === false) {
            $this->CI->db->group_by($appendGrpbyStr);
        }

        //setup order by
        $customExpArray = is_null($this->model->appendToSelectStr()) ?
        array() :
        $this->model->appendToSelectStr();

        if ($f->post('order')) {
            foreach ($f->post('order') as $o) {
                if ($o['column'] !== '') {
                    $colName = $columnIdxArray[$o['column']];
                    //handle custom sql expressions/subselects
                    if (substr($colName, 0, 2) === '$.') {
                        $aliasKey = substr($colName, 2);
                        if (isset($customExpArray[$aliasKey]) === false) {
                            throw new Exception('Alias[' . $aliasKey . '] Could Not Be Found In appendToSelectStr() Array');
                        }

                        $colName = $customExpArray[$aliasKey];
                    }
                    $this->current_model != 'contactsdatatable'?$this->CI->db->order_by($colName, $o['dir'], false):'';
                }
            }
        }

        $postedvals = $f->post();

        foreach ($postedvals as $key => $value) {
            if (is_null($value) || $value == '') {
                unset($postedvals[$key]);
            }

        }
        unset($postedvals['draw']);
        unset($postedvals['columns']);
        unset($postedvals['draw']);
        unset($postedvals['order']);
        unset($postedvals['start']);
        unset($postedvals['length']);
        unset($postedvals['search']);

        if (!empty($postedvals)) {
            foreach ($postedvals as $key => $val) {
                if (is_array($val)) {
                    $postvals .= $key . implode(',', $val);
                } else {
                    $postvals .= $key . $val;
                }
            }

        }
        $this->CI->db->select($sqlSelectStr);
        $whereDebug = $this->sqlJoinsAndWhere();


        $selectfieldsstr = implode(',', $selectArray);
        if ($limit > 0) {
            $this->CI->db->limit($limit, $start);
        }

            //$this -> CI -> db -> where('p.price >=', $min_price);
            // $this->CI->db->select('dfdf, fdfd');

            $query = $this->CI->db->get();

            //echo $this -> CI -> db ->last_query();exit;
            if (!$query) {
                $jsonArry['errorMessage'] = $this->CI->db->_error_message();
                return $jsonArry;
            }

            //process the results and create the JSON objects
            $dataArray = array();
            $allColsArray = array_merge($selectArray, $customCols);
            foreach ($query->result() as $row) {
                $colObj = array();
                //loop rows returned by the query
                foreach ($allColsArray as $c) {
                    if (trim($c) === '') {
                        continue;
                    }

                    $propParts = explode('.', $c);

                    $prop = trim(end($propParts));
                    //loop columns in each row that the grid has requested
                    if (count($propParts) > 1) {
                        //nest the objects correctly in the json if the column name includes
                        //the table alias
                        $nestedObj = array();
                        if (isset($colObj[$propParts[0]])) {
                            //check if we alraedy have a object for this alias in the array
                            $nestedObj = $colObj[$propParts[0]];
                        }

                        $nestedObj[$propParts[1]] = $this->formatValue($formats, $prop, $row->$prop);
                        $colObj[$propParts[0]] = $nestedObj;
                    } else {
                        $colObj[$c] = $this->formatValue($formats, $prop, $row->$prop);
                    }
                }

                if ($this->rowIdCol !== null) {
                    $tmpRowIdSegments = explode('.', $this->rowIdCol);
                    $idCol = trim(end($tmpRowIdSegments));
                    $colObj['DT_RowId'] = $row->$idCol;
                }
                $dataArray[] = $colObj;
            }

            $wr = ($this->current_model == 'Listingsdatatable') ? $this->sqlCountJoinsAndWhere() : $this->sqlJoinsAndWhere();
            if (is_null($appendGrpbyStr) === false) {
                $this->CI->db->group_by($appendGrpbyStr);
            }
            ($this->current_model == 'Listingsdatatable') ? $this->CI->db->select("p.id") : '';
            $query = $this->CI->db->get();
            $totalRecords = $query->num_rows();

            // $totalRecordsQuery = $this->CI->db->query("SELECT  COUNT(*) as `totoalRecoreds` FROM `crm_listings`");
            // $totalRecords = $totalRecordsQuery->result()[0]->totoalRecoreds;

            $jsonArry = array();
            $jsonArry['start'] = $start;
            $jsonArry['limit'] = $limit;
            $jsonArry['draw'] = (int) $f->post('draw');
            $jsonArry['recordsTotal'] = $totalRecords;
            $jsonArry['recordsFiltered'] = $totalRecords;
            $jsonArry['data'] = $dataArray;
            $jsonArry['debug'] = $whereDebug;
//var_dump($dataArray);
            //var_dump($jsonArry);
           // $this->RC->cache->save($rediskey, $jsonArry, 1209600); //store for 14 days
            //var_dump($this->RC->cache->get($rediskey));EXIT;
        // } else {
            //$jsonArry = $this->RC->cache->get($rediskey);
            $jsonArry['draw'] = ''; // rand();
        // }
//echo "<pre>";print_r($jsonArry);exit;
        return $jsonArry;

    }

    private function formatValue($formats, $column, $value)
    {
        if (isset($formats[$column]) === false) {
            return $value;
        }

        switch ($formats[$column]) {
            case 'date':
                $dtFormats = array('Y-m-d H:i:s', 'Y-m-d');
                $dt = null;
                //try to parse the date as 2 different formats
                foreach ($dtFormats as $f) {
                    $dt = DateTime::createFromFormat($f, $value);
                    if ($dt !== false) {
                        break;
                    }
                }
                if ($dt === false) {
                    //neither pattern could parse the date
                    throw new Exception('Could Not Parse To Date For Formatting [' . $value . ']');
                }
                return $dt->format('m/d/Y');
            case 'dateonly':
                $dtFormats = array('d/m/y', 'Y-m-d');
                $dt = null;
                //try to parse the date as 2 different formats
                foreach ($dtFormats as $f) {
                    $dt = DateTime::createFromFormat($f, $value);
                    if ($dt !== false) {
                        break;
                    }
                }
                if ($dt === false) {
                    //neither pattern could parse the date
                    throw new Exception('Could Not Parse To Date For Formatting [' . $value . ']');
                }
                return $dt->format('m/d/Y');
            case 'datetime':
                $dtFormats = array('Y-m-d H:i:s', 'Y-m-d');
                $dt = null;
                //try to parse the date as 2 different formats
                foreach ($dtFormats as $f) {
                    $dt = DateTime::createFromFormat($f, $value);
                    if ($dt !== false) {
                        break;
                    }
                }
                if ($dt === false) {
                    //neither pattern could parse the date
                    throw new Exception('Could Not Parse To Date For Formatting [' . $value . ']');
                }
                return $dt->format('m/d/Y h:i A');
            case 'percent':
                ///$formatter = new \NumberFormatter('en_US', \NumberFormatter::PERCENT);
                //return $formatter -> format(floatval($value) * .01);
                return $value . '%';
            case 'currency':
                return '$' . number_format(floatval($value), 2);
            case 'priceformat':
                return number_format(floatval($value)) . '&nbsp;AED';
            case 'boolean':
                $b = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                return $b ? 'Yes' : 'No';
        }

        return $value;
    }

    //specify the joins and where clause for the Active Record. This code is common to
    //fetch the data and get a total record count
    private function sqlJoinsAndWhere()
    {
        $debug = '';
        $this->CI->db->from($this->model->fromTableStr());

        $joins = $this->model->joinArray() === null ? array() : $this->model->joinArray();
        foreach ($joins as $table => $on) {
            $joinTypeArray = explode('|', $table);
            $tableName = $joinTypeArray[0];
            $join = 'inner';
            if (count($joinTypeArray) > 1) {
                $join = $joinTypeArray[1];
            }
            $this->CI->db->join($tableName, $on, $join);
        }

        $customExpArray = is_null($this->model->appendToSelectStr()) ?
        array() :
        $this->model->appendToSelectStr();

        $f = $this->CI->input;

        if ($f->post('columns')) {
            foreach ($f->post('columns') as $c) {
                if ($c['search']['value'] !== '') {
                    $colName = $c['data'];
                    //handle custom sql expressions/subselects
                    if (substr($colName, 0, 2) === '$.') {
                        $aliasKey = substr($colName, 2);
                        if (isset($customExpArray[$aliasKey]) === false) {
                            throw new Exception('Alias[' . $aliasKey . '] Could Not Be Found In appendToSelectStr() Array');
                        }

                        $colName = $customExpArray[$aliasKey];
                    }

                    $colvalue = $c['search']['value'];
                    if ($colName == 'added_date') {$colName = "date(added_date)";
                        $colvalue = date('Y-m-d', strtotime($colvalue));}
                    $debug .= 'col[' . $c['data'] . '] value[' . $c['search']['value'] . '] ' . PHP_EOL;
                    
                    if($colName == 'p.status' && $colvalue == 'G' ){
                        $colName = 'p.leadgen';$colvalue = 'Yes';
                    }
                    $this->CI->db->like($colName, $colvalue);

                }
            
         else {
            $colvalue = $c['search']['value'];
            $array = array('refno' => $colvalue, 'firstname' => $colvalue, 'lastname' => $colvalue, 'detail.mobile' => $colvalue, 'detail.email' => $colvalue, 'users.display_name' => $colvalue);
            $this->CI->db->like($array, '%a');

        }}}

        //append a static where clause to what the user has filtered, if the model tells us to do so
        $wArray = $this->model->whereClauseArray();
				if($this->current_model != 'contactsdatatable'){
					if ($wArray['c.delete_status'] == 'N') {
							$array = array('refno' => $colvalue, 'firstname' => $colvalue, 'lastname' => $colvalue, 'detail.mobile' => $colvalue, 'detail.email' => $colvalue, 'users.display_name' => $colvalue);
							$this->CI->db->or_like($array, '%a');
					}
				}
        if (is_null($wArray) === false && is_array($wArray) === true && count($wArray) > 0) {

            //echo "<pre>";print_r($wArray);exit;
            if (isset($wArray['p.price >=']) && $wArray['p.price >='] == '100000000' && $f->post('fmnprice') != '') {
                $wArray['p.price >='] = $f->post('fmnprice');
            }
            $this->CI->db->where($wArray);

            if ($this->CI->session->userdata('user_role') == '') {
                $this->CI->db->where(array('agents_id!=' => 30));
                $this->CI->db->where(array('agents_id!=' => 34));
            }
        }
        if (is_null($this->conds) === false && is_array($this->conds) === true && count($this->conds) > 0) {
            $this->CI->db->where($this->conds);
            if ($this->CI->session->userdata('user_role') == '') {
                $this->CI->db->where(array('agents_id!=' => 30));
                $this->CI->db->where(array('agents_id!=' => 34));
            }
        }
        if (is_null($this->matchedrefs) === false && is_array($this->matchedrefs) === true && $this->conds != '') {
           // $matchedrefs = implode(', ', $this->matchedrefs);
            $this->CI->db->where_in('p.refno',$this->matchedrefs);           
        }
        
        $wArray = ($wArray)?implode(', ', $wArray):'';
        $conds = implode(', ', $this->conds);
        $debug = $debug . $wArray . $conds;
        return $debug;
    }

    private function sqlCountJoinsAndWhere()
    {
        $debug = '';
        $this->CI->db->from($this->model->fromTableStr());

        $joins = $this->model->joinCountArray() === null ? array() : $this->model->joinCountArray();
        foreach ($joins as $table => $on) {
            $joinTypeArray = explode('|', $table);
            $tableName = $joinTypeArray[0];
            $join = 'inner';
            if (count($joinTypeArray) > 1) {
                $join = $joinTypeArray[1];
            }
            $this->CI->db->join($tableName, $on, $join);
        }

        $customExpArray = is_null($this->model->appendToSelectStr()) ?
        array() :
        $this->model->appendToSelectStr();

        $f = $this->CI->input;

        if ($f->post('columns')) {
            foreach ($f->post('columns') as $c) {
                if ($c['search']['value'] !== '') {
                    $colName = $c['data']; 
                    //handle custom sql expressions/subselects
                    if (substr($colName, 0, 2) === '$.') {
                        $aliasKey = substr($colName, 2);
                        if (isset($customExpArray[$aliasKey]) === false) {
                            throw new Exception('Alias[' . $aliasKey . '] Could Not Be Found In appendToSelectStr() Array');
                        }

                        $colName = $customExpArray[$aliasKey];
                    }

                    $colvalue = $c['search']['value'];
                    if ($colName == 'added_date') {$colName = "date(added_date)";
                        $colvalue = date('Y-m-d', strtotime($colvalue));}
                    $debug .= 'col[' . $c['data'] . '] value[' . $c['search']['value'] . '] ' . PHP_EOL;
                    $this->CI->db->like($colName, $colvalue, 'after');

                }
            }
        } else {
            $colvalue = $c['search']['value'];
            $array = array('refno' => $colvalue, 'firstname' => $colvalue, 'lastname' => $colvalue, 'detail.mobile' => $colvalue, 'detail.email' => $colvalue, 'users.display_name' => $colvalue);
            $this->CI->db->like($array, '%a');

        }

        //append a static where clause to what the user has filtered, if the model tells us to do so
        $wArray = $this->model->whereClauseArray();
        if ($wArray['c.delete_status'] == 'N') {
            $array = array('refno' => $colvalue, 'firstname' => $colvalue, 'lastname' => $colvalue, 'detail.mobile' => $colvalue, 'detail.email' => $colvalue, 'users.display_name' => $colvalue);
            $this->CI->db->or_like($array, '%a');
        }
        if (is_null($wArray) === false && is_array($wArray) === true && count($wArray) > 0) {

            //echo "<pre>";print_r($wArray);exit;
            if (isset($wArray['p.price >=']) && $wArray['p.price >='] == '100000000' && $f->post('fmnprice') != '') {
                $wArray['p.price >='] = $f->post('fmnprice');
            }
            $this->CI->db->where($wArray);

            if ($this->CI->session->userdata('user_role') == '') {
                $this->CI->db->where(array('agents_id!=' => 30));
                $this->CI->db->where(array('agents_id!=' => 34));
            }
        }
        if (is_null($this->conds) === false && is_array($this->conds) === true && count($this->conds) > 0) {
            $this->CI->db->where($this->conds);
            if ($this->CI->session->userdata('user_role') == '') {
                $this->CI->db->where(array('agents_id!=' => 30));
                $this->CI->db->where(array('agents_id!=' => 34));
            }
        }
        $wArray = implode(', ', $wArray);
        $conds = implode(', ', $this->conds);
        $debug = $debug . $wArray . $conds;
        return $debug;
    }

}

interface DatatableModel
{

    /**
     * @ return
     *         Expressions / Columns to append to the select created by the Datatable library.
     *         Associative array where the key is the sql alias and the value is the sql expression
     */
    public function appendToSelectStr();

    /**
     * @return
     *      String table name to select from
     */
    public function fromTableStr();

    /**
     * @return
     *     Associative array of joins.  Return NULL or empty array  when not joining
     */
    public function joinArray();

    /**
     *
     *@return
     *     Static where clause to be appended to all search queries.  Return NULL or empty array
     * when not filtering by additional criteria
     */
    public function whereClauseArray();
}

// END Datatable Class
/* End of file Datatable.php */
