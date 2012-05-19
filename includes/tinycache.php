<?php
/**
 * WhispyForum
 * 
 * /includes/tinycache.php
*/

if ( !defined("WHISPYFORUM") )
	die("Direct opening.");

class cache
{
	/**
	 * TinyCache is a lightweight caching class for easy, HDD-based caches.
	 * 
	 * Due to how TinyCache works, it is DISCOURAGED to use this system to store ANY sort
	 * of information which is to be kept for a long time and is not available elsewhere.
	*/
	
	// $_depot stores the depot information.
	// _depot is always an array, having the following structure:
	// 		access count	number of access made to the depot
	// 		caches			array, containing a list of cache files
	// 			cache => file		where cache is the name of the cache and file is the filename
	//		create time		time() when the cache was created
	//		last xyz time	time() when the last xyz action was made
	// 			flush			last save of the depot to the disk
	// 			obtain			last read of depot from the disk
	// 			get				last read of a cache file
	// 			modify			last modification (set or delete) of a cache file
	private $_depot = array();
	
	// $_basedir stores the base working directory of the instance.
	private $_basedir = NULL;
	
	function __construct()
	{
		/**
		 * The constructor automatically executes when a new instance is invoked.
		*/
		$this->_basedir = getcwd()."/cache";
		
		// We load the depot of caches.
		$this->_load_depot();
		
		// Define a constant for get()
		@define('TINYCACHE_NO_KEY', "requested-key-not-present");
	}
	
	private function _load_depot()
	{
		/**
		 * This function loads the known cache depot description into the memory.
		 * The depot file is called "depot.wcf" and it is stored in the set basedir folder.
		 * 
		 * If the folder does not exist, we create the depot structure.
		*/
		
		if ( is_readable($this->_basedir. "/depot.wcf") )
		{
			// Load the depot contents.
			$this->_obtain();
		} elseif ( is_dir($this->_basedir) && is_writable($this->_basedir) && !file_exists($this->_basedir. "/depot.wcf") )
		{
			// Create the depot structure.
			
			// Create a basic $_depot array and fill it into the file.
			$depot_wcf = array(
				'access count'	=>	0,
				'create time'	=>	time(),
				'caches'	=>	array()
			);
			
			$this->_depot = $depot_wcf;
			$this->_flush();
			unset($depot_wcf);
		}
	}
	
	function get( $cache )
	{
		/**
		 * This function reads the depot and returns the contents of $cache if present.
		 * 
		 * If the cache is not present or expired, TINYCACHE_NO_KEY will be returned.
		*/
		
		$this->_obtain();
		
		if ( array_key_exists($cache, $this->_depot['caches']) )
		{
			// If the requested cache exists, we read it.
			
			if ( !is_readable($this->_basedir ."/". $this->_depot['caches'][$cache]) )
			{
				// If the file is unreadable, we remove the pointer from the depot.
				$this->delete($cache);
				$ret = TINYCACHE_NO_KEY;
			} else {
				$cachefile = fopen($this->_basedir ."/". $this->_depot['caches'][$cache], "r");
				$cachedata = fread($cachefile, filesize($this->_basedir ."/". $this->_depot['caches'][$cache]));
				fclose($cachefile);
				
				$content = unserialize($cachedata);
				
				if ( time() >= $content['expiry'] )
				{
					// If the cache expired, we delete the cache and return TINYCACHE_NO_KEY.
					$this->delete($cache);
					$ret = TINYCACHE_NO_KEY;
				} else {
					// If the cache is still valid, we return the content itself.
					$ret = $content['content'];
				}
			}
		} else {
			// Missing cache will result in TINYCACHE_NO_KEY being returned.
			$ret = TINYCACHE_NO_KEY;
		}
		$this->_depot['last get time'] = time();
		$this->_flush();
		
		// Empty the internal depot information to free memory space.
		$this->_clean();
		
		// Return the previously fetched return value.
		return ( isset($ret) ? $ret : NULL );
	}
	
	function is_key( $value )
	{
		/**
		 * This function checks whether the argument $value is a valid cache value or not.
		 * Returns TRUE if value is valid, FALSE if not.
		 * 
		 * Usage of
		 * 		if ( $cache->get("key") === TINYCACHE_NO_KEY )
		 * and
		 * 		if ( !$cache->is_key( $cache->get("key") ) )
		 * results in the exact same behavious (IF-block will run if the key doesn't exist.)
		*/
		
		return ( $value === TINYCACHE_NO_KEY ? FALSE : TRUE );
	}
	
