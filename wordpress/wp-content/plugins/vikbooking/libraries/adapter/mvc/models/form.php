<?php
/** 
 * @package   	VikWP - Libraries
 * @subpackage 	adapter.mvc
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.mvc.model');
JLoader::import('adapter.form.form');

/**
 * The form model class used by the MVC framework.
 * A form model can be used by a controller or a view to handle 
 * a FORM entity of the plugin.
 *
 * The model can be invoked when the value contained 
 * in $_REQUEST['task'] is equals to 'ComponentModel' + $_REQUEST['task'].
 *
 * e.g. $_REQUEST['task'] = 'groups.save' -> ComponentModelGroups
 *
 * @since 10.0
 */
abstract class JModelForm extends JModel
{
	/**
	 * Creates or updates the specified record.
	 *
	 * @param 	object 	 &$data  The record to insert.
	 *
	 * @return 	boolean  True if the record has been inserted/updated, otherwise false.
	 */
	public function save(&$data)
	{
		$dbo = JFactory::getDbo();

		$table  = $this->getTableName();
		$pk 	= $this->getPrimaryKey();

		if (isset($data->{$pk}) && $data->{$pk} > 0)
		{
			/**
			 * Lean on the status returned by the database because
			 * the plugins might use this return value to check whether
			 * the save process was successful.
			 *
			 * @since 10.1.30
			 */
			$res = $dbo->updateObject($table, $data, $pk);
		}
		else
		{
			$res = $dbo->insertObject($table, $data, $pk) && $data->{$pk} > 0;
		}

		return $res;
	}

	/**
	 * Deletes the specified records.
	 *
	 * @param 	mixed 	 $ids 	The PK value (or a list of values) of the record(s) to remove.
	 *
	 * @return 	boolean  True if at least a record has been removed, otherwise false.
	 */
	public function delete($ids)
	{
		if (!is_array($ids))
		{
			$ids = array($ids);
		}

		if (!count($ids))
		{
			return false;
		}

		$dbo = JFactory::getDbo();

		$table  = $this->getTableName();
		$pk 	= $this->getPrimaryKey();

		$implode = implode(',', array_map('intval', $ids));

		$q = "DELETE FROM `{$table}` WHERE `{$pk}` IN ({$implode})";

		$dbo->setQuery($q);
		$dbo->execute();

		return (bool) $dbo->getAffectedRows();
	}

	/**
	 * Retrieves the specified item.
	 *
	 * @param 	mixed 	 $pk 	  The primary key value or a list of keys.
	 *
	 * @return 	object 	 The item found if exists, otherwise null.
	 */
	public function getItem($pk)
	{
		if (empty($pk))
		{
			return null;
		}

		$dbo = JFactory::getDbo();

		$table = $this->getTableName();

		// if the primary key is a value, create an associative array
		// to support multi-columns query
		if (is_scalar($pk))
		{
			$pk = array($this->getPrimaryKey() => $pk);
		}

		$where = array();
		// build WHERE statement
		foreach ($pk as $k => $v)
		{
			$where[] = $dbo->qn($k) . ' = ' . $dbo->q($v);
		}

		$where = implode(" AND ", $where);

		$q = "SELECT * FROM `{$table}` WHERE {$where}";

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			return $dbo->loadObject();
		}

		return null;
	}

	/**
	 * Obtains the JForm object related to the model view.
	 *
	 * @return 	JForm 	The form object.
	 */
	public function getForm()
	{
		$comp   = $this->getComponentName();
		$name   = $this->getModelName();
		$client = $this->getClientFolder();

		$id   = serialize(array($comp, $name, $client));
		$path = implode(DIRECTORY_SEPARATOR, array(WP_PLUGIN_DIR, $comp, $client, 'views', $name, 'tmpl', 'default.xml'));

		try
		{
			$form = JForm::getInstance($id, $path);
		}
		catch (Exception $e)
		{
			$form = null;
		}

		return $form;
	}

	/**
	 * This method should be used to pre-load an item considering
	 * the data set in the request.
	 * 
	 * For example, if the request owns an ID, this method may try 
	 * to retrieve the item from the database.
	 * Otherwise it may return an empty object.
	 *
	 * @return 	array|object  The object found.
	 */
	public function loadFormData()
	{
		return array();
	}

	/**
	 * This method should be used to retrieve the posted data
	 * after the form submission.
	 *
	 * @return 	array|object  The data object.
	 */
	public function getFormData()
	{
		return array();
	}
}
