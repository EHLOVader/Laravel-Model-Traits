<?php namespace Humweb\Traits;
/**
 * 
 * @package     Humweb\Traits\Versions
 * @license 	MIT License <http://www.opensource.org/licenses/mit>
 * 
 */

use DB;

trait Versions {

	/**
	 * DB table that holds the versions
	 * @var string
	 */
	protected $versionsTable      = "versions";

	/**
	 * Versions Primary key
	 * @var string
	 */
	protected $versionsPk         = "id";

	/**
	 * Field from the model to use as the versions name
	 * @var string
	 */
	protected $versionsNameColumn = "title";

	/**
	* Save the current models state to the database
	*
	* @param string $name Optional name or short description
	* @return bool|integer
	*/
	public function addVersion($name='')
	{
		if (isset($this->id))
		{

			$data      = json_encode($this->original);
			$timestamp = date('Y-m-d H:i:s');

			return $this->getVersionQuery()->insertGetId(array(
				'data'         => $data,
				'object_table' => get_class($this),
				'object_id'    => $this->id,
				'name'         => $this->getVersionName($name),
				'hash'         => md5($data),
				'created_at'   => $timestamp,
				'updated_at'   => $timestamp
			));
		}

		return false;
	}

	
	/**
	* Fetch version and init new object
	*
	* @param int $id
	* @return object
	*/
	public function getVersion($id)
	{
		$version = $this->getVersionQuery()
						->select('data', 'object_table')
						->where($this->versionsPk, $id)
						->first();

		$version->data = json_decode($version->data);

		return new $version->object_table($version->data);
	}


	/**
	* Fetch all versions of an object
	*
	* @return Collection
	*/
	public function getAllVersions()
	{
		return $this->getVersionQueryWhere()
					->orderBy('updated_at', 'desc')
					->get();
	}


	/**
	* Count versions of an object
	*
	* @return int
	*/
	public function countVersions($obj)
	{
		return $this->getVersionQueryWhere()->count();
	}

	
	/**
	* Get lastest version of an object
	*
	* @return object
	*/
	public function latestVersion()
	{
		return $this->getVersionQueryWhere()
					->orderBy('created_at', 'desc')
					->first();
	}


	/**
	* Delete a version of an object
	*
	* @param int $id versions id
	* @return bool
	*/
	public function removeVersion($id)
	{
		return $this->getVersionQuery()
					->where($this->versionsPk, $id)
					->delete();
	}


	/**
	* Delete all versions of an object
	*
	* @return bool
	*/
	public function removeAllVersions()
	{
		return $this->getVersionQueryWhere()->delete();
	}


	/**
	 * Get the versions name field
	 * @param  string $name 
	 * @return string       versions name field
	 */
	private function getVersionName($name='')
	{
		$col_name = $this->versionsNameColumn;

		if (empty($name) and isset($this->$col_name))
		{
			return $this->$col_name;
		}

		return $name;
	}


	/**
	 * Start DB version query
	 * @return object
	 */
	private function getVersionQuery()
	{
		return DB::table($this->versionsTable);
	}


	/**
	 * Query DB where
	 * @return object
	 */
	private function getVersionQueryWhere()
	{
		return $this->getVersionQuery()
					->where('object_id', '=', $this->id)
					->where('object_table', '=', get_class($this));
	}

}