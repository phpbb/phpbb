<?php
/***************************************************************************
 *                                 mssql.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/
if(!defined("SQL_LAYER")){

define("SQL_LAYER","mssql");

class sql_db {

   var $db_connect_id;
   var $query_result;
   var $row;

   //
   // Constructor
   //
   function sql_db($sqlserver, $sqluser, $sqlpassword, $database, $persistency=false){

      $this->persistency = $persistency;
      $this->user = $sqluser;
      $this->password = $sqlpassword;
      $this->host = $sqlserver;
      $this->dbname = $database;

      if($this->persistency){
         $this->db_connect_id = @mssql_pconnect($this->server,$this->user,$this->password);
      } else {
         $this->db_connect_id = @mssql_connect($this->server,$this->user,$this->password);
      }
      if($this->db_connect_id){
         if($this->dbname != ""){
            $dbselect = @mssql_select_db($this->dbname);
            if(!$dbselect){
               @mssql_close($this->db_connect_id);
               $this->db_connect_id = $dbselect;
            }
         }
      }
      return $this->db_connect_id;
   }
   //
   // Other base methods
   //
   function sql_setdb($database){
      $this->dbname = $database;
      $dbselect = @mssql_select_db($this->dbname);
      if(!$dbselect){
         sql_close();
         $this->db_connect_id = $dbselect;
      }
      return $this->db_connect_id;
   }
   function sql_close(){
      if($this->db_connect_id){
         if($this->query_result){
            @mssql_free_result($this->query_result);
         }
         $result = @mssql_close($this->db_connect_id);
         return $result;
      } else {
         return false;
      }
   }


   //
   // Query method
   //
   function sql_query($query=""){
      // Remove any pre-existing queries
      unset($this->query_result);
      unset($this->row);
      if($query != ""){
         // Does query contain any LIMIT code?
         // If so pull out relevant start and num_results
         // This isn't terribly easy with MSSQL, the best way is
         // to use a temporary table.
         if(eregi("LIMIT ",$query){
            eregi("LIMIT ([0-9]+)[, ]+([0-9]+)", $query, $limits);
            $row_offset = $limits[1];
            if($limits[2])
               $num_rows = $limits[2];
         } else {
            $this->query_result = @mssql_query($query, $this->db_connect_id);
         }
         return $this->query_result;
      } else {
         return 0;
      }
   }
   //
   // Other query methods
   //
   function sql_numrows(){
      if($this->query_result){
         $result = @mssql_num_rows($this->query_result);
         return $result;
      } else {
         return false;
      }
   }
   function sql_numfields(){
      if($this->query_result){
         $result = @mssql_num_fields($this->query_result);
         return $result;
      } else {
         return false;
      }
   }
   function sql_fieldname($offset){
      if($this->query_result){
         $result = @mssql_field_name($this->query_result, $offset);
         return $result;
      } else {
         return false;
      }
   }
   function sql_fieldtype($offset){
      if($this->query_result){
         $result = @mssql_field_type($this->query_result, $offset);
         return $result;
      } else {
         return false;
      }
   }
   function sql_fetchrow(){
      if($this->query_result){
         $this->row = @mssql_fetch_array($this->query_result);
         return $this->row;
      } else {
         return false;
      }
   }
   function sql_fetchrowset(){
      if($this->query_result){
         empty($this->rowset);
         while($this->rowset = @mssql_fetch_array($this->query_result)){
            $result[] = $this->rowset;
         }
         return $result;
      } else {
         return false;
      }
   }
   function sql_fetchfield($field, $row=-1) {
      if($this->query_result){
         if($row != -1){
            $result=@mssql_result($this->query_result, $row, $field);
         } else {
            if(empty($this->row))
               $this->row = @mssql_fetch_array($this->query_result);
            $result = $this->row[$field];
         }
         return $result;
      } else {
         return false;
      }
   }
   function sql_rowseek($offset){
      if($this->query_result){
         $result = @mssql_data_seek($this->query_result, $rownum);
         return $result;
      } else {
         return false;
      }
   }
   function sql_nextid(){
      if($this->query_result){
         return $result;
      } else {
         return false;
      }
   }
   function sql_freeresult(){
      if($this->query_result){
         @mssql_free_result($this->query_result);
         return;
      } else {
         return false;
      }
   }
   function sql_error(){
      $result[message] = @mssql_get_last_message();
      return $result;
   }

} // class sql_db

} // if ... define

?>
