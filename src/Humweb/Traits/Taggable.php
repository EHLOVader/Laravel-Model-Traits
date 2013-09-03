<?php namespace Humweb\Traits;

/**
 * Taggable Trait
 * 
 * @author  ryun <ryun@humboldtweb.com>
 * 
 */

trait Taggable {

    protected $tags;

    public $opts = [
        'model'      => 'Basic\Modules\Tags\Models\Tag',
        'table_tags' => 'tags',
        'table_rel'  => 'tags_rel',
        'foreignKey' => '',
        'otherKey'   => 'tag_id',
        'seperator'  => '_'
        ];

    public function tags()
    {
        return $this->belongsToMany(
            $this->opts['model'],
            $this->opts['table_rel'],
            $this->opts['foreignKey'],
            $this->opts['otherKey']
        );
    }
  
    public function saveTags(array $tags)
    {
        $ids = [];
        $model = $this->opts('model');

        if (is_array($tags))
        {

            //Clear/Detach tags relationships
            $this->tags()->detach($this->id);

            foreach($tags AS $item)
            {
                $newTags[\Str::slug($item)] = $item;
            }

            $existing = $model::whereIn('slug', array_keys($newTags))->get();


            //Get existing ids and remove them from the new array
            foreach ($existing as $row)
            {
                if (isset($newTags[$row->slug]))
                {
                    unset($newTags[$row->slug]);
                }
                $ids[] = $row->id;
            }

            foreach($newTags AS $slug => $name)
            {
                $ids[] = $model::insertGetId(['name' => $name, 'slug' => $slug]);
            }
        }
        //Sync
        $this->tags()->sync($ids);
    }
   
    public function opts($key=null, $val=null)
    {
        //Return config array
        if ( ! $key)
        {
            return $this->opts;
        }

        //Merge with new array
        elseif (is_array($key))
        {
            foreach ($key as $k => $v)
            {
                $this->opts[$k] = $v;
            }
        }
        //Get single
        elseif ($key and !$val and isset($this->opts[$key]))
        {
            return $this->opts[$key];
        }
        //Set single
        elseif ($key and $val)
        {
            $this->opts[$key] = $val;
        }
    }
  }
