<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\db\extractor;

use phpbb\db\extractor\exception\extractor_not_initialized_exception;

class postgres_extractor extends base_extractor
{
	/**
	* {@inheritdoc}
	*/
	public function write_start($table_prefix)
	{
		if (!$this->is_initialized)
		{
			throw new extractor_not_initialized_exception();
		}

		$sql_data = "--\n";
		$sql_data .= "-- phpBB Backup Script\n";
		$sql_data .= "-- Dump of tables for $table_prefix\n";
		$sql_data .= "-- DATE : " . gmdate("d-m-Y H:i:s", $this->time) . " GMT\n";
		$sql_data .= "--\n";
		$sql_data .= "BEGIN TRANSACTION;\n";
		$this->flush($sql_data);
	}

	/**
	* {@inheritdoc}
	*/
	public function write_table($table_name)
	{
		static $domains_created = array();

		if (!$this->is_initialized)
		{
			throw new extractor_not_initialized_exception();
		}

		$sql = "SELECT a.domain_name, a.data_type, a.character_maximum_length, a.domain_default
			FROM INFORMATION_SCHEMA.domains a, INFORMATION_SCHEMA.column_domain_usage b
			WHERE a.domain_name = b.domain_name
				AND b.table_name = '{$table_name}'";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (empty($domains_created[$row['domain_name']]))
			{
				$domains_created[$row['domain_name']] = true;
				//$sql_data = "DROP DOMAIN {$row['domain_name']};\n";
				$sql_data = "CREATE DOMAIN {$row['domain_name']} as {$row['data_type']}";
				if (!empty($row['character_maximum_length']))
				{
					$sql_data .= '(' . $row['character_maximum_length'] . ')';
				}
				$sql_data .= ' NOT NULL';
				if (!empty($row['domain_default']))
				{
					$sql_data .= ' DEFAULT ' . $row['domain_default'];
				}
				$this->flush($sql_data . ";\n");
			}
		}
		$this->db->sql_freeresult($result);

		$sql_data = '-- Table: ' . $table_name . "\n";
		$sql_data .= "DROP TABLE $table_name;\n";
		// PGSQL does not "tightly" bind sequences and tables, we must guess...
		$sql = "SELECT relname
			FROM pg_class
			WHERE relkind = 'S'
				AND relname = '{$table_name}_seq'";
		$result = $this->db->sql_query($sql);
		// We don't even care about storing the results. We already know the answer if we get rows back.
		if ($this->db->sql_fetchrow($result))
		{
			$sql_data .= "DROP SEQUENCE IF EXISTS {$table_name}_seq;\n";
			$sql_data .= "CREATE SEQUENCE {$table_name}_seq;\n";
		}
		$this->db->sql_freeresult($result);

		$field_query = "SELECT a.attnum, a.attname as field, t.typname as type, a.attlen as length, a.atttypmod as lengthvar, a.attnotnull as notnull
			FROM pg_class c, pg_attribute a, pg_type t
			WHERE c.relname = '" . $this->db->sql_escape($table_name) . "'
				AND a.attnum > 0
				AND a.attrelid = c.oid
				AND a.atttypid = t.oid
			ORDER BY a.attnum";
		$result = $this->db->sql_query($field_query);

		$sql_data .= "CREATE TABLE $table_name(\n";
		$lines = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			// Get the data from the table
			$sql_get_default = "SELECT pg_get_expr(d.adbin, d.adrelid) as rowdefault
				FROM pg_attrdef d, pg_class c
				WHERE (c.relname = '" . $this->db->sql_escape($table_name) . "')
					AND (c.oid = d.adrelid)
					AND d.adnum = " . $row['attnum'];
			$def_res = $this->db->sql_query($sql_get_default);
			$def_row = $this->db->sql_fetchrow($def_res);
			$this->db->sql_freeresult($def_res);

			if (empty($def_row))
			{
				unset($row['rowdefault']);
			}
			else
			{
				$row['rowdefault'] = $def_row['rowdefault'];
			}

			if ($row['type'] == 'bpchar')
			{
				// Internally stored as bpchar, but isn't accepted in a CREATE TABLE statement.
				$row['type'] = 'char';
			}

			$line = '  ' . $row['field'] . ' ' . $row['type'];

			if (strpos($row['type'], 'char') !== false)
			{
				if ($row['lengthvar'] > 0)
				{
					$line .= '(' . ($row['lengthvar'] - 4) . ')';
				}
			}

			if (strpos($row['type'], 'numeric') !== false)
			{
				$line .= '(';
				$line .= sprintf("%s,%s", (($row['lengthvar'] >> 16) & 0xffff), (($row['lengthvar'] - 4) & 0xffff));
				$line .= ')';
			}

			if (isset($row['rowdefault']))
			{
				$line .= ' DEFAULT ' . $row['rowdefault'];
			}

			if ($row['notnull'] == 't')
			{
				$line .= ' NOT NULL';
			}

