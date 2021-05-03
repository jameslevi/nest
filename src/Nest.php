<?php

namespace Graphite\Component\Nest;

use Graphite\Component\Objectify\Objectify;

class Nest
{
    /**
     * Current nest version.
     * 
     * @var string
     */
    private static $version = "v1.0.0";

    /**
     * Store loaded cache objects.
     * 
     * @var array
     */
    private static $repository = array();

    /**
     * Store default storage cache path.
     * 
     * @var string
     */
    private static $default_path;

    /**
     * Store hash algorithm to use.
     * 
     * @var string
     */
    private static $hash_algo = 'md5';

    /**
     * Cache database name.
     * 
     * @var string
     */
    private $name;

    /**
     * Cache database hash key.
     * 
     * @var string
     */
    private $hash;

    /**
     * Cache storage path.
     * 
     * @var string
     */
    private $path;

    /**
     * Instance hash algorithm.
     * 
     * @var string
     */
    private $algo;

    /**
     * Stored cache data.
     * 
     * @var array
     */
    private $data;

    /**
     * Determine if cache data is changed.
     * 
     * @var bool
     */
    private $changed = false;

    /**
     * Determine the number of data changed.
     * 
     * @var int
     */
    private $changes = 0;

    /**
     * Construct a new nest object.
     * 
     * @param   string $name
     * @param   string $path
     * @return  void
     */
    public function __construct(string $name, string $path = null, string $hash_algo = null)
    {
        $this->data = new Objectify(array());
        $this->name = $name;
        $this->algo = $hash_algo;
        $this->hash = self::hash($name, $hash_algo);
        $this->path = $path ?? self::$default_path;
        $this->load();
    }

    /**
     * Convert string into hash.
     * 
     * @param   string $string
     * @param   string $algo
     * @return  string
     */
    private static function hash(string $string, string $algo = null)
    {
        return hash($algo ?? self::$hash_algo, $string);
    }

    /**
     * Load the stored cache file from the storage.
     * 
     * @return  void
     */
    private function load()
    {
        $path = $this->path . "/" . $this->getHash() . ".php";
        $data = array();

        if(!self::exists($this->name))
        {
            if(file_exists($path) && is_readable($path))
            {
                $data = require $path;

                self::$repository[$this->hash] = $this;
            }
        }
        else
        {
            $data = self::$repository[$this->hash]->toArray();
        }

        foreach($data as $key => $value)
        {
            $this->data->add($key, $value);
        }
    }

    /**
     * Write or overwrite cache file.
     * 
     * @return  $this
     */
    public function write()
    {
        if($this->changed && $this->changes > 0)
        {
            $path = $this->path . "/" . $this->getHash() . ".php";
            
            if(file_exists($path) && is_writable($path))
            {
                unlink($path);
            }

            $file = fopen($path, "w");
            fwrite($file, "<?php return " . var_export($this->data->toArray(), true) . ";");
            fclose($file);
            chmod($path, 0777);
        }

        return $this;
    }

    /**
     * Return formatted cache value.
     * 
     * @param   mixed $value
     * @return  mixed
     */
    private function format($value)
    {
        if(is_array($value))
        {
            $value = json_encode($value);
        }

        return $value;
    }

    /**
     * Add new data to cache database.
     * 
     * @param   string $key
     * @param   mixed $value
     * @return  $this
     */
    public function add(string $key, $value)
    {
        if(!$this->has($key))
        {
            $this->data->add(self::hash($key, $this->algo), $this->format($value));
            $this->changed = true;
            $this->changes++;
        }

        return $this;
    }

    /**
     * Return stored cache value.
     * 
     * @param   string $key
     * @return  mixed
     */
    public function get(string $key)
    {
        $data = $this->data->get(self::hash($key, $this->algo));

        if(is_string($data))
        {
            $json = json_decode($data, true);
            if(json_last_error() == JSON_ERROR_NONE)
            {
                $data = $json;
            }
        }

        return $data;
    }

    /**
     * Update the value of cache data.
     * 
     * @param   string $key
     * @param   mixed $value
     * @return  $this
     */
    public function set(string $key, $value)
    {
        if($this->has($key) && $this->get($key) !== $value)
        {
            $this->data->set(self::hash($key, $this->algo), $this->format($value));
            $this->changed = true;
            $this->changes++;
        }

        return $this;
    }

