<?php

class DBP {
	
	public static $truncate_text_longer = 50;

	public static $records_per_page = 10;
	
	static function select($column, $table, $filter = null, $order = null, $limit = null, $offset = null) {
		switch(DB::getConn()->getDatabaseServer()) {
			case 'mssql':
				if($filter) $filter = sprintf(" WHERE (%s)\n", $filter);
				if(empty($order)) $order = '"ID" ASC';
				if($limit || $offset) {
					return sprintf('WITH results AS (
						    SELECT 
						        %s,
						        rowNo_hide = ROW_NUMBER() OVER( ORDER BY %s )
						    FROM "%s"
							%s
						) 
						SELECT * 
						FROM results
						WHERE rowNo_hide between %d and %d
						', $column, $order, $table, $filter, $offset, $offset + $limit);
				} else {
					return sprintf('SELECT %s FROM "%s"%s', $column, $table, $filter);
				}
			default:
				if($filter) $filter = sprintf(" WHERE (%s)", $filter);
				if($order) $order = sprintf(" ORDER BY %s", $order);
				if($limit) $limit = isset($offset) ? sprintf(" LIMIT %d OFFSET %d", $limit, $offset) : sprintf(" LIMIT %d", $limit);
				return sprintf('SELECT %s FROM "%s"%s%s%s', $column, $table, $filter, $order, $limit);
		}
	}
}