			$lines[] = $line;
		}
		$this->db->sql_freeresult($result);

		// Get the listing of primary keys.
		$sql_pri_keys = "SELECT ic.relname as index_name, bc.relname as tab_name, ta.attname as column_name, i.indisunique as unique_key, i.indisprimary as primary_key
			FROM pg_class bc, pg_class ic, pg_index i, pg_attribute ta, pg_attribute ia
			WHERE (bc.oid = i.indrelid)
				AND (ic.oid = i.indexrelid)
				AND (ia.attrelid = i.indexrelid)
				AND	(ta.attrelid = bc.oid)
				AND (bc.relname = '" . $this->db->sql_escape($table_name) . "')
				AND (ta.attrelid = i.indrelid)
				AND (ta.attnum = i.indkey[ia.attnum-1])
			ORDER BY index_name, tab_name, column_name";

		$result = $this->db->sql_query($sql_pri_keys);

		$index_create = $index_rows = $primary_key = array();

		// We do this in two steps. It makes placing the comma easier
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['primary_key'] == 't')
			{
				$primary_key[] = $row['column_name'];
				$primary_key_name = $row['index_name'];
			}
			else
			{
				// We have to store this all this info because it is possible to have a multi-column key...
				// we can loop through it again and build the statement
				$index_rows[$row['index_name']]['table'] = $table_name;
				$index_rows[$row['index_name']]['unique'] = ($row['unique_key'] == 't') ? true : false;
				$index_rows[$row['index_name']]['column_names'][] = $row['column_name'];
			}
		}
		$this->db->sql_freeresult($result);

		if (!empty($index_rows))
		{
			foreach ($index_rows as $idx_name => $props)
			{
				$index_create[] = 'CREATE ' . ($props['unique'] ? 'UNIQUE ' : '') . "INDEX $idx_name ON $table_name (" . implode(', ', $props['column_names']) . ");";
			}
		}

		if (!empty($primary_key))
		{
			$lines[] = "  CONSTRAINT $primary_key_name PRIMARY KEY (" . implode(', ', $primary_key) . ")";
		}

		// Generate constraint clauses for CHECK constraints
		$sql_checks = "SELECT pc.conname AS index_name, pg_get_expr(pc.conbin, pc.conrelid) AS constraint_expr
			FROM pg_constraint pc, pg_class bc
			WHERE pc.conrelid = bc.oid
				AND bc.relname = '" . $this->db->sql_escape($table_name) . "'
				AND NOT EXISTS (
					SELECT *
					FROM pg_constraint AS c, pg_inherits AS i
						WHERE i.inhrelid = pc.conrelid
							AND c.conname = pc.conname
							AND pg_get_constraintdef(c.oid) = pg_get_constraintdef(pc.oid)
							AND c.conrelid = i.inhparent
				)";
		$result = $this->db->sql_query($sql_checks);

		// Add the constraints to the sql file.
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!empty($row['constraint_expr']))
			{
				$lines[] = '  CONSTRAINT ' . $row['index_name'] . ' CHECK ' . $row['constraint_expr'];
			}
		}
		$this->db->sql_freeresult($result);

		$sql_data .= implode(", \n", $lines);
		$sql_data .= "\n);\n";

		if (!empty($index_create))
		{
			$sql_data .= implode("\n", $index_create) . "\n\n";
		}
		$this->flush($sql_data);
	}

	/**
	* {@inheritdoc}
	*/
	public function write_data($table_name)
	{
		if (!$this->is_initialized)
		{
			throw new extractor_not_initialized_exception();
		}

		// Grab all of the data from current table.
		$sql = "SELECT *
			FROM $table_name";
		$result = $this->db->sql_query($sql);

		$i_num_fields = pg_num_fields($result);
		$seq = '';

		for ($i = 0; $i < $i_num_fields; $i++)
		{
			$ary_type[] = pg_field_type($result, $i);
			$ary_name[] = pg_field_name($result, $i);

			$sql = "SELECT pg_get_expr(d.adbin, d.adrelid) as rowdefault
				FROM pg_attrdef d, pg_class c
				WHERE (c.relname = '{$table_name}')
					AND (c.oid = d.adrelid)
					AND d.adnum = " . strval($i + 1);
			$result2 = $this->db->sql_query($sql);
			if ($row = $this->db->sql_fetchrow($result2))
			{
				// Determine if we must reset the sequences
				if (strpos($row['rowdefault'], "nextval('") === 0)
				{
					$seq .= "SELECT SETVAL('{$table_name}_seq',(select case when max({$ary_name[$i]})>0 then max({$ary_name[$i]})+1 else 1 end FROM {$table_name}));\n";
				}
			}
		}

		$this->flush("COPY $table_name (" . implode(', ', $ary_name) . ') FROM stdin;' . "\n");
		while ($row = $this->db->sql_fetchrow($result))
		{
			$schema_vals = array();

			// Build the SQL statement to recreate the data.
			for ($i = 0; $i < $i_num_fields; $i++)
			{
				$str_val = $row[$ary_name[$i]];

				if (preg_match('#char|text|bool|bytea#i', $ary_type[$i]))
				{
					$str_val = str_replace(array("\n", "\t", "\r", "\b", "\f", "\v"), array('\n', '\t', '\r', '\b', '\f', '\v'), addslashes($str_val));
					$str_empty = '';
				}
				else
				{
					$str_empty = '\N';
				}

				if (empty($str_val) && $str_val !== '0')
				{
					$str_val = $str_empty;
				}

				$schema_vals[] = $str_val;
			}

			// Take the ordered fields and their associated data and build it
			// into a valid sql statement to recreate that field in the data.
			$this->flush(implode("\t", $schema_vals) . "\n");
		}
		$this->db->sql_freeresult($result);
		$this->flush("\\.\n");

		// Write out the sequence statements
		$this->flush($seq);
	}

	/**
	* Writes closing line(s) to database backup
	*
	* @return null
	* @throws extractor_not_initialized_exception when calling this function before init_extractor()
	*/
	public function write_end()
	{
		if (!$this->is_initialized)
		{
			throw new extractor_not_initialized_exception();
		}

		$this->flush("COMMIT;\n");
		parent::write_end();
	}
}
