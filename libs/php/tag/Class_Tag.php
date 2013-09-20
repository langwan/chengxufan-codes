<?php

/**
 * author langwan@chengxufan.com
 * version: 2013.09.20.13.02
 *
 * add or change content tags
 *
 * example:
 * $tag = new Class_Tag();
 * $store = new My_Tag_Store();
 * $tag->setStore($store);
 * $documentId = 1;
 * $newTags = array('a', 'b', 'c');
 * $tag->replace($documentId, $newTags);
 * $newTags = array('b', 'c', 'd');
 * $tag->replace($documentId, $newTags);
 *
 * note:
 * the Class_Tag_Store is demo, so you need to implement new one.
 * method:
 * get - get document tags.
 * remove - remove document tags and update count.
 * add - add document tags and update count.
 * make - make a new tag.
 *
 */

class Class_Tag {

	private $store = null;

	public function setStore($store) {
		$this->store = $store;
	}
	
	public function replace($key, $new) {
		$old = $this->store->get($key);

		$remove = $add = array();
		
		if(count($old) == 0 && count($new) != 0) {
			$add = $new;
		} else if(count($new) == 0 && count($old) != 0) {
			$remove = $old;
		} else if(count($old) == 0 && count($new) == 0) {
			return false;
		} else {
			$remove = array_diff($old, $new);
			$add = array_diff($new, $old);
	
			if(count($remove) == 0 && count($add) == 0) {
				return false;
			}
		}
		print_r($remove);
		print_r($add);
		if(count($remove) != 0)
			$this->store->remove($key, $remove);
		if(count($add) != 0) {
			$this->store->make($add);
			$this->store->add($key, $add);
		}
	}

}

class Class_Tag_Store {

	public function remove($key, $tags = null) {

		$db = core()->instance('lib.mysql');
		$idstring = my()->ids($tags);

		if($tags === null)
			$tags = $this->get($key);
		
		$sql = sprintf("DELETE FROM tb_feed_tag WHERE `id`='%s' AND tag IN(%s)", $key, $idstring);
		$db->sql($sql)->execute();

		$sql = sprintf("UPDATE tb_tag SET `number`=`number`-1 WHERE `name` IN(%s)", $idstring);
		$db->sql($sql)->execute();
	}

	public function get($key) {
		$db = core()->instance('lib.mysql');
		$sql = sprintf("SELECT `tag` FROM tb_feed_tag WHERE `id`='%s'", $key);
		$db->sql($sql)->execute();
		$items = $db->getRows();
		$dbTags = array();
		foreach($items as $item) {
			$dbTags[] = $item['tag'];
		}
		return $dbTags;		
	}

	public function add($key, $tags) {

		$this->make($tags);
		$db = core()->instance('lib.mysql');
		$idstring = my()->ids($tags);

		$sql = "INSERT INTO tb_feed_tag VALUES";
		$comma = "";

		foreach($tags as $item) {
			$sql .= $comma;
			$sql .= "(".sprintf("'%s', '%s'", $key, $item).")";
			$comma = ",";
		}
		$db->sql($sql)->execute();

		$sql = sprintf("UPDATE tb_tag SET `number`=`number`+1 WHERE `name` IN(%s)", $idstring);
		$db->sql($sql)->execute();

	}

	public function make($tags) {
		$db = core()->instance('lib.mysql');
		$idstring = my()->ids($tags);
		$sql = sprintf("SELECT `name` FROM tb_tag WHERE `name` IN(%s)", $idstring);
		$db->sql($sql)->execute();
		$items = $db->getRows();
		$dbTags = array();
		foreach($items as $item) {
			$dbTags[$item['name']] = true;
		}
		$ret = array();
		foreach($tags as $item) {
			if(!isset($dbTags[$item])) {
				$ret[] = $item;
			}
		}
		if(count($ret) != 0) {
			$sql = "INSERT INTO tb_tag VALUES ";
			$comma = "";
			foreach($ret as $item) {
				$sql .= $comma;
				$sql .= "(".sprintf("'%s', 0", $item).")";
				$comma = ",";
			}
			
			$db->sql($sql)->execute();
		}
	}	
}