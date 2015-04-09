<?php namespace Humweb\Traits;

/**
 * User Assignable fields trait for Eloquent ORM
 * 
 * @package     Humweb\Traits\Versions
 * @author Ryun Shofner <ryun@humboldtweb.com>
 * @license     MIT License <http://www.opensource.org/licenses/mit>
 * 
 */


trait UserAssignable
{

    /**
     * Flag so we don't run boot more than one time
     * @var boolean
     */
    static $userAssignableBooted = false;

    /**
     * This holds the current user's ID
     * @var integer
     */
    private static $userAssignableId = 0;

    /**
     * Database fields map
     * @var array
     */
    protected static $userAssignableFields = [];

    /**
     * The User model used for relationships
     * @var string
     */
    protected static $userAssignableModel = 'App\User';
    
    /**
     * Method to fetch the current user's ID
     * @return integer user's ID
     */
    protected static function userAssignableCallback()
    {
        if (\Auth::check())
        {
            return \Auth::user()->id;
        }

        return 0;
    }


    /**
     * Fetch the fields map
     * @return array
     */
    protected static function getUserAssignableFields()
    {
        if (empty(static::$userAssignableFields))
        {
            static::$userAssignableFields = [
                'created' => 'created_by',
                'updated' => 'updated_by',
                'deleted' => 'deleted_by',
            ];
        }
        return static::$userAssignableFields;
    }


    /**
     * Check if we have a mapping to a specific action
     * @param  string  $action Action name
     * @return boolean
     */
    public static function isUserAssignable($action = null)
    {
        if ($action === 'deleted')
        {
            return ! empty(static::$userAssignableFields[$action]) and static::softDeleteCheck();
        }
        return ! empty(static::$userAssignableFields[$action]);
    }

    /**
     * Fetch the active user's ID
     * @return int User's ID
     */
    protected static function getUserAssignableId()
    {
        if ( ! is_callable('static::userAssignableCallback'))
        {
            throw new \Exception("userAssingableCallback should be a closure");
        }

        return static::userAssignableCallback();
    }

    /**
     * Set a value on the model
     * @param string $action Action name
     * @param object $model  Model instance
     */
    protected static function setUserAssignableData($action, $model)
    {

        if ( ! static::$userAssignableId)
        {
           static::$userAssignableId = static::getUserAssignableId();
        }

        $field = static::$userAssignableFields[$action];

        if (static::isUserAssignable($action) and ! $model->isDirty($field))
        {
            $model->{$field} = static::$userAssignableId;
            // dd($model->{$field});
            if ($action === 'deleted')
            {
                $model->save();
            }
        }
    }

    /**
     * Setup model's event listeners
     * @return void
     */
    protected static function handleUserAssignableEvents()
    {
        $model = new static;

        static::creating(function ($model) {
            $model->setUserAssignableData('created', $model);
        });

        static::updating(function ($model) {
            $model->setUserAssignableData('updated', $model);
        });

        if ($model->softDeleteCheck())
        {
            static::deleting(function ($model) {
                // In case this is a soft-deletable model
                // @todo Does this issue an UPDATE before the DELETE if not?
                $model->setUserAssignableData('deleted', $model);
            });
        }
    }

    /**
     * Trigger events when we touch
     */
    public function touch()
    {
        $this->setUserAssignableData('created');
        $this->setUserAssignableData('updated');
        $this->setUserAssignableData('deleted');
        return parent::touch();
    }

   
    /**
     * Fetch the model name that we use for relationships
     * @return string User model class
     */
    protected static function getUserAssignableModel()
    {
        return static::$userAssignableModel;
    }

    
    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope Query whereCreatedBy
     * @return object
     */
    public function scopeWherCreatedBy($query, $id)
    {
        return $query->where($this->userAssignableField['created'], $id);
    }


    /**
     * Scope Query whereUpdatedBy
     * @return object
     */
    public function scopeWherUpdatedBy($query, $id)
    {
        return $query->where($this->userAssignableField['updated'], $id);
    }


    /**
     * Scope Query whereDeletedBy
     * You must use the method Model::withTrashed() to get any results
     * @return object
     */
    public function scopeWherDeletedBy($query, $id)
    {
        return $query->where($this->userAssignableField['deleted'], $id);
    }


    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------
    
    /**
     * Relationship to fetch created by
     * @return object
     */
    public function createdBy()
    {
        if ($this->isUserAssignable('created'))
        {
            return $this->belongsTo($this->getUserAssignableModel(), $this->userAssignableField['created']);
        }
        return $this;
    }


    /**
     * Relationship to fetch updated by
     * @return object
     */
    public function updatedBy()
    {
        if ($this->isUserAssignable('updated'))
        {
            return $this->belongsTo($this->getUserAssignableModel(), $this->userAssignableField['updated']);
        }
        return $this;
    }

    /**
     * Relationship to fetch deleted by
     * @return object User instance
     */
    public function deletedBy()
    {
        if ($this->isUserAssignable('deleted'))
        {
            return $this->belongsTo($this->getUserAssignableModel(), $this->userAssignableField['deleted']);
        }
        return $this;
    }

    /**
     * Check if soft deletes are enabled
     * @return bool
     */
    private static function softDeleteCheck()
    {
        $model = new static;
        return isset($model->softDelete) and $model->softDelete === true;
    }

    /**
     * Register events when the model boots
     * @return void
     */
    public static function boot()
    {
        if ( ! static::$userAssignableBooted)
        {
            parent::boot();
            static::$userAssignableBooted = true;
            static::getUserAssignableFields();
            static::handleUserAssignableEvents();
        }
    }
}