    /**
     * Remove data from the cache database.
     * 
     * @param   string $key
     * @return  $this
     */
    public function remove(string $key)
    {
        if($this->has($key))
        {
            $this->data->remove(self::hash($key, $this->algo));
            $this->changed = true;
            $this->changes++;
        }

        return $this;
    }

    /**
     * Determine if cache key exists.
     * 
     * @param   string $key
     * @return  bool
     */
    public function has(string $key)
    {
        return $this->data->has(self::hash($key, $this->algo));
    }

    /**
     * Return the name of the database.
     * 
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the name of the cache database.
     * 
     * @return  string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Return cache storage path.
     * 
     * @return  string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Return the number of data from the cache database.
     * 
     * @return  int
     */
    public function count()
    {
        return count($this->data->keys());
    }

    /**
     * Return cache array data.
     * 
     * @return  array
     */
    public function toArray()
    {
        return $this->data->toArray();
    }
    
    /**
     * Return cache data in json format.
     * 
     * @return  string
     */
    public function toJson()
    {
        return $this->data->toJson();
    }

    /**
     * Return true if cache data is changed.
     * 
     * @return  bool
     */
    public function isChanged()
    {
        return $this->changed;
    }

    /**
     * Return the number of changed data from cache.
     * 
     * @return  int
     */
    public function changed()
    {
        return $this->changes;
    }

    /**
     * Set default storage path for cache database.
     * 
     * @param   string $path
     * @return  bool
     */
    public static function setStoragePath(string $path)
    {
        if(file_exists($path))
        {
            self::$default_path = $path;
        
            return true;
        }

        return false;
    }

    /**
     * Return default storage path.
     * 
     * @return string
     */
    public static function getStoragePath()
    {
        return self::$default_path;
    }

    /**
     * Set the default hash algorithm to use.
     * 
     * @param   string $algo
     * @return  bool
     */
    public static function setHashAlgorithm(string $algo)
    {
        if(in_array($algo, hash_algos()))
        {
            self::$hash_algo = $algo;

            return true;
        }

        return false;
    }

    /**
     * Return the default hash algorithm to use.
     * 
     * @return  string 
     */
    public static function getHashAlgorithm()
    {
        return self::$hash_algo;
    }

    /**
     * Determine if cache database exists.
     * 
     * @param   string $key
     * @return  bool
     */
    public static function exists(string $key)
    {
        return array_key_exists(self::hash($key), self::$repository);
    }

    /**
     * Destroy or delete a cache database.
     * 
     * @param   string $key
     * @param   string $path
     * @param   string $algo
     * @return  bool
     */
    public static function destroy(string $key, string $path = null, string $algo = null)
    {
        $path = ($path ?? self::getStoragePath());

        if(!str_end_with($path, "/"))
        {
            $path .= "/";
        }

        $path .= self::hash($key, $algo ?? self::getHashAlgorithm()) . ".php";

        if(file_exists($path) && is_writable($path))
        {
            unlink($path);

            return true;
        }

        return false;
    }

    /**
     * Destroy all saved cache databases.
     * 
     * @param   string $path
     * @return  bool
     */
    public static function destroyAll(string $path = null)
    {
        $path = $path ?? self::getStoragePath();

        if(!str_end_with($path, "/"))
        {
            $path .= "/";
        }

        if(file_exists($path))
        {
            foreach(glob($path . "*") as $file)
            {
                if(strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'php' && is_file($file))
                {
                    unlink($file);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Return cache instance object.
     * 
     * @param   string $key
     * @return  mixed
     */
    private static function context(string $key)
    {
        return self::exists($key) ? self::$repository[self::hash($key)] : new self($key, self::getStoragePath());
    }

    /**
     * Statically return cached data or object.
     * 
     * @param   string $name
     * @param   array $arguments
     * @return  mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $key = str_camel_to_kebab($name);
        $obj = self::context($key);
        $val = $arguments[0] ?? null;
        $set = $arguments[1] ?? null;

        if(!is_null($obj))
        {
            if(!is_null($val))
            {
                if($obj->has($val))
                {
                    if(!is_null($set))
                    {
                        $obj->set($val, $set);
                    }
                    else
                    {
                        return $obj->get($val);
                    }
                }
                else
                {
                    return false;
                }
            }

            return $obj;
        }
    }

    /**
     * Return current nest version.
     * 
     * @return  string
     */
    public static function version()
    {
        return self::$version;
    }
}