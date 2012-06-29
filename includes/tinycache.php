<?php
/**
 * WhispyForum
 * 
 * /includes/tinycache.php
*/

if ( !defined("WHISPYFORUM") )
	die("Direct opening.");

/**
 * TinyCache is a lightweight caching class for easy, HDD-based caches.
 * 
 * Due to how TinyCache works, it is DISCOURAGED to use this system to store ANY sort
 * of information which is to be kept for a long time and is not available elsewhere.
*/

// The base directory where the cache files are kept.
define('TC_BASEDIR', ini_get("upload_tmp_dir"));

// Define the maximum filesize a chunk can reach (in bytes, the default is 4 096, which equals 4 KiB).
define('TC_MAX_CHUNK_SIZE', 4096);

// Define the 'not found' constant used by cache_get().
define('TC_NO_KEY', -1);

function cache_set( $name, $content, $expiry = 3600 )
{
	/**
	 * The set() function takes $content and saves it under the name $name.
	 * $expiry is the number of seconds before the cache expires (default value is 3600, which equals 1 hour).
	*/
	
	// We first attempt to remove the old cache.
	cache_delete($name);
	
	// Clear the filesize caches.
	clearstatcache();
	
	// Determine the file size.
	$filesize = strlen($content);
	
	// Create the file header
	$header = "\x14" . $name . "\x7F" . $filesize . "\x7F" . (time() + $expiry) . "\x14" . "\x02";
	
	/**
	 * Header explanation:
	 * 	\x14 - a paragraph sign character marks the beginning of header
	 * 	name - name of the cache save
	 * 	\x7F - delimiter of header information
	 * 	filesize - size of the whole data
	 * 	\x7F - another delimiter
	 * 	time() + expiry - the epoch timestemp the cache is "best before"
	 * 	\x14 - paragraph sign denoting the end of header
	 * 	\x02 - control bit denoting the start of real data
	*/
	
	// The filename of the cache is the md5 of the original name because
	// this way, we can prevent the need of index files.
	// When a cache is read back, the name identificates it in the system,
	// and because the md5() is the same, we can use the md5 hash to identificate on the disk.
	$filename = md5($name);
	
	if ( $filesize + strlen($header) > TC_MAX_CHUNK_SIZE )
	{
		// If the content would be bigger than the maximum file size, we need to chunk the file up.
		
		// The first chunk needs to contain the header information,
		// so the first chunk needs to be TC_MAX_CHUNK_SIZE - size of header big.
		$first = str_split($content, TC_MAX_CHUNK_SIZE - strlen($header));
		$first = $first[0];
		
		// Now we cut the first (TC_MAX_CHUNK_SIZE - size of header) bytes
		// of content from the beginning.
		$content = substr($content, TC_MAX_CHUNK_SIZE - strlen($header));
		
		// After the header and the first chunk is prepared, the rest of the chunks
		// can have the defined size. We split the content again.
		$buffer = str_split($content, TC_MAX_CHUNK_SIZE);
		$buffer[-1] = $header.$first;
		
		for ( $i = 1; $i <= count($buffer); $i++ )
		{
			// Write each chunk to the disk.
			
			// We need to subtract 2 from $i at writing because the first
			// chunk-to-write has the index of -1, while the first value of $i is 1.
			
			$handle = fopen( TC_BASEDIR ."/". $filename .".". $i, "w+b");
			fwrite($handle, $buffer[$i - 2]);
			fclose($handle);
		}
	} elseif ( $filesize + strlen($header) <= TC_MAX_CHUNK_SIZE )
	{
		// If the data (and header) is smaller than the maximum size allowed, we write a single file.
		$handle = fopen( TC_BASEDIR ."/". $filename .".1", "w+b");
		fwrite($handle, $header.$content);
		fclose($handle);
	}
}

function cache_get( $name )
{
	/**
	 * The get() function reads the cache named $name and returns it.
	*/
	
	// Clear the filesize caches.
	clearstatcache();
	
	// Get the identifier name from the name of the cache.
	$filename = md5($name);
	
	// Check whether the file exists, and if yes, open the file.
	if ( is_readable(TC_BASEDIR ."/". $filename .".1") )
	{
		$first_handle = fopen( TC_BASEDIR ."/". $filename .".1", "rb");
		$first = fread($first_handle, filesize( TC_BASEDIR ."/". $filename .".1"));
		fclose($first_handle);
		
		// $first contains the content of the first (.1) file.
		
		// Using the delimiter-based explode of string, we chunk the content
		// and retrieve the header informations: name, size and expiry.
		$exploded = explode("\x02", $first);
		$header = explode("\x14", $exploded[0]);
		$header = explode("\x7F", $header[1]);
		
		// Terminate retrieving if the loaded cache file is different than the one we want.
		// (This is HIGHLY unlikely to happen, but we must make sure.)
		if ( $header[0] != $name )
			return TC_NO_KEY;
		
		// Also terminate retrieving if the cache has already expired.
		if ( time() >= $header[2] )
		{
			// Running into an expired cache automatically triggers its removal.
			cache_delete($name);
			return TC_NO_KEY;
		}
		
		if ( $header[1] <= TC_MAX_CHUNK_SIZE )
		{
			// If the size of the data in the file (excluding header) is smaller than
			// the maximum chunk size, it means that this is a single-file cache.
			
			// We return the already-loaded (whole) data from the file.
			// (By starting reading it from the end of the header
			// and the header delimiter character (0 + strlen + 1).)
			return substr($first, strlen($exploded[0]) + 1);
		} elseif ( $header[1] > TC_NO_KEY )
		{
			// If the size is bigger, it means that the cache has been chunked.
			
			// An internal buffer is created to store the data.
			// (The first loaded content is appended automatically.)
			$buffer = substr($first, strlen($exploded[0]) + 1);
			
			// The number of chunks is the rounded-up value of the division.
			// Example: if the division if 12.5, it means that we have 12 full and 1 half-sized files: total 13.
			$chunk_count = ceil($header[1] / TC_MAX_CHUNK_SIZE);
			
			for ( $i = 2; $i <= $chunk_count; $i++ )
			{
				// Read every chunk and append the read data to the buffer.
				$handle = fopen( TC_BASEDIR ."/". $filename .".". $i, "rb");
				$buffer .= fread($handle, filesize( TC_BASEDIR ."/". $filename .".". $i ));
				fclose($handle);
			}
			
			// Return the full readed cache entry.
			return $buffer;
		}
	} else {
		// If the file is unreadable, we return signifying that the cache does not exist.
		return TINYCACHE_NO_KEY;
	}
}

function cache_delete( $name )
{
	/**
	 * Delete the cache named $name.
	*/
	
	// Clear the filesize caches.
	clearstatcache();
	
	// Get the identifier name from the name of the cache.
	$filename = md5($name);
	
	// Delete the files.
	array_map( "unlink", glob(TC_BASEDIR ."/". $filename .".*") );
}
?>