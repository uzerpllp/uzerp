<?php

/**
*  @author uzERP LLP and Martyn Shiner <mshiner@uzerp.com>
*  @license GPLv3 or later
*  @copyright (c) 2000-2017 uzERP LLP (support@uzerp.com). All rights reserved.     Released under GPLv3 license; see LICENSE.
**/

class Report extends DataObject {

      protected $version='$Revision: 1.20 $';
      protected $defaultDisplayFields = array('description',
                                            'report_group',
                                            'tablename',
                                                                  'owner');

      protected $do;

      function __construct($tablename='reports') {
            parent::__construct($tablename);
            $this->idField='id';
            $this->identifierField='tablename';
            $this->orderby = 'description';
      }

      function createDataObject ($fields = [], &$idField='', $columns = []) {
            // we cannot use '*' for all datatypes in the coalesce function, so
            // preset the value before we start. This may need ammending depending
            // on the datatype
            // might be an idea for dave to check this out
            $coalesce=array();
            foreach($columns as $key => $value) {
                  switch($value->type) {
                        case 'text':
                        case 'varchar':
                        case 'bpchar':
                              $coalesce[$value->name]="'*'";
                              break;
                        case 'bool':
                              // not sure what bool would be :S
                              break;
                        case 'int4':
                        case 'int8':
                        case 'float4':
                        case 'float8':
                        case 'numeric':
                              $coalesce[$value->name]="0";
                              break;
                        case 'date':
                        case 'datetime':
                              $coalesce[$value->name]="now()";
                              break;
                        default:
                              $searchtypes=array('select'=>'select');
                  }
            }

            // check datatype here, looks like numeric coalese has to be 0, as you'd expect
            $this->do=new DataObject($this->tablename);
// Need to create a pseudo id field, used by the collection as the row key
// but do not want to display this field so need to set it as DO idField

            foreach ($fields as $key=>$field)
            {
                  if (isset($coalesce[$field]))
                  {
                        $fields[$key]="coalesce(".$field.",".$coalesce[$field].")";
                  }
            }

            $idField=implode('||', $fields);

            $this->do->idField=$idField;
            return $this->do;
      }

      function createSearch ($search_fields, $s_data=array(), $defaults=array()) {

            $search=new BaseSearch($defaults);

            $search->addSearchField('report_id', 'report_id', 'hidden', $this->{$this->idField}, 'hidden');

            foreach ($search_fields as $field=>$options) {

                  if ($options['search_type']=='multi_select') {
                        // ATTN: how would we set an array of values from a single field... comma delimited?
                        $default_value=array($options['search_default_value']);
                  } else {
                        $default_value=$options['search_default_value'];
                  }

                  // check / set the label
                  if (!empty($options['normal_field_label'])) {
                        $label = $options['normal_field_label'];
                  } else {
                        $label = $field;
                  }

                  $search->addSearchField($field, $label, $options['search_type'], $default_value);
                  if (substr((string) $options['search_type'],-6)=='select') {
                        $search->setOptions($field, $this->getSelectList($field));
                  }

            }

            $search->setSearchData($s_data, $errors);
            return $search;
      }

      private function getSelectList($field) {

            // get and sort initial list
            $list=$this->do->getDistinct($field);
            asort($list);

            // get the month names for a month field
            if($field=='month') {
                  foreach($list as $key=>$month) {
                        $list[$key]=month_to_string($month);
                  }
            }

            // add 'All' option to list
            if (is_numeric(current(array_keys($list)))) {
                  $output=array('0'=>'All');
            } else {
                  $output=array(''=>'All');
            }

            // merge and return list
            $output+=$list;
            return $output;

      }

      public static function getAggregateMethods($field) {

            $aggregatemethods=array();
            switch ($field) {
                  case 'text':
                  case 'varchar':
                  case 'date':
                  case 'datetime':
                  case 'bool':
                        $aggregatemethods=array('count'=>'count');
                        break;
                  case 'int4':
                  case 'int8':
                  case 'float4':
                  case 'float8':
                  case 'numeric':
                        $aggregatemethods=array('sum'=>'sum'
                                                            ,'avg'=>'average'
                                                            ,'count'=>'count');
                        break;
                  default:
                        $aggregatemethods=array('sum'=>'sum'
                                                            ,'avg'=>'average'
                                                            ,'count'=>'count');

            }

            $aggregatemethods=array_merge(array('dont_total'=>'Don\'t Total'),$aggregatemethods);

            return $aggregatemethods;

      }

      public static function getSearchType($field) {

            $searchtypes=array();

            switch ($field) {

                  case 'text':
                  case 'varchar':
                        $searchtypes=array(
                              'select'            => 'select',
                              'multi_select'      => 'multi-select',
                              'begins'            => 'begins with',
                              'contains'            => 'contains',
                              //'ends'                  => 'ends with', // not implemented
                              'is'                  => 'equals',
                              'null'                  => 'null/not-null'
                        );

                        break;

                  case 'bool':
                        $searchtypes=array(
                              'show'      => 'show',
                              'hide'      => 'hide'
                        );

                        break;

                  case 'int4':
                  case 'int8':
                  case 'float4':
                  case 'float8':
                  case 'numeric':
                        $searchtypes=array(
                              'select'            => 'select',
                              'multi_select'      => 'multi-select',
                              'equal'                  => 'equal',
                              'greater'            => 'greater',
                              'less'                  => 'less'
                        );

                        break;

                  case 'date':
                  case 'datetime':
                  case 'timestamp':
                        $searchtypes=array(
                              'before'            => 'before',
                              'beforeornull'      => 'beforeornull',
                              'after'                  => 'after',
                              'afterornull'      => 'afterornull',
                              'betweenfields'      => 'betweenfields',
                              'to'                  => 'to',
                              'between'            => 'between'
                        );

                        break;

                  default:
                        $searchtypes=array('select'=>'select');
            }
            return $searchtypes;
      }

      public static function getTables () {

            $db=DB::Instance();

            $tables=$db->getAll(
                  "SELECT viewname FROM pg_catalog.pg_views
                  WHERE schemaname = 'reports';"
            );

            $array = [];
            foreach ($tables as $key=>$table) {
                  $array['reports.' . $table['viewname']]=$table['viewname'];
            }

            asort($array);
            return $array;
      }
}
?>