	function set( $cache, $content, $expiry = 3600 )
	{
		/**
		 * This function stores $content into the $cache using the TinyCache library.
		 * The cache's lifespan can be set with $expire, it sets the expirity in seconds.
		*/
		
		// First, we fetch a name for our cache (dname stands for depot name).
		$this->_obtain();
		
		if ( array_key_exists($cache, $this->_depot['caches']) )
		{
			// If the named cache already exists, we read the name from the depot.
			$dname = $this->_depot['caches'][$cache];
		} else {
			// If the cache doesn't exist, create a new name for it.
			$dname = token();
		}
		
		$write = array(
			'expiry' =>	time() + $expiry,
			'content'	=>	$content
		);
		
		// Put the cache data onto the HDD.
		$fhandle = fopen($this->_basedir ."/". $dname, "w+");
		fwrite($fhandle, serialize($write));
		fclose($fhandle);
	
		// Update the cache information in the memory and flush to the disk.
		$this->_depot['caches'][$cache] = $dname;
		$this->_depot['last modify time'] = time();
		$this->_flush();
		
		// Empty the internal depot information.
		$this->_clean();
	}
	
	function delete( $cache )
	{	
		/**
		 * This function deletes the said $cache from the hard drive.
		*/
		
		$this->_obtain();
		
		// If the cache points to a valid cache file and it is writable, we delete it.
		if ( array_key_exists($cache, $this->_depot['caches']) )
		{
			if ( is_writable($this->_basedir ."/". $this->_depot['caches'][$cache]) )
			{
				unlink($this->_basedir ."/". $this->_depot['caches'][$cache]);
			}
			
			// We unset the key from the caches array then flush the depot info back to the disk.
			unset($this->_depot['caches'][$cache]);
			$this->_depot['last modify time'] = time();
			$this->_flush();
			$this->_clean();
		}
	}
	
	function run_maintenance()
	{
		/**
		 * This function runs a maintenance skimming on the cache folder.
		 * This skim run deletes every file which is no longer related to a
		 * cache entry and every cache entry linking to a missing file.
		 * 
		 * After it, every cache file is read and those expired will be deleted.
		*/
		
		// First obtain the concurrent list of caches from the disk.
		$this->_obtain();
		
		// Go through the cache list one-by-one, 
		foreach ($this->_depot['caches'] as $v)
		{
			// The cache::get(); function will attempt to read the cache file from the disk.
			// This call removes every expired entry from the depot and
			// every cache linking to nonexistant files on the disk.
			$this->get( array_search($v, $this->_depot['caches']) );
		}
		
		// To clean the files which no longer point to depot entries,
		// we first map the cache folder and check if files point to any entry.
		foreach ( scandir($this->_basedir) as $file )
		{
			// Scandir of the basedir returns us an array of every file in the basedir.
			if ( !in_array($file, array(".", "..", ".svn", "depot.wcf") ) )
			{
				// After omitting some files, we check whether the name is in the cache array.
				if ( !array_search( $file, $this->_depot['caches'] ) )
				{
					// If the file is no longer linked to a cache entry, we delete it.
					@unlink($this->_basedir."/".$file);
				}
			}
		}
	}
	
	private function _obtain()
	{
		/**
		 * This function updates the memory-stored depot information from the hard disk.
		*/
		
		// Because file sizes are cached, multiple obtains in a single execution
		// (which usually happens) would return an errorneous string to unserialize.
		// Thus, we forcedly clear the status cache.
		clearstatcache();
		
		$depotfile = fopen($this->_basedir. "/depot.wcf", "rb");
		$data = fread($depotfile, filesize($this->_basedir. "/depot.wcf"));
		$this->_depot = unserialize($data);
		unset($data);
		fclose($depotfile);
		
		$this->_depot['last obtain time'] = time();
		$this->_depot['access count'] = $this->_depot['access count'] + 1;
	}
	
	private function _clean()
	{
		/**
		 * This function empties the internal depot stack stored in memory.
		*/
		
		// ** Cleaning is not needed as _obtain() automatically overwrites the current data. **
		//$this->_depot = array();
	}
	
	private function _flush()
	{
		/**
		 * The flush function exports the memory-based depot info to the hard disk,
		 * into the depot.wcf index file.
		 * 
		 * The command will not run if the internal stack is empty.
		*/
		
		if ( count($this->_depot) > 0 )
		{
			$this->_depot['last flush time'] = time();
			$this->_depot['access count'] = $this->_depot['access count'] + 1;
			
			$depotfile = fopen($this->_basedir. "/depot.wcf", "w");
			fwrite($depotfile, serialize($this->_depot));
			fclose($depotfile);
		}
	}
}
